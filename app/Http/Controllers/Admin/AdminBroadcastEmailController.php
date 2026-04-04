<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\BroadcastMail;
use App\Models\BroadcastEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use App\Mail\WaitlistLaunchMail;
use App\Models\WaitlistEntry;

class AdminBroadcastEmailController extends Controller
{
    /**
     * Audience options and their labels — single source of truth.
     */
    public const AUDIENCES = [
        'all'             => 'All opted-in customers',
        'ordered_last_90' => 'Opted-in, ordered in last 90 days',
        'never_ordered'   => 'Opted-in, never ordered',
    ];

    /**
     * Display broadcast history.
     */
    public function index()
    {
        $broadcasts = BroadcastEmail::with('sender:id,name')
            ->latest('sent_at')
            ->paginate(20);

        $totalOptedIn = User::where('is_admin', false)->where('marketing_opt_in', true)->count();
        $waitlistCount = WaitlistEntry::count();

        return Inertia::render('admin/broadcast/Index', [
            'broadcasts'    => $broadcasts,
            'totalOptedIn'  => $totalOptedIn,
            'waitlistCount' => $waitlistCount,
        ]);
    }

    /**
     * Show the compose form, including a live recipient count per audience.
     */
    public function create()
    {
        $audienceCounts = [];
        foreach (array_keys(self::AUDIENCES) as $key) {
            $audienceCounts[$key] = $this->resolveRecipients($key)->count();
        }

        return Inertia::render('admin/broadcast/Create', [
            'audiences'      => self::AUDIENCES,
            'audienceCounts' => $audienceCounts,
        ]);
    }

    /**
     * Send the broadcast and log it.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject'  => ['required', 'string', 'max:255'],
            'body'     => ['required', 'string', 'min:10'],
            'audience' => ['required', 'in:' . implode(',', array_keys(self::AUDIENCES))],
        ]);

        $recipients = $this->resolveRecipients($validated['audience'])->get();

        if ($recipients->isEmpty()) {
            return back()->withErrors(['audience' => 'No recipients found for the selected audience.']);
        }

        // Queue one email per recipient
        foreach ($recipients as $user) {
            Mail::to($user->email)->queue(
                new BroadcastMail(
                    subject:   $validated['subject'],
                    body:      $validated['body'],
                    recipient: $user,
                )
            );
        }

        // Log the broadcast
        BroadcastEmail::create([
            'sent_by'         => Auth::id(),
            'subject'         => $validated['subject'],
            'body'            => $validated['body'],
            'audience'        => $validated['audience'],
            'recipient_count' => $recipients->count(),
            'sent_at'         => now(),
        ]);

        return redirect()
            ->route('admin.broadcasts.index')
            ->with('success', "Broadcast queued for {$recipients->count()} recipient(s).");
    }

    /**
     * Show a previously sent broadcast (read-only).
     */
    public function show(BroadcastEmail $broadcast)
    {
        $broadcast->load('sender:id,name');

        return Inertia::render('admin/broadcast/Show', [
            'broadcast'     => $broadcast,
            'audienceLabel' => self::AUDIENCES[$broadcast->audience] ?? $broadcast->audience,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Build the base query for a given audience key (without ->get() so we can
     * call ->count() and ->get() separately).
     */
    private function resolveRecipients(string $audience)
    {
        // Always restrict to opted-in, non-admin users
        $query = User::where('is_admin', false)
            ->where('marketing_opt_in', true)
            ->select('id', 'name', 'email');

        return match ($audience) {
            'ordered_last_90' => $query->whereHas('orders', function ($q) {
                $q->where('status', 'successful')
                  ->where('created_at', '>=', now()->subDays(90));
            }),
            'never_ordered' => $query->whereDoesntHave('orders', function ($q) {
                $q->where('status', 'successful');
            }),
            default => $query, // 'all' — every non-admin user
        };
    }

    /**
    * Send the waitlist launch email to all waitlist entries.
    */
    public function sendWaitlistLaunch(Request $request)
    {
        $recipients = WaitlistEntry::all();

        if ($recipients->isEmpty()) {
            return response()->json(['error' => 'No waitlist entries found.'], 422);
        }

        foreach ($recipients as $entry) {
            Mail::to($entry->email)->queue(new WaitlistLaunchMail('CHAPTERONE'));
        }

        return response()->json([
            'sent' => $recipients->count(),
            'message' => "Launch email queued for {$recipients->count()} waitlist subscriber(s).",
        ]);
    }
}
