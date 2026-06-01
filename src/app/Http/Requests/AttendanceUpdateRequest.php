<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceUpdateRequest extends FormRequest
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
            'clock_in' => [
                'date_format:H:i',
                'before:clock_out',
            ],

            'clock_out' => [
                'date_format:H:i',
                'after:clock_in',
            ],

            'break_start.*' => [
                'nullable',
                'date_format:H:i',
                'after:clock_in',
                'before:clock_out',
            ],

            'break_end.*' => [
                'nullable',
                'date_format:H:i',
                'before:clock_out',
            ],

            'note' => [
                'required',
            ],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $breakStarts = $this->break_start ?? [];

            $breakEnds = $this->break_end ?? [];

            foreach ($breakStarts as $index => $breakStart) {

                $breakEnd = $breakEnds[$index] ?? null;

                if ($breakStart && !$breakEnd) {

                    $validator->errors()->add(

                        "break_end.$index",

                        '休憩終了時間を入力してください'

                    );
                }

                if (!$breakStart && $breakEnd) {

                    $validator->errors()->add(

                        "break_start.$index",

                        '休憩開始時間を入力してください'

                    );
                }

                if (
                    $breakStart &&
                    $breakEnd &&
                    $breakEnd <= $breakStart
                ) {

                    $validator->errors()->add(

                        "break_end.$index",

                        '休憩時間が不適切な値です'

                    );
                }
            }
        });
    }

    public function messages()
    {
        return [

            'clock_in.before' =>
                '出勤時間もしくは退勤時間が不適切な値です',

            'clock_out.after' =>
                '出勤時間もしくは退勤時間が不適切な値です',

            'break_start.*.after' =>
                '休憩時間が不適切な値です',

            'break_start.*.before' =>
                '休憩時間が不適切な値です',

            'break_end.*.before' =>
                '休憩時間もしくは退勤時間が不適切な値です',

            'note.required' =>
                '備考を記入してください',
        ];
    }
}
