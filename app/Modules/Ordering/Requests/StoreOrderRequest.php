<?php

namespace App\Modules\Ordering\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
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
            'eventId' => ['required', 'exists:events,id'],
            'items' => ['required', 'array'],
            'items.*.productId' => ['required', 'exists:products,id'],
            'items.*.productPriceId' => ['required', 'exists:product_prices,id'],
            'items.*.quantity' => ['required', 'min:1', 'integer'],
            'referral' => ['nullable', 'string'],
        ];
    }
}
