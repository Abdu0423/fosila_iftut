<?php

namespace App\Http;

use Illuminate\Http\JsonResponse;

class SpaResponse extends JsonResponse
{
    public function toResponse($request)
    {
        return $this;
    }
}
