<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $fillable = [

        'material_name',
        'category',
        'unit',
        'stock',
        'minimum_stock',
        'price_per_unit',
        'selling_price'

    ];

    public function workActivityRequirements()
    {
        return $this->hasMany(WorkActivityMaterial::class);
    }
}
