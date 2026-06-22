<?php

namespace App\Filament\Resources\Calculations\Pages;

use App\Filament\Resources\Calculations\CalculationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCalculations extends ManageRecords
{
    protected static string $resource = CalculationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateDataUsing(function (array $data): array {
                    $data['user_id'] = auth()->id();

                    return $data;
                }),
        ];
    }
}
