<?php

namespace App\Filament\Resources\Alternatives\Pages;

use App\Filament\Resources\Alternatives\AlternativeResource;
use App\Models\Alternative;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ManageAlternatives extends ManageRecords
{
    protected static string $resource = AlternativeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
            Action::make('bulkCreate')
                ->label('Tambah Alternatif')
                ->modalHeading('Bulk Create Alternatif')
                ->modalSubmitActionLabel('Simpan Semua')
                ->schema([
                    Select::make('calculation_id')
                        ->label('Nama Perhitungan')
                        ->required()
                        ->relationship('calculation', 'name'),
                    Repeater::make('alternatives')
                        ->label('Alternatif')
                        ->minItems(1)
                        ->defaultItems(1)
                        ->schema([
                            TextInput::make('code')
                                ->label('Kode')
                                ->required()
                                ->rules(['distinct'])
                                ->unique(ignoreRecord: true),
                            TextInput::make('name')
                                ->label('Name')
                                ->required(),
                        ])
                        ->columns(2)
                        ->addActionLabel('Tambah Alternatif'),
                ])
                ->action(function (array $data): void {
                    $codes = array_map(
                        static fn(array $alternative): string => $alternative['code'],
                        $data['alternatives'],
                    );

                    $duplicateCodes = array_values(array_unique(array_diff_assoc($codes, array_unique($codes))));

                    if ($duplicateCodes !== []) {
                        throw ValidationException::withMessages([
                            'alternatives' => 'Kode alternatif tidak boleh sama dalam bulk form.',
                        ]);
                    }

                    $existingCodes = Alternative::query()
                        ->whereIn('code', $codes)
                        ->pluck('code')
                        ->all();

                    if ($existingCodes !== []) {
                        throw ValidationException::withMessages([
                            'alternatives' => 'Kode alternatif sudah digunakan: ' . implode(', ', $existingCodes),
                        ]);
                    }

                    DB::transaction(function () use ($data): void {
                        foreach ($data['alternatives'] as $alternative) {
                            Alternative::create([
                                'calculation_id' => $data['calculation_id'],
                                'code' => $alternative['code'],
                                'name' => $alternative['name'],
                            ]);
                        }
                    });
                }),
        ];
    }
}
