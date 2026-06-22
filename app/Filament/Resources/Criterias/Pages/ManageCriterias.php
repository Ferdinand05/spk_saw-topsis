<?php

namespace App\Filament\Resources\Criterias\Pages;

use App\Filament\Resources\Criterias\CriteriaResource;
use App\Models\Calculation;
use App\Models\Criteria;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ManageCriterias extends ManageRecords
{
    protected static string $resource = CriteriaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
            Action::make('bulkCreate')
                ->label('Tambah Kriteria')
                ->modalHeading('Bulk Create Kriteria')
                ->modalSubmitActionLabel('Simpan Semua')
                ->form([
                    Select::make('calculation_id')
                        ->label('Nama Perhitungan')
                        ->required()
                        ->options(function (): array {
                            return Calculation::query()
                                ->orderByDesc('created_at')
                                ->pluck('name', 'id')
                                ->toArray();
                        }),
                    Repeater::make('criterias')
                        ->label('Kriteria')
                        ->minItems(1)
                        ->defaultItems(1)
                        ->schema([
                            TextInput::make('code')
                                ->label('Kode Unik')
                                ->required(),
                            TextInput::make('name')
                                ->label('Nama')
                                ->required(),
                            TextInput::make('weight')
                                ->label('Bobot')
                                ->required()
                                ->numeric(),
                            Select::make('type')
                                ->label('Tipe')
                                ->options([
                                    'cost' => 'Cost',
                                    'benefit' => 'Benefit',
                                ])
                                ->required(),
                        ])
                        ->columns(4)
                        ->addActionLabel('Tambah Kriteria'),
                ])
                ->action(function (array $data): void {
                    $codes = array_map(
                        static fn(array $criteria): string => $criteria['code'],
                        $data['criterias'],
                    );

                    $duplicateCodes = array_values(array_unique(array_diff_assoc($codes, array_unique($codes))));

                    if ($duplicateCodes !== []) {
                        throw ValidationException::withMessages([
                            'criterias' => 'Kode kriteria tidak boleh sama dalam bulk form.',
                        ]);
                    }

                    $existingCodes = Criteria::query()
                        ->whereIn('code', $codes)
                        ->pluck('code')
                        ->all();

                    if ($existingCodes !== []) {
                        throw ValidationException::withMessages([
                            'criterias' => 'Kode kriteria sudah digunakan: ' . implode(', ', $existingCodes),
                        ]);
                    }

                    DB::transaction(function () use ($data): void {
                        foreach ($data['criterias'] as $criteria) {
                            Criteria::create([
                                'calculation_id' => $data['calculation_id'],
                                'code' => $criteria['code'],
                                'name' => $criteria['name'],
                                'weight' => $criteria['weight'],
                                'type' => $criteria['type'],
                            ]);
                        }
                    });
                }),
        ];
    }
}
