<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryLog extends Model
{
    protected $fillable = [
        'inventory_date',
        'user_name',
        'action',
        'item_type',
        'sku',
        'item_name',
        'category',
        'unit',
        'stock',
        'note',
    ];

}
