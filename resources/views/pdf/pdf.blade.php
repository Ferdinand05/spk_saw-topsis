<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Laporan Hasil Hybrid SAW + TOPSIS</title>
    <style>
        @page {
            margin: 24px 28px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            color: #1f2937;
            line-height: 1.5;
        }

        .header {
            border-bottom: 2px solid #111827;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        .brand {
            font-size: 10px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #6b7280;
            font-weight: 700;
        }

        .title {
            margin: 4px 0 2px;
            font-size: 22px;
            font-weight: 700;
            color: #111827;
        }

        .meta {
            margin: 0;
            color: #4b5563;
        }

        .section {
            margin-top: 18px;
        }

        .section-title {
            margin: 0 0 8px;
            font-size: 15px;
            font-weight: 700;
            color: #111827;
        }

        .note {
            margin: 0;
            color: #4b5563;
        }

        .winner {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 12px;
            padding: 14px;
        }

        .winner-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: #047857;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .winner-value {
            font-size: 16px;
            font-weight: 700;
            color: #065f46;
        }

        .winner-score {
            margin-top: 4px;
            color: #065f46;
        }

        table.report {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        table.report th,
        table.report td {
            border: 1px solid #d1d5db;
            padding: 8px 10px;
            text-align: left;
            vertical-align: top;
        }

        table.report th {
            background: #111827;
            color: #fff;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        table.report tbody tr:nth-child(even) td {
            background: #f9fafb;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .rank {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 999px;
            background: #111827;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .4px;
            text-transform: uppercase;
        }

        .footer {
            margin-top: 20px;
            font-size: 10px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
    </style>
</head>

<body>
    @php
        $criteria = collect($calculation->criteria ?? [])->values();
        $alternatives = collect($calculation->alternatives ?? [])->values();
        $scores = collect($calculation->scores ?? []);
        $printedAt = now()->timezone(config('app.timezone', 'Asia/Jakarta'));

        $matrix = [];
        foreach ($scores as $score) {
            $matrix[$score->alternative_id][$score->criteria_id] = (float) $score->value;
        }

        $sawNormalizedMatrix = [];
        $sawWeightedMatrix = [];
        $weightedMatrix = [];
        $idealPositive = [];
        $idealNegative = [];
        $distancePositive = [];
        $distanceNegative = [];
        $rankedResults = collect();

        if ($criteria->count() && $alternatives->count() && $scores->count()) {
            $nCriteria = $criteria->count();
            $nAlt = $alternatives->count();
            $totalWeight = $criteria->sum('weight');
            $weights = $criteria
                ->map(fn ($criterion) => $criterion->weight / ($totalWeight ?: 1))
                ->values()
                ->all();

            for ($j = 0; $j < $nCriteria; $j++) {
                $columnValues = [];

                for ($i = 0; $i < $nAlt; $i++) {
                    $altId = $alternatives[$i]->id;
                    $critId = $criteria[$j]->id;

                    $columnValues[] = (float) data_get($matrix, $altId . '.' . $critId, 0);
                }

                $max = ! empty($columnValues) ? max($columnValues) : 0;
                $min = ! empty($columnValues) ? min($columnValues) : 0;

                for ($i = 0; $i < $nAlt; $i++) {
                    $altId = $alternatives[$i]->id;
                    $critId = $criteria[$j]->id;
                    $value = (float) data_get($matrix, $altId . '.' . $critId, 0);

                    if (($criteria[$j]->type ?? 'benefit') === 'benefit') {
                        $sawNormalizedMatrix[$i][$j] = $max ? $value / $max : 0;
                    } else {
                        $sawNormalizedMatrix[$i][$j] = $value > 0 ? $min / $value : 0;
                    }

                    $sawWeightedMatrix[$i][$j] = $sawNormalizedMatrix[$i][$j] * ($weights[$j] ?? 0);
                }
            }

            $weightedMatrix = $sawWeightedMatrix;

            for ($j = 0; $j < $nCriteria; $j++) {
                $column = array_column($weightedMatrix, $j);
                $idealPositive[$j] = ! empty($column) ? max($column) : 0;
                $idealNegative[$j] = ! empty($column) ? min($column) : 0;
            }

            $results = [];
            foreach ($alternatives as $i => $alternative) {
                $sumPos = 0;
                $sumNeg = 0;

                for ($j = 0; $j < $nCriteria; $j++) {
                    $sumPos += pow(($weightedMatrix[$i][$j] ?? 0) - $idealPositive[$j], 2);
                    $sumNeg += pow(($weightedMatrix[$i][$j] ?? 0) - $idealNegative[$j], 2);
                }

                $distancePositive[$i] = sqrt($sumPos);
                $distanceNegative[$i] = sqrt($sumNeg);
                $scoreValue = $distanceNegative[$i] / (($distancePositive[$i] + $distanceNegative[$i]) ?: 1);

                $results[] = [
                    'id' => $alternative->id,
                    'code' => $alternative->code,
                    'name' => $alternative->name,
                    'd_plus' => $distancePositive[$i],
                    'd_minus' => $distanceNegative[$i],
                    'score' => $scoreValue,
                ];
            }

            usort($results, fn ($a, $b) => $b['score'] <=> $a['score']);

            foreach ($results as $index => $result) {
                $results[$index]['rank'] = $index + 1;
            }

            $rankedResults = collect($results);
        }

        $topResult = $rankedResults->first();
    @endphp

    <div class="header">
        <div class="brand">Sistem Pendukung Keputusan</div>
        <h1 class="title">Laporan Hasil Hybrid SAW + TOPSIS</h1>
        <p class="meta">
            Perhitungan: <strong>{{ $calculation->name }}</strong> | Dicetak: {{ $printedAt->format('d M Y H:i') }}
        </p>
    </div>

    <div class="section">
        <h2 class="section-title">Hasil Utama</h2>

        @if ($topResult)
            <div class="winner">
                <div class="winner-label">Alternatif Terbaik</div>
                <div class="winner-value">
                    {{ $topResult['code'] ?? '-' }} - {{ $topResult['name'] ?? '-' }}
                </div>
                <div class="winner-score">
                    Skor preferensi: <strong>{{ number_format((float) ($topResult['score'] ?? 0), 4) }}</strong>
                </div>
            </div>
        @else
            <p class="note">Belum ada hasil Hybrid SAW + TOPSIS yang bisa dicetak untuk perhitungan ini.</p>
        @endif
    </div>

    <div class="section">
        <h2 class="section-title">Ranking Alternatif Hybrid</h2>

        @if ($rankedResults->count())
            <table class="report">
                <thead>
                    <tr>
                        <th class="center" style="width: 10%;">Rank</th>
                        <th style="width: 40%;">Alternatif</th>
                        <th class="right" style="width: 16%;">D+</th>
                        <th class="right" style="width: 16%;">D-</th>
                        <th class="right" style="width: 18%;">Score</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rankedResults as $result)
                        <tr>
                            <td class="center">
                                <span class="rank">#{{ $result['rank'] }}</span>
                            </td>
                            <td>
                                <strong>{{ $result['code'] ?? '-' }}</strong>
                                <div>{{ $result['name'] ?? '-' }}</div>
                            </td>
                            <td class="right">{{ number_format((float) ($result['d_plus'] ?? 0), 4) }}</td>
                            <td class="right">{{ number_format((float) ($result['d_minus'] ?? 0), 4) }}</td>
                            <td class="right"><strong>{{ number_format((float) ($result['score'] ?? 0), 4) }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="note">Tidak ada hasil ranking yang tersedia.</p>
        @endif
    </div>

    <div class="section">
        <h2 class="section-title">Kriteria</h2>

        @if ($criteria->count())
            <table class="report">
                <thead>
                    <tr>
                        <th style="width: 16%;">Kode</th>
                        <th style="width: 54%;">Nama</th>
                        <th style="width: 14%;">Tipe</th>
                        <th class="right" style="width: 16%;">Bobot</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($criteria as $criterion)
                        <tr>
                            <td><strong>{{ $criterion->code }}</strong></td>
                            <td>{{ $criterion->name }}</td>
                            <td>{{ ucfirst($criterion->type) }}</td>
                            <td class="right">{{ $criterion->weight }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="note">Kriteria belum tersedia.</p>
        @endif
    </div>

    <div class="footer">
        Dokumen ini digenerate otomatis dari sistem Hybrid SAW + TOPSIS dan siap dicetak.
    </div>
</body>

</html>
