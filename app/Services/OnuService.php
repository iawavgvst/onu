<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

class OnuService
{
    protected string $token;

    public function __construct()
    {
        $this->token = env('ONU_API_TOKEN');
    }

    public function fetchAndParseData(): array
    {
        $onuDataResponse = Http::withToken($this->token)
            ->get('https://exsrv.asarta.ru/api/test-task/get_onu_data.php');

        $onuStatsResponse = Http::withToken($this->token)
            ->get('https://exsrv.asarta.ru/api/test-task/get_onu_stats.php');

        Log::info('Ответ ONU Stats: ' . $onuStatsResponse->body());

        $onuDataBody = $onuDataResponse->body();
        $onuStatsBody = $onuStatsResponse->body();

        $onuData = $this->parseTextResponse($onuDataBody, 'data');
        $onuStats = $this->parseTextResponse($onuStatsBody, 'stats');

        $statsByInterface = [];
        foreach ($onuStats as $stat) {
            $statsByInterface[$stat['interface']] = $stat;
        }

        $finalData = [];
        foreach ($onuData as $item) {
            $interface = $item['interface'];
            $finalData[] = [
                'interface' => $interface,
                'data' => $item,
                'stats' => $statsByInterface[$interface] ?? null,
            ];
        }

        $this->saveToJson($finalData);

        return $finalData;
    }

    private function decodeResponse(string $response)
    {
        json_decode($response);
        if (json_last_error() === JSON_ERROR_NONE) {
            return json_decode($response, true);
        } else {
            return $this->parseTextResponse($response);
        }
    }

    private function parseTextResponse(string $response, string $type = 'data'): array
    {
        $lines = explode("\n", $response);
        $result = [];
        $dataStart = false;

        Log::debug('Ответ API статистики: ' . $response);

        foreach ($lines as $line) {
            $line = trim($line);
            if (!$dataStart) {
                if (strpos($line, 'IntfName') !== false || strpos($line, 'Temp(degree)') !== false) {
                    $dataStart = true;
                }
                continue;
            }
            if (empty($line)) continue;

            preg_match_all('/\S+/', $line, $matches);
            $parts = $matches[0];

            if ($type === 'data' && count($parts) >= 8) {
                $result[] = [
                    'interface' => $parts[0],
                    'vendor_id' => $parts[1],
                    'model_id' => $parts[2],
                    'sn' => $parts[3],
                    'loid' => $parts[4],
                    'status' => $parts[5],
                    'config_status' => $parts[6],
                    'active_time' => $parts[7],
                ];
            } elseif ($type === 'stats' && count($parts) >= 6) {
                $result[] = [
                    'interface' => $parts[0],
                    'temperature' => $parts[1],
                    'voltage' => $parts[2],
                    'bias' => $parts[3],
                    'rx_power' => $parts[4],
                    'tx_power' => $parts[5],
                ];
            } else {
                Log::warning('Некорректная строка: ' . $line);
            }
        }

        return $result;
    }

    public function saveToJson($data): void
    {
        try {
            $jsonData = json_encode($data, JSON_PRETTY_PRINT);
            if ($jsonData === false) {
                Log::error('Ошибка кодирования данных в JSON: ' . json_last_error_msg());
                throw new Exception('Ошибка кодирования данных в JSON.');
            }
            Storage::disk('public')->put('onu_data.json', $jsonData);
            Log::info('Данные успешно сохранены в onu_data.json');
        } catch (Exception $e) {
            Log::error('Не удалось сохранить данные в JSON: ' . $e->getMessage());
            throw $e;
        }
    }
}
