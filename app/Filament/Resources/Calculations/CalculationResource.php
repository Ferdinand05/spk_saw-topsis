<?php

namespace App\Filament\Resources\Calculations;

use App\Filament\Resources\Calculations\Pages\ManageCalculations;
use App\Models\Calculation;
use BackedEnum;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class CalculationResource extends Resource
{
    protected static ?string $model = Calculation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ClipboardDocumentList;
    protected static ?string $navigationLabel = 'Jenis Perhitungan';
    protected static ?string $label = 'Jenis Perhitungan';
    protected static string | UnitEnum | null $navigationGroup = 'Data';
    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make("name")
                    ->label("Nama Perhitungan")
                    ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label("Nama Perhitungan"),
                TextColumn::make('user.name')
                    ->label('Dibuat Oleh')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label("Tgl. Dibuat")
                    ->sortable()
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make("print")
                    ->label("PDF")
                    ->icon(Heroicon::Printer)
                    ->color("secondary")
                    ->action(function (Calculation $calculation) {
                        $calculation->load([
                            'criteria' => fn($query) => $query->orderBy('id'),
                            'alternatives' => fn($query) => $query->orderBy('id'),
                            'scores' => fn($query) => $query->orderBy('alternative_id')->orderBy('criteria_id'),
                            'results' => fn($query) => $query->with('alternative')->orderBy('rank'),
                        ]);

                        $pdf = Pdf::loadView('pdf.pdf', compact('calculation'))
                            ->setPaper('a4');

                        return response()->streamDownload(
                            function () use ($pdf) {
                                echo $pdf->output();
                            },
                            'topsis-' . Str::slug($calculation->name ?: 'calculation') . '.pdf'
                        );
                    })
                    ->disabled(function (Calculation $calculation) {
                        return $calculation->results()->count() === 0;
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCalculations::route('/'),
        ];
    }
}
