<?php

namespace App\Http\Controllers;

use App\Actions\ExtractPlaintTextFromJsonAction;
use App\Actions\MountPagesDataFromDigitalizationBatch;
use App\Models\Digitalization;
use App\Models\DigitalizationBatch;
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
        $digitalizationBatches = $user->digitalizationBatches()->paginate(8);

        return view('auth.user-dashboard', compact('digitalizationBatches'));
    }

    public function show(DigitalizationBatch $digitalizationBatch)
    {
        if (!auth()->user()->can('view', $digitalizationBatch)) {
            abort(403, 'Unauthorized');
        }

        $pages = (new MountPagesDataFromDigitalizationBatch)->execute($digitalizationBatch);

        return view('scan-result', compact('pages', 'digitalizationBatch'));

    }
}
