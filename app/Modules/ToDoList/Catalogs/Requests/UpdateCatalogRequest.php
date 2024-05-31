<?php

namespace App\Modules\ToDoList\Catalogs\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCatalogRequest extends FormRequest
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
            'name' => ['min:1', 'max:255'],
            'description' => ['nullable', 'max:65536'],
            'position' => ['gte:0'],
        ];
    }
}
