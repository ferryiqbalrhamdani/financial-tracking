<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Tables\Table;
use App\Models\Localization;
use Livewire\Attributes\Locked;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\Alignment;
use App\Models\Budget as ModelsBudget;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\Summarizers\Sum;
use Illuminate\Database\Eloquent\Collection;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Filament\Clusters\Settings\Resources\BudgetResource;

class Budget extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';

    protected static string $view = 'filament.pages.budget';

    protected static ?int $navigationSort = 3;

    public ?int $budgetId = null;
    public Collection $record;

    public function mount(): void
    {
        $this->record = ModelsBudget::with('category')->get();
    }


    public function table(Table $table): Table
    {
        $dayStart = Localization::where('user_id', Auth::id())->value('monthly_period_start_day') ?? 1;

        $today = Carbon::today();

        if ($today->day >= $dayStart) {
            $startDate = Carbon::create($today->year, $today->month, $dayStart)->startOfDay();
            $endDate = $startDate->copy()->addMonth()->subDay()->endOfDay();
        } else {
            $startDate = Carbon::create($today->year, $today->month, $dayStart)->subMonth();
            $endDate = $startDate->copy()->addMonth()->subDay();
        }

        return $table
            ->query(
                ModelsBudget::query()->where('user_id', Auth::id())->orderBy('amount', 'desc')
            )
            ->heading('Periode ' . $startDate->format('d M') . ' – ' . $endDate->format('d M'))
            ->description('Anggaran anda dalam periode ini.')
            ->paginated(false)
            ->deferLoading()
            ->emptyStateHeading('Belum ada anggaran yang dibuat')
            ->emptyStateDescription('Buat anggaran untuk memulai.')
            ->emptyStateActions([
                Action::make('create')
                    ->label('Buat Anggaran Baru')
                    ->url(BudgetResource::getUrl('create'))
                    ->icon('heroicon-m-plus')
                    ->button(),
            ])
            ->columns([
                TextColumn::make('category.name')
                    ->label('Kategori'),
                ViewColumn::make('progresBudget')
                    ->view('tables.columns.progres-budget')
                    ->getStateUsing(function ($record) use ($startDate, $endDate) {
                        $totalExpense = abs(
                            $record->category
                                ?->transactions()
                                ->whereBetween('date', [
                                    $startDate->startOfDay(),
                                    $endDate->endOfDay()
                                ])
                                ->where('ex_report', false) // hanya transaksi ex_report = false
                                ->sum('amount') ?? 0
                        );

                        $percentage = $record->amount > 0
                            ? round(($totalExpense / $record->amount) * 100)
                            : 0;

                        return [
                            'budget' => $record,
                            'percentage' => $percentage,
                        ];
                    })

                    ->label('Persentase Anggaran')
                    ->alignment(Alignment::Center),
                TextColumn::make('amount')
                    ->label('Jumlah')
                    ->alignment(Alignment::Right)
                    ->money('IDR')
                    ->summarize([
                        Sum::make()
                            ->label('Total Anggaran')
                            ->money('IDR', locale: 'id'),
                    ]),
                TextColumn::make('category.transactions.amount')
                    ->label('Pengeluaran')
                    ->money('IDR')
                    ->color('danger')
                    ->alignment(Alignment::Right)
                    ->getStateUsing(
                        fn($record): float =>
                        $record->category
                            ->transactions()
                            ->whereBetween('date', [
                                $startDate->startOfDay(),
                                $endDate->endOfDay()
                            ])
                            ->where('ex_report', false) // tambahkan filter disini juga
                            ->sum('amount') ?? 0
                    )
                    // ->summarize([
                    //     Sum::make()
                    //         ->label('Total Pengeluaran')
                    //         ->money('IDR', locale: 'id'),
                    // ])
                    ->description(fn($record) => 'Sisa: Rp ' . number_format(
                        $record->amount + $record->category
                            ->transactions()
                            ->whereBetween('date', [
                                $startDate->startOfDay(),
                                $endDate->endOfDay()
                            ])
                            ->where('ex_report', false) // tambahkan filter disini juga
                            ->sum('amount') ?? 0,
                        2,
                        ',',
                        '.'
                    )),


            ]);
    }

    public function getTitle(): string
    {
        return 'Anggaran';
    }

    public static function getNavigationLabel(): string
    {
        return 'Anggaran';
    }
}
