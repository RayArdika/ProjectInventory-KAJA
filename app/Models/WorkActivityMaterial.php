<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkActivityMaterial extends Model
{
    protected $fillable = [
        'work_activity_id',
        'material_id',
        'qty_needed',
    ];

    public function workActivity()
    {
        return $this->belongsTo(WorkActivity::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}
