<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'end',
        'is_active',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function boot()
    {
        parent::boot();

        // Event model "creating" => otomatis isi user_id
        static::creating(function ($event) {
            if (Auth::check() && empty($event->user_id)) {
                $event->user_id = Auth::id();
            }
        });
    }

     public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

}
