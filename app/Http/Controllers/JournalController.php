<?php

namespace App\Http\Controllers;

use App\Models\JournalPost;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class JournalController extends Controller
{
    /**
     * Public journal listing page — /journal
     */
    public function index()
    {
        $posts = JournalPost::published()
            ->select('id', 'title', 'slug', 'excerpt', 'cover_image', 'tags', 'published_at', 'author_id')
            ->with('author:id,name')
            ->latest('published_at')
            ->paginate(12);

        return Inertia::render('journal/Index', [
            'posts' => $posts->through(fn ($p) => [
                'id'           => $p->id,
                'title'        => $p->title,
                'slug'         => $p->slug,
                'excerpt'      => $p->excerpt,
                'cover_image'  => $p->cover_image
                                    ? asset('storage/' . $p->cover_image)
                                    : null,
                'tags'         => $p->tags_array,
                'published_at' => $p->published_at->format('d M Y'),
                'reading_time' => $p->reading_time,
            ]),
        ]);
    }

    /**
     * Public single post page — /journal/{slug}
     */
    public function show(string $slug)
    {
        $post = JournalPost::published()
            ->where('slug', $slug)
            ->with('author:id,name')
            ->firstOrFail();

        try {
            if (!Auth::check() || !Auth::user()->is_admin) {
                $post->increment('views');
            }
        } catch (\Exception $e) {
        }

        // Related posts — same tags, exclude current
        $related = JournalPost::published()
            ->where('id', '!=', $post->id)
            ->latest('published_at')
            ->limit(3)
            ->get()
            ->map(fn ($p) => [
                'id'           => $p->id,
                'title'        => $p->title,
                'slug'         => $p->slug,
                'excerpt'      => $p->excerpt,
                'cover_image'  => $p->cover_image
                                    ? asset('storage/' . $p->cover_image)
                                    : null,
                'published_at' => $p->published_at->format('d M Y'),
                'reading_time' => $p->reading_time,
            ]);

        return Inertia::render('journal/Show', [
            'post' => [
                'id'               => $post->id,
                'title'            => $post->title,
                'slug'             => $post->slug,
                'excerpt'          => $post->excerpt,
                'body'             => $post->body,
                'cover_image'      => $post->cover_image
                                        ? asset('storage/' . $post->cover_image)
                                        : null,
                'tags'             => $post->tags_array,
                'published_at'     => $post->published_at->format('d M Y'),
                'views'            => $post->views,
                'reading_time'     => $post->reading_time,
                'meta_title'       => $post->meta_title ?: $post->title,
                'meta_description' => $post->meta_description ?: $post->excerpt,
                'author'           => 'Chapter of You', // $post->author?->name
            ],
            'related' => $related,
        ]);
    }
}
