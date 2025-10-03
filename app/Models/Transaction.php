<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'account_id',
        'tipe_transaksi',
        'amount',
        'description',
        'date',
        'is_transfer',
        'debt_id',
        'bill_id',
        'subscription_id',
        'ex_report',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function debt()
    {
        return $this->belongsTo(Debt::class);
    }
    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}
