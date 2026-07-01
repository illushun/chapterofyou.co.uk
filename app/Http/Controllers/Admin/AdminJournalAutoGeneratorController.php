<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JournalAutoGeneratorSetting;
use App\Models\JournalPost;
use App\Services\Journal\JournalPostGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class AdminJournalAutoGeneratorController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('admin/journal/AutoGenerator', [
            'settings' => JournalAutoGeneratorSetting::current(),
            'recentAiPosts' => JournalPost::query()
                ->where('is_ai_generated', true)
                ->latest('published_at')
                ->limit(10)
                ->get(['id', 'title', 'slug', 'published_at']),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'enabled' => ['required', 'boolean'],
            'frequency' => ['required', Rule::in(['daily', 'weekly', 'biweekly', 'monthly'])],
            'day_of_week' => ['nullable', 'integer', 'between:0,6'],
            'topic_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        JournalAutoGeneratorSetting::current()->update($validated);

        return back()->with('success', 'Auto-generator settings saved.');
    }

    public function generateNow(): RedirectResponse
    {
        $settings = JournalAutoGeneratorSetting::current();

        try {
            // Resolved inside the try block (not via method injection) so that a
            // missing ANTHROPIC_API_KEY — thrown from the constructor — is caught
            // and turned into a flash message, rather than a 500 error.
            $post = app(JournalPostGenerator::class)->generate($settings->topic_notes);

            $settings->update([
                'last_generated_at' => now(),
                'last_run_status' => 'success',
                'last_error' => null,
            ]);

            return redirect()
                ->route('admin.journal.show', $post)
                ->with('success', "\"{$post->title}\" generated and published.");
        } catch (Throwable $e) {
            report($e);

            $settings->update([
                'last_run_status' => 'failed',
                'last_error' => $e->getMessage(),
            ]);

            return back()->with('error', "Generation failed: {$e->getMessage()}");
        }
    }
}
