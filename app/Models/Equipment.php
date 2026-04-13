<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Category;
use App\Models\Sport;
use App\Models\Rental;

class Equipment extends Model
{
    protected $fillable = [
        'name',
        'description',
        'daily_price',
        'category_id'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function sports()
    {
        return $this->belongsToMany(Sport::class);
    }

    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }
}
