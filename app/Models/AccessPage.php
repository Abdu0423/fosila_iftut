<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessPage extends Model
{
    protected $fillable = ['key', 'title', 'sort'];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_page');
    }
}
