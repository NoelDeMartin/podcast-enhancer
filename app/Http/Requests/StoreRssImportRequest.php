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
            'url' => ['required', 'url'],
            'episodes' => ['required', 'array', 'max:50'],
            'episodes.*' => ['required', 'string'],
        ];
    }
}
