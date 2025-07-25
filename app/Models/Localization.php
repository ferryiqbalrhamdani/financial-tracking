<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class Localization extends Model
{
    protected $fillable = [
        'user_id',
        'locale',
        'currency',
        'timezone',
        'date_format',
        'monthly_period_start_day',
        'monthly_period_start_month',
    ];

    protected $casts = [
        'monthly_period_start_day' => 'integer',
        'monthly_period_start_month' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function getCurrencySymbol(): string
    {
        // Contoh sederhana: bisa dari database user preference
        $user = Auth::user();
        return match ($user?->currency ?? 'IDR') {
            'USD' => '$',
            'IDR' => 'Rp',
            default => 'Rp',
        };
    }
}
