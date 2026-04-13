<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Equipment;

class Sport extends Model
{
    protected $fillable = ['name'];

    public function equipment()
    {
        return $this->belongsToMany(Equipment::class);
    }
}
