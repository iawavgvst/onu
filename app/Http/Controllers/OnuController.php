<?php

namespace App\Http\Controllers;

use App\Services\OnuService;
use Illuminate\Support\Facades\Log;
use Exception;

class OnuController extends Controller
{
    protected OnuService $onuService;

    public function __construct(OnuService $onuService)
    {
        $this->onuService = $onuService;
    }

    public function loadData(): \Illuminate\Http\JsonResponse
    {
        try {
            $parsedData = $this->onuService->fetchAndParseData();
            $this->onuService->saveToJson($parsedData);
            return response()->json($parsedData);
        } catch (Exception $e) {
            Log::error('Ошибка при загрузке данных ONU: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
