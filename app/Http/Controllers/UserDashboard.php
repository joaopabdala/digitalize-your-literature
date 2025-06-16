<?php

namespace App\Http\Controllers;

use App\Actions\MountPagesDataFromDigitalizationBatchAction;
use App\Models\DigitalizationBatch;
use function abort;
use function auth;
use function compact;
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

        $pages = (new MountPagesDataFromDigitalizationBatchAction)->execute($digitalizationBatch);
        return view('scan-result', compact('pages', 'digitalizationBatch'));
    }
}
