<?php

namespace App\Filament\Widgets;

use App\Models\Account;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class EndingBalance extends Widget
{
    protected int | string | array $columnSpan = 'full';
    protected static string $view = 'filament.widgets.ending-balance';
    protected static ?int $sort = 2;

    public function getAccounts()
    {
        return Account::where('user_id', Auth::user()->id)->with('transactions')->orderBy('sort')->get();
    }
}
