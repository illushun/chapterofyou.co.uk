<?php

namespace App\Services\Journal;

use Anthropic\Client;
use Anthropic\Messages\TextBlock;
use App\Models\JournalPost;
use RuntimeException;

class JournalPostGenerator
{
    private const MODEL = 'claude-sonnet-5';

    private Client $client;

    public function __construct(private readonly UnsplashImageService $images)
    {
        $apiKey = config('services.anthropic.api_key');

        if (! $apiKey) {
            throw new RuntimeException('ANTHROPIC_API_KEY is not configured.');
        }

        $this->client = new Client(apiKey: $apiKey);
    }

    public function generate(?string $topicNotes = null): JournalPost
    {
        $existingPosts = JournalPost::query()
            ->latest()
            ->limit(40)
            ->get(['title', 'tags']);

        $data = $this->requestPostFromClaude($existingPosts, $topicNotes);

        $slug = JournalPost::generateSlug($data['title']);
        $cover = $this->images->fetch($data['image_query']);

        return JournalPost::create([
            'title' => $data['title'],
            'slug' => $slug,
            'excerpt' => $data['excerpt'],
            'body' => $data['body'],
            'meta_title' => $data['meta_title'],
            'meta_description' => $data['meta_description'],
            'tags' => $data['tags'],
            'cover_image' => $cover,
            'status' => 'published',
            'published_at' => now(),
            'author_id' => null,
            'is_ai_generated' => true,
        ]);
    }

    /** @return array{title:string,excerpt:string,body:string,meta_title:string,meta_description:string,tags:string,image_query:string} */
    private function requestPostFromClaude($existingPosts, ?string $topicNotes): array
    {
        $existingTitles = $existingPosts->isEmpty()
            ? 'None yet — this is the first post.'
            : $existingPosts->map(fn ($p) => "- {$p->title}".($p->tags ? " (tags: {$p->tags})" : ''))->implode("\n");

        $steering = $topicNotes
            ? "\n\nAdditional guidance from the site owner for this post: {$topicNotes}"
            : '';

        $systemPrompt = <<<'SYSTEM'
            You are the in-house content writer for Chapter of You, a UK home-fragrance brand
            selling reed diffusers, candles, and scented home accessories. Write in a warm,
            genuine, expert tone using UK English spelling. You are writing a new journal
            (blog) post for the site's journal section.

            Your goal: a genuinely useful, engaging, SEO-optimised article that will attract
            organic search traffic and be worth sharing on social media. Avoid generic filler
            and avoid duplicating the topic or angle of any existing post listed below.
            SYSTEM;

        $userPrompt = <<<PROMPT
            Existing journal posts (do not repeat these topics or angles):
            {$existingTitles}
            {$steering}

            Write one new journal post. The body must be well-structured HTML suitable for a
            rich-text article field: use <h2>/<h3> subheadings, <p> paragraphs, and <ul>/<li>
            lists where useful. Do not include <html>/<body> wrapper tags. Aim for roughly
            600-900 words of genuinely useful content.
            PROMPT;

        $message = $this->client->messages->create(
            model: self::MODEL,
            maxTokens: 8000,
            system: $systemPrompt,
            messages: [
                ['role' => 'user', 'content' => $userPrompt],
            ],
            outputConfig: [
                'format' => [
                    'type' => 'json_schema',
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'title' => ['type' => 'string', 'description' => 'Engaging, SEO-friendly post title'],
                            'excerpt' => ['type' => 'string', 'description' => 'Short 1-2 sentence summary shown on listing pages'],
                            'body' => ['type' => 'string', 'description' => 'Full HTML article body'],
                            'meta_title' => ['type' => 'string', 'description' => 'SEO meta title, 60 characters or fewer'],
                            'meta_description' => ['type' => 'string', 'description' => 'SEO meta description, 155 characters or fewer'],
                            'tags' => ['type' => 'string', 'description' => 'Comma-separated list of 3-6 short tags'],
                            'image_query' => ['type' => 'string', 'description' => 'A short 2-4 word stock-photo search phrase matching the post topic'],
                        ],
                        'required' => ['title', 'excerpt', 'body', 'meta_title', 'meta_description', 'tags', 'image_query'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
        );

        foreach ($message->content as $block) {
            if ($block instanceof TextBlock) {
                $data = json_decode($block->text, true);

                if (! is_array($data)) {
                    throw new RuntimeException('Claude returned an unparsable response for the journal post.');
                }

                return $data;
            }
        }

        throw new RuntimeException('Claude did not return any text content for the journal post.');
    }
}
