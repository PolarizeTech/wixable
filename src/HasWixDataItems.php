<?php

namespace Wixable;

use Illuminate\Database\Eloquent\Builder;

trait HasWixDataItems
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public static function bootHasWixDataItems(): void
    {
        static::addGlobalScope('wix_data_items', function (Builder $builder) {
            $builder->where('wix_data_collection', (new static)->getWixDataCollection());
        });
    }
}
