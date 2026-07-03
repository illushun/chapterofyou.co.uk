<?php

namespace App\Services\Product;

use Anthropic\Client;
use Anthropic\Messages\TextBlock;
use App\Models\Product;
use RuntimeException;

class ScentTagSuggester
{
    private const MODEL = 'claude-sonnet-5';

    private Client $client;

    public function __construct()
    {
        $apiKey = config('services.anthropic.api_key');

        if (! $apiKey) {
            throw new RuntimeException('ANTHROPIC_API_KEY is not configured.');
        }

        $this->client = new Client(apiKey: $apiKey);
    }

    /** @return array{scent_families:array<string>,mood_tags:array<string>,intensity:int,reasoning:string} */
    public function suggest(string $name, ?string $description = null, ?string $details = null): array
    {
        $systemPrompt = <<<'SYSTEM'
            You are a fragrance expert helping tag products for Chapter of You, a UK home-fragrance
            brand selling reed diffusers, candles, and scented home accessories. Given a product's
            name and description, classify it against a fixed scent taxonomy so it can be matched to
            customers taking a "scent finder" quiz. Only ever use the allowed values provided — never
            invent new ones.
            SYSTEM;

        $productText = collect([$name, strip_tags((string) $description), strip_tags((string) $details)])
            ->filter()
            ->implode("\n\n");

        $userPrompt = <<<PROMPT
            Product to classify:
            {$productText}

            Allowed scent families: {$this->allowedList(Product::SCENT_FAMILIES)}
            Allowed mood tags: {$this->allowedList(Product::MOOD_TAGS)}

            Choose 1-2 scent families and 1-3 mood tags that best fit this product, and rate its
            fragrance intensity from 1 (very subtle) to 5 (very strong).
            PROMPT;

        $message = $this->client->messages->create(
            model: self::MODEL,
            maxTokens: 1024,
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
                            'scent_families' => [
                                'type' => 'array',
                                'items' => ['type' => 'string', 'enum' => Product::SCENT_FAMILIES],
                                'description' => '1-2 best-matching scent families',
                            ],
                            'mood_tags' => [
                                'type' => 'array',
                                'items' => ['type' => 'string', 'enum' => Product::MOOD_TAGS],
                                'description' => '1-3 best-matching mood/occasion tags',
                            ],
                            'intensity' => [
                                'type' => 'integer',
                                'minimum' => 1,
                                'maximum' => 5,
                                'description' => 'Fragrance intensity, 1 (subtle) to 5 (strong)',
                            ],
                            'reasoning' => [
                                'type' => 'string',
                                'description' => 'One short sentence explaining the choice, for admin review',
                            ],
                        ],
                        'required' => ['scent_families', 'mood_tags', 'intensity', 'reasoning'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
        );

        foreach ($message->content as $block) {
            if ($block instanceof TextBlock) {
                $data = json_decode($block->text, true);

                if (! is_array($data)) {
                    throw new RuntimeException('Claude returned an unparsable response for scent tag suggestions.');
                }

                return $data;
            }
        }

        throw new RuntimeException('Claude did not return any text content for scent tag suggestions.');
    }

    private function allowedList(array $values): string
    {
        return implode(', ', $values);
    }
}
