<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 p-4">
    {{-- Akun yang exclude_from_total false (ditampilkan pertama) --}}
    <div class="col-span-full">
        <h1 class="text-lg font-bold text-gray-800 dark:text-white mb-2 pb-2 border-b-2 border-green-500 inline-block">
            <span class="bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 px-3 py-1 rounded-full mr-2">
                Termasuk Total
            </span>
        </h1>
    </div>
    @foreach ($records->where('exclude_from_total', false)->sortBy('sort') as $record)
    @php
    $saldoAwal = $record->starting_balance;
    $pengeluaran = $record->transactions->where('tipe_transaksi', 'Pengeluaran')->sum('amount');
    $pemasukan = $record->transactions->where('tipe_transaksi', 'Pemasukan')->sum('amount');
    $saldoAkhir = $saldoAwal + $pemasukan + $pengeluaran;
    @endphp

    <div x-data="{
                showSaldo: localStorage.getItem('showSaldo_{{ $record->id }}') === 'true',
                toggleSaldo() {
                    this.showSaldo = !this.showSaldo;
                    localStorage.setItem('showSaldo_{{ $record->id }}', this.showSaldo);
                }
            }"
        class="bg-white dark:bg-gray-900 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-lg hover:shadow-xl transition-shadow">
        <div class="flex justify-between items-start">
            <div>
                <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Nama Akun</div>
                <div class="text-lg font-semibold text-gray-800 dark:text-white mb-4">
                    {{ $record->name }}
                </div>
            </div>
            <span
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                Termasuk Total
            </span>
        </div>

        <div class="flex items-center justify-between mb-1">
            <div class="text-sm text-gray-500 dark:text-gray-400">Saldo Akhir</div>
            <button @click.stop.prevent="toggleSaldo()"
                class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 focus:outline-none"
                x-on:keydown.escape.stop>
                <svg x-show="!showSaldo" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <svg x-show="showSaldo" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.98 9.98 0 012.093-3.368m1.977-1.846A9.955 9.955 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.977 9.977 0 01-4.112 5.225M15 12a3 3 0 00-3-3m0 0a3 3 0 00-3 3m3-3L3 3" />
                </svg>
            </button>
        </div>

        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
            <span x-show="showSaldo">
                Rp {{ number_format($saldoAkhir, 2, ',', '.') }}
            </span>
            <span x-show="!showSaldo">••••••••</span>
        </div>
    </div>
    @endforeach

    {{-- Akun yang exclude_from_total true (ditampilkan terpisah) --}}
    <div class="col-span-full mt-8">
        <h1 class="text-lg font-bold text-gray-800 dark:text-white mb-2 pb-2 border-b-2 border-yellow-500 inline-block">
            <span
                class="bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 px-3 py-1 rounded-full mr-2">
                Tidak Termasuk Total
            </span>
        </h1>
    </div>
    @foreach ($records->where('exclude_from_total', true)->sortBy('sort') as $record)
    @php
    $saldoAwal = $record->starting_balance;
    $pengeluaran = $record->transactions->where('tipe_transaksi', 'Pengeluaran')->sum('amount');
    $pemasukan = $record->transactions->where('tipe_transaksi', 'Pemasukan')->sum('amount');
    $saldoAkhir = $saldoAwal + $pemasukan + $pengeluaran;
    @endphp

    <div x-data="{
                showSaldo: localStorage.getItem('showSaldo_{{ $record->id }}') === 'true',
                toggleSaldo() {
                    this.showSaldo = !this.showSaldo;
                    localStorage.setItem('showSaldo_{{ $record->id }}', this.showSaldo);
                }
            }"
        class="bg-white dark:bg-gray-900 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-lg hover:shadow-xl transition-shadow">
        <div class="flex justify-between items-start">
            <div>
                <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Nama Akun</div>
                <div class="text-lg font-semibold text-gray-800 dark:text-white mb-4">
                    {{ $record->name }}
                </div>
            </div>
            <span
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                Tidak Termasuk Total
            </span>
        </div>

        <div class="flex items-center justify-between mb-1">
            <div class="text-sm text-gray-500 dark:text-gray-400">Saldo Akhir</div>
            <button @click.stop.prevent="toggleSaldo()"
                class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 focus:outline-none"
                x-on:keydown.escape.stop>
                <svg x-show="!showSaldo" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <svg x-show="showSaldo" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.98 9.98 0 012.093-3.368m1.977-1.846A9.955 9.955 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.977 9.977 0 01-4.112 5.225M15 12a3 3 0 00-3-3m0 0a3 3 0 00-3 3m3-3L3 3" />
                </svg>
            </button>
        </div>

        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
            <span x-show="showSaldo">
                Rp {{ number_format($saldoAkhir, 2, ',', '.') }}
            </span>
            <span x-show="!showSaldo">••••••••</span>
        </div>
    </div>
    @endforeach
</div>