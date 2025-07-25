<?php

namespace App\Filament\Clusters\Settings\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Budget;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use App\Models\Localization;
use Filament\Resources\Resource;
use App\Filament\Clusters\Settings;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\Alignment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Clusters\Settings\Resources\BudgetResource\Pages;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use App\Filament\Clusters\Settings\Resources\BudgetResource\RelationManagers;
use Filament\Tables\Columns\Summarizers\Sum;

class BudgetResource extends Resource
{
    protected static ?string $model = Budget::class;

    protected static ?string $cluster = Settings::class;

    protected static ?int $navigationSort = 53;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', Auth::id());
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('category_id')
                    ->label('Kategori')
                    ->required()
                    ->unique(Budget::class, 'category_id', ignoreRecord: true)
                    ->relationship(
                        name: 'category',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn($query) => $query->where('user_id', Auth::id())->where('tipe_transaksi', 'Pengeluaran'),
                    ),
                Forms\Components\TextInput::make('amount')
                    ->label('Anggaran')
                    ->required()
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(',')
                    ->numeric()
                    ->prefix('Rp '),
            ]);
    }

    public static function table(Table $table): Table
    {
        $dayStart = Localization::where('user_id', Auth::id())->value('monthly_period_start_day') ?? 1;

        $today = Carbon::today();

        if ($today->day >= $dayStart) {
            $startDate = Carbon::create($today->year, $today->month, $dayStart);
            $endDate = $startDate->copy()->addMonth()->subDay();
        } else {
            $startDate = Carbon::create($today->year, $today->month, $dayStart)->subMonth();
            $endDate = $startDate->copy()->addMonth()->subDay();
        }

        return $table
            ->heading('Periode ' . $startDate->format('d M') . ' – ' . $endDate->format('d M'))
            ->description('Kelola transaksi dan anggaran anda dalam periode ini.')
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Anggaran')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->alignment(Alignment::Right)
                    ->summarize([
                        Sum::make()
                            ->money('IDR', locale: 'id'),
                    ]),
                // Tables\Columns\TextColumn::make('total_transactions_per_month')
                //     ->label('Total Transaksi / Bulan')
                //     ->money('IDR', locale: 'id')
                //     ->alignment(Alignment::Right)
                //     ->getStateUsing(function ($record) use ($startDate, $endDate) {
                //         return abs(
                //             $record->category
                //                 ?->transactions()
                //                 ->whereBetween('date', [$startDate, $endDate])
                //                 ->sum('amount') ?? 0
                //         );
                //     }),
                // Tables\Columns\ViewColumn::make('progresBudget')->view('tables.columns.progres-budget')
                //     ->label('Progres Anggaran')
                //     ->sortable()
                //     ->alignment(Alignment::Center),
            ])
            ->defaultSort('amount', 'desc')
            ->filters([])
            ->filtersApplyAction(
                fn(Action $action) => $action
                    ->link()
                    ->label('Save filters to table'),
            )
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
                    ->tooltip('Aksi'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBudgets::route('/'),
            'create' => Pages\CreateBudget::route('/create'),
            'edit' => Pages\EditBudget::route('/{record}/edit'),
        ];
    }
}
