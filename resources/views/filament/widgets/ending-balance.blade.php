@php
    $accountsInclude = $this->getAccounts()->where('exclude_from_total', false);
    $startingBalance = $accountsInclude->sum('starting_balance');
    $income = $accountsInclude
        ->flatMap(fn($account) => $account->transactions)
        ->where('tipe_transaksi', 'Pemasukan')
        ->sum('amount');
    $expense = $accountsInclude
        ->flatMap(fn($account) => $account->transactions)
        ->where('tipe_transaksi', 'Pengeluaran')
        ->sum('amount');
    $total = $startingBalance + ($income + $expense);

    $accountsExclude = $this->getAccounts()->where('exclude_from_total', true);
    $startingBalanceExclude = $accountsExclude->sum('starting_balance');
    $incomeExclude = $accountsExclude
        ->flatMap(fn($account) => $account->transactions)
        ->where('tipe_transaksi', 'Pemasukan')
        ->sum('amount');
    $expenseExclude = $accountsExclude
        ->flatMap(fn($account) => $account->transactions)
        ->where('tipe_transaksi', 'Pengeluaran')
        ->sum('amount');
    $totalExclude = $startingBalanceExclude + ($incomeExclude + $expenseExclude);
@endphp

<x-filament-widgets::widget>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Card 1 -->
        <x-filament::card>
            <div
                x-data="{
                    showSaldo: JSON.parse(localStorage.getItem('showSaldoGlobal') ?? 'true'),
                    toggle() {
                        this.showSaldo = !this.showSaldo;
                        localStorage.setItem('showSaldoGlobal', JSON.stringify(this.showSaldo));
                    }
                }"
            >
                <div class="flex justify-between items-start mb-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Saldo Akhir</div>
                    <button @click="toggle"
                            class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 focus:outline-none"
                            title="Lihat atau Sembunyikan Saldo">
                        <svg x-show="!showSaldo" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="showSaldo" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.98 9.98 0 012.093-3.368m1.977-1.846A9.955 9.955 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.977 9.977 0 01-4.112 5.225M15 12a3 3 0 00-3-3m0 0a3 3 0 00-3 3m3-3L3 3" />
                        </svg>
                    </button>
                </div>
                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                    <span x-show="showSaldo" x-cloak>Rp {{ number_format($total, 2, ',', '.') }}</span>
                    <span x-show="!showSaldo" x-cloak>••••••••</span>
                </div>
            </div>
        </x-filament::card>

        <!-- Card 2 -->
        <x-filament::card>
            <div
                x-data="{
                    showSaldo: JSON.parse(localStorage.getItem('showSaldoExclude') ?? 'true'),
                    toggle() {
                        this.showSaldo = !this.showSaldo;
                        localStorage.setItem('showSaldoExclude', JSON.stringify(this.showSaldo));
                    }
                }"
            >
                <div class="flex justify-between items-start mb-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Saldo Akhir (Tidak Termasuk Total)</div>
                    <button @click="toggle"
                            class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 focus:outline-none"
                            title="Lihat atau Sembunyikan Saldo">
                        <svg x-show="!showSaldo" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="showSaldo" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.98 9.98 0 012.093-3.368m1.977-1.846A9.955 9.955 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.977 9.977 0 01-4.112 5.225M15 12a3 3 0 00-3-3m0 0a3 3 0 00-3 3m3-3L3 3" />
                        </svg>
                    </button>
                </div>
                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                    <span x-show="showSaldo" x-cloak>Rp {{ number_format($totalExclude, 2, ',', '.') }}</span>
                    <span x-show="!showSaldo" x-cloak>••••••••</span>
                </div>
            </div>
        </x-filament::card>
    </div>
</x-filament-widgets::widget>
