<?php

namespace App\Services\Journal;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UnsplashImageService
{
    private const RANDOM_PHOTO_URL = 'https://api.unsplash.com/photos/random';

    private ?string $accessKey;

    public function __construct()
    {
        $this->accessKey = config('services.unsplash.access_key');
    }

    // Finds a photo matching the query, downloads it, and stores it under journal_images.
    // Returns the stored relative path, or null if anything goes wrong — a missing cover
    // image should never block a journal post from being generated/published.
    public function fetch(string $query): ?string
    {
        if (! $this->accessKey) {
            Log::warning('UnsplashImageService: no access key configured, skipping cover image.');

            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "Client-ID {$this->accessKey}",
            ])->get(self::RANDOM_PHOTO_URL, [
                'query' => $query,
                'orientation' => 'landscape',
                'content_filter' => 'high',
            ]);

            if (! $response->successful()) {
                Log::warning('UnsplashImageService: photo search failed.', [
                    'query' => $query,
                    'status' => $response->status(),
                ]);

                return null;
            }

            $photo = $response->json();
            $imageUrl = $photo['urls']['regular'] ?? null;

            if (! $imageUrl) {
                return null;
            }

            $imageResponse = Http::get($imageUrl);

            if (! $imageResponse->successful()) {
                return null;
            }

            $fileName = time().'_'.Str::random(10).'.jpg';
            $path = 'journal_images/'.$fileName;

            Storage::disk('public')->put($path, $imageResponse->body());

            // Unsplash API guidelines require pinging the download endpoint when a photo is used.
            $downloadLocation = $photo['links']['download_location'] ?? null;
            if ($downloadLocation) {
                Http::withHeaders(['Authorization' => "Client-ID {$this->accessKey}"])
                    ->get($downloadLocation);
            }

            return $path;
        } catch (\Throwable $e) {
            Log::warning('UnsplashImageService: failed to fetch cover image.', [
                'query' => $query,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
