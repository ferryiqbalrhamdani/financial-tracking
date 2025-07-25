<x-filament-panels::page>
    {{-- <p>{{ dd($record) }}</p> --}}
    {{-- {{ $record}}
    <br> --}}
    {{-- {{ $record->sum('amount') }}
    {{ $record->category->transactions->sum('amount') }} --}}
    {{-- @foreach ($record as $budget)
    <div class="mb-3 p-4 border rounded">
        <h3 class="font-bold">{{ $budget->category->name }}</h3>
        <p>Anggaran: Rp {{ number_format($budget->amount, 0, ',', '.') }}</p>

        @php
        $totalTransaksi = $budget->category->transactions->sum('amount');
        @endphp

        <p>Total Pengeluaran: Rp {{ number_format($totalTransaksi, 0, ',', '.') }}</p>
    </div>
    @endforeach --}}



    {{ $this->table }}
</x-filament-panels::page>