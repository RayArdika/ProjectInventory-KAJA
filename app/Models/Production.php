<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Production extends Model
{
    protected $fillable = [

        'production_date',
        'worker_name',
        'activity_name',
        'work_activity_id',
        'category',
        'product_name',
        'qty_pass',
        'qty_reject',
        'status',
        'note',

    ];

    public function workActivity()
    {
        return $this->belongsTo(WorkActivity::class);
    }
}
