<?php

namespace Wixable\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncWixDataItem implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $model;

    protected array $dataItem;

    public function __construct(string $model, array $dataItem)
    {
        $this->model = $model;
        $this->dataItem = $dataItem;
    }

    public function handle(): void
    {
        $model = $this->model;
        $dataCollectionId = (new $model)->getWixDataCollection();

        $wixable = $model::firstWhere([
            'wix_data_collection' => $dataCollectionId,
            'wix_data_item' => $this->dataItem['id']
        ]);

        if (! $wixable) {
            $wixable = new $model;
        }

        $wixable->forceFill([
            'wix_data_collection' => $dataCollectionId,
            'wix_data_item' => $this->dataItem['id'],
            'wix_data' => json_encode($this->dataItem['data']),
        ])->save();
    }
}
