<x-admin.layout title="Dashboard" active="dashboard">
    @if (! empty($dbError))
        <div class="notice">{{ $dbError }}</div>
    @endif

    <div class="grid stats">
        <a class="card pad stat" href="{{ route('admin.leads.index') }}"><strong>{{ number_format($totalLeads) }}</strong><span>Total leads</span></a>
        <a class="card pad stat" href="{{ route('admin.leads.index', ['pipeline' => 'new']) }}"><strong>{{ number_format($todayLeads) }}</strong><span>New today</span></a>
        <a class="card pad stat" href="{{ route('admin.leads.index', ['pipeline' => 'qualified']) }}"><strong>{{ number_format($qualifiedLeads ?? 0) }}</strong><span>Qualified leads</span></a>
        <a class="card pad stat" href="{{ route('admin.leads.index', ['pipeline' => 'meeting_scheduled']) }}"><strong>{{ number_format($scheduledLeads ?? 0) }}</strong><span>Meetings scheduled</span></a>
    </div>

    <div class="card pad">
        <div class="lead-header">
            <div><p class="eyebrow">Latest Activity</p><h2>Recent leads</h2></div>
            <a class="btn btn-light" href="{{ route('admin.leads.index') }}">View pipeline</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Date</th><th>Name</th><th>Contact</th><th>Source</th><th>Interest</th><th>Status</th><th>Score</th><th></th></tr></thead>
                <tbody>
                    @forelse ($recentLeads as $lead)
                        <tr>
                            <td>{{ $lead->created_at?->format('M d, Y H:i') }}</td>
                            <td><strong>{{ $lead->name }}</strong><br><span class="muted">{{ $lead->country }}</span></td>
                            <td>{{ $lead->phone ?: '-' }}<br><span class="muted">{{ $lead->email }}</span></td>
                            <td>{{ $lead->source_label }}</td>
                            <td>{{ $lead->interest ?: '-' }}</td>
                            <td>{{ $lead->status_label }}</td>
                            <td>{{ $lead->lead_score }} / 100<br><span class="muted">{{ $lead->temperature_label }}</span></td>
                            <td><a class="text-link" href="{{ route('admin.leads.show', $lead) }}">Open</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="muted">No leads collected yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid stats" style="margin-top: 22px;">
        <a class="card pad stat" href="{{ route('admin.leads.index', ['source' => 'whatsapp']) }}"><strong>{{ number_format($whatsappLeads ?? 0) }}</strong><span>WhatsApp leads</span></a>
        <a class="card pad stat" href="{{ route('admin.leads.index', ['pipeline' => 'follow_up_needed']) }}"><strong>{{ number_format($followUpLeads ?? 0) }}</strong><span>Follow-up needed</span></a>
        <div class="card pad stat"><strong>{{ $recentLeads->first()?->created_at?->diffForHumans() ?? 'No leads yet' }}</strong><span>Latest activity</span></div>
    </div>
</x-admin.layout>
