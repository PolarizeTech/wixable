<?php

namespace Wixable;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wixable extends Model
{
    use SoftDeletes, HasWixDataItems;

    protected string $wixDataCollection = '';

    public function getTable(): string
    {
        return 'wixable_data_items';
    }

    public function getWixDataCollection(): string
    {
        $dataCollectionId = ! empty(trim($this->wixDataCollection))
            ? $this->wixDataCollection
            : class_basename(get_called_class());

        return str($dataCollectionId)->plural()->studly()->toString();
    }

    public function wix()
    {
        return app('wixable');
    }

    public function data(): Attribute
    {
        return new Attribute(

            get: fn (): ?array => json_decode($this->wix_data, true),

            set: function (string|array $value): void {
                $this->wix_data = json_encode($value);
            }

        );
    }
}
