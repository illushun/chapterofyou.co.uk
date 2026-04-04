<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class PingSitemapCommand extends Command
{
    protected $signature   = 'sitemap:ping';
    protected $description = 'Notify Google and Bing that the sitemap has been updated';

    public function handle(): int
    {
        $sitemapUrl = urlencode(config('app.url') . '/sitemap.xml');

        $engines = [
            'Google' => "https://www.google.com/ping?sitemap={$sitemapUrl}",
            'Bing'   => "https://www.bing.com/indexnow?url={$sitemapUrl}&key=1",
        ];

        foreach ($engines as $name => $pingUrl) {
            try {
                $response = Http::timeout(10)->get($pingUrl);

                if ($response->successful()) {
                    $this->info("✓ {$name} pinged successfully.");
                } else {
                    $this->warn("⚠ {$name} returned status {$response->status()}.");
                }
            } catch (\Exception $e) {
                $this->error("✗ {$name} ping failed: {$e->getMessage()}");
            }
        }

        $this->info('Done. It may take a few days for search engines to re-crawl.');

        return Command::SUCCESS;
    }
}
