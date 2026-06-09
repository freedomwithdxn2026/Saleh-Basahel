<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Services\OpenClawLeadImporter;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Throwable;

class DashboardController extends Controller
{
    public function __invoke(OpenClawLeadImporter $importer): View
    {
        try {
            $importer->import();

            return view('admin.dashboard', [
                'totalLeads' => Lead::count(),
                'todayLeads' => Lead::whereDate('created_at', now()->toDateString())->count(),
                'whatsappLeads' => Lead::where('source', 'whatsapp')->count(),
                'qualifiedLeads' => Lead::whereIn('lead_temperature', ['warm', 'hot'])->count(),
                'scheduledLeads' => Lead::where('meeting_status', 'scheduled')->count(),
                'followUpLeads' => Lead::whereNotNull('next_follow_up_at')->count(),
                'recentLeads' => Lead::latest()->limit(6)->get(),
                'dbError' => null,
            ]);
        } catch (Throwable) {
            return view('admin.dashboard', [
                'totalLeads' => 0,
                'todayLeads' => 0,
                'whatsappLeads' => 0,
                'qualifiedLeads' => 0,
                'scheduledLeads' => 0,
                'followUpLeads' => 0,
                'recentLeads' => new Collection(),
                'dbError' => 'Database is not connected yet. Start MySQL and run migrations to show live lead data here.',
            ]);
        }
    }
}
