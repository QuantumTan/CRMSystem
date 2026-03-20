<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\Activity;
use App\Models\FollowUp;

class DashboardController extends Controller
{
    public function index()
    {
        $data = [
            'totalCustomers'      => Customer::count(),
            'totalActiveLeads'    => Lead::where('status', '!=', 'won')->where('status', '!=', 'lost')->count(),
            'completedFollowUps'  => FollowUp::where('status', 'completed')->count(),
            'recentActivities'    => Activity::with(['customer', 'lead', 'user'])->latest()->take(5)->get(),
            'upcomingFollowUps'   => FollowUp::where('status', 'pending')->orderBy('due_date')->take(5)->get(),
        ];

        return view('dashboard.index', compact('data'));
    }
}