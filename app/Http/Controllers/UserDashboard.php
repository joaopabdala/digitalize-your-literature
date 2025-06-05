<?php

namespace App\Http\Controllers;

use App\Actions\ExtractPlaintTextFromJsonAction;
use App\Models\Digitalization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use function abort;
use function auth;
use function compact;
use function is_array;
use function str_replace;
use function trim;
use function view;

class UserDashboard extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $digitalizations = $user->digitalizations()->paginate(8);

        return view('auth.user-dashboard', compact('digitalizations'));
    }

    public function show(Digitalization $digitalization)
    {
        if (!auth()->user()->can('view', $digitalization)) {
            abort(403, 'Unauthorized');
        }

        $imageUrl = Storage::url($digitalization->original_file_path);
        $transcriptionPath = $digitalization->transcription_file_path;
        $jsonContent = Storage::disk('public')->get($transcriptionPath);
        $pageData = json_decode($jsonContent, true);

        $pageData = $pageData['page'];
        $plainText = (new ExtractPlaintTextFromJsonAction)->execute($pageData);

        return view('scan-result', compact('pageData', 'plainText', 'imageUrl', 'digitalization'));

    }
}
