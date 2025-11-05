<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UploadController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {

        if ($request->hasFile('file')) {
            $files = $request->file('file');
            $folder = uniqid() . '-' . now()->timestamp;
            foreach ($files as $file) {
                $fileName = $file->getClientOriginalName();
                $path = 'tmp_files/' . $folder . '/' . $fileName;
                $file->storeAs($path);

                Log::info($path);
                return $path;
            }
        }

        return '';
    }
}
