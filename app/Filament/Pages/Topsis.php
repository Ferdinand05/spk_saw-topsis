<?php

namespace App\Filament\Pages;

use App\Ai\Agents\TopsisAgent;
use App\Filament\Widgets\TopsisRankChart;
use App\Models\Alternative;
use App\Models\Calculation;
use App\Models\Criteria;
use App\Models\Result;
use App\Models\Score;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class Topsis extends Page
{
    protected string $view = 'filament.pages.topsis';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Calculator;
    protected static ?int $navigationSort = 4;
    protected static string|UnitEnum|null $navigationGroup = 'Perhitungan';
    protected static ?string $navigationLabel = 'Hybrid SAW + TOPSIS';

    public $calculation_id = null;
    public $calculations = [];

    public $criteria = [];
    public $alternatives = [];
    public $scores = [];

    public $sawNormalizedMatrix = [];
    public $sawWeightedMatrix = [];
    public $sawResults = [];

    public $normalizedMatrix = [];
    public $weightedMatrix = [];
    public $idealPositive = [];
    public $idealNegative = [];
    public $distancePositive = [];
    public $distanceNegative = [];

    public $results = [];
    public $aiConclusion = null;
    public $disabledHitung = true;
    public $disabledAi = true;

    public function mount(): void
    {
        $this->calculations = Calculation::orderBy('name')->get()->toArray();
    }

    public function updatedCalculationId(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        if (! $this->calculation_id) {
            $this->criteria = [];
            $this->alternatives = [];
            $this->scores = [];
            $this->results = [];
            $this->sawResults = [];
            $this->normalizedMatrix = [];
            $this->weightedMatrix = [];
            $this->idealPositive = [];
            $this->idealNegative = [];
            $this->distancePositive = [];
            $this->distanceNegative = [];
            $this->sawNormalizedMatrix = [];
            $this->sawWeightedMatrix = [];
            $this->aiConclusion = null;
            $this->disabledHitung = true;
            $this->disabledAi = true;

            return;
        }

        $this->criteria = Criteria::where('calculation_id', $this->calculation_id)->get()->toArray();
        $this->alternatives = Alternative::where('calculation_id', $this->calculation_id)->get()->toArray();

        $scores = Score::where('calculation_id', $this->calculation_id)->get();
        $matrix = [];

        foreach ($scores as $score) {
            $matrix[$score->alternative_id][$score->criteria_id] = $score->value;
        }

        $this->scores = $matrix;
        $this->results = [];
        $this->sawResults = [];
        $this->normalizedMatrix = [];
        $this->weightedMatrix = [];
        $this->idealPositive = [];
        $this->idealNegative = [];
        $this->distancePositive = [];
        $this->distanceNegative = [];
        $this->sawNormalizedMatrix = [];
        $this->sawWeightedMatrix = [];
        $this->aiConclusion = null;
        $this->disabledHitung = true;
        $this->disabledAi = true;
    }

    private function normalizedWeights(Collection $criteria): array
    {
        $totalWeight = $criteria->sum('weight');

        return $criteria
            ->map(fn(array $criterion) => $criterion['weight'] / ($totalWeight ?: 1))
            ->values()
            ->all();
    }

    private function calculateSaw(Collection $criteria, Collection $alternatives): array
    {
        $nCriteria = $criteria->count();
        $nAlt = $alternatives->count();
        $weights = $this->normalizedWeights($criteria);

        $normalized = [];
        $weighted = [];

        for ($j = 0; $j < $nCriteria; $j++) {
            $columnValues = [];

            for ($i = 0; $i < $nAlt; $i++) {
                $columnValues[] = $this->getScore($i, $j);
            }

            $max = ! empty($columnValues) ? max($columnValues) : 0;
            $min = ! empty($columnValues) ? min($columnValues) : 0;

            for ($i = 0; $i < $nAlt; $i++) {
                $value = $this->getScore($i, $j);

                if ($criteria[$j]['type'] === 'benefit') {
                    $normalized[$i][$j] = $max ? $value / $max : 0;
                } else {
                    $normalized[$i][$j] = $value > 0 ? $min / $value : 0;
                }

                $weighted[$i][$j] = $normalized[$i][$j] * ($weights[$j] ?? 0);
            }
        }

        $results = [];

        foreach ($alternatives as $i => $alternative) {
            $score = 0;

            for ($j = 0; $j < $nCriteria; $j++) {
                $score += $weighted[$i][$j] ?? 0;
            }

            $results[] = [
                'id' => $alternative['id'],
                'name' => $alternative['name'],
                'score' => $score,
            ];
        }

        usort($results, fn(array $a, array $b) => $b['score'] <=> $a['score']);

        return [
            'normalized' => $normalized,
            'weighted' => $weighted,
            'results' => $results,
        ];
    }

    private function calculateTopsisHybrid(Collection $criteria, Collection $alternatives, array $sawWeightedMatrix): array
    {
        $nCriteria = $criteria->count();
        $nAlt = $alternatives->count();

        // Dalam Hybrid, kita langsung gunakan matriks terbobot dari SAW
        $weighted = $sawWeightedMatrix;

        $idealPositive = [];
        $idealNegative = [];

        for ($j = 0; $j < $nCriteria; $j++) {
            $column = array_column($weighted, $j);

            // KARENA SUDAH DI-NORMALISASI SAW:
            // Semua kriteria (termasuk Cost) sudah searah (semakin besar semakin baik)
            // Jadi A+ selalu MAX dan A- selalu MIN
            $idealPositive[$j] = max($column);
            $idealNegative[$j] = min($column);
        }

        $distancePositive = [];
        $distanceNegative = [];

        for ($i = 0; $i < $nAlt; $i++) {
            $sumPositive = 0;
            $sumNegative = 0;

            for ($j = 0; $j < $nCriteria; $j++) {
                $sumPositive += pow(($weighted[$i][$j] ?? 0) - $idealPositive[$j], 2);
                $sumNegative += pow(($weighted[$i][$j] ?? 0) - $idealNegative[$j], 2);
            }

            $distancePositive[$i] = sqrt($sumPositive);
            $distanceNegative[$i] = sqrt($sumNegative);
        }

        $results = [];
        foreach ($alternatives as $i => $alternative) {
            $denominator = ($distancePositive[$i] + $distanceNegative[$i]) ?: 1;
            $score = $distanceNegative[$i] / $denominator;

            $results[] = [
                'id' => $alternative['id'],
                'name' => $alternative['name'],
                'd_plus' => $distancePositive[$i],
                'd_minus' => $distanceNegative[$i],
                'score' => $score,
            ];
        }

        usort($results, fn(array $a, array $b) => $b['score'] <=> $a['score']);

        return [
            'weighted' => $weighted,
            'ideal_positive' => $idealPositive,
            'ideal_negative' => $idealNegative,
            'distance_positive' => $distancePositive,
            'distance_negative' => $distanceNegative,
            'results' => $results,
        ];
    }

    public function hitung(): void
    {
        $criteria = collect($this->criteria)->values();
        $alternatives = collect($this->alternatives)->values();

        if ($criteria->isEmpty() || $alternatives->isEmpty()) {
            return;
        }

        // 1. Jalankan SAW untuk mendapatkan Normalisasi dan Terbobot
        $saw = $this->calculateSaw($criteria, $alternatives);

        // 2. Jalankan TOPSIS menggunakan MATRIKS TERBOBOT dari SAW
        $topsis = $this->calculateTopsisHybrid($criteria, $alternatives, $saw['weighted']);

        // Simpan ke state untuk ditampilkan di UI
        $this->sawNormalizedMatrix = $saw['normalized'];
        $this->sawWeightedMatrix = $saw['weighted'];
        $this->sawResults = $saw['results'];

        $this->weightedMatrix = $topsis['weighted'];
        $this->idealPositive = $topsis['ideal_positive'];
        $this->idealNegative = $topsis['ideal_negative'];
        $this->distancePositive = $topsis['distance_positive'];
        $this->distanceNegative = $topsis['distance_negative'];
        $this->results = $topsis['results'];
        $this->aiConclusion = null;

        DB::transaction(function () {
            Result::where('calculation_id', $this->calculation_id)->delete();

            foreach ($this->results as $rank => $result) {
                Result::create([
                    'calculation_id' => $this->calculation_id,
                    'alternative_id' => $result['id'],
                    'score' => $result['score'],
                    'rank' => $rank + 1,
                ]);
            }
        });

        $this->disabledAi = false;
    }

    public function generateConclusion(): void
    {
        if (empty($this->results) || empty($this->sawResults)) {
            $this->aiConclusion = 'Silakan jalankan perhitungan hybrid SAW + TOPSIS terlebih dahulu sebelum meminta kesimpulan AI.';

            return;
        }

        $calculation = collect($this->calculations)
            ->firstWhere('id', $this->calculation_id) ?? [];

        $criteria = collect($this->criteria)->map(function (array $criterion) {
            return [
                'id' => $criterion['id'],
                'code' => $criterion['code'],
                'name' => $criterion['name'],
                'weight' => $criterion['weight'],
                'type' => $criterion['type'],
            ];
        })->values()->all();

        $alternatives = collect($this->alternatives)->map(function (array $alternative) {
            return [
                'id' => $alternative['id'],
                'code' => $alternative['code'],
                'name' => $alternative['name'],
            ];
        })->values()->all();

        $sawResults = collect($this->sawResults)->values()->map(function (array $result, int $index) {
            return [
                'rank' => $index + 1,
                'id' => $result['id'],
                'name' => $result['name'],
                'score' => $result['score'],
            ];
        })->all();

        $results = collect($this->results)->values()->map(function (array $result, int $index) {
            return [
                'rank' => $index + 1,
                'id' => $result['id'],
                'name' => $result['name'],
                'score' => $result['score'],
                'd_plus' => $result['d_plus'],
                'd_minus' => $result['d_minus'],
            ];
        })->all();

        $matrices = [
            'saw_normalized' => $this->sawNormalizedMatrix,
            'saw_weighted' => $this->sawWeightedMatrix,
            'normalized' => $this->normalizedMatrix,
            'weighted' => $this->weightedMatrix,
            'ideal_positive' => $this->idealPositive,
            'ideal_negative' => $this->idealNegative,
            'distance_positive' => $this->distancePositive,
            'distance_negative' => $this->distanceNegative,
        ];

        try {
            $response = TopsisAgent::make()
                ->conclude(
                    calculation: $calculation,
                    criteria: $criteria,
                    alternatives: $alternatives,
                    sawResults: $sawResults,
                    results: $results,
                    matrices: $matrices,
                );

            $this->aiConclusion = (string) $response;
        } catch (\Throwable $exception) {
            report($exception);

            $this->aiConclusion = $exception->getMessage();
        }
    }

    private function getScore($i, $j)
    {
        $altId = $this->alternatives[$i]['id'];
        $critId = $this->criteria[$j]['id'];

        return $this->scores[$altId][$critId] ?? 0;
    }

    public function saveScores(): void
    {
        DB::transaction(function () {
            foreach ($this->alternatives as $alternative) {
                foreach ($this->criteria as $criterion) {
                    $value = $this->scores[$alternative['id']][$criterion['id']] ?? 0;

                    Score::updateOrCreate(
                        [
                            'calculation_id' => $this->calculation_id,
                            'alternative_id' => $alternative['id'],
                            'criteria_id' => $criterion['id'],
                        ],
                        [
                            'value' => $value,
                        ]
                    );
                }
            }
        });

        $this->disabledHitung = false;
    }

    public function getWidgetData(): array
    {
        return [
            'stats' => [
                'calculation_id' => $this->calculation_id,
            ],
        ];
    }

    protected function getFooterWidgets(): array
    {
        if (! $this->calculation_id) {
            return [];
        }

        return [
            TopsisRankChart::make([
                'stats' => [
                    'calculation_id' => $this->calculation_id,
                ],
            ]),
        ];
    }
}
