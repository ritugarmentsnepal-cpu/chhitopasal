<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class PathaoService
{
    private string $baseUrl;
    private string $clientId;
    private string $clientSecret;
    private string $username;
    private string $password;
    private string $storeId;

    public function __construct()
    {
        $this->baseUrl = setting('pathao_base_url', config('services.pathao.base_url'));
        $this->clientId = setting('pathao_client_id', config('services.pathao.client_id'));
        $this->clientSecret = setting('pathao_client_secret', config('services.pathao.client_secret'));
        $this->username = setting('pathao_username', config('services.pathao.username'));
        $this->password = setting('pathao_password', config('services.pathao.password'));
        $this->storeId = setting('pathao_store_id', config('services.pathao.store_id'));
    }

    /**
     * Get a valid OAuth Access Token, utilizing Cache to avoid frequent requests.
     */
    private function getAccessToken(): string
    {
        return Cache::remember('pathao_access_token', 43200, function () { // Cache for 12h
            return $this->requestNewToken();
        });
    }

    /**
     * INT-02: Request a fresh token and cache it.
     */
    private function requestNewToken(): string
    {
        $response = Http::post("{$this->baseUrl}/aladdin/api/v1/issue-token", [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'username' => $this->username,
            'password' => $this->password,
            'grant_type' => 'password',
        ]);

        if ($response->failed()) {
            Log::error('Pathao Authentication Failed', ['response' => $response->body()]);
            throw new Exception("Could not authenticate with Pathao API.");
        }

        return $response->json('access_token');
    }

    /**
     * INT-02: Make an authenticated request with automatic token refresh on 401.
     */
    private function authenticatedRequest(string $method, string $url, array $data = [], array $headers = [])
    {
        $token = $this->getAccessToken();
        $request = Http::withToken($token);
        if (!empty($headers)) {
            $request = $request->withHeaders($headers);
        }
        $response = $request->{$method}($url, $data);

        if ($response->status() === 401) {
            // Token expired — refresh and retry once
            Cache::forget('pathao_access_token');
            $token = $this->getAccessToken();
            $request = Http::withToken($token);
            if (!empty($headers)) {
                $request = $request->withHeaders($headers);
            }
            $response = $request->{$method}($url, $data);
        }

        return $response;
    }

    /**
     * Create a new parcel order in Pathao.
     * 
     * @param Order $order
     * @return string|null The Pathao Consignment ID or null on failure.
     */
    public function createOrder(Order $order): array
    {
        // 1. Calculate total weight in kg (minimum 0.5kg as per Pathao standard)
        $totalWeightGrams = 0;
        foreach ($order->orderItems as $item) {
            $totalWeightGrams += ($item->product->weight_grams ?? 500) * $item->quantity;
        }
        $weightKg = max(0.5, $totalWeightGrams / 1000);

        try {
            // 2. Prepare payload mapped to Pathao's expected schema
            $payload = [
                'store_id' => $this->storeId,
                'merchant_order_id' => (string) $order->id,
                'sender_name' => setting('company_name', 'Chhito Pasal'),
                'sender_phone' => setting('company_phone', '9800000000'),
                'recipient_name' => $order->customer_name,
                'recipient_phone' => $order->customer_phone,
                'recipient_address' => $order->address . ($order->city ? ', ' . $order->city : ''),
                'recipient_city' => $order->pathao_city_id,
                'recipient_zone' => $order->pathao_zone_id,
                'delivery_type' => 48, // 48 = Normal Delivery
                'item_type' => 2,      // 2 = Parcel
                'special_instruction' => 'Handle with care.',
                'item_quantity' => $order->orderItems->sum('quantity'),
                'item_weight' => $weightKg,
                'amount_to_collect' => (int) round($order->total_amount - ($order->paid_amount ?? 0)),
                'item_description' => $order->orderItems->map(fn($item) => $item->quantity . 'x ' . ($item->product->name ?? 'Product'))->join(', '),
            ];

            Log::info("Sending order to Pathao API", ['payload' => $payload]);

            // 3. NEW-SEC-02: Send via authenticatedRequest for automatic token refresh on 401
            $response = $this->authenticatedRequest(
                'post',
                "{$this->baseUrl}/aladdin/api/v1/orders",
                $payload,
                ['Accept' => 'application/json']
            );
            
            if ($response->successful()) {
                $consignmentId = $response->json('data.consignment_id');
                Log::info("Pathao Order Created Successfully", ['order_id' => $order->id, 'consignment_id' => $consignmentId]);
                return ['success' => true, 'consignment_id' => $consignmentId];
            }

            // Extract human-readable error from Pathao response
            $errorBody = $response->json();
            $errorMsg = $errorBody['message'] ?? 'Unknown error';
            if (isset($errorBody['errors']) && is_array($errorBody['errors'])) {
                $fieldErrors = [];
                foreach ($errorBody['errors'] as $field => $messages) {
                    $fieldErrors[] = $field . ': ' . (is_array($messages) ? implode(', ', $messages) : $messages);
                }
                $errorMsg = implode(' | ', $fieldErrors);
            }

            Log::error("Pathao Order Creation Failed", [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return ['success' => false, 'error' => $errorMsg];

        } catch (Exception $e) {
            Log::error("Pathao Service Exception: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getCities()
    {
        $cities = Cache::get('pathao_cities');
        if (!empty($cities)) {
            return $cities;
        }

        try {
            $response = $this->authenticatedRequest('get', "{$this->baseUrl}/aladdin/api/v1/countries/1/city-list");
            
            if ($response->successful()) {
                $data = $response->json('data.data') ?? [];
                if (!empty($data)) {
                    Cache::put('pathao_cities', $data, 86400);
                }
                return $data;
            }
        } catch (Exception $e) {
            Log::error("Failed to fetch Pathao cities: " . $e->getMessage());
        }

        return [];
    }

    public function getZones($cityId)
    {
        $cacheKey = "pathao_zones_{$cityId}";
        $zones = Cache::get($cacheKey);
        if (!empty($zones)) {
            return $zones;
        }

        try {
            $response = $this->authenticatedRequest('get', "{$this->baseUrl}/aladdin/api/v1/cities/{$cityId}/zone-list");
            
            if ($response->successful()) {
                $data = $response->json('data.data') ?? [];
                if (!empty($data)) {
                    Cache::put($cacheKey, $data, 86400);
                }
                return $data;
            }
        } catch (Exception $e) {
            Log::error("Failed to fetch Pathao zones for city {$cityId}: " . $e->getMessage());
        }

        return [];
    }

    public function getAreas($zoneId)
    {
        $cacheKey = "pathao_areas_{$zoneId}";
        $areas = Cache::get($cacheKey);
        if (!empty($areas)) {
            return $areas;
        }

        try {
            $response = $this->authenticatedRequest('get', "{$this->baseUrl}/aladdin/api/v1/zones/{$zoneId}/area-list");
            
            if ($response->successful()) {
                $data = $response->json('data.data') ?? [];
                if (!empty($data)) {
                    Cache::put($cacheKey, $data, 86400);
                }
                return $data;
            }
        } catch (Exception $e) {
            Log::error("Failed to fetch Pathao areas for zone {$zoneId}: " . $e->getMessage());
        }

        return [];
    }

    public function getOrderStatus($consignmentId)
    {
        $details = $this->getOrderDetails($consignmentId);
        return $details['order_status'] ?? null;
    }

    /**
     * Get full order details from Pathao API.
     * Returns all available fields: order_status, invoice, pickup info, delivery info, etc.
     */
    public function getOrderDetails($consignmentId): ?array
    {
        try {
            // NEW-SEC-01: Use authenticatedRequest for automatic token refresh on 401
            $response = $this->authenticatedRequest('get', "{$this->baseUrl}/aladdin/api/v1/orders/{$consignmentId}");
            
            if ($response->successful()) {
                return $response->json('data') ?? null;
            }
            Log::error("Failed to fetch Pathao details for {$consignmentId}", ['response' => $response->body()]);
        } catch (Exception $e) {
            Log::error("Failed to fetch Pathao details for {$consignmentId}: " . $e->getMessage());
        }
        return null;
    }
}

