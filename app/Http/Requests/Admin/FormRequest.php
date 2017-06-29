<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use DB;
use App\Exceptions\ValidationFailedException;

class FormRequest extends Request
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
            'invoice_key'        => 'required',


        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'invoice_key'       => '发票编号必须填写！',

        ];
    }

}
