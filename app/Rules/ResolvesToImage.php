<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ResolvesToImage implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            return;
        }

        if (! filter_var($value, FILTER_VALIDATE_URL)) {
            if (Storage::disk('public')->exists($value) && str_starts_with(Storage::disk('public')->mimeType($value), 'image/')) {
                return;
            }

            $fail('The :attribute must be a valid image URL.');

            return;
        }

        if (! $this->urlResolvesToImage($value)) {
            $fail('The :attribute must be a URL that resolves to an image.');
        }
    }

    private function urlResolvesToImage(string $url): bool
    {
        try {
            $response = Http::timeout(5)->head($url);

            if (! $response->successful()) {
                $response = Http::timeout(5)->get($url);
            }

            return $response->successful()
                && str_starts_with($response->header('Content-Type') ?? '', 'image/');
        } catch (Throwable) {
            return false;
        }
    }
}
