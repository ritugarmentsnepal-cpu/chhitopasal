<?php

namespace Tests\Feature;

use App\Models\CustomerLogo;
use App\Models\Mockup;
use App\Models\MockupTemplate;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * PHASE-2.1: Customer Logo Library.
 */
class CustomerLogoTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    // 1x1 transparent PNG
    protected const TINY_PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
    }

    protected function fakeAiImage(): void
    {
        Http::fake([
            'openrouter.ai/*' => Http::response([
                'choices' => [[
                    'message' => [
                        'images' => [[
                            'type' => 'image_url',
                            'image_url' => ['url' => 'data:image/png;base64,' . self::TINY_PNG],
                        ]],
                    ],
                ]],
            ]),
        ]);
    }

    protected function makeTemplate(): MockupTemplate
    {
        Storage::disk('public')->put('mockup_templates/tpl.png', base64_decode(self::TINY_PNG));

        return MockupTemplate::create([
            'name' => 'Test Pouch',
            'product_type' => 'drawstring_pouch',
            'image_path' => 'mockup_templates/tpl.png',
        ]);
    }

    public function test_manual_upload_creates_library_record(): void
    {
        $response = $this->actingAs($this->admin)->post('/customer-logos', [
            'name' => 'AAVA Jewellers',
            'customer_name' => 'Aava',
            'customer_phone' => '9811111111',
            'logo' => UploadedFile::fake()->image('aava.png'),
        ]);

        $response->assertSessionHas('success');
        $logo = CustomerLogo::first();
        $this->assertEquals('AAVA Jewellers', $logo->name);
        $this->assertEquals('9811111111', $logo->customer_phone);
        $this->assertTrue(Storage::disk('public')->exists($logo->file_path));
    }

    public function test_generate_with_library_logo_uses_its_file(): void
    {
        // Settings cache may be primed; API key comes from env fallback
        putenv('OPENROUTER_API_KEY=test-key');
        $this->fakeAiImage();

        Storage::disk('public')->put('mockup_logos/lib.png', base64_decode(self::TINY_PNG));
        $logo = CustomerLogo::create(['name' => 'Lib Logo', 'file_path' => 'mockup_logos/lib.png']);
        $template = $this->makeTemplate();

        $response = $this->actingAs($this->admin)->postJson('/mockups/generate', [
            'template_id' => $template->id,
            'customer_logo_id' => $logo->id,
        ]);

        $response->assertSuccessful();
        $this->assertEquals($logo->id, $response->json('customer_logo_id'));
        $this->assertEquals('mockup_logos/lib.png', $response->json('logo_path'));
        putenv('OPENROUTER_API_KEY');
    }

    public function test_generate_with_upload_auto_creates_library_record(): void
    {
        putenv('OPENROUTER_API_KEY=test-key');
        $this->fakeAiImage();
        $template = $this->makeTemplate();

        $response = $this->actingAs($this->admin)->postJson('/mockups/generate', [
            'template_id' => $template->id,
            'logo' => UploadedFile::fake()->image('ram-shop-logo.png'),
        ]);

        $response->assertSuccessful();
        $this->assertNotNull($response->json('customer_logo_id'));
        $this->assertEquals('ram-shop-logo', CustomerLogo::find($response->json('customer_logo_id'))->name);
        putenv('OPENROUTER_API_KEY');
    }

    public function test_saving_order_linked_mockup_enriches_logo_customer(): void
    {
        Storage::disk('public')->put('mockups/ai_gen1.png', base64_decode(self::TINY_PNG));
        Storage::disk('public')->put('mockup_logos/anon.png', base64_decode(self::TINY_PNG));

        $logo = CustomerLogo::create(['name' => 'Untitled logo', 'file_path' => 'mockup_logos/anon.png']);
        $order = Order::factory()->create(['customer_name' => 'Sita Sharma', 'customer_phone' => '9822222222']);

        $this->actingAs($this->admin)->postJson('/mockups/save-generated', [
            'title' => 'Sita Mockup',
            'path' => 'mockups/ai_gen1.png',
            'logo_path' => 'mockup_logos/anon.png',
            'customer_logo_id' => $logo->id,
            'order_id' => $order->id,
        ])->assertSuccessful();

        $logo->refresh();
        $this->assertEquals('Sita Sharma', $logo->customer_name);
        $this->assertEquals('9822222222', $logo->customer_phone);
        $this->assertEquals('Sita Sharma logo', $logo->name);
        $this->assertEquals($logo->id, Mockup::first()->customer_logo_id);
    }

    public function test_deleting_used_logo_keeps_the_file(): void
    {
        Storage::disk('public')->put('mockup_logos/used.png', base64_decode(self::TINY_PNG));
        $logo = CustomerLogo::create(['name' => 'Used', 'file_path' => 'mockup_logos/used.png']);
        Mockup::create([
            'title' => 'Uses it',
            'image_path' => 'mockups/x.png',
            'logo_path' => 'mockup_logos/used.png',
            'customer_logo_id' => $logo->id,
            'tags' => [],
        ]);

        $this->actingAs($this->admin)->delete("/customer-logos/{$logo->id}");

        $this->assertDatabaseMissing('customer_logos', ['id' => $logo->id]);
        $this->assertTrue(Storage::disk('public')->exists('mockup_logos/used.png'));
        // mockup keeps working via its own logo_path; FK nulled
        $this->assertNull(Mockup::first()->customer_logo_id);
    }
}
