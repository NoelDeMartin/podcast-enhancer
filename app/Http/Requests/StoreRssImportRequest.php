<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRssImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'episodes' => ['required', 'array'],
            'episodes.*.name' => ['required', 'string'],
            'episodes.*.audio_url' => ['required', 'url'],
            'episodes.*.image_url' => ['nullable', 'url'],
            'episodes.*.summary' => ['nullable', 'string'],
            'episodes.*.published_at' => ['nullable', 'date'],
        ];
    }
}
