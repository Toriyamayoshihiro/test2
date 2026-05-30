<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class StampCorrectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            
           'start_time' => [
            'required',
            'date_format:H:i',
            'before:end_time',
        ],
        'end_time' => [
            'required',
            'date_format:H:i',
            'after:start_time',
        ],
        'note' => [
            'required',
        ],

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
        public function withValidator(Validator $validator)
        {
        $validator->after(function ($validator) {
        $startTime = $this->input('start_time');
        $endTime = $this->input('end_time');
            $hasRest = false;

            foreach ($this->input('rests', []) as $index => $rest) {
                $restStart = $rest['rest_start'] ?? null;
                $restEnd = $rest['rest_end'] ?? null;

                if (!empty($restStart) && !empty($restEnd)) {
                    $hasRest = true;
                }

                if (empty($restStart) && empty($restEnd)) {
                    continue;
                }

                if (empty($restStart) || empty($restEnd)) {
                    continue;
                }

                if ($restStart >= $endTime) {
                    $validator->errors()->add(
                        "rests.$index.rest_start",
                        '休憩時間が不適切な値です'
                    );
                }

                if ($restEnd > $endTime) {
                    $validator->errors()->add(
                        "rests.$index.rest_end",
                        '休憩時間もしくは退勤時間が不適切な値です'
                    );
                }

                if ($restEnd <= $restStart) {
                    $validator->errors()->add(
                        "rests.$index.rest_end",
                        '休憩終了時間は休憩開始時間より後にしてください'
                    );
                }
            }

            if (!$hasRest) {
                $validator->errors()->add(
                    'rests.0.rest_start',
                    '休憩時間を入力してください'
                );
            }
        });

        }

    public function messages(): array
    {
        return [

            'start_time.required' => '出勤時間を入力してください',
            'start_time.before' => '出勤時間が不適切な値です',

            'end_time.required' => '退勤時間を入力してください',
            'end_time.after' => '出勤時間が不適切な値です',

            'note.required' => '備考を記入してください',

            'rests.*.rest_start.required_with' => '休憩開始時間を入力してください',
            'rests.*.rest_end.required_with' => '休憩終了時間を入力してください',

            'rests.*.rest_start.date_format' => '休憩開始時間は「HH:MM」形式で入力してください',
            'rests.*.rest_end.date_format' => '休憩終了時間は「HH:MM」形式で入力してください',
        ];    
    }      
}
