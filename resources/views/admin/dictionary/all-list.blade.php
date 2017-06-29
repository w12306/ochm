{{-- 继承主要布局模板 --}}
@extends('admin/layouts/main')

{{-- 插入到<head>的代码 --}}
@section('head')
    <title>{{env('SITENAME')}}</title>

@endsection

{{-- 内容 --}}
@section('content')
	<script>
        $.extend(ST.ACTION,{
            addData:"{{ route("admin.config.dictionary.store") }}",   //新增、编辑数据form提交接口
			dataStatusChange:"{{ route("admin.config.dictionary.update")}}" , //启用，停用接口
			getdep:"{{ route("admin.config.dictionary.api.get-dept") }}"//获得部门数据
        });
    </script>
    <div class="heading clearfix">
        <div class="pull-right">
            
        </div>
    </div>

            <ul class="nav nav-tabs mt10 mb10">
			@foreach ($typedata as $k=>$d)
			<?php 
			if(empty($type)){$type=$k;}
			$active="";
			if($type==$k){$active='class="active"';}
			?>
			<li <?php echo $active;?> ><a href="{{ route('admin.config.dictionary.dictionary-list') }}/{{$k}}">{{$d}}</a></li> 
			@endforeach	   
        	</ul>

        <div class="heading clearfix">
            <!-- 传递type-->
            <input type="hidden" id="type-input" data-type="{{$type}}" data-type-name="{{$typedata[$type]}}">
            <div class="pull-right">
			<?php if($type=="dept"){?>
				<!-- 添加部门-->
                <a title="新增部门" href="javascript:;" class="btn mr5 js-add-dep-type"> <i class="icon i-add"></i>新增</a>  
			<?php }else if($type=="team"){?>
				<!-- 添加执行小组时候 dep 对应部门-->
                <a title="新增执行小组" href="javascript:;" class="btn mr5 js-add-team-type" data-dep='{{$deptjson}}'> <i class="icon i-add"></i>新增</a>
			<?php }else{ ?>
			<a title="新增业务类型" href="javascript:;" class="btn mr5 js-add-data-type"><i class="icon i-add"></i>新增</a> 
			<?php }?>
			
			
			
			 	

            
  
            </div>
        </div>

        <table class="table table-listing mt20 border-table">
            <thead class="thead-gray">
                <tr>
                    <th>序号</th>
                    <th>{{$typedata[$type]}}名称</th>
					
					<?php  if($type=="team"){?>
					<th>所属部门</th>
					<?php }?>
					
					<?php  if($type=="dept"){?>
					<th>部门合同标号</th>
					<?php }?>
					
					<?php  if($type=="business_line"){?>
					<th>业务类型</th>
					<?php }?>
					<!--<th>VALUE</th>-->
					<th>状态</th>
                    <th>描述</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
			@foreach ($listdata as $d)
                <tr>
                    <td>{{$d->id}}</td>
                    
                    <td>
					<?php if($d->type=="team"){
						echo $d->key.'('.$d->v1.')';
					}else{
						echo $d->key;
					}?>
					</td>
					
					<?php  if($type=="business_line"){?>
					<td><?php if($d->v1==1){echo '外部业务线 ';}else{echo '内部业务线';}?></td>
					<?php }?>
					
					<?php  if($type=="dept"){?>
					<td><?php echo $d->v1;?></td>
					<?php }?>
					
					<?php  if($type=="team"){?>
					<td><?php echo $d->v1;?></td>
					<?php }?>
					
					<td>{{$d->status_text}}</td>
					<td>{{$d->remark}}</td>
                    <td>
					<?php if($type=="dept"){?>
						<a href="javascript:;" title="修改" data-id="{{$d->id}}" class="js-edit-dep-type" data-name="{{$d->key}}" data-detail="{{$d->remark}}" data-value="{{$d->value}}"  data-contract='{{$d->v1}}'>部门修改</a>
					<?php }else if($type=="team"){?>
						<a href="javascript:;" title="修改" data-id="{{$d->id}}" class="js-edit-team-type" data-name="{{$d->key}}" data-detail="{{$d->remark}}" data-value="{{$d->value}}"  data-dep='[{"key": "\u534e\u5357\u533a","value": "\u534e\u5357\u533a","s": 0}, {"key": "\u534e\u5317\u533a","value": "\u534e\u5317\u533a","s": 1}]'>修改</a>
					<?php }else if($type=="business_line"){?>
						<a href="javascript:;" title="修改" data-id="{{$d->id}}" class="js-edit-data-type" data-name="{{$d->key}}" data-detail="{{$d->remark}}"   data-btype='{{$d->v1}}'>修改</a>
					<?php }else{ ?>
					<a href="javascript:;" title="修改" data-id="{{$d->id}}" data-name="{{$d->key}}" data-detail="{{$d->remark}}"  class="js-edit-data-type">修改</a>
					<?php }?>
                        
						
						
						
						
						
						<?php 
						$status=1;
						if($d->status==1){
							$status=0;
						}?>
                        <a href="javascript:;" title="启用(停用)" data-id="{{$d->id}}" data-status="{{$status}}" class="js-data-status">
						<?php if($d->status==1){ 
							echo ' <font color="#FF0000">停用</font> ';
						}else{
							echo ' 启用 ';
						}?>	</a>
						
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
    seajs.use('view/tool/data', function (app) {
        app({
            el: '#js-container'
        })
    })
    </script>

@endsection