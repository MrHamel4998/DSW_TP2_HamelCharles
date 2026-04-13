<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Equipment;

class Category extends Model
{
    public $timestamps = false;

    protected $fillable = ['name'];

    public function equipment()
    {
        return $this->hasMany(Equipment::class);
    }
}
