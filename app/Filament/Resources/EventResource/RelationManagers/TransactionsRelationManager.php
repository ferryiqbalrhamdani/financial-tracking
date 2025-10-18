<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\Summarizers\Sum;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('category.name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('category.name')
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record): string => $record->description ?? 'Tidak ada deskripsi'),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah Transaksi')
                    ->numeric()
                    ->sortable()
                    ->color(function ($record) {
                        if ($record->tipe_transaksi == 'Pengeluaran') {
                            return 'danger';
                        } else if ($record->tipe_transaksi == 'Pemasukan') {
                            return 'success';
                        } else {
                            return 'gray';
                        }
                    })
                    ->description(fn($record): string => $record->account->name ?? 'Tidak ada akun')
                    ->alignment(Alignment::Right)
                    ->money('idr', locale: 'id')
                    ->summarize(
                        Sum::make()
                            ->label('')
                            ->money('IDR')
                    ),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                DateRangeFilter::make('date')
                    ->label('Tanggal Transaksi'),


            ], 
            layout: FiltersLayout::Modal
            )
            ->deferLoading()
            // ->filtersFormColumns(3)
            ->filtersFormWidth(MaxWidth::TwoExtraLarge)
            ->defaultSort('date', 'desc')
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
            ])
            ->groups([
                Tables\Grouping\Group::make('date')
                    ->label('Tanggal Transaksi')
                    ->collapsible()
                    ->orderQueryUsing(
                        fn($query) =>
                        $query
                            ->orderBy('date', 'desc')      // urutkan group utama berdasarkan tanggal transaksi
                            ->orderBy('created_at', 'desc') // dalam setiap group, urutkan berdasarkan waktu input
                    )
                    ->date(),
            ])
            ->groupingSettingsHidden()
            ->defaultGroup('date');
    }
}
