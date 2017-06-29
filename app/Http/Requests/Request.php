<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use App\Exceptions\ValidationFailedException;

abstract class Request extends FormRequest
{

    /**
     * @param Validator $validator
     * @throws ValidationFailedException
     * @return void
     */
    protected function failedValidation(Validator $validator)
    {
        throw new ValidationFailedException($validator->errors()->first());
    }

}
