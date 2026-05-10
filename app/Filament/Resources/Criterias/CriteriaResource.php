<?php

namespace App\Filament\Resources\Criterias;

use App\Filament\Resources\Criterias\Pages\ManageCriterias;
use App\Models\Calculation;
use App\Models\Criteria;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class CriteriaResource extends Resource
{
    protected static ?string $model = Criteria::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentDuplicate;
    protected static ?string $navigationLabel = 'Kriteria';
    protected static string | UnitEnum | null $navigationGroup = 'Data';
    protected static ?int $navigationSort = 3;
    protected static ?string $label = 'Kriteria';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label("Kode Unik")
                    ->unique("criterias", "code", ignoreRecord: true)
                    ->default(function () {
                        $last = Criteria::query()->latest()->first();

                        $number = $last ? ((int) substr($last->code, 1)) + 1 : 1;

                        $code = 'C' . str_pad($number, 2, '0', STR_PAD_LEFT);

                        return $code;
                    }),
                TextInput::make('name')
                    ->label("Nama")
                    ->required(),
                TextInput::make('weight')
                    ->required()
                    ->numeric()

                    ->label("Bobot"),
                Select::make('type')
                    ->options([
                        "cost" => "Cost",
                        "benefit" => "Benefit"
                    ])
                    ->required()
                    ->label("Tipe"),
                Select::make("calculation_id")
                    ->label("Nama Perhitungan")
                    ->required()
                    ->options(function () {
                        return Calculation::orderBy("created_at", "desc")->get()->pluck("name", "id");
                    })
                    ->helperText("Pilih Peruntukan Perhitungan"),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("code")
                    ->label("Kode"),
                TextColumn::make("name")
                    ->label("Name"),
                TextColumn::make("weight")
                    ->label("Bobot")
                    ->summarize(Sum::make()),
                SelectColumn::make('type')
                    ->label("Tipe")
                    ->options([
                        "cost" => "Cost",
                        "benefit" => "Benefit"
                    ]),
                TextColumn::make("calculation.name")
                    ->label("Nama Perhitungan"),
            ])
            ->recordUrl(null)
            ->filters([
                SelectFilter::make("filterCalculation")
                    ->label("Filter Perhitungan")
                    ->relationship('calculation', "name")
            ])
            ->recordActions([
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
            'index' => ManageCriterias::route('/'),
        ];
    }
}
