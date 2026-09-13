<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkActivity extends Model
{
    protected $fillable = [
        'product_id',
        'activity_name',
        'unit',
        'fee_per_unit',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'fee_per_unit' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function materialRequirements()
    {
        return $this->hasMany(WorkActivityMaterial::class);
    }
}
