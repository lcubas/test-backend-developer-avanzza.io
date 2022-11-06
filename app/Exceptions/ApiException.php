<?php

namespace App\Exceptions;

use Exception;

class ApiException extends Exception
{
    /**
     * Render the exception into an HTTP response.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        $error = [
            'code' => $this->code,
            'message' => $this->message,
        ];

        return response()->json(['error' => $error], $this->code);
    }
}
