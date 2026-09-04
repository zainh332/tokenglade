<?php

namespace App\Console\Commands;

use App\Services\SitemapService;
use Illuminate\Console\Command;

class ClearSitemapCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge all cached sitemap XML entries to force immediate dynamic re-generation';

    /**
     * Execute the console command.
     */
    public function handle(SitemapService $sitemapService): int
    {
        $this->info('Clearing TokenGlade sitemap cache...');
        $sitemapService->clearCache();
        $this->info('Sitemap cache successfully cleared!');

        return Command::SUCCESS;
    }
}
