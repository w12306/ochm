{{-- 继承主要布局模板 --}}
@extends('admin/layouts/main')

{{-- 插入到<head>的代码 --}}
@section('head')
    <title>{{env('SITENAME')}}</title>

    <script>
        $.extend(ST.ACTION,{
            addData:"{{route('admin.advertis.store-advertis')}}",   //表单保存接口
            adPosDetail:"{{route('admin.advertis.api.edit')}}", //编辑展示数据接口
            deleteAdPos:"{{route('admin.advertis.delete-advertis')}}" //广告删除接口
        });
    </script>
@endsection

{{-- 内容 --}}
@section('content')

    <div class="heading clearfix">
        <div class="pull-left">
            <h2>广告位管理>广告位列表</h2>
        </div>
		 <!-- 传递type-->
        <div class="pull-right">
                <!-- 添加广告位-->
                <a title="新增广告位" href="javascript:;" class="btn mr5 js-add-ad-type"> <i class="icon i-add"></i>新增广告位</a>  
        </div>
    </div>
	

     <table class="table table-listing mt20 border-table">
            <thead class="thead-gray">
                <tr>
                    <th>序号</th>
                    <th>广告位</th>
                    <th>code值</th>
                    <th>最大轮数</th>
                    <th>平时刊例价</th>
                    <th>周末刊例价</th>
                    <th>平时折扣</th>
                    <th>周末折扣</th>
                    <th>平时价值</th>
                    <th>周末价值</th>
                    <th>广告规格</th>
					<th>状态</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
			 @if ($listdata->count() == 0)
             <tr>
                 <td colspan="13">没有内容</td>
             </tr>
       
	   		 @else
			 	@foreach ($listdata as $d)
           		 <tr>
                    <td>{{$d->id}}</td>
                    <td>{{$d->name}}</td>
                    <td>{{$d->code}}</td>
                    <td>{{$d->max_rounds}}</td>
                    <td>{{$d->usually_price}}</td>
                    <td>{{$d->weekend_price}}</td>
                    <td>{{$d->usually_discount}}</td>
                    <td>{{$d->weekend_discount}}</td>
                    <td>{{$d->usually_value}}</td>
                    <td>{{$d->weekend_value}}</td>
                    <td>{{$d->remark}}</td>
					<td>
					@if ($d->isshow==1)
						{{$d->isshow_text}}
					@else
						<font color="#FF0000">{{$d->isshow_text}}</font>
					@endif
					</td>
                    <td>
					@if ($d->ishotgame!= 1)	
                        <a href="javascript:;" title="修改" data-id="{{$d->id}}" class="js-edit-ad-type"  ><i hidid="" class="icon i-edit" title="编辑"></i></a>
						@if ($d->isshow==1)
							<a href="javascript:;" title="删除" data-id="{{$d->id}}"  class="js-delete-ad"><i hidid="" class="icon i-del" title="删除"></i></a>
						@endif
					@endif	
                    </td>
                </tr>            
				 @endforeach
			@endif
                    
            </tbody>
        </table>
    <div class="clearfix mt10">
	{!! with(new \App\Presenters\SemanticUi\PaginatorPresenter($listdata))->render() !!}
    </div>

@endsection

{{-- 插入到尾部的代码 --}}
@section('end')

    <script>
      seajs.use('view/tool/data', function (app) {
        app({
            el: '#js-container'
        })
    })
    </script>

@endsection