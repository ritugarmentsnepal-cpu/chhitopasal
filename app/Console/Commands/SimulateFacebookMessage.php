<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;

class SimulateFacebookMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'facebook:simulate-message {message} {--page=} {--sender=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulate an incoming Facebook Messenger webhook event for local testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $messageText = $this->argument('message');
        
        // Find a connected page to use or fallback
        $page = \App\Models\FacebookPage::first();
        $pageId = $this->option('page') ?: ($page ? $page->page_id : 'test_page_123');
        $senderId = $this->option('sender') ?: 'test_sender_123';

        if (!$page) {
            $this->warn('No Facebook Pages connected in DB. Using fallback test page ID: ' . $pageId);
        }

        $this->info("Simulating message from {$senderId} to page {$pageId}: '{$messageText}'");

        $payload = [
            'object' => 'page',
            'entry' => [
                [
                    'id' => $pageId,
                    'time' => now()->timestamp * 1000,
                    'messaging' => [
                        [
                            'sender' => ['id' => $senderId],
                            'recipient' => ['id' => $pageId],
                            'timestamp' => now()->timestamp * 1000,
                            'message' => [
                                'mid' => 'm_' . uniqid(),
                                'text' => $messageText,
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // Instantiate the webhook controller and call handle directly
        $request = Request::create('/webhook/facebook', 'POST', $payload);
        
        $controller = app(\App\Http\Controllers\Api\FacebookWebhookController::class);
        $response = $controller->handle($request);

        $this->info('Webhook handler response status: ' . $response->getStatusCode());
        
        $this->info('Wait a moment... processing queue jobs.');
        
        // Process the queued job immediately so we don't need a separate worker running for local testing
        \Illuminate\Support\Facades\Artisan::call('queue:work', ['--once' => true]);
        
        $this->info('Done! Check the Facebook Inbox UI to see if the AI replied.');
        return 0;
    }
}
