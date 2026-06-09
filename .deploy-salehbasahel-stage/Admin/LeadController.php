<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Services\OpenClawLeadImporter;
use App\Services\LeadScoringService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;
use Throwable;

class LeadController extends Controller
{
    public function index(Request $request, OpenClawLeadImporter $importer): View
    {
        try {
            $importedCount = $importer->import();
            $query = Lead::query();

            if ($search = trim((string) $request->query('search'))) {
                $query->where(function ($builder) use ($search) {
                    $builder
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('country', 'like', "%{$search}%")
                        ->orWhere('interest', 'like', "%{$search}%")
                        ->orWhere('lead_category', 'like', "%{$search}%")
                        ->orWhere('lead_subcategory', 'like', "%{$search}%")
                        ->orWhere('lead_detail_option', 'like', "%{$search}%")
                        ->orWhere('preferred_time_interest', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%")
                        ->orWhere('conversation_history', 'like', "%{$search}%");
                });
            }

            if ($source = trim((string) $request->query('source'))) {
                $query->where('source', $source);
            }

            if ($status = trim((string) $request->query('status'))) {
                $query->where('status', $status);
            }

            if ($temperature = trim((string) $request->query('temperature'))) {
                $query->where('lead_temperature', $temperature);
            }

            if ($meeting = trim((string) $request->query('meeting'))) {
                $query->where('meeting_status', $meeting);
            }

            if ($pipeline = trim((string) $request->query('pipeline'))) {
                match ($pipeline) {
                    'new' => $query->where('status', 'new'),
                    'qualified' => $query->whereIn('lead_temperature', ['warm', 'hot']),
                    'meeting_scheduled' => $query->where('meeting_status', 'scheduled'),
                    'follow_up_needed' => $query->whereNotNull('next_follow_up_at'),
                    'converted' => $query->whereNotNull('converted_at'),
                    default => null,
                };
            }

            $sort = (string) $request->query('sort', 'created_at');
            $direction = strtolower((string) $request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
            $allowedSorts = ['created_at', 'name', 'phone', 'email', 'source', 'status', 'lead_score', 'meeting_scheduled_at'];

            if (! in_array($sort, $allowedSorts, true)) {
                $sort = 'created_at';
            }

            $query->orderBy($sort, $direction);

            return view('admin.leads', [
                'leads' => $query->paginate(20)->withQueryString(),
                'search' => $search ?? '',
                'source' => $source ?? '',
                'status' => $status ?? '',
                'temperature' => $temperature ?? '',
                'meeting' => $meeting ?? '',
                'pipeline' => $pipeline ?? '',
                'sort' => $sort,
                'direction' => $direction,
                'sources' => Lead::query()->select('source')->whereNotNull('source')->distinct()->orderBy('source')->pluck('source'),
                'statuses' => Lead::query()->select('status')->whereNotNull('status')->distinct()->orderBy('status')->pluck('status'),
                'pipelineCounts' => $this->pipelineCounts(),
                'importedCount' => $importedCount,
                'dbError' => null,
            ]);
        } catch (Throwable $exception) {
            return view('admin.leads', [
                'leads' => collect(),
                'search' => trim((string) $request->query('search')),
                'source' => trim((string) $request->query('source')),
                'status' => trim((string) $request->query('status')),
                'temperature' => trim((string) $request->query('temperature')),
                'meeting' => trim((string) $request->query('meeting')),
                'pipeline' => trim((string) $request->query('pipeline')),
                'sort' => 'created_at',
                'direction' => 'desc',
                'sources' => collect(),
                'statuses' => collect(),
                'pipelineCounts' => collect(),
                'importedCount' => 0,
                'dbError' => 'Database is not connected yet. Start MySQL and run migrations to show collected leads here.',
            ]);
        }
    }

    public function show(Lead $lead): View
    {
        $lead->load(['communications' => fn ($query) => $query->oldest('created_at')]);

        return view('admin.lead-show', ['lead' => $lead]);
    }

    public function update(Request $request, Lead $lead, LeadScoringService $scoring): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:160'],
            'country' => ['nullable', 'string', 'max:120'],
            'occupation' => ['nullable', 'string', 'max:160'],
            'interest' => ['nullable', 'string', 'max:120'],
            'lead_category' => ['nullable', 'string', 'max:120'],
            'lead_subcategory' => ['nullable', 'string', 'max:160'],
            'lead_detail_option' => ['nullable', 'string', 'max:180'],
            'preferred_time_interest' => ['nullable', 'string', 'max:160'],
            'status' => ['required', 'string', 'max:40'],
            'meeting_status' => ['required', 'string', 'max:40'],
            'meeting_scheduled_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:10000'],
            'consent' => ['nullable', 'boolean'],
        ]);

        $validated['consent'] = $request->boolean('consent');
        $lead->fill($validated);
        $lead->converted_at = $lead->status === 'converted' ? ($lead->converted_at ?: now()) : null;
        $scoring->assess($lead)->save();

        return back()->with('status', 'Lead updated successfully.');
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        if ($lead->external_id && Schema::hasTable('lead_import_tombstones')) {
            DB::table('lead_import_tombstones')->updateOrInsert(
                ['external_id' => $lead->external_id],
                ['source' => $lead->source, 'deleted_at' => now()]
            );
        }

        $lead->delete();

        return redirect()
            ->route('admin.leads.index')
            ->with('status', 'Lead deleted successfully.');
    }

    public function export(Request $request): StreamedResponse
    {
        $fileName = 'saleh-basahel-leads-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function (): void {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Created', 'Name', 'Phone', 'Email', 'Country', 'Occupation', 'Source', 'Main Category', 'Subcategory', 'Detailed Option', 'Preferred Time / Interest', 'Interest', 'Score', 'Temperature', 'Status', 'Meeting Status', 'Meeting Time', 'Notes']);

            Lead::query()->latest()->chunk(250, function ($leads) use ($output): void {
                foreach ($leads as $lead) {
                    fputcsv($output, [
                        $lead->created_at?->toIso8601String(),
                        $lead->name,
                        $lead->phone,
                        $lead->email,
                        $lead->country,
                        $lead->occupation,
                        $lead->source_label,
                        $lead->lead_category,
                        $lead->lead_subcategory,
                        $lead->lead_detail_option,
                        $lead->preferred_time_interest,
                        $lead->interest,
                        $lead->lead_score,
                        $lead->temperature_label,
                        $lead->status_label,
                        $lead->meeting_status_label,
                        $lead->meeting_scheduled_at?->toIso8601String(),
                        $lead->notes,
                    ]);
                }
            });

            fclose($output);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }

    private function pipelineCounts(): array
    {
        return [
            'all' => Lead::count(),
            'new' => Lead::where('status', 'new')->count(),
            'qualified' => Lead::whereIn('lead_temperature', ['warm', 'hot'])->count(),
            'meeting_scheduled' => Lead::where('meeting_status', 'scheduled')->count(),
            'follow_up_needed' => Lead::whereNotNull('next_follow_up_at')->count(),
            'converted' => Lead::whereNotNull('converted_at')->count(),
        ];
    }
}
