<?php

namespace App\Http\Requests;

use App\Models\Pair;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class LatestRequest extends FormRequest
{
    /**
     * Returns a pair of Currency & Coin
     *
     * @return \App\Models\Pair
     */
    public function pair()
    {
        $data = $this->validated();

        return new Pair(
            Arr::get($data, 'currency'),
            Arr::get($data, 'coin')
        );
    }

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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'coin' => [
                'sometimes',
                'required',
                'string',
                Rule::in(array_keys(config('dacxi.coins', []))),
            ],
            'currency' => [
                'sometimes',
                'required',
                'string',
                Rule::in(config('dacxi.currencies', [])),
            ],
        ];
    }

    /**
     * Get data to be validated from the request.
     *
     * @return array
     */
    public function validationData()
    {
        $data = parent::validationData();

        $data['coin'] = Str::lower(Arr::get($data, 'coin', array_keys(config('dacxi.coins', []))[0]));
        $data['currency'] = Str::lower(Arr::get($data, 'currency', config('dacxi.currencies', [])[0]));

        return $data;
    }
}
