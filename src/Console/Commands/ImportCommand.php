<?php

namespace Wixable\Console\Commands;

use Illuminate\Console\Command;
use Wixable\Facades\Wixable;
use Wixable\Jobs\BulkImportWixDataItems;

class ImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wixable:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports data items from Wix for all of your Wixable models';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $wixables = app('wixable')->getWixableModels()->each(fn ($model) =>
            BulkImportWixDataItems::dispatch($model)
        );

        $this->comment("Queued imports for {$wixables->count()} Wixable models.");
    }
}
