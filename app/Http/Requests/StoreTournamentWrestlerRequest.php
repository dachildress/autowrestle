<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTournamentWrestlerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $wrestler = \App\Models\Wrestler::find($this->route('wid'));
        return $wrestler && $wrestler->user_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'wr_weight' => ['required', 'numeric', 'min:1', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'wr_weight.required' => 'A weight must be entered.',
        ];
    }
}
