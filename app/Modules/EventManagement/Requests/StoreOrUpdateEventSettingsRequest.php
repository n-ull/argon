<?php

namespace App\Modules\EventManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrUpdateEventSettingsRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "title" => "required|string|max:255",
            "description" => "nullable|string|max:8000",
            "start_date" => "required|date",
            "end_date" => "nullable|date",
            "location_info" => "array",
            "location_info.site" => "nullable|string|max:255",
            "location_info.address" => "required|string|max:255",
            "location_info.city" => "required|string|max:255",
            "location_info.country" => "required|string|max:255",
            "location_info.mapLink" => "nullable|string|max:255",
            "taxes_and_fees" => "nullable|array",
            "taxes_and_fees.*" => "required|exists:taxes_and_fees,id",
        ];
    }
}
