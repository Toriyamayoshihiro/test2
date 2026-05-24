<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RestStampCorrectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'rests.*.rest_start' => [
            'nullable',
            'date_format:H:i',
            'required_with:rests.*.rest_end',
        ],
        'rests.*.rest_end' => [
            'nullable',
            'date_format:H:i',
            'required_with:rests.*.rest_start',
        ],
        ];
    }
}
