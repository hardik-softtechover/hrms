<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        Event::create([...$data, 'created_by' => $request->user()->id]);

        return back()->with('success', 'Event created.');
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $event->update($this->validateData($request));
        return back()->with('success', 'Event updated.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $event->delete();
        return back()->with('success', 'Event deleted.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'title'       => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'starts_at'   => ['required', 'date'],
            'ends_at'     => ['nullable', 'date', 'after_or_equal:starts_at'],
            'location'    => ['nullable', 'string', 'max:120'],
        ]);
    }
}
