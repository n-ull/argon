<?php

namespace App\Modules\EventManagment\Requests;

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
            'description' => ['string', 'max:2000', 'min:255'],
            'start_date' => ['datetime'],
            'end_date' => ['datetime'],
            'location_info' => ['required', 'array'],
            'location_info.address' => ['required', 'string'],
            'location_info.city' => ['required', 'string'],
            'location_info.country' => ['required', 'string'],
            'location_info.mapLink' => ['string'],
            'location_info.site' => ['string'],
        ];
    }
}
