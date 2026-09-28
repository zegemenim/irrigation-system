<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\BuildRelaySyncResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\SyncRelayRequest;
use Illuminate\Http\JsonResponse;

class RelayApiController extends Controller
{
    public function __invoke(SyncRelayRequest $request, BuildRelaySyncResponse $buildResponse): JsonResponse
    {
        return response()->json($buildResponse->execute($request->validated()));
    }
}
