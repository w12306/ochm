{{-- 继承主要布局模板 --}}
@extends('admin/layouts/main')

{{-- 插入到<head>的代码 --}}
@section('head')
    <title>{{env('SITENAME')}}</title>
	<script>
        $.extend(ST.ACTION,{
            
        });
    </script>
@endsection

{{-- 内容 --}}
@section('content')

    <div class="heading clearfix">
        <div class="pull-left">
		<h2>个人密码修改 </h2>        
        </div>
    </div>

     <div id="js-container">
	 <form action=""  method="get"  onsubmit="return false;">
	 	<input type="hidden" value="1" name="submit"  id="submit"/>
		<input type="hidden" value="{{$id}}" name="user_id"  id="user_id"/>
		<input type="hidden" value="{{ route('admin.password')}}" name="action_url" id="action_url" />
		 <table class="table table-listing table-hovered">
        <tbody>
        <tr>
            <td class="span4"><em class="text-red">* </em>原密码：</td>
            <td>
               <input type="password" name="old_psw" value="" id="old_psw" autocomplete="false"/>(重新输入原密码)
            </td>
        </tr>
		<tr>
            <td class="span4"><em class="text-red">* </em>新密码：</td>
            <td>
               <input type="password" name="new_psw" value="" id="new_psw" autocomplete="false"/>
            </td>
        </tr>
		<tr>
            <td class="span4"><em class="text-red">* </em>确认新密码：</td>
            <td>
               <input type="password" name="cf_new_psw" value="" id="cf_new_psw" autocomplete="false"/>
            </td>
        </tr>
      
        <tr>
            <td class="span4"></td>
            <td>
                <input class="btn btn-blue" type="submit" value="确定" onclick="ck();">
   
            </td>
        </tr>
        </tbody>
    </table>
	</form>
    </div>  

@endsection

{{-- 插入到尾部的代码 --}}
@section('end')

<script>
function ck(){
	var postdata="";
	if($.trim($("#old_psw").val())=="" ){
		alert("请填写原始密码!");
		return false;
	}
	postdata="old_psw="+$.trim($("#old_psw").val());
	
	if($.trim($("#new_psw").val())=="" ){
		alert("请填写新密码!");
		return false;
	}
	if($.trim($("#cf_new_psw").val())=="" ){
		alert("请填写确认密码!");
		return false;
	}
	
	if($.trim($("#new_psw").val())!=$.trim($("#cf_new_psw").val())){
		alert("确认密码和新密码不一致!");
		return false;
	}
	
	if($.trim($("#old_psw").val())==$.trim($("#cf_new_psw").val())){
		alert("新密码不能和原来密码一样！");
		return false;
	}
	postdata=postdata+"&new_psw="+$.trim($("#new_psw").val())+"&id="+$("#user_id").val()+"&submit="+$("#submit").val();
	var url=$("#action_url").val();
	$.ajax({
				   type: "get",
				   url: url,
				   data: postdata,
				   dataType: "json",
				   timeout : 10000,
				   success: function(d){
					  if(d.status=="error"){
					  	alert(d.info);
						return false;
					  }else{
					  	alert("更新成功!请重新登录!");
						location.reload();
						return true;
					  }	
					  					
				   } ,
				   error: function(XMLHttpRequest,textStatus){  
						 if(textStatus=='timeout'){//超时操作
							alert("对不起，连接超时，请重新操作。");
						 }else{//其他错误
							alert("操作失败！");
						 }
						 return false;	
				   } 
	});
	
	
	return false;
		
}
</script>


@endsection