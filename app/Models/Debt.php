<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Debt extends Model
{
    protected $fillable = ['user_id', 'name', 'type', 'amount', 'description', 'start_date', 'due_date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payments()
    {
        return $this->hasMany(Transaction::class)->whereNotNull('debt_id');
    }
}
