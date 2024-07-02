<?php

namespace App\Modules\Assets\Media\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class MediaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                File::types(['pdf', 'txt', 'zip', '7z', 'bmp', 'gif', 'jpeg', 'png', 'svg', 'webp'])
                    ->max('5mb'),
            ],
        ];
    }
}
