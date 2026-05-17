<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:1024'],
            'audio_url' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'mimes:mp3,mp4,m4a,ogg,wav,flac,aac,opus,wma'],
            'delete_file' => ['nullable', 'boolean'],
            'image_url' => ['nullable', 'string'],
            'image_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp,svg'],
            'delete_image_file' => ['nullable', 'boolean'],
        ];
    }
}
