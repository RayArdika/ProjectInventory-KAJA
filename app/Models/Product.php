<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [

        'sku',
        'product_name',
        'category',
        'unit',
        'stock',
        'worker_fee',
        'minimum_stock',
        'warning_stock',
        'price_per_unit',
        'selling_price',

    ];

    public function workActivities()
    {
        return $this->hasMany(WorkActivity::class);
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (! $product->sku) {
                $product->sku = static::nextSku();
            }
        });
    }

    public static function nextSku(): string
    {
        $activeSkus = static::query()->pluck('sku');
        $historicalSkus = InventoryLog::query()
            ->where('item_type', 'product')
            ->pluck('sku');

        $highestNumber = $activeSkus
            ->merge($historicalSkus)
            ->filter(fn ($sku) => preg_match('/^\d{3,}$/', (string) $sku))
            ->map(fn ($sku) => (int) $sku)
            ->max() ?? 0;

        return str_pad((string) ($highestNumber + 1), 3, '0', STR_PAD_LEFT);
    }
}
