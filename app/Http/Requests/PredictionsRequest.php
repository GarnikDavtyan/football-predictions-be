<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PredictionsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'predictions.*.fixture_id' => 'required|integer|exists:fixtures,id',
            'predictions.*.score_home' => 'nullable|integer',
            'predictions.*.score_away' => 'nullable|integer',
            'x2_fixture_id' => 'nullable|integer|exists:fixtures,id'
        ];
    }
}
