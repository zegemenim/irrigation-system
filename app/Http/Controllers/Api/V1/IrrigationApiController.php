<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\BuildIrrigationSyncResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\SyncIrrigationRequest;
use Illuminate\Http\JsonResponse;

class IrrigationApiController extends Controller
{
    public function __invoke(SyncIrrigationRequest $request, BuildIrrigationSyncResponse $buildResponse): JsonResponse
    {
        return response()->json($buildResponse->execute($request->validated()));
    }
}
