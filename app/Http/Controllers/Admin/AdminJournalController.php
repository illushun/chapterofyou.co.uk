<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JournalPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AdminJournalController extends Controller
{
    public function index(Request $request)
    {
        $posts = JournalPost::with('author:id,name')
            ->select('id', 'title', 'slug', 'status', 'published_at', 'author_id', 'created_at')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('search'), fn ($q) =>
                $q->where('title', 'like', "%{$request->search}%"))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('admin/journal/Index', [
            'posts'   => $posts,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/journal/CreateEdit', [
            'isEditing' => false,
            'suggested' => [
                'How long do reed diffusers last?',
                'Reed diffuser vs candle — which is right for you?',
                'Best scents for each room in your home',
                'The benefits of aromatherapy at home',
                'How to make your reed diffuser last longer',
                'Gift ideas for someone who loves home fragrance',
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatePost($request);

        $validated['author_id']   = Auth::id();
        $validated['slug']        = $validated['slug'] ?: JournalPost::generateSlug($validated['title']);
        $validated['published_at'] = $validated['status'] === 'published'
            ? ($validated['published_at'] ?? now())
            : null;

        $post = JournalPost::create($validated);

        if ($request->hasFile('cover_image')) {
            $this->handleImageUpload($post, $request->file('cover_image'));
        }

        return redirect()->route('admin.journal.show', $post)
            ->with('success', "{$post->title} published successfully.");
    }

    public function show(JournalPost $journal)
    {
        return Inertia::render('admin/journal/Show', [
            'post' => $journal->load('author:id,name'),
        ]);
    }

    public function edit(JournalPost $journal)
    {
        return Inertia::render('admin/journal/CreateEdit', [
            'post'      => $journal->load('author:id,name'),
            'isEditing' => true,
        ]);
    }

    public function update(Request $request, JournalPost $journal)
    {
        $validated = $this->validatePost($request, $journal->id);

        $validated['published_at'] = $validated['status'] === 'published'
            ? ($validated['published_at'] ?? $journal->published_at ?? now())
            : null;

        $journal->update($validated);

        if ($request->hasFile('cover_image')) {
            // Delete old image
            if ($journal->cover_image) {
                Storage::disk('public')->delete($journal->cover_image);
            }
            $this->handleImageUpload($journal, $request->file('cover_image'));
        }

        if ($request->boolean('remove_cover_image') && $journal->cover_image) {
            Storage::disk('public')->delete($journal->cover_image);
            $journal->update(['cover_image' => null]);
        }

        return redirect()->route('admin.journal.show', $journal)
            ->with('success', "{$journal->title} updated successfully.");
    }

    public function destroy(JournalPost $journal)
    {
        $title = $journal->title;
        if ($journal->cover_image) {
            Storage::disk('public')->delete($journal->cover_image);
        }
        $journal->delete();

        return redirect()->route('admin.journal.index')
            ->with('success', "{$title} deleted.");
    }

    private function handleImageUpload(JournalPost $post, $file): void
    {
        $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $path     = $file->storeAs('journal_images', $fileName, 'public');
        $post->update(['cover_image' => $path]);
    }

    private function validatePost(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'title'            => ['required', 'string', 'max:255'],
            'slug'             => ['nullable', 'string', 'max:255',
                                   \Illuminate\Validation\Rule::unique('journal_posts', 'slug')->ignore($ignoreId)],
            'excerpt'          => ['nullable', 'string', 'max:500'],
            'body'             => ['required', 'string'],
            'cover_image'      => ['nullable', 'image', 'max:4096', 'mimes:jpeg,png,webp'],
            'meta_title'       => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'tags'             => ['nullable', 'string', 'max:255'],
            'status'           => ['required', \Illuminate\Validation\Rule::in(['draft', 'published'])],
            'published_at'     => ['nullable', 'date'],
        ]);
    }
}
