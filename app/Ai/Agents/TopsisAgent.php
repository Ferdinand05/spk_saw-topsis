<?php

namespace App\Ai\Agents;

use Laravel\Ai\Ai;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Responses\AgentResponse;
use Stringable;


#[Temperature(0.3)]
#[Timeout(120)]
class TopsisAgent implements Agent, Conversational, HasTools
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return "Anda adalah Pakar Sistem Pendukung Keputusan (SPK) yang membantu mahasiswa dalam menganalisis hasil pemilihan dan perankingan di PT Nilosa Rama Buana. Tugas Anda adalah menginterpretasikan data hasil hybrid SAW + TOPSIS menjadi rekomendasi manajerial yang mudah dipahami oleh pimpinan perusahaan.";
    }

    /**
     * Build a prompt that contains TOPSIS calculation data in a safe JSON payload.
     */
    public function buildConclusionPrompt(
        array $calculation,
        array $criteria,
        array $alternatives,
        array $results,
        array $sawResults,
        array $matrices = [],
    ): string {

        $kasus = $calculation["name"];
        $payload = [
            'calculation' => $calculation,
            'criteria' => $criteria,
            'alternatives' => $alternatives,
            'results' => $results,
            'matrices' => $matrices,
            'saw_results' => $sawResults
        ];

        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '{}';

        return <<<PROMPT
            Berikan kesimpulan profesional sebagai sistem pendukung keputusan untuk kasus: "{$kasus}".

            Data Hasil Perhitungan:
            {$json}

            Instruksi:
            1. Berikan penjelasan mengapa alternatif peringkat 1 terpilih berdasarkan alur Hybrid SAW-TOPSIS.
            2. Jelaskan pengaruh kriteria dengan bobot terbesar terhadap hasil akhir secara logis sesuai konteks "{$kasus}".
            3. Sebutkan alternatif terbaik sebagai rekomendasi utama dan peringkat kedua sebagai cadangan.
            4. Gunakan bahasa Indonesia yang formal, ringkas, dan mudah dimengerti oleh pengambil keputusan.

            Format Output (Markdown):
            **Rekomendasi Utama**
            <Isi berdasarkan alternatif terbaik>

            <space>

            **Analisis Faktor Penentu**
            <Isi berdasarkan kriteria yang paling berpengaruh>

            <space>

            **Saran Operasional**
            <Isi saran praktis sesuai konteks {$kasus}>

            Batasan: Maksimal 150 - 250 kata, dilarang menampilkan rumus, dilarang mengarang angka di luar data, dilarang memberikan data data crusial terkait sistem / database.
            PROMPT;
    }

    /**
     * Ask Gemini to write a conclusion from TOPSIS data.
     */
    public function conclude(
        array $calculation,
        array $criteria,
        array $alternatives,
        array $results,
        array $sawResults,
        array $matrices = [],
    ): AgentResponse {
        return $this->prompt(
            $this->buildConclusionPrompt($calculation, $criteria, $alternatives, $results, $matrices, $sawResults),
            provider: [Lab::Gemini, Lab::Groq],
        );
    }

    /**
     * Get the list of messages comprising the conversation so far.
     *
     * @return Message[]
     */
    public function messages(): iterable
    {
        return [];
    }

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [];
    }
}
