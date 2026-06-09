<x-admin.layout title="Leads Management" active="leads">
    @php
        $sortUrl = function (string $column) use ($sort, $direction) {
            $nextDirection = $sort === $column && $direction === 'asc' ? 'desc' : 'asc';

            return route('admin.leads.index', array_merge(request()->query(), [
                'sort' => $column,
                'direction' => $nextDirection,
            ]));
        };

        $sortIndicator = fn (string $column): string => $sort === $column ? ($direction === 'asc' ? ' (asc)' : ' (desc)') : '';
    @endphp

    @if (! empty($dbError))
        <div class="notice">{{ $dbError }}</div>
    @endif

    @if (($importedCount ?? 0) > 0)
        <div class="notice">{{ $importedCount }} OpenClaw backup lead{{ $importedCount === 1 ? '' : 's' }} imported into the database.</div>
    @endif

    <div class="card pad lead-manager">
        <div class="lead-header">
            <div>
                <p class="eyebrow">Unified Pipeline</p>
                <h2>All collected leads in one place</h2>
                <p class="muted">Landing page, WhatsApp, OpenClaw backups, and future lead sources are managed from the same durable database.</p>
            </div>
            <div class="live-pill" title="This page refreshes automatically while open.">
                <span></span>
                Live refresh
            </div>
        </div>

        <div class="pipeline-tabs" aria-label="Lead pipeline">
            @foreach ([
                '' => 'All Leads',
                'new' => 'New Leads',
                'qualified' => 'Qualified',
                'meeting_scheduled' => 'Meeting Scheduled',
                'follow_up_needed' => 'Follow Up Needed',
                'converted' => 'Converted',
            ] as $pipelineKey => $pipelineLabel)
                <a class="{{ $pipeline === $pipelineKey ? 'active' : '' }}" href="{{ route('admin.leads.index', array_filter(['pipeline' => $pipelineKey])) }}">
                    {{ $pipelineLabel }}
                    <strong>{{ $pipelineCounts[$pipelineKey ?: 'all'] ?? 0 }}</strong>
                </a>
            @endforeach
        </div>

        <form class="filters" method="get" action="{{ route('admin.leads.index') }}">
            <input type="search" name="search" value="{{ $search }}" placeholder="Search name, phone, email, country, category, option, message">

            <select name="source" aria-label="Filter by source">
                <option value="">All sources</option>
                @foreach ($sources as $sourceOption)
                    <option value="{{ $sourceOption }}" @selected($source === $sourceOption)>{{ match ($sourceOption) {
                        'whatsapp' => 'WhatsApp',
                        'landing_page', 'website' => 'Landing Page',
                        default => ucfirst((string) $sourceOption),
                    } }}</option>
                @endforeach
            </select>

            <select name="status" aria-label="Filter by status">
                <option value="">All statuses</option>
                @foreach ($statuses as $statusOption)
                    <option value="{{ $statusOption }}" @selected($status === $statusOption)>{{ str((string) $statusOption)->replace('_', ' ')->title() }}</option>
                @endforeach
            </select>

            <select name="temperature" aria-label="Filter by lead temperature">
                <option value="">All temperatures</option>
                @foreach (['hot', 'warm', 'cold'] as $temperatureOption)
                    <option value="{{ $temperatureOption }}" @selected($temperature === $temperatureOption)>{{ ucfirst($temperatureOption) }}</option>
                @endforeach
            </select>

            <select name="meeting" aria-label="Filter by meeting status">
                <option value="">All meetings</option>
                @foreach (['not_scheduled', 'scheduled', 'completed', 'cancelled'] as $meetingOption)
                    <option value="{{ $meetingOption }}" @selected($meeting === $meetingOption)>{{ str($meetingOption)->replace('_', ' ')->title() }}</option>
                @endforeach
            </select>

            <select name="sort" aria-label="Sort leads">
                <option value="created_at" @selected($sort === 'created_at')>Date created</option>
                <option value="name" @selected($sort === 'name')>Name</option>
                <option value="source" @selected($sort === 'source')>Source</option>
                <option value="status" @selected($sort === 'status')>Status</option>
                <option value="lead_score" @selected($sort === 'lead_score')>Lead score</option>
            </select>

            <select name="direction" aria-label="Sort direction">
                <option value="desc" @selected($direction === 'desc')>Newest / Z-A</option>
                <option value="asc" @selected($direction === 'asc')>Oldest / A-Z</option>
            </select>

            <button class="btn" type="submit">Apply</button>
            <a class="btn btn-light" href="{{ route('admin.leads.export') }}">Export CSV</a>
            @if ($search || $source || $status || $temperature || $meeting || $pipeline || $sort !== 'created_at' || $direction !== 'desc')
                <a class="btn btn-light" href="{{ route('admin.leads.index') }}">Clear</a>
            @endif
        </form>

        <div class="table-wrap">
            <table class="lead-table">
                <thead>
                    <tr>
                        <th><a href="{{ $sortUrl('created_at') }}">Created{{ $sortIndicator('created_at') }}</a></th>
                        <th><a href="{{ $sortUrl('name') }}">Lead Name{{ $sortIndicator('name') }}</a></th>
                        <th>Contact</th>
                        <th><a href="{{ $sortUrl('source') }}">Source{{ $sortIndicator('source') }}</a></th>
                        <th><a href="{{ $sortUrl('status') }}">Status{{ $sortIndicator('status') }}</a></th>
                        <th><a href="{{ $sortUrl('lead_score') }}">Score{{ $sortIndicator('lead_score') }}</a></th>
                        <th>Meeting</th>
                        <th>Main Category</th>
                        <th>Subcategory</th>
                        <th>Detailed Option</th>
                        <th>Preferred Time</th>
                        <th>Interest</th>
                        <th>Conversation</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($leads as $lead)
                        <tr>
                            <td><strong>{{ $lead->created_at?->format('M d, Y') }}</strong><span class="muted block">{{ $lead->created_at?->format('H:i') }}</span></td>
                            <td><strong>{{ $lead->name ?: 'Unknown Lead' }}</strong>@if ($lead->country)<span class="muted block">{{ $lead->country }}</span>@endif</td>
                            <td>{{ $lead->phone ?: '-' }}<span class="muted block small">{{ $lead->email ?: '' }}</span></td>
                            <td><span class="source-badge source-{{ str($lead->source)->slug() }}">{{ $lead->source_label }}</span>@if ($lead->source_detail)<span class="muted block small">{{ $lead->source_detail }}</span>@endif</td>
                            <td><span class="status-badge">{{ $lead->status_label }}</span></td>
                            <td><span class="temperature-badge temperature-{{ $lead->lead_temperature }}">{{ $lead->temperature_label }}</span><span class="muted block small">{{ $lead->lead_score }} / 100</span></td>
                            <td><strong>{{ $lead->meeting_status_label }}</strong>@if ($lead->meeting_scheduled_at)<span class="muted block small">{{ $lead->meeting_scheduled_at->format('M d, Y H:i') }}</span>@endif</td>
                            <td><strong>{{ $lead->lead_category ?: ($lead->interest ?: '-') }}</strong></td>
                            <td>{{ $lead->lead_subcategory ?: '-' }}</td>
                            <td>{{ $lead->lead_detail_option ?: '-' }}</td>
                            <td>{{ $lead->preferred_time_interest ?: '-' }}</td>
                            <td>{{ $lead->interest ?: '-' }}</td>
                            <td class="conversation-cell">
                                @if ($lead->conversation_history || $lead->message)
                                    <details><summary>View conversation</summary><pre>{{ $lead->conversation_history ?: $lead->message }}</pre></details>
                                @else
                                    <span class="muted">No conversation saved</span>
                                @endif
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a class="btn btn-small" href="{{ route('admin.leads.show', $lead) }}">Manage</a>
                                    <form method="post" action="{{ route('admin.leads.destroy', $lead) }}" onsubmit="return confirm('Delete this lead permanently? This action cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-small btn-danger" type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="14" class="muted">No leads found yet. New landing page or WhatsApp leads will appear here automatically after they are saved.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if (method_exists($leads, 'links'))
            <div class="pagination-wrap">{{ $leads->links() }}</div>
        @endif
    </div>

    <script>
        window.setInterval(() => {
            const active = document.activeElement;
            const isEditing = active && ['INPUT', 'SELECT', 'TEXTAREA'].includes(active.tagName);

            if (document.visibilityState === 'visible' && !isEditing) {
                window.location.reload();
            }
        }, 30000);
    </script>
</x-admin.layout>
