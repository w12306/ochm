<?php

namespace App\Http\Requests\Admin\AdController;

use App\Http\Requests\Request;
use DB;
use App\Exceptions\ValidationFailedException;

class SaveRequest extends Request
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
            'ad_space_type_id' => 'required|exists:ad_space_business,id',
            'ad_space_ids'     => 'required|array',
            'ad_template_id'   => 'required|integer|exists:ad_template,id',
            'name'             => 'required|string|between:1,100',
            'start_time'       => 'required|date_format:Y-m-d H:i:s',
            'end_time'         => 'required|date_format:Y-m-d H:i:s|after:start_time',
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'ad_space_type_id'       => '二级广告位格式不正确。 #联系开发者#',
            'ad_space_ids.array'     => '广告位格式不正确。 #联系开发者#',
            'target_type.integer'    => '定向类型值超出合法范围。 #联系开发者#',
            'target_type.in'         => '定向类型值超出合法范围。 #联系开发者#',
            'ad_template_id.integer' => '广告模板ID不正确。 #联系开发者#',
            'ad_template_id.exists'  => '广告模板不存在。',
            'name.between'           => '广告名称长度为1到100个字符',
            'start_time.date_format' => '查询条件中广告发布开始时间格式不正确。',
            'end_time.date_format'   => '查询条件中广告发布结束时间格式不正确。',
            'end_time.after'         => '查询条件中广告发布结束时间必须大于开始时间。',
        ];
    }

}
