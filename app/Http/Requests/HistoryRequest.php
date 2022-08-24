<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;

class HistoryRequest extends LatestRequest
{
    /**
     * Return field date & time as Date instance.
     *
     * @return \Illuminate\Support\Carbon
     */
    public function datetime()
    {
        $data = $this->validated();

        try {
            return Carbon::createFromFormat('Y-m-d H:i:s', Arr::get($data, 'date') . ' ' . Arr::get($data, 'time'));
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules['date'] = [
            'required',
            'date_format:Y-m-d',
            'before_or_equal:' . now()->format('Y-m-d'),
        ];

        $rules['time'] = [
            'sometimes',
            'required',
            'date_format:H\:i\:s',
        ];

        return $rules;
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $datetime = $this->datetime();

            if (!is_null($datetime) && $datetime->isFuture()) {
                $validator->errors()->add('time', 'Date/time should not be in future.');
            }
        });
    }

    /**
     * Get data to be validated from the request.
     *
     * @return array
     */
    public function validationData()
    {
        $data = parent::validationData();

        $data['time'] = Arr::get($data, 'time', '00:00:00');

        return $data;
    }
}
