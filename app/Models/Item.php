<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'status'];

    protected $casts = [
        'status' => 'string',
    ];

    public function scopeAllowed(Builder $query)
    {
        return $query->where('status', 'Allowed');
    }

    public function scopeProhibited(Builder $query)
    {
        return $query->where('status', 'Prohibited');
    }
}
