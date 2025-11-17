<?php

namespace App\Modules\EventManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // authorize if user has a organization
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255', 'min:8'],
            'description' => ['nullable', 'string', 'max:2000', 'min:10'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'location_info' => ['required', 'array'],
            'location_info.address' => ['required', 'string'],
            'location_info.city' => ['required', 'string'],
            'location_info.country' => ['required', 'string'],
            'location_info.mapLink' => ['nullable', 'string'],
            'location_info.site' => ['nullable', 'string'],
        ];
    }
}
