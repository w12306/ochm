{{-- 继承主要布局模板 --}}
@extends('admin/layouts/main')

{{-- 插入到<head>的代码 --}}
@section('head')
    <title>ADPS</title>

@endsection

{{-- 内容 --}}
@section('content')

    <div class="heading clearfix">
        <div class="pull-left">
            <h2>添加广告位</h2>
        </div>
    </div>

    <form action="{{ $formUrl }}"  method="post" onsubmit="return false" stverify="true" erroappend="true" ajaxpost="true" afterSubFun="_afterSubFun">
		<input type="hidden" name="id" value="<?php if (isset($data['id'])){echo $data['id'];}?>" />
        <table class="table table-listing">
            <tbody>
            <tr>
                <td class="span4"><em class="text-red">* </em>广告位代码</td>
                <td>
                    <input class="input input-small" opt="rq" name="code" placeholder="请输入广告位代码" maxlength="30" value="<?php if (isset($data['code'])){echo $data['code'];}?>" type="text">
                </td>
            </tr>


			<tr>
                <td class="span4"><em class="text-red">* </em>所属上级</td>
                <td>
                    <select name="parent_id">
						<option  value="0">顶级</option>
						@foreach ($adSpaceNameList as $key=>$v)
							<option  value="{{$key}}">{{$v}}</option>
						@endforeach
					</select>
                </td>
            </tr>
			<!--<tr>
                <td class="span4"><em class="text-red">* </em>广告层级</td>
                <td>
                    <input type="radio" name="tier" value="1"  <?php if (!isset($data['tier']) || $data['tier']==1){echo 'checked="checked"';}?>/>业务线&nbsp;&nbsp;&nbsp;&nbsp;
					<input type="radio" name="tier" value="2"  <?php if (isset($data['tier']) && $data['tier']==2){echo 'checked="checked"';}?>/>广告位&nbsp;&nbsp;&nbsp;&nbsp;
					<input type="radio" name="tier" value="3"  <?php if (isset($data['tier']) && $data['tier']==3){echo 'checked="checked"';}?>/>具体广告位&nbsp;&nbsp;&nbsp;&nbsp;
                </td>
            </tr>-->
			
            <tr>
                <td class="span4"><em class="text-red">* </em>广告位名称</td>
                <td>
                    <input class="input input-small" opt="rq" name="name" placeholder="请输入广告位名称" value="<?php if (isset($data['name'])){echo $data['name'];}?>" maxlength="30" type="text">
                </td>
            </tr>

			<tr>
                <td class="span4"><em class="text-red">* </em>是否允许发布广告</td>
                <td>
                   <input type="radio" name="publishable" value="1"  <?php if (isset($data['publishable']) && $data['publishable']==1){echo 'checked="checked"';}?>/>允许 &nbsp;&nbsp;&nbsp;&nbsp;
				   <input type="radio" name="publishable" value="-1"  <?php if (!isset($data['publishable']) || $data['publishable']!=1){echo 'checked="checked"';}?>/>不允许
                </td>
            </tr>
			
			<tr>
                <td class="span4"><em class="text-red">* </em>广告位预计宽度</td>
                <td>
                    <input class="input input-small" opt="rq" name="mock_width" placeholder="请输入广告位宽度" value="<?php if (isset($data['mock_width'])){echo $data['mock_width'];}?>" maxlength="30" type="text">
                </td>
            </tr>
			
			
			<tr>
                <td class="span4"><em class="text-red">* </em>广告位预计高度</td>
                <td>
                    <input class="input input-small" opt="rq" name="mock_height" placeholder="请输入广告位高度" value="<?php if (isset($data['mock_height'])){echo $data['mock_height'];}?>" maxlength="30" type="text">
                </td>
            </tr>
			
			<tr>
                <td class="span4"><em class="text-red">* </em>自动发布初审通过物料</td>
                <td>
                    <input type="radio" name="auto_publish" value="1"  <?php if (isset($data['auto_publish']) && $data['auto_publish']==1){echo 'checked="checked"';}?>/>是 &nbsp;&nbsp;&nbsp;&nbsp;
				   <input type="radio" name="auto_publish" value="-1" <?php if (!isset($data['auto_publish']) || $data['auto_publish']!=1){echo 'checked="checked"';}?> />否
                </td>
            </tr>

            <tr>
                <td class="span4"></td>
                <td>
                    <input class="btn btn-blue" type="submit" value="提交">
                    <a class="btn" href="#" onclick="javascript:history.back(-1);">取消</a>
                </td>
            </tr>
            </tbody>
        </table>
    </form>

@endsection

{{-- 插入到尾部的代码 --}}
@section('end')
    <script>
        $.extend(ST, {
  
        });

    </script>

@endsection