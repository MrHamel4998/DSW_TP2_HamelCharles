<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Equipment;
use App\Models\Review;
use App\Models\User;

class Rental extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'equipment_id',
        'start_date',
        'end_date',
        'total_price'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }
}
