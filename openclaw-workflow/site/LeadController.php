<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:160'],
            'country' => ['nullable', 'string', 'max:120'],
            'interest' => ['nullable', 'string', 'max:120'],
            'message' => ['nullable', 'string', 'max:1200'],
            'consent' => ['accepted'],
        ]);

        $validated['source'] = 'website';

        Lead::create($validated);

        return back()->with('status', 'Thanks. Your details were saved and our team will follow up privately.');
    }
}
