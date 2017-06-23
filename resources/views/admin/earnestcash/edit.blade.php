{{-- 继承主要布局模板 --}}
@extends('admin/layouts/main')

{{-- 插入到<head>的代码 --}}
@section('head')
    <title>录入保证金 - {{env('SITENAME')}}</title>

    <script>
        $.extend(ST.ACTION, {});
    </script>
@endsection

{{-- 内容 --}}
@section('content')
	<div class="heading clearfix">
        <div class="pull-left">
            <h2>保证金管理>录入保证金</h2>
        </div>
    </div>
    @if (isset($earnestcash))
        {!! Form::model($earnestcash, [
            'url' => route('admin.earnestcash.update', ['id' => $earnestcash->id]),
            'id' => 'js-form-validate',
        ]) !!}
    @else
        {!! Form::open([
            'url' => route('admin.earnestcash.store'),
            'id' => 'js-form-validate',
        ]) !!}
    @endif

    <table class="table table-listing table-hovered">
        <tbody>
        <tr>
            <td class="span4"><em class="text-red">* </em>合作方：</td>
            <td>
                {!! Form::select('partner_id', $partners->pluck('value', 'key'), null, [
                    'placeholder' => '请选择',
                    'class'       => 'js-selectbox',
                    'data-rules'  => 'required',
                ]) !!}
            </td>
        </tr>

        <tr>
            <td class="span4"><em class="text-red">* </em>保证金金额：</td>
            <td>
                {!! Form::text('amount', null, [
                    'class'      => 'input input-small',
                    'data-rules' => 'required match(/^(-?\d+)(\.\d+)?$/)',
                    'data-msg'   => '必填 请输入数值',
					'maxlength'  => '9'
                ]) !!}
            </td>
        </tr>

        <tr>
            <td class="span4"><em class="text-red">* </em>收款时间：</td>
            <td>
                {!! Form::text('reception_time', null, [
                    'class'      => 'input input-small js-calender',
                    'readonly'   => 'readonly',
                    'data-rules' => 'required',
                ]) !!}
            </td>
        </tr>

        <tr>
            <td class="span4"><em class="text-red">* </em>收款银行：</td>
            <td>
                {!! Form::select('bank', $banks->pluck('value', 'key'), null, [
                    'placeholder' => '请选择',
                    'class'       => 'js-selectbox',
                    'data-rules'  => 'required',
                ]) !!}
            </td>
        </tr>

        <tr>
            <td class="span4"><em class="text-red">* </em>票据：</td>
            <td>
                @foreach($billTypes as $billType)
                    <label>
                        <input type="radio" data-group="bill_type" data-rules="least" data-holder="#bill_type_help" name="bill_type" value="{{ $billType->key }}" @if (isset($earnestcash) && $billType->key == $earnestcash->bill_type) checked="checked" @endif>
                        <span class="pr10">{{ $billType->value }}</span>
                    </label>
                @endforeach

                <div id="bill_type_help" class="inline-block">
                </div>
            </td>
        </tr>

        <tr>
            <td class="span4"><em class="text-red">* </em>票据编号：</td>
            <td>
                {!! Form::text('bill_num', null, [
                    'class'      => 'input input-small',
                    'data-rules' => 'required',
                ]) !!}
            </td>
        </tr>

        <tr>
            <td class="span4">备注：</td>
            <td>
                {!! Form::textarea('remark', null, [
                    'cols' => '50',
                    'rows' => '5',
                ]) !!}
            </td>
        </tr>

        <tr>
            <td class="span4"></td>
            <td>
                <input class="btn btn-blue" type="submit" value="确定">
                <a class="btn" href="{{ route('admin.earnestcash.list') }}">取消</a>
            </td>
        </tr>
        </tbody>
    </table>

    {!! Form::close() !!}

@endsection

{{-- 插入到尾部的代码 --}}
@section('end')
    <script>
        seajs.use('lang/common', function (app) {
            app.com();
        });
    </script>
@endsection