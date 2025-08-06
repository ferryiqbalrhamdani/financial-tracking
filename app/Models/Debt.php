<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Debt extends Model
{
    protected $fillable = ['user_id', 'name', 'type', 'amount', 'description', 'start_date', 'due_date', 'account_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
