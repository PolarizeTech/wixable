<?php

namespace Wixable\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BulkImportWixDataItems implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $model;

    public function __construct(string $model)
    {
        $this->model = $model;
    }

    public function handle()
    {
        $model = $this->model;
        $dataCollectionId = (new $model)->getWixDataCollection();

        $status = app('wixable')->queryLatestUpdateTime($dataCollectionId);
        $outOfSync = $status['last_update'] !== null && (
            Carbon::parse($status['last_update'])->timestamp > Carbon::parse($model::max('updated_at'))->timestamp ||
            $status['total'] !== $model::count()
        );

        if (! $outOfSync) {
            return;
        }

        // do {
            $results = app('wixable')->queryDataItems(
                dataCollectionId: $dataCollectionId
            );

            collect($results['dataItems'])->each(fn ($dataItem) =>
                SyncWixDataItem::dispatch($this->model, $dataItem)
            );
        // } while ($results['pagingMetadata']['hasNext'] === true);

    }
}
