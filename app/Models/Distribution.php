<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Distribution extends Model
{
    protected $fillable = [

        'invoice_number',

        'distribution_date',

        'product_name',

        'qty_out',

        'price',

        'shipping_cost',

        'tax',

        'total_amount',

        'destination',

        'client_phone',

        'note',

        'payment_status',

        'payment_method',

        'paid_amount',

    ];

    public function items(): HasMany
    {
        return $this->hasMany(DistributionItem::class);
    }
}
