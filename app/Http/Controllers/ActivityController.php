<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Http\Requests\StoreActivityRequest;
use Illuminate\Support\Facades\Auth;

class ActivityController extends Controller
{
    public function index()
    {
        $activities = Activity::with(['customer', 'lead', 'user'])->latest()->paginate(10);
        return view('activities.index', compact('activities'));
    }

    public function create()
    {
        return view('activities.create');
    }

    public function store(StoreActivityRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();

        Activity::create($data);
        return redirect()->route('activities.index')->with('success', 'Activity logged successfully.');
    }
}