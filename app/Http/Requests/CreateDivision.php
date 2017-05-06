<?php

namespace App\Http\Requests;

use App\Division;
use Illuminate\Foundation\Http\FormRequest;

class CreateDivision extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('create', [Division::class]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|unique:divisions',
            'abbreviation' => 'required|unique:divisions',
            'description' => 'required',
        ];
    }

    public function persist()
    {
        $division = Division::create($this->all());
        $division->save();
    }
}
