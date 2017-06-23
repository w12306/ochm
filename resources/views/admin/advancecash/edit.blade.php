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
            <h2>预收款管理>录入预收款</h2>
        </div>
    </div>
    @if (isset($advancecash))
        {!! Form::model($advancecash, [
            'url' => route('admin.advancecash.update', ['id' => $advancecash->id]),
            'id' => 'js-form-validate',
        ]) !!}
    @else
        {!! Form::open([
            'url' => route('admin.advancecash.store'),
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
            <td class="span4"><em class="text-red">* </em>预收款金额：</td>
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
            <td class="span4"><em class="text-red">* </em>出票方：</td>
            <td>
                {!! Form::text('bill_user', null, [
                    'class'      => 'input input-small',
                    'data-rules' => 'required',
                ]) !!}
            </td>
        </tr>
		<tr>
            <td class="span4"><em class="text-red">* </em>票据类型：</td>
            <td>
                {!! Form::text('bill_type', null, [
                    'class'      => 'input input-small',
                    'data-rules' => 'required',
                ]) !!}
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
            <td class="span4"><em class="text-red">* </em>出票时间：</td>
            <td>
                {!! Form::text('reception_time', null, [
                    'class'      => 'input input-small js-calender',
                    'readonly'   => 'readonly',
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
                <a class="btn" href="{{ route('admin.advancecash.list') }}">取消</a>
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