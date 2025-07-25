<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = ['user_id', 'name', 'amount', 'cycle', 'start_date', 'next_payment_date', 'description', 'is_paid'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payments()
    {
        return $this->hasMany(Transaction::class)->whereNotNull('subscription_id');
    }
}
