<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function index(Request $request): Response
    {
        $now = Carbon::now();

        $events = Event::orderBy('starts_at')
            ->get()
            ->map(fn (Event $e) => [
                'id'          => $e->id,
                'title'       => $e->title,
                'description' => $e->description,
                'starts_at'   => $e->starts_at->toIso8601String(),
                'ends_at'     => $e->ends_at?->toIso8601String(),
                'location'    => $e->location,
                'is_upcoming' => $e->starts_at->greaterThanOrEqualTo($now),
                'is_past'     => $e->starts_at->lessThan($now),
            ]);

        $isAdmin = (bool) $request->user()?->isAdmin();

        return Inertia::render($isAdmin ? 'Admin/Events' : 'Employee/Events', [
            'events' => $events,
        ]);
    }
}
