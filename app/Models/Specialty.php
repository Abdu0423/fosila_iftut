<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Specialty extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'short_name',
        'code',
        'description',
        'duration_years',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function groups()
    {
        return $this->hasMany(Group::class);
    }
}
