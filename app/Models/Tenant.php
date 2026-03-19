<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'plan', 'status', 'trial_ends_at', 'subscribed_at',
    ];

    protected $casts = [
        'trial_ends_at'  => 'datetime',
        'subscribed_at'  => 'datetime',
    ];

    public function isSubscribed(): bool
    {
        return $this->status === 'active' || 
               ($this->trial_ends_at && $this->trial_ends_at->isFuture());
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
