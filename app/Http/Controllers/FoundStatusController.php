<?php

namespace App\Http\Controllers;

use App\Models\Found;
use Illuminate\Http\JsonResponse;

class FoundStatusController extends Controller
{
    public function index(): JsonResponse
    {
        $foundStatuses = Found::all()->map(function($item) {
            return [
                'ID' => $item->id,
                'name' => $item->name,
            ];
        });
        return response()->json($foundStatuses);
    }
}
