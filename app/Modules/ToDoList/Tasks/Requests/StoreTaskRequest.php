<?php

namespace App\Modules\ToDoList\Tasks\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
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
            'catalog_id' => ['required', 'gt:0'],
            'name' => ['required', 'min:1', 'max:255'],
            'description' => ['nullable', 'max:65536'],
            'date' => ['nullable', 'date_format:"Y-m-d H:i"'],
            'place' => ['nullable', 'max:255'],
        ];
    }
}
