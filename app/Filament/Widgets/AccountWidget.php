<?php

namespace App\Filament\Widgets;

use App\Models\Account;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class AccountWidget extends Widget
{
    protected int | string | array $columnSpan = 'full';

    protected static string $view = 'filament.widgets.account-widget';

    protected static ?int $sort = 1;

    public function getAccounts()
    {
        return Account::where('user_id', Auth::user()->id)->with('transactions')->orderBy('sort')->get();
    }
}
