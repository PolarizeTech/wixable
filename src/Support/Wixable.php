<?php

namespace Wixable\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class Wixable
{
    protected ?PendingRequest $wixApi = null;

    protected function wixApi()
    {
        if ($this->wixApi) {
            return $this->wixApi;
        }

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => config('services.wix.api_key'),
            'wix-account-id' => config('services.wix.account_id'),
            'wix-site-id' => config('services.wix.site_id'),
        ];

        return $this->wixApi = Http::withHeaders($headers)->baseUrl('https://www.wixapis.com/wix-data/v2/');
    }

    public function getWixableModels(): Collection
    {
        return collect(File::files(app_path('Models')))
            ->map(fn ($file) => str($file->getRelativePathname())->prepend('App\\Models\\')->before('.php')->replace('/', '\\')->toString())
            ->filter(fn ($modelClass) => is_subclass_of($modelClass, \Wixable\Wixable::class))
            ->values();
    }

    public function queryLatestUpdateTime(string $dataCollectionId): array
    {
        $response = $this->wixApi()->post('items/query-distinct-values', [
            'dataCollectionId' => $dataCollectionId,
            'fieldName' => '_updatedDate',
            'returnTotalCount' => true,
            'order' => 'DESC',
            'consistentRead' => true,
        ])->throw();

        $data = $response->json();

        return [
            'last_update' => $data['distinctValues'][0]['$date'] ?? null,
            'total' => $data['pagingMetadata']['total']
        ];
    }

    public function getDataItem(string $dataItemId): array
    {
        $response = $this->wixApi()->get("items/{$dataItemId}")->throw();
        return $response->json();
    }

    public function queryDataItems(string $dataCollectionId, array $query = []): array
    {
        $response = $this->wixApi()->post('items/query', array_merge([
            'dataCollectionId' => $dataCollectionId,
            'consistentRead' => true,
        ], $query ? compact('query') : []))->throw();

        return $response->json();
    }
}
