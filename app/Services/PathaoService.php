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
        $this->baseUrl = config('services.pathao.base_url');
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
        return Cache::remember('pathao_access_token', 86400, function () { // Cache for 24h, though token is usually longer
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
        });
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
            // 2. Retrieve valid Access Token
            $token = $this->getAccessToken();

            // 3. Prepare payload mapped to Pathao's expected schema
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

            // 4. Send API Request
            $response = Http::withToken($token)
                ->withHeaders(['Accept' => 'application/json'])
                ->post("{$this->baseUrl}/aladdin/api/v1/orders", $payload);
            
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
        return Cache::remember('pathao_cities', 86400, function () {
            try {
                $response = Http::withToken($this->getAccessToken())
                    ->get("{$this->baseUrl}/aladdin/api/v1/countries/1/city-list");
                
                if ($response->successful()) {
                    return $response->json('data.data') ?? [];
                }
            } catch (Exception $e) {
                Log::error("Failed to fetch Pathao cities: " . $e->getMessage());
            }

            // Mock Data Fallback for Development/Testing
            return [
                ['city_id' => 1, 'city_name' => 'Kathmandu'],
                ['city_id' => 2, 'city_name' => 'Lalitpur'],
                ['city_id' => 3, 'city_name' => 'Bhaktapur'],
            ];
        });
    }

    public function getZones($cityId)
    {
        return Cache::remember("pathao_zones_{$cityId}", 86400, function () use ($cityId) {
            try {
                $response = Http::withToken($this->getAccessToken())
                    ->get("{$this->baseUrl}/aladdin/api/v1/cities/{$cityId}/zone-list");
                
                if ($response->successful()) {
                    return $response->json('data.data') ?? [];
                }
            } catch (Exception $e) {
                Log::error("Failed to fetch Pathao zones for city {$cityId}: " . $e->getMessage());
            }

            // Mock Data Fallback for Development/Testing
            if ($cityId == 1) return [['zone_id' => 101, 'zone_name' => 'Baneshwor'], ['zone_id' => 102, 'zone_name' => 'Koteshwor']];
            if ($cityId == 2) return [['zone_id' => 201, 'zone_name' => 'Patan'], ['zone_id' => 202, 'zone_name' => 'Jawalakhel']];
            if ($cityId == 3) return [['zone_id' => 301, 'zone_name' => 'Suryabinayak']];
            return [['zone_id' => 999, 'zone_name' => 'Default Zone']];
        });
    }

    public function getAreas($zoneId)
    {
        return Cache::remember("pathao_areas_{$zoneId}", 86400, function () use ($zoneId) {
            try {
                $response = Http::withToken($this->getAccessToken())
                    ->get("{$this->baseUrl}/aladdin/api/v1/zones/{$zoneId}/area-list");
                
                if ($response->successful()) {
                    return $response->json('data.data') ?? [];
                }
            } catch (Exception $e) {
                Log::error("Failed to fetch Pathao areas for zone {$zoneId}: " . $e->getMessage());
            }

            // Mock Data Fallback for Development/Testing
            if ($zoneId == 101) return [['area_id' => 1001, 'area_name' => 'New Baneshwor'], ['area_id' => 1002, 'area_name' => 'Old Baneshwor']];
            if ($zoneId == 102) return [['area_id' => 1003, 'area_name' => 'Balkumari']];
            if ($zoneId == 201) return [['area_id' => 2001, 'area_name' => 'Mangal Bazar']];
            return [['area_id' => 9999, 'area_name' => 'Default Area']];
        });
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
            $response = Http::withToken($this->getAccessToken())
                ->get("{$this->baseUrl}/aladdin/api/v1/orders/{$consignmentId}");
            
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

