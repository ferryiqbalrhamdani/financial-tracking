<?php

namespace App\Filament\Clusters\Settings\Pages;

use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;

use App\Enums\Settings\Locale;
use Livewire\Attributes\Locked;
use App\Enums\Settings\DayStart;
use function Filament\authorize;
use App\Enums\Settings\MonthStart;
use App\Filament\Clusters\Settings;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Support\Exceptions\Halt;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Component;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Support\Htmlable;
use App\Models\Localization as ModelsLocalization;
use Illuminate\Auth\Access\AuthorizationException;
use Filament\Pages\Concerns\InteractsWithFormActions;

class Localization extends Page
{
    use InteractsWithFormActions;

    protected static string $view = 'filament.clusters.settings.pages.localization';

    protected static ?string $cluster = Settings::class;

    protected static ?int $navigationSort = 54;

    public ?array $data = [];

    #[Locked]
    public ?ModelsLocalization $record = null;

    public function getTitle(): string | Htmlable
    {
        return static::$title ?? 'Pengaturan Lokalisasi';
    }

    public static function getNavigationLabel(): string
    {
        return static::$title ?? 'Lokalisasi';
    }

    // public function getMaxContentWidth(): MaxWidth | string | null
    // {
    //     return MaxWidth::ScreenTwoExtraLarge;
    // }


    public function mount(): void
    {
        $this->record = ModelsLocalization::firstOrNew([
            'user_id' => Auth::user()->id,
        ]);

        abort_unless(static::canView($this->record), 404);

        $this->fillForm();
    }

    public function fillForm(): void
    {
        $data = $this->record->attributesToArray();

        $this->form->fill($data);
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();

            $this->handleRecordUpdate($this->record, $data);
        } catch (Halt $exception) {
            return;
        }

        $this->getSavedNotification()->send();
    }

    protected function getSavedNotification(): Notification
    {
        return Notification::make()
            ->success()
            ->title(__('filament-panels::resources/pages/edit-record.notifications.saved.title'));
    }


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getGeneralSection(),
                $this->getLanguageAndCurrencySection(),
            ])
            ->model($this->record)
            ->statePath('data')
            ->operation('edit');
    }

    protected function getGeneralSection(): Component
    {
        return Section::make('General')
            ->schema([
                Select::make('monthly_period_start_day')
                    ->label('Tanggal Mulai Periode Bulanan')
                    ->options(DayStart::class)
                    ->required(),
                Select::make('monthly_period_start_month')
                    ->label('Bulan Mulai Periode Bulanan')
                    ->options(MonthStart::class)
                    ->required(),
            ])
            ->columns(2); // ← tambahkan nilai kolom
    }
    protected function getLanguageAndCurrencySection(): Component
    {
        return Section::make('Bahasa dan Mata Uang')
            ->schema([
                Select::make('locale')
                    ->label('Bahasa')
                    ->options(Locale::class),
                Select::make('currency')
                    ->options(\App\Enums\Settings\Currency::options())
                    ->required(),

            ])
            ->columns(2); // ← tambahkan nilai kolom
    }

    protected function handleRecordUpdate(ModelsLocalization $record, array $data): ModelsLocalization
    {
        $record->fill($data);


        $record->save();

        return $record;
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return Action::make('save')
            ->label(__('filament-panels::resources/pages/edit-record.form.actions.save.label'))
            ->submit('save')
            ->keyBindings(['mod+s']);
    }

    public static function canView(Model $record): bool
    {
        try {
            return authorize('update', $record)->allowed();
        } catch (AuthorizationException $exception) {
            return $exception->toResponse()->allowed();
        }
    }
}
