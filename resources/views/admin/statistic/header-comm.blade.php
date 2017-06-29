
                        <tr>
                            <td class="span4">客户名称</td>
                            <td>
                                <select name="" id="" class="js-selectbox" placeholder="请选择-支持多选" multiple="multiple">
								@foreach ($companyData as $d)
												<option value="{{$d['key']}}">{{$d['value']}}</option>
								@endforeach				
								 </select>
								 <input name="company_id" type="hidden" class="hidden js-selectbox-multiple-txt"  value="{{Request::get('company_id')}}">
                            </td>
                        </tr>
                        <tr>
                            <td class="span4">合作方</td>
                            <td>
                                <select name="" id="" class="js-selectbox" placeholder="请选择-支持多选" multiple="multiple">
								@foreach ($partnersData as $d)
												<option value="{{$d['key']}}">{{$d['value']}}</option>
								@endforeach				
								 </select>
								 <input name="partner_id" type="hidden" class="hidden js-selectbox-multiple-txt"  value="{{Request::get('partner_id')}}">
                            </td>
                        </tr>
						
						<tr>
                            <td class="span4">执行小组</td>
                            <td>
                             <table class="table table-listing">
                        <tbody>
						<?php foreach($team as $k=>$t){  ?>
						<tr>
                            <td style="width: 120px; text-align: right;">
                        	<span style="text-align:left; width: auto;" class="inline-block span3"><?php echo $k; ?><input type="checkbox" onclick="selectAllBoxId('team_<?php echo $k; ?>',this)" name="team_<?php echo $k; ?>_parent" /></span>
                            </td>
                            <td>
                            <?php foreach($t as $d){  ?>
								<?php if(!empty(Request::get('team'))){ ?>
								<label style="text-align:left;width: auto;margin-left: 10px;" class="inline-block span3" for="">
                               <input type="checkbox" value="{{$d['value']}}" <?php if(in_array($d['value'],Request::get('team'))){echo 'checked="checked"'; }?> data-holder="#deparment_help" data-group="deparment" data-rules="least" data-department-name="{{$d['key']}}" name="team[]" id="team_<?php echo $k; ?>" onclick="childSelectAllBox('team_<?php echo $k; ?>_parent',this)">{{$d['key']}}
                            	</label>
								<?php }else{ ?>
								<label style="text-align:left;width: auto;margin-left: 10px;" class="inline-block span3" for="">
                               <input type="checkbox" value="{{$d['value']}}" data-holder="#deparment_help" data-group="deparment" data-rules="least" data-department-name="{{$d['key']}}" name="team[]" id="team_<?php echo $k; ?>" onclick="childSelectAllBox('team_<?php echo $k; ?>_parent',this)">
                                {{$d['key']}}
                            	</label>
								<?php } ?>
							<?php } ?>                                
                            </td>
                        </tr>
						<?php }	?>    
					</tbody>
				</table>
                            </td>
                        </tr>
             <tr>
                <td class="span4">业务线:<input type="checkbox" onclick="selectAllBox('business_line[]',this)" name="business_line_parent" /></td>
                <td>
				@foreach ($business_line as $d)
					<?php if(!empty(Request::get('business_line'))){ ?>
					<label><input type="checkbox" name="business_line[]" onclick="childSelectAllBox('business_line_parent',this)" value="{{$d['key']}}"  <?php if(in_array($d['key'],Request::get('business_line'))){echo 'checked="checked"'; }?> />&nbsp;&nbsp;{{$d['value']}}&nbsp;&nbsp;</label>
					<?php }else{ ?>
					<label><input type="checkbox" name="business_line[]" onclick="childSelectAllBox('business_line_parent',this)" value="{{$d['key']}}"  />&nbsp;&nbsp;{{$d['value']}}&nbsp;&nbsp;</label>
					<?php } ?>
				@endforeach
                </td>
            </tr>
						
			
						

<script>
	function selectAllBox(name,obj){
		$("input[name='"+name+"']").each(function(i){
			if(obj.checked){
				this.checked=true;
			}else{
				this.checked=false;
			}
		});	
	}
	function selectAllBoxId(name,obj){
		$("input[id='"+name+"']").each(function(i){
			if(obj.checked){
				this.checked=true;
			}else{
				this.checked=false;
			}
		});	
	}
	function childSelectAllBox(name,obj){
		$("input[name='"+name+"']").each(function(i){
			if(!obj.checked){
				this.checked=false;
			}
		});	
	}
</script>                   

                 