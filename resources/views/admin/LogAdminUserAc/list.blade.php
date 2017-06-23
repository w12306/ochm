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
            <h2>管理员操作日志</h2>
        </div>
        <div class="pull-right">
            
        </div>
    </div>

    <form action="{{ Request::getRequestUri() }}" method="get" stverify="true">
        <table class="table table-listing">
            <tbody>

            <tr>
                <td class="span4">后台用户</td>
                <td>
                   <select name='admin_user_id'>
				   		<option value="">请选择用户</option>
						@foreach ($userlist as $user)
							<option value="{{$user->id}}" <?php if(Request::get('admin_user_id')==$user->id){echo 'selected="selected"';}?> >{{$user->real_name}}</option>	
						@endforeach
				   </select>
                </td>
            </tr>
			<tr>
                <td class="span4">操作类型</td>
                <td>
                   <select  name="action_type">
				   <option value="">请选择操作类型</option>
						@foreach ($actionType as $acname=>$actext)
							<option value="{{$acname}}" <?php if(Request::get('action_type')==$acname){echo 'selected="selected"';}?> >{{$actext}}</option>	
						@endforeach
				   </select>
                </td>
            </tr>

            <tr class="tr_bg">
                <th></th>
                <td><input type="submit" class="btn btn-blue" value="查询"></td>
            </tr>
            </tbody>
        </table>
    </form>

    <table class="table table-listing mt20">
        <thead class="thead-gray">
        <tr>
			<th>编号</th>
            <th>操作人</th>
            <th>操作类型</th>
			<th>操作模块</th>
            <th>操作详细</th>
			<th>操作时间</th>
        </tr>
        </thead>
		
        <tbody>
	   @if ($listdata->count() == 0)
             <tr>
                 <td colspan="6">没有内容</td>
             </tr>
       @endif
       @foreach ($listdata as $adspace)
            <tr>
				<td>
                    {{ $adspace->id }}
                </td>
                <td>
                    {{ $adspace->admin_user_name }}
                </td>
                
                <td>
                    {{ $adspace->action_type }}
                </td>
				
                <td>
                    {{ $adspace->action_name }}
                </td>
				<td>
                    {{ $adspace->action_string }}
                </td>
			
				<td>
                    {{ $adspace->created_at }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
	<div class="clearfix mt10">
			{!! with(new \App\Presenters\SemanticUi\PaginatorPresenter($listdata))->render() !!}
    </div>
@endsection

{{-- 插入到尾部的代码 --}}
@section('end')

    <script>
        $.extend(ST, {
            //页面初始化方法写在此处
            pageInit: function () {
                //初始化下拉框
                ST.ddList('_ddlist').selByValue($('#_ddlist_val').val());
            }
        });
        //删除
        $('.i-del').click(function () {
            var id = $(this).attr('hidid');
            if (confirm('确定要删除吗?')) {
                $.ajax({
                    type    : 'POST',
                    url     : '{{ route("admin.ad.delete-ad-space") }}',
                    data    : { id: id },
                    dataType: 'json',
                    success : function (data) {
                        ST.tipMsg(data.info);
                        if (data.status == 'success') {
                            window.location.reload();
                        }
                    }
                });
            }
        });
    </script>

@endsection