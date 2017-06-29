<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => ':attribute 必须接受.',
    'active_url'           => ':attribute 必须为可用的 URL.',
    'after'                => ':attribute 必须晚于 :date.',
    'alpha'                => ':attribute 只能包含字母.',
    'alpha_dash'           => ':attribute 只能包含字母、数字、连字符和下划线.',
    'alpha_num'            => ':attribute 只能包含字母和数字.',
    'array'                => ':attribute 必须为数组.',
    'before'               => ':attribute 必须为 :date 之前的日期.',
    'between'              => [
        'numeric' => ':attribute 必须在 :min 到 :max 之间.',
        'file'    => ':attribute 的文件大小必须介于 :min 到 :max KB之间.',
        'string'  => ':attribute 必须包含 :min 到 :max 个字符.',
        'array'   => ':attribute 必须包含 :min 到 :max 个元素.',
    ],
    'boolean'              => ':attribute 字段必须为 true 或 false.',
    'confirmed'            => ':attribute 字段和其确认字段值不符.',
    'date'                 => ':attribute 不是合法的日期格式.',
    'date_format'          => ':attribute 的日期格式必须符合 :format.',
    'different'            => ':attribute 和 :other 的值必须不同.',
    'digits'               => ':attribute 的位数必须为 :digits.',
    'digits_between'       => ':attribute 的位数必须在 :min 与 :max 之间.',
    'email'                => ':attribute 必须为合法的Email地址.',
    'filled'               => ':attribute 字段不可为空.',
    'exists'               => '指定的 :attribute 在数据库中不存在。',
    'image'                => ':attribute 必须为一个图片文件.',
    'in'                   => ' :attribute 不符合规定的条件。',
    'integer'              => ':attribute 必须为整数.',
    'ip'                   => ':attribute 必须为合法的IP地址.',
    'max'                  => [
        'numeric' => ':attribute 的值不可大于 :max.',
        'file'    => ':attribute 的文件大小不可大于 :max KB.',
        'string'  => ':attribute 不可多于 :max 个字符.',
        'array'   => ':attribute 不可多余 :max 个元素.',
    ],
    'mimes'                => ':attribute 必须为以下文件类型: :values.',
    'min'                  => [
        'numeric' => ':attribute 字段必须至少为 :min.',
        'file'    => ':attribute 的文件大小不可小于 :min KB.',
        'string'  => ':attribute 不可少于 :min 个字符.',
        'array'   => ':attribute 不可少于 :min 个元素.',
    ],
    'not_in'               => ':attribute 的值不在规定之中.',
    'numeric'              => ':attribute 必须为数字.',
    'regex'                => ':attribute 的格式不符合要求.',
    'required'             => ':attribute 字段为必填项.',
    'required_if'          => '当 :other 为 :value 时 :attribute 字段为必填项.',
    'required_with'        => '当 :values 字段存在时 :attribute 字段为必填项.',
    'required_with_all'    => '当 :values 字段均存在时 :attribute 字段为必填项.',
    'required_without'     => '当 :values 字段不存在时 :attribute 字段为必填项.',
    'required_without_all' => '当 :values 字段均不存在时 :attribute 字段为必填项.',
    'same'                 => ':attribute 和 :other 必须相同.',
    'size'                 => [
        'numeric' => ':attribute 必须为 :size.',
        'file'    => ':attribute 的文件大小必须为 :size KB.',
        'string'  => ':attribute 必须包含 :size 个字符.',
        'array'   => ':attribute 必须包含 :size 个元素.',
    ],
    'string'               => ':attribute 必须为字符串.',
    'timezone'             => ':attribute 必须为合法的时区.',
    'unique'               => ':attribute 的值已存在.',
    'url'                  => ':attribute 不是合法的URL.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [
        'filedata' => '上传的文件',
        'Filedata' => '上传的文件',
    ],

];
