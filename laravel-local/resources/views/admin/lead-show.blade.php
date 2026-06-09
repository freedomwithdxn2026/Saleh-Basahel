<x-admin.layout title="Manage Lead" active="leads">
    <div class="lead-detail-head">
        <a class="text-link" href="{{ route('admin.leads.index') }}">&larr; Back to leads</a>
        <div class="action-buttons">
            <span class="temperature-badge temperature-{{ $lead->lead_temperature }}">{{ $lead->temperature_label }}</span>
            <span class="status-badge">{{ $lead->lead_score }} / 100</span>
            <form method="post" action="{{ route('admin.leads.destroy', $lead) }}" onsubmit="return confirm('Delete this lead permanently? This action cannot be undone.')">
                @csrf
                @method('DELETE')
                <button class="btn btn-small btn-danger" type="submit">Delete Lead</button>
            </form>
        </div>
    </div>

    <div class="detail-grid">
        <form class="card pad" method="post" action="{{ route('admin.leads.update', $lead) }}">
            @csrf
            <p class="eyebrow">Lead Record</p>
            <h2 style="margin-top: 0;">{{ $lead->name }}</h2>

            <div class="form-grid">
                <div class="field"><label for="name">Full name</label><input id="name" name="name" value="{{ old('name', $lead->name) }}" required></div>
                <div class="field"><label for="phone">Phone</label><input id="phone" name="phone" value="{{ old('phone', $lead->phone) }}"></div>
                <div class="field"><label for="email">Email</label><input id="email" type="email" name="email" value="{{ old('email', $lead->email) }}"></div>
                <div class="field"><label for="country">Country</label><input id="country" name="country" value="{{ old('country', $lead->country) }}"></div>
                <div class="field"><label for="occupation">Occupation</label><input id="occupation" name="occupation" value="{{ old('occupation', $lead->occupation) }}"></div>
                <div class="field"><label for="interest">Interest</label><input id="interest" name="interest" value="{{ old('interest', $lead->interest) }}"></div>
                <div class="field"><label for="lead_category">Main category</label><input id="lead_category" name="lead_category" value="{{ old('lead_category', $lead->lead_category) }}"></div>
                <div class="field"><label for="lead_subcategory">Subcategory</label><input id="lead_subcategory" name="lead_subcategory" value="{{ old('lead_subcategory', $lead->lead_subcategory) }}"></div>
                <div class="field"><label for="lead_detail_option">Detailed option</label><input id="lead_detail_option" name="lead_detail_option" value="{{ old('lead_detail_option', $lead->lead_detail_option) }}"></div>
                <div class="field"><label for="preferred_time_interest">Preferred time / interest</label><input id="preferred_time_interest" name="preferred_time_interest" value="{{ old('preferred_time_interest', $lead->preferred_time_interest) }}"></div>
                <div class="field"><label for="status">Lead status</label><select id="status" name="status">@foreach (['new', 'qualified', 'follow_up_needed', 'meeting_scheduled', 'converted', 'closed'] as $option)<option value="{{ $option }}" @selected(old('status', $lead->status) === $option)>{{ str($option)->replace('_', ' ')->title() }}</option>@endforeach</select></div>
                <div class="field"><label for="meeting_status">Meeting status</label><select id="meeting_status" name="meeting_status">@foreach (['not_scheduled', 'scheduled', 'completed', 'cancelled'] as $option)<option value="{{ $option }}" @selected(old('meeting_status', $lead->meeting_status) === $option)>{{ str($option)->replace('_', ' ')->title() }}</option>@endforeach</select></div>
                <div class="field"><label for="meeting_scheduled_at">Meeting date and time</label><input id="meeting_scheduled_at" type="datetime-local" name="meeting_scheduled_at" value="{{ old('meeting_scheduled_at', $lead->meeting_scheduled_at?->format('Y-m-d\TH:i')) }}"></div>
                <label class="check-field"><input type="checkbox" name="consent" value="1" @checked(old('consent', $lead->consent))> Consent received for private follow-up</label>
            </div>

            <div class="field"><label for="notes">Admin notes</label><textarea id="notes" name="notes">{{ old('notes', $lead->notes) }}</textarea></div>
            <button class="btn" type="submit">Save Lead</button>
        </form>

        <aside class="grid detail-side">
            <div class="card pad">
                <p class="eyebrow">Automation</p>
                <dl class="detail-list">
                    <div><dt>Source</dt><dd>{{ $lead->source_label }}</dd></div>
                    <div><dt>Main category</dt><dd>{{ $lead->lead_category ?: '-' }}</dd></div>
                    <div><dt>Subcategory</dt><dd>{{ $lead->lead_subcategory ?: '-' }}</dd></div>
                    <div><dt>Detailed option</dt><dd>{{ $lead->lead_detail_option ?: '-' }}</dd></div>
                    <div><dt>Preferred time</dt><dd>{{ $lead->preferred_time_interest ?: '-' }}</dd></div>
                    <div><dt>Next follow-up</dt><dd>{{ $lead->next_follow_up_at?->format('M d, Y H:i') ?? 'Not scheduled' }}</dd></div>
                    <div><dt>Follow-up step</dt><dd>{{ $lead->follow_up_step }} / 4</dd></div>
                    <div><dt>Calendly</dt><dd>{{ str($lead->calendly_status)->replace('_', ' ')->title() }}</dd></div>
                    <div><dt>Created</dt><dd>{{ $lead->created_at?->format('M d, Y H:i') }}</dd></div>
                </dl>
            </div>
            <div class="card pad">
                <p class="eyebrow">Communication Summary</p>
                <dl class="detail-list">
                    <div><dt>Total records</dt><dd>{{ $lead->communications->count() }}</dd></div>
                    <div><dt>WhatsApp</dt><dd>{{ $lead->communications->where('channel', 'whatsapp')->count() }}</dd></div>
                    <div><dt>Email</dt><dd>{{ $lead->communications->where('channel', 'email')->count() }}</dd></div>
                    <div><dt>Failed / pending</dt><dd>{{ $lead->communications->whereIn('status', ['failed', 'pending'])->count() }}</dd></div>
                </dl>
            </div>
        </aside>
    </div>

    <section class="card pad communication-panel">
        <div class="communication-head">
            <div>
                <p class="eyebrow">Complete Communication History</p>
                <h2>Message timeline from day one</h2>
                <p class="muted">Welcome messages, conversations, guidance, follow-ups, reminders, emails, and delivery attempts appear here chronologically.</p>
            </div>
            <span class="status-badge">{{ $lead->communications->count() }} records</span>
        </div>

        @if ($lead->communications->isEmpty())
            <div class="communication-empty">
                <strong>No communication records yet.</strong>
                <span>New WhatsApp and email activity will appear here automatically.</span>
            </div>
        @else
            <div class="communication-timeline">
                @foreach ($lead->communications as $communication)
                    <article class="communication-item direction-{{ $communication->direction }} channel-{{ $communication->channel }} status-{{ $communication->status }}">
                        <div class="communication-marker" aria-hidden="true"></div>
                        <div class="communication-bubble">
                            <div class="communication-meta">
                                <div class="communication-tags">
                                    <span class="communication-channel">{{ str($communication->channel)->replace('_', ' ')->title() }}</span>
                                    <span>{{ str($communication->category)->replace('_', ' ')->title() }}</span>
                                    <span class="delivery-status">{{ str($communication->status)->title() }}</span>
                                </div>
                                <time>{{ $communication->occurred_at?->format('M d, Y H:i:s') }}</time>
                            </div>
                            @if ($communication->subject)
                                <strong class="communication-subject">{{ $communication->subject }}</strong>
                            @endif
                            <div class="communication-body">{{ $communication->body }}</div>
                            <div class="communication-foot">
                                <span>{{ $communication->direction === 'outbound' ? 'Sent by OpenClaw / team' : 'Received from lead' }}</span>
                                @if ($communication->recipient)
                                    <span>Recipient: {{ $communication->recipient }}</span>
                                @endif
                                @if ($communication->attempt_count > 1)
                                    <span>Attempts: {{ $communication->attempt_count }}</span>
                                @endif
                            </div>
                            @if ($communication->status === 'failed' && $communication->failure_reason)
                                <div class="communication-error">{{ $communication->failure_reason }}</div>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    @if ($lead->conversation_history && $lead->communications->isEmpty())
        <section class="card pad" style="margin-top: 20px;">
            <p class="eyebrow">Legacy Imported Conversation</p>
            <pre class="conversation-full">{{ $lead->conversation_history }}</pre>
        </section>
    @endif
</x-admin.layout>
