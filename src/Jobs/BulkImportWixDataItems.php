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

    protected ?string $cursor;

    public function __construct(string $model, ?string $cursor = null)
    {
        $this->model = $model;
        $this->cursor = $cursor;
    }

    public function handle()
    {
        $model = $this->model;
        $dataCollectionId = (new $model)->getWixDataCollection();

        $results = app('wixable')->queryDataItems(
            dataCollectionId: $dataCollectionId,
            query: ! $this->cursor ? [] : [
                'cursorPaging' => ['cursor' => $this->cursor]
            ]
        );

        collect($results['dataItems'])->each(fn ($dataItem) =>
            SyncWixDataItem::dispatch($this->model, $dataItem)
        );

        if ($results['pagingMetadata']['hasNext'] === true) {
            self::dispatch($this->model, $results['pagingMetadata']['cursors']['next']);
        }
    }

    private function shouldSync(): bool
    {
        $model = $this->model;
        $dataCollectionId = (new $model)->getWixDataCollection();

        $status = app('wixable')->queryLatestUpdateTime($dataCollectionId);
        $outOfSync = $status['last_update'] !== null && (
            Carbon::parse($status['last_update'])->timestamp > Carbon::parse($model::max('updated_at'))->timestamp ||
            $status['total'] !== $model::count()
        );

        return ! $this->cursor && ! $outOfSync;
    }
}
