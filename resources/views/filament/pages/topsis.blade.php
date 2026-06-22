<x-filament::page>
    <div class="space-y-6">
        <div
            class="rounded-3xl border border-slate-200 bg-linear-to-br from-white to-slate-50 p-6 shadow-sm dark:border-slate-800 dark:from-slate-900 dark:to-slate-950">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500 dark:text-slate-400">
                        Sistem Pendukung Keputusan
                    </p>
                    <h1 class="mt-2 text-2xl font-semibold tracking-tight text-slate-900 dark:text-slate-100">
                        Metode Hybrid SAW + TOPSIS
                    </h1>
                    <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
                        SAW (Simple Additive Weighting) dihitung terlebih dahulu untuk mendapatkan skor awal setiap
                        alternatif, lalu TOPSIS (Technique for Order Preference by Similarity to Ideal Solution)
                        digunakan untuk pemeringkatan akhir berdasarkan jarak ke solusi ideal positif dan negatif.
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-3 lg:min-w-md">
                    <div
                        class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Perhitungan
                        </div>
                        <div class="mt-1 text-lg font-semibold text-slate-900 dark:text-slate-100">
                            {{ $calculation_id ? 'Aktif' : 'Belum dipilih' }}
                        </div>
                    </div>
                    <div
                        class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Alternatif</div>
                        <div class="mt-1 text-lg font-semibold text-slate-900 dark:text-slate-100">
                            {{ count($alternatives) }}
                        </div>
                    </div>
                    <div
                        class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Kriteria</div>
                        <div class="mt-1 text-lg font-semibold text-slate-900 dark:text-slate-100">
                            {{ count($criteria) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                Pilih Perhitungan
            </label>

            <select wire:model.live="calculation_id"
                class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm transition focus:border-slate-400 focus:ring-0 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:focus:border-slate-500">
                <option value="">Pilih perhitungan</option>
                @foreach ($calculations as $calc)
                    <option value="{{ $calc['id'] }}">{{ $calc['name'] }}</option>
                @endforeach
            </select>
        </div>

        @if ($calculation_id)
            @php
                $criteriaItems = collect($criteria)->values();
                $alternativeItems = collect($alternatives)->values();
                $selectedCalculation = collect($calculations)->firstWhere('id', $calculation_id);
            @endphp

            <div class="grid gap-4 md:grid-cols-3">
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Perhitungan dipilih
                    </div>
                    <div class="mt-2 text-lg font-semibold text-slate-900 dark:text-slate-100">
                        {{ $selectedCalculation['name'] ?? 'Tidak ditemukan' }}
                    </div>
                </div>
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Langkah kerja</div>
                    <div class="mt-2 text-lg font-semibold text-slate-900 dark:text-slate-100">1. Input, 2. Simpan, 3.
                        SAW, 4. TOPSIS</div>
                </div>
            </div>

            <div
                class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                            Panduan singkat
                        </span>

                        <x-filament::icon-button icon="heroicon-m-question-mark-circle" label="Panduan penilaian"
                            tooltip="Skala 1-5: 1 sangat rendah, 2 rendah, 3 cukup, 4 baik, 5 sangat baik. Benefit = semakin tinggi semakin baik. Cost = semakin rendah semakin baik." />
                    </div>

                    <div class="flex flex-wrap gap-2">
                        @foreach ([1 => 'Sangat rendah', 2 => 'Rendah', 3 => 'Cukup', 4 => 'Baik', 5 => 'Sangat baik'] as $value => $label)
                            <span title="{{ $value }} = {{ $label }}"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 bg-slate-50 text-xs font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200">
                                {{ $value }}
                            </span>
                        @endforeach
                    </div>

                    <div class="text-sm text-slate-600 dark:text-slate-300">
                        <span class="font-medium text-emerald-700 dark:text-emerald-300">Benefit</span> tinggi lebih
                        baik,
                        <span class="font-medium text-amber-700 dark:text-amber-300">Cost</span> rendah lebih baik.
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100">Matriks Nilai Alternatif</h2>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                        Isi nilai tiap alternatif terhadap setiap kriteria dengan tampilan yang tetap ringan dan mudah
                        dibaca.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                        <thead class="bg-slate-50 dark:bg-slate-800/70">
                            <tr>
                                <th
                                    class="sticky left-0 z-10 bg-slate-50 px-4 py-3 text-left font-semibold text-slate-700 dark:bg-slate-800/70 dark:text-slate-200">
                                    Alternatif
                                </th>
                                @foreach ($criteriaItems as $crit)
                                    <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200">
                                        <div class="flex flex-col">
                                            <span>{{ $crit['code'] }}</span>
                                            <span class="text-xs font-normal text-slate-500 dark:text-slate-400">
                                                {{ $crit['name'] }} | {{ ucfirst($crit['type']) }} | Bobot
                                                {{ $crit['weight'] }}
                                            </span>
                                        </div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white dark:divide-slate-800 dark:bg-slate-900">
                            @foreach ($alternativeItems as $alt)
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/60">
                                    <td
                                        class="sticky left-0 z-10 bg-white px-4 py-3 font-medium text-slate-900 dark:bg-slate-900 dark:text-slate-100">
                                        <span class="text-gray-500">
                                            {{ $alt['code'] }}
                                        </span>
                                        <span>
                                            {{ ' ' . $alt['name'] }}
                                        </span>
                                    </td>

                                    @foreach ($criteriaItems as $crit)
                                        <td class="px-4 py-3">
                                            <input type="number" step="any" required="true"
                                                wire:model="scores.{{ $alt['id'] }}.{{ $crit['id'] }}"
                                                class="w-28 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm transition focus:border-slate-400 focus:ring-0 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:focus:border-slate-500">
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <button wire:click="saveScores" wire:loading.attr="disabled"
                    class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                    Simpan Nilai
                </button>

                <button wire:click="hitung" wire:loading.attr="disabled" wire:bind:disabled="disabledHitung"
                    wire:target="hitung"
                    class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-white">

                    <span wire:loading.remove wire:target="hitung">
                        Hitung Hybrid SAW-TOPSIS
                    </span>

                    <span wire:loading wire:target="hitung">
                        Menghitung..
                    </span>

                </button>

                @if (!$aiConclusion)
                    <button wire:click="generateConclusion" wire:loading.attr="disabled"
                        wire:target="generateConclusion" wire:bind:disabled="disabledAi "
                        class="inline-flex items-center rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-2.5 text-sm font-medium text-emerald-800 shadow-sm transition hover:bg-emerald-100 disabled:cursor-not-allowed disabled:opacity-60 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-200 dark:hover:bg-emerald-950">
                        <span wire:loading.remove wire:target="generateConclusion">
                            Analisis AI
                        </span>
                        <span wire:loading wire:target="generateConclusion">
                            Menganalisis...
                        </span>
                    </button>
                @endif
            </div>
            @if ($results)
                @php
                    $rankedResults = collect($results)->values();
                @endphp

                <div class="grid gap-4">
                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Hasil TOPSIS Terbaik
                        </div>
                        <div class="mt-2 text-lg font-semibold text-slate-900 dark:text-slate-100">
                            {{ $rankedResults->first()['name'] ?? '-' }}
                        </div>
                        <div class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                            Skor TOPSIS: {{ number_format($rankedResults->first()['score'] ?? 0, 4) }}
                        </div>
                    </div>
                </div>

                <div class="grid gap-6 xl:grid-cols-3">
                    <div
                        class="rounded-2xl border border-slate-200 bg-white shadow-sm xl:col-span-2 dark:border-slate-800 dark:bg-slate-900">
                        <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                            <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100">Hasil Ranking</h2>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                                Semakin besar nilai preferensi, semakin dekat alternatif pada solusi ideal positif.
                            </p>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                                <thead class="bg-slate-50 dark:bg-slate-800/70">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200">
                                            Rank</th>
                                        <th
                                            class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200">
                                            Alternatif</th>
                                        <th
                                            class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200">
                                            D+</th>
                                        <th
                                            class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200">
                                            D-</th>
                                        <th
                                            class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200">
                                            Score</th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="divide-y divide-slate-100 bg-white dark:divide-slate-800 dark:bg-slate-900">
                                    @foreach ($rankedResults as $index => $r)
                                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/60">
                                            <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">
                                                {{ $index + 1 }}</td>
                                            <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                                                {{ $r['name'] }}</td>
                                            <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                                                {{ number_format($r['d_plus'], 4) }}</td>
                                            <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                                                {{ number_format($r['d_minus'], 4) }}</td>
                                            <td class="px-4 py-3 font-semibold text-slate-900 dark:text-slate-100">
                                                {{ number_format($r['score'], 4) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">Ringkasan Hasil</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
                            Nilai preferensi dihitung dengan rumus:
                            <span class="font-medium text-slate-800 dark:text-slate-200">V<sub>i</sub> = D<sub>-</sub>
                                /
                                (D<sub>+</sub> + D<sub>-</sub>)</span>.
                        </p>

                        <div class="mt-5 space-y-3">
                            <div
                                class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-800 dark:bg-slate-950">
                                <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Alternatif terbaik</div>
                                <div class="mt-1 text-base font-semibold text-slate-900 dark:text-slate-100">
                                    {{ $rankedResults->first()['name'] ?? '-' }}
                                </div>
                            </div>
                            <div
                                class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-800 dark:bg-slate-950">
                                <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Skor
                                    tertinggi</div>
                                <div class="mt-1 text-base font-semibold text-slate-900 dark:text-slate-100">
                                    {{ number_format($rankedResults->first()['score'] ?? 0, 4) }}
                                </div>
                            </div>
                            <div
                                class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-800 dark:bg-slate-950">
                                <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Jumlah
                                    alternatif</div>
                                <div class="mt-1 text-base font-semibold text-slate-900 dark:text-slate-100">
                                    {{ $rankedResults->count() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div
                        class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                            <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100">Normalisasi SAW
                                (Rij)</h2>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                                Nilai awal setiap alternatif setelah disesuaikan dengan tipe benefit atau cost.
                            </p>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                                <thead class="bg-slate-50 dark:bg-slate-800/70">
                                    <tr>
                                        <th
                                            class="sticky left-0 z-10 bg-slate-50 px-4 py-3 text-left font-semibold text-slate-700 dark:bg-slate-800/70 dark:text-slate-200">
                                            Alternatif
                                        </th>
                                        @foreach ($criteriaItems as $crit)
                                            <th
                                                class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200">
                                                {{ $crit['code'] }}
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody
                                    class="divide-y divide-slate-100 bg-white dark:divide-slate-800 dark:bg-slate-900">
                                    @foreach ($alternativeItems as $altIndex => $alt)
                                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/60">
                                            <td
                                                class="sticky left-0 z-10 bg-white px-4 py-3 font-medium text-slate-900 dark:bg-slate-900 dark:text-slate-100">
                                                {{ $alt['name'] }}
                                            </td>
                                            @foreach ($criteriaItems as $critIndex => $crit)
                                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                                                    {{ number_format(data_get($sawNormalizedMatrix, $altIndex . '.' . $critIndex, 0), 4) }}
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                            <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100">Matriks Bobot SAW
                                (Wij)</h2>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                                Hasil normalisasi SAW yang sudah dikalikan bobot kriteria.
                            </p>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                                <thead class="bg-slate-50 dark:bg-slate-800/70">
                                    <tr>
                                        <th
                                            class="sticky left-0 z-10 bg-slate-50 px-4 py-3 text-left font-semibold text-slate-700 dark:bg-slate-800/70 dark:text-slate-200">
                                            Alternatif
                                        </th>
                                        @foreach ($criteriaItems as $crit)
                                            <th
                                                class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200">
                                                {{ $crit['code'] }}
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody
                                    class="divide-y divide-slate-100 bg-white dark:divide-slate-800 dark:bg-slate-900">
                                    @foreach ($alternativeItems as $altIndex => $alt)
                                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/60">
                                            <td
                                                class="sticky left-0 z-10 bg-white px-4 py-3 font-medium text-slate-900 dark:bg-slate-900 dark:text-slate-100">
                                                {{ $alt['name'] }}
                                            </td>
                                            @foreach ($criteriaItems as $critIndex => $crit)
                                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                                                    {{ number_format(data_get($sawWeightedMatrix, $altIndex . '.' . $critIndex, 0), 4) }}
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                            <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100">Hasil Ranking TOPSIS
                            </h2>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                                Semakin besar nilai preferensi, semakin dekat alternatif pada solusi ideal positif.
                            </p>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                                <thead class="bg-slate-50 dark:bg-slate-800/70">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200">
                                            Rank</th>
                                        <th
                                            class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200">
                                            Alternatif</th>
                                        <th
                                            class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200">
                                            D+</th>
                                        <th
                                            class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200">
                                            D-</th>
                                        <th
                                            class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200">
                                            Score</th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="divide-y divide-slate-100 bg-white dark:divide-slate-800 dark:bg-slate-900">
                                    @foreach ($rankedResults as $index => $r)
                                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/60">
                                            <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">
                                                {{ $index + 1 }}</td>
                                            <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                                                {{ $r['name'] }}</td>
                                            <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                                                {{ number_format($r['d_plus'], 4) }}</td>
                                            <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                                                {{ number_format($r['d_minus'], 4) }}</td>
                                            <td class="px-4 py-3 font-semibold text-slate-900 dark:text-slate-100">
                                                {{ number_format($r['score'], 4) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                @if ($aiConclusion)
                    <div
                        class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm dark:border-emerald-900 dark:bg-emerald-950/30">

                        <h3 class="text-base font-semibold text-emerald-900 dark:text-emerald-100">
                            Hasilil Analisis & Kesimpulan AI
                        </h3>

                        <p class="prose prose-sm mt-3 max-w-none dark:prose-invert">
                            {!! \Illuminate\Support\Str::markdown($aiConclusion) !!}
                        </p>

                    </div>
                @endif

            @endif
        @else
            <div
                class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100">Pilih perhitungan terlebih
                    dahulu</h2>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                    Setelah memilih perhitungan, tabel input hybrid dan seluruh detail hasil SAW serta TOPSIS akan
                    muncul di bawah.
                </p>
            </div>
        @endif
    </div>
</x-filament::page>
