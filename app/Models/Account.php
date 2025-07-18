<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'starting_balance',
        'exclude_from_total',
    ];

    protected $casts = [
        'exclude_from_total' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
