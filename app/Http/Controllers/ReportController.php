<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\Activity;
use App\Models\FollowUp;

class ReportController extends Controller
{
    public function index()
    {
        $data = [
            'totalCustomers'         => Customer::count(),
            'leadsByStatus'          => Lead::selectRaw('status, count(*) as total')->groupBy('status')->get(),
            'completedFollowUps'     => FollowUp::where('status', 'completed')->count(),
            'pendingFollowUps'       => FollowUp::where('status', 'pending')->count(),
            'activitiesByType'       => Activity::selectRaw('activity_type, count(*) as total')->groupBy('activity_type')->get(),
        ];

        return view('reports.index', compact('data'));
    }
}