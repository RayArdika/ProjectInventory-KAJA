<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    protected $fillable = [

        'production_id',

        'worker_name',

        'product_name',

        'activity_name',

        'qty',

        'unit',

        'fee',

        'total_salary',

    ];

    public function production()
    {
        return $this->belongsTo(Production::class);
    }
}
