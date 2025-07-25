<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    protected $fillable = ['user_id', 'name', 'amount', 'start_date', 'due_date', 'term', 'description'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payments()
    {
        return $this->hasMany(Transaction::class)->whereNotNull('bill_id');
    }
}
