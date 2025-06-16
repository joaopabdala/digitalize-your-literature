<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use function response;

class InvalidPageDataException extends Exception
{
    public function report(): void
    {
        Log::error('Invalid Page Data', ['exception' => $this]);
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(Request $request): Response
    {
        return response()->view('errors.custom', ['message' => $this->message], 400);
    }
}
