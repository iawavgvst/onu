<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OnuController extends Controller
{
    protected $service;

    public function __construct(OnuService $service) // надо будет отделить бизнес-логику
    {
        $this->service = $service;
    }

    public function loadData(): \Illuminate\Http\JsonResponse
    {
        try {
            $parsedData = $this->service->fetchAndParseData();
            $this->service->saveToJson($parsedData);
            return response()->json($parsedData);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
