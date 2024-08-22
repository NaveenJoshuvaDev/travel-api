<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class ToursListRequest extends FormRequest
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


                'priceFrom' => 'numeric|nullable',
                'priceTo' => 'numeric|nullable',
                'dateFrom' => 'date|nullable',
                'dateTo' => 'date|nullable',
                'sortBy' => [
                    'nullable',
                    Rule::in(['price']),
                ],
                'sortOrder' => [
                    'nullable',
                    Rule::in(['asc', 'desc']),
                ],

            ];
    }
    public function messages()
    {
        return [

                'sortBy.in' => 'Only price value is accepted for sorting.',
                'sortOrder.in' => 'Only asc or desc values are accepted for sort order.',


        ];
    }
}
