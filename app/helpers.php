<?php

use App\Http\SpaResponse;

if (! function_exists('spa')) {
    /**
     * JSON-ответ страницы для Vue SPA.
     */
    function spa(string $component, array $props = [], int $status = 200): SpaResponse
    {
        return new SpaResponse([
            'component' => $component,
            'props' => $props,
        ], $status);
    }
}
