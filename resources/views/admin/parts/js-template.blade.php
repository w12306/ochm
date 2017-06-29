<!--底部公共部分-->
<div id='ST_temp' style="display:none">
    <!--
    {common_login}
    <div style="width:410px;height:185px;">
     <div class="login_box" style="padding-left:10px;">
      <form id="loginForm"  onsubmit="return false;"  action="{ST.PATH.ROOT+ST.PATH.LOGIN}" method="post" dynamicForm="true" afterSubFun="_loginCB" stverify="true" ajaxpost="true" errtar="loginForm_tip">
        <div>
          <div style="height:20px;{if(!msg)}visibility:hidden;{/if}margin-right:18px;" class="msg_miss" id="loginForm_tip" >{msg||"请输入易游通行证"}</div>
          <ul>
            <li>
              <label>用户名:</label>
              <input type="text" name="username" maxlength="16" placeholder="请输入易游通行证" emsg="请输入易游通行证 请输入6-16位用户名" tabindex="1" ml="6-16" opt="rq ml">
               </li>
            <li>
              <label>密&nbsp;&nbsp;&nbsp;码:</label>
              <input type="password" maxlength="16" name="password" placeholder="请输入密码" emsg="请输入密码 请输入6-16位密码" ml="6-16" tabindex="2" opt="rq ml">
               </li>
            <li id="login_vcode_area" {if(!needCode)} style="visibility:hidden;" {/if}>
              <label>验证码</label>
              <input  id="loginForm_vcode"    type="text" tabindex=3 name="vcode" maxlength="4">
            </li>
          </ul>
          <div class="rememb">
              <a class="rememb_forget" href="http://eyus.tnt.com.com/user/forgetpass" target="_blank">忘记密码</a>
              <div class="mb_log_btn">
                <a href="javascript:;" target="_self" class="general-btn">
                    <input type="submit" value="登 录">
                </a>
             </div>
         </div>
        </div>
      </form>
    </div>
    <div class="common_box2">
    <p>还未开通？<br>赶紧免费注册一个吧！</p>
    <div class="zhuce"><a href="http://eyus.tnt.com.com/user/reg" target="_blank">新用户注册</a></div>
    <div class="intro"><em></em><a href="javascript:;" target="_self" id="login_passTip" style="clolor:#257ED1;cursor:help;">易游通行证<span>{ST.LRes.passTip}</span></a></div>
    </div>
    </div>
    {/common_login}

    {common_dc}
    <div class="droplist">
        <ul style="min-width:150px;_width:150px;">
            {for(var i=0,j=data.length,dl;i<j,dl=data[i];i++)}
            <li{if(i>=j-1)} class="m-nobdr"{/if}><a href="javascript:;" target="_self" title="{dl.text}" value="{(dl.value+'').encodeURIComponent()}" class="grn2">{dl.text}</a></li>
            {/for}
             <li ><a href="http://www.baidu.com" target="_blank"  class="grn2">tset</a></li>
        </ul>
    </div>
    {/common_dc}

    {common_ac_cnt_temp}
    <div id="{controlId}_datalist"></div>
    <div id="{controlId}_tipmsg" class="dropdown-feedback">{commonMsg}</div>
    {/common_ac_cnt_temp}

    {common_ac_list_temp}
    <ul class="dropdown-menu" style="{if(list.length>maxDisplay)}overflow-y:scroll;height:{itemHeight*maxDisplay}px;{/if}">
        {for(var i=0,j=list.length,item;i<j,item=list[i];i++)}
            <li idx="{i}" title="{item.name}"><a href="javascript:;" id="{controlId}_item_{i}">{item.name}</a></li>
        {/for}
    </ul>
    {/common_ac_list_temp}

    {common_tooltip}
    <div class="tooltip tooltip-gray">
    {if(setting.hasdir)}
      <div class="tooltip-arrow sw" id="st_tipbox_{controlId}_dir">
        <span class="tooltip-arrow-before">◆</span>
        <span class="tooltip-arrow-after">◆</span>
      </div>
    {/if}
      <div class="tooltip-inner" id="st_tipbox_{controlId}_msg">
        {data.tipmsg}
      </div>
      {if(setting.hold)}
      <a class="close" href="javascript:;" onclick="return false" id="st_tipbox_{controlId}_close">×</a>
      {/if}
    </div>
    {/common_tooltip}

    {common_pager}
    <div class="pagination">
      <ul>
        <li><a href="javascript:;">共{pageNum}页</a></li>
        <li class="first"><a href="javascript:;" class="btnIndex">首页</a></li>
        <li class="prev"><a href="javascript:;" class="btnPrev">上一页</a></li>
        <li class="pagerArea">
        {ST.JTE.fetch("common_pager_num").getFilled({cp:1,pn:pageNum,num:num})}
        </li>
        <li class="next"><a href="javascript:;" class="btnNext">下一页</a></li>
        <li class="last"><a href="javascript:;" class="btnLast">尾页</a></li>
      </ul>
    </div>
    {/common_pager}

    {common_pager_num}
    <ul>
        {if(cp>num+1)}
        <li>.....</li>
      {/if}
        {for(var i=(cp>num?cp-num:1);i<=(cp+num>=pn?pn-1:cp+num);i++)}
        {if(i==cp)}
          <li class="active"><a href="javascript:;">{i}</a></li>
        {else}
          <li><a href="javascript:;">{i}</a></li>
        {/if}
      {/for}
      {if(cp<pn-num-1)}
        <li>.....</li>
      {/if}
        {if(cp==pn)}
        <li class="active"><a href="javascript:;">{pn}</a></li>
        {else}
            <li><a href="javascript:;">{pn}</a></li>
      {/if}
    </ul>
    {/common_pager_num}

    {common_uploadPanel}
    <div {if(width)} style="width:{width+"px"||"auto"}" {/if}>
    <div class="panel_upload">
        <div>
            <span class="upload_Btn"></span><button class="upload_startBtn" >开始上传</button>
        </div>
    </div>
    <div class="panel_table">
     <div class="panel_head">
     {title}列表(总共可以添加<span class="file_upload_limit">-</span>个)
     </div>
      <div class="panel_body mh190">
        <table class="st_ui_table" width="100%" height="100%">
            <thead>
                <tr class="h20 lh20">
                    <th width="35%" class="fileupname">{title}名</th>
                    <th width="25%" class="fileupsize">{title}大小</th>
                    <th width="20%" class="fileupstat">状态</th>
                    <th width="20%" class="fileupoprt">操作</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="4">
                    <div class="st_ui_scroll h140 file_upload_list">
                        <table width="100%" >
                            <tr>
                                <td style="vertical-align:middle;">
                                    <div class="tlc">请选择{title}开始上传</div>
                                </td>
                            </tr>
                        </table>
                     </div>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="h20 lh20">
                    <td colspan="4" style="padding:0 10px;">
                        <span class="fr icon_tip h20"></span>
                        已选择<span class="file_uploaded_count">0</span>个{title}(共大小<span class="file_uploaded_size">0.0KB</span>)
                    </td>
                </tr>
            </tfoot>
        </table>
       </div>
    </div>
    </div>
    {/common_uploadPanel}

    {common_uploadList_tmp}
    {var l=data.length}
    <table width="100%" {if(l==0)} height="100%" {/if}>
        {for(var i=0,fl;i<l,fl=data[i];i++)}
         <tr {if(i%2==0)} class="bggray" {/if}>
            <td width="35%"><div style="width:136px;overflow:hidden;white-space: nowrap;" title="{fl.name}"><span class="icon_file file_{fl.type.replace(".","").toLowerCase()}"></span>{fl.name}</div></td>
            <td width="25%">{fl.size/1000|0}KB</td>
            <td width="20%" id="uploadFile_{fl.id}">{fileState[fl.filestatus]}</td>
            <td width="20%"><span class="icon_del iblk" title="删除" data-id={fl.id}></span></td>
         </tr>
         {/for}
         {if(l==0)}
         <tr>
            <td style="vertical-align:middle;">
                <div class="tlc">请选择{type}开始上传</div>
            </td>
         </tr>
         {/if}
    </table>
    {/common_uploadList_tmp}



    {common_tip}
    <div class="notice notice-{type}">
        <i class="icon"></i>
      <span class="notice-inner">
        {msg}
      </span>
    </div>
    {/common_tip}

    {common_noticebox}
    <div class="dialog" >
      <div class="dialog-head" onmousedown="return false;">
        <div class="dialog-title">{ops.title}</div>
      </div>
      <div class="dialog-body" style="height:{ops.height-52}px;">
          {ops.content}
      </div>
    </div>
    {/common_noticebox}

    {common_msgbox}
    <div class="dialog" id="st_msgbox_outer_{controlId}">
      <div class="dialog-head" onmousedown="return false;"  id="st_msgbox_tlecnt_{controlId}">
        <div class="dialog-title">{ops.title}</div>
        <a class="close" href="javascript:;" onclick="return false;" id="st_msgbox_{controlId}_close">×</a>
      </div>
      <div class="dialog-body{if(ops.url)} dialog-body-noborder{/if}" id="st_msgbox_inner_{controlId}">
        <div id="st_msgbox_cnt_{controlId}">
          {if(ops.url)}
          <iframe style="height:100%;width:100%; display:none;"  frameborder="0"{if(ops.ifmops)}{for(var p in ops.ifmops)}{p}="{ops.ifmops[p]}"   {/for}{/if}src="{ops.url}" ></iframe>
          <div class="center" id="{controlId}_tip">正在载入，请稍后...</div>
          {else}
          {ops.content}
          {/if}
        </div>
      </div>
      {if(!ops.hideBottom&&btns.length)}
      <div class="dialog-foot" id="st_msgbox_btns_{controlId}">
      {for(var i=0,j=btns.length;i<j;i++)}<input id="st_msgbox_{controlId}_btn_{i}" type="button" value="{btns[i].text.length>2?btns[i].text:btns[i].text.split("").join(" ")}" class="btn btn-blue mr5">{/for}
      </div>
      {/if}
    </div>
    {/common_msgbox}

    {common_ddl}
    <ul class="dropdown-menu">
      {for(var i=0,j=datalist.length,dl;i<j,dl=datalist[i];i++)}
      <li>
          <a href="javascript:;" target="_self" value="{dl.value}" title="{dl.text}">
          {ST.JTE.fetch('common_ddl_item').getFilled({data:dl})}
          </a>
      </li>
      {/for}
    </ul>
    {/common_ddl}

    {common_ddl_item}
    {if(data.icon)}<img src="{data.icon}" height="15"/> {/if}{data.text}
    {/common_ddl_item}

    {form_erromsg_temp}
    <div class="alert alert-{type}">
      <i class="icon"></i>
      <span>{msg}</span>
    </div>
    {/form_erromsg_temp}

    {form_confmsg_temp}
    <div class="alert alert-warning">
      <i class="icon i-dialog-warning"></i>
      <span class="alert-text">{msg}</span>
    </div>
    {/form_confmsg_temp}

    {common_exec_temp}
    <div class="dialog-prompt prompt-warning">
      <i class="icon"></i>
      <div class="dialog-inner">
        {msg}
      </div>
    </div>
    {/common_exec_temp}

    {common_vcode}
    <a id="PVCode_Img_{cID}" href="javascript:;" title="看不清？换一张">
      {if(imgsrc)}<img src="{imgsrc}" alt="auth Code">{/if}
    </a>
    {/common_vcode}

    {common_rating}
    <div class="rating-inner" id="{controlId}_rating_area">
      <div class="rating-active" id="{controlId}_active_area" style="width:0"></div>
      <div class="rating-hover" id="{controlId}_hover_area" style="width:0"></div>
      <div class="rating-star"></div>
    </div>
    <span class="rating-text" id="{controlId}_text_area"></span>
    {/common_rating}
    {common_spinner}
    <div class="spinner" id="{id}_st_spinner">
      <div class="spinner-label" id="{id}_st_spinner_wrap"></div>
      <div class="spinner-btn">
        <a class="spinner-prev" href="javascript:;" data-cmd="add">prev</a>
        <a class="spinner-next" href="javascript:;" data-cmd="discount">next</a>
      </div>
    </div>
    {/common_spinner}

    {common_timepicker}
    <div class="spinner">
      <div class="spinner-label">
        {if(config.showHours)}<input class="input-mini" id="{controlId}_st_timepicker_H" type="text" value="">{/if}
        {if(config.showMinutes)}{if(config.showHours)}:{/if}<input class="input-mini" id="{controlId}_st_timepicker_M" type="text" value="">{/if}
        {if(config.showSeconds)}{if(config.showHours||config.showMinutes)}:{/if}<input class="input-mini" id="{controlId}_st_timepicker_S" type="text" value="">{/if}
      </div>
      <div class="spinner-btn">
        <a class="spinner-prev" href="javascript:;" data-cmd="add">prev</a>
        <a class="spinner-next" href="javascript:;" data-cmd="discount">next</a>
      </div>
    </div>
    {/common_timepicker}

    {common_vcode}
    <a id="PVCode_Img_{cID}" href="javascript:;" title="看不清？换一张">
      {if(imgsrc)}<img src="{imgsrc}" alt="auth Code">{/if}
    </a>
    {/common_vcode}

    {common_cityselect}
    <div class="dataSelect-source" id="{controlId}_plist"></div>
    <div class="dataSelect-btn">
      <a class="btn btn-blue" data-cmd="fillClist" href="javascript:;">选择-&gt;</a>
    </div>
    <div class="dataSelect-selected" id="{controlId}_clist"></div>
    {/common_cityselect}

    {common_cityselect_plist}
    {if(data && data.length>0)}
    <div class="dataSelect-list">
    <ul class="dropdown-menu">
      {for(var i=0,l=data.length,d;i<l,d=data[i];i++)}
        <li>
          <label for="{controlId}_plist_a_{i}">
            <input type="checkbox" class="fn_{controlId}_a" id="{controlId}_plist_a_{i}" data-cmd="checkAll" data-{config.idkey}="{d[config.idkey]}" data-pid="{controlId}_plist_checkAll" value=""/>{d.name}
          </label>
          {if(d.data && d.data.length>0)}
          <ul class="dropdown-submenu">
          {for(var j=0,jl=d.data.length,jd;i<jl,jd=d.data[j];j++)}
            <li>
              <input type="checkbox" class="fn_{controlId}_p" id="{controlId}_plist_p_{i}_{j}" data-pid="{controlId}_plist_a_{i}" data-{config.idkey}="{jd[config.idkey]}" data-cmd="filterPVS" value=""{if(selected.getIndex(jd[config.idkey])>-1)} checked="checked"{/if} />
              <label for="{controlId}_plist_p_{i}_{j}">{jd.name}</label>
            </li>
          {/for}
          </ul>
          {/if}
        </li>
      {/for}
    </ul>
    </div>
    <div class="dataSelect-pager">
        <input type="checkbox" id="{controlId}_plist_checkInverse" data-cmd="checkInverse" value="" data-id="{controlId}_plist_checkAll"/>
        <label for="{controlId}_plist_checkInverse" class="pr10">反选</label>

        <input type="checkbox" id="{controlId}_plist_checkAll" data-cmd="checkAll" value="" />
        <label for="{controlId}_plist_checkAll">全选</label>
      </div>
    {/if}
    {/common_cityselect_plist}

    {common_cityselect_clist}
    {if(data && data.length>0)}
    <div class="dataSelect-list">
    <ul class="dropdown-menu">
      {for(var i=0,l=data.length,d;i<l,d=data[i];i++)}
      <li>
        <label for="{controlId}_clist_p_{i}">
          <input type="checkbox" class="fn_{controlId}_p" id="{controlId}_clist_p_{i}" data-cmd="selectPVS" data-{config.idkey}="{d[config.idkey]}" data-pid="{controlId}_clist_checkAll" value=""/>{d.name}
        </label>
        {if(d.data && d.data.length>0)}
        <ul class="dropdown-submenu">
        {for(var j=0,jl=d.data.length,jd;i<jl,jd=d.data[j];j++)}
          <li>
            <input type="checkbox" class="fn_{controlId}_c" id="{controlId}_clist_c_{i}_{j}" data-{config.idkey}="{jd[config.idkey]}" data-pid="{controlId}_clist_p_{i}" data-cmd="selectCity" value=""{if(selected.getIndex(jd[config.idkey])>-1)} checked="checked"{/if} />
            <label for="{controlId}_clist_c_{i}_{j}">{jd.name}</label>
          </li>
        {/for}
        </ul>
        {/if}
      </li>
      {/for}
    </ul>
    </div>
    <div class="dataSelect-pager">
        <input type="checkbox" id="{controlId}_plist_checkInverse" data-cmd="checkInverse" value="" data-id="{controlId}_clist_checkAll"/>
        <label for="{controlId}_plist_checkInverse" class="pr10">反选</label>

        <input type="checkbox" id="{controlId}_clist_checkAll" data-cmd="checkAll" value="" />
        <label for="{controlId}_clist_checkAll">全选</label>
      </div>
    {/if}
    {/common_cityselect_clist}

    {common_dataselect}
    <div class="dataSelect-source">
      <div class="dataSelect-search">
        {if(config.enableSearch)}
        <input class="input" type="text" id="{controlId}_st_dataselect_searchIpt">
        <button class="btn" data-cmd="search" type="button">Search</button>
        {/if}
      </div>
      <div class="dataSelect-list" id="{controlId}_st_dataselect_list"></div>
      <div class="dataSelect-pager">
        <div id="{controlId}_st_dataselect_pager" class="pagination pagination-smallest"></div>
      </div>
    </div>
    <div class="dataSelect-selected" id="{controlId}_st_dataselect_selected"></div>
    {/common_dataselect}

    {common_dataselect_datalist}
    {if(data&&data.length>0)}
    <ul>
    {for(var i=0,l=data.length,d;i<l,d=data[i];i++)}
      <li><a{if(pars['selected'].getIndex(d[pars['idkey']])>-1)} class="active"{/if} href="javascript:;" data-{pars['idkey']}="{d[pars['idkey']]}" data-cmd="toggle" data-pars="{pars['idkey']}:{d[pars['idkey']]}">{d.title}</a></li>
    {/for}
    </ul>
    {/if}
    {/common_dataselect_datalist}

    {common_dataselect_selected}
    <div class="dataSelect-search">
      您已选择：
    </div>
    <div class="dataSelect-list">
    {if(data&&data.length>0)}
      <ul>
      {for(var i=0,l=data.length,d;i<l,d=data[i];i++)}
        <li><a href="javascript:;" data-{config.idkey}="{d[config.idkey]}" data-cmd="toggle" data-pars="{config.idkey}:{d[config.idkey]}">{d.title}</a></li>
      {/for}
      </ul>
    {/if}
    </div>
    <div class="dataSelect-pager"></div>
    {/common_dataselect_selected}

    {common_dataselect_pager}
      <ul>
        <li class="prev"><a href="javascript:;" class="btnPrev">&lt;</a></li>
        <li class="pagerArea">
        {ST.JTE.fetch("common_dataselect_pager_num").getFilled({cp:1,pn:pageNum,num:num})}
        </li>
        <li class="next"><a href="javascript:;" class="btnNext">&gt;</a></li>
      </ul>
    {/common_dataselect_pager}

    {common_dataselect_pager_num}
    {var x = parseInt((cp-1)/num,10)*num+1}
    {var j = x>pn-num?pn-num:x}
    {var i = j<2?2:j}
    {var l=i+num-1>=pn-1?pn-1:(i+num-1)}
    <ul>
    <li{if(cp==1)} class="active"{/if}><a href="javascript:;">1</a></li>
    {if(i>2)}
    <li>.</li>
    {/if}
    {for(;i<=l;i++)}
    <li{if(i==cp)} class="active"{/if}><a href="javascript:;">{i}</a></li>
    {/for}
    {if(l<pn-1)}
    <li>.</li>
    {/if}
    <li{if(cp==pn)} class="active"{/if}><a href="javascript:;">{pn}</a></li>
    </ul>
    {/common_dataselect_pager_num}
    {common_calender_cnt_temp}
    <div class="calendar" style="width: {205*displayMonth-5}px;">
      <div class="calendar-head">
        <a class="calendar-prev" id="PCAL_PM_{controlId}" href="javascript:;" target="_self">prev</a>
        <a class="calendar-next" id="PCAL_NM_{controlId}" href="javascript:;" target="_self">next</a>
        <div class="calendar-title">
          <div class="dropdown" id="PCAL_CY_{controlId}">
            <a class="dropdown-toggle" href="javascript:;">
              <span class="dropdown-label" id="PCAL_CY_{controlId}_lab"></span>
              <span class="dropdown-arrow"><i class="icon i-chevron-down"></i></span>
            </a>
            <input type="hidden" value="" id="_ddlist_val">
          </div>
          年
          <div class="dropdown" id="PCAL_CM_{controlId}">
            <a class="dropdown-toggle" href="javascript:;">
              <span class="dropdown-label" id="PCAL_CM_{controlId}_lab"></span>
              <span class="dropdown-arrow"><i class="icon i-chevron-down"></i></span>
            </a>
            <input type="hidden" value="" id="_ddlist_val">
          </div>
          月
        </div>
      </div>
      <div class="calendar-body">
        <div class="clearfix" id="PCAL_DAYS_{controlId}"></div>
         {if(timePicker)}
         <input id="PCAL_TIME_{controlId}" type="hidden" value=""/>
        {/if}
      </div>
      <div class="calendar-foot">
      {if(!mult)}
      <button class="btn btn-mini" id="PCAL_TODAY_{controlId}" type="button">今天</button>
      {/if}
      {if(showClearBtn)}
      <button class="btn btn-mini" id="PCAL_CLEAR_{controlId}" type="button">清空</button>
      {/if}
      {if(timePicker)}
      <button class="btn btn-mini btn-blue" id="PCAL_SURE_{controlId}" type="button">确定</button>
      {/if}
      </div>
    </div>
    {/common_calender_cnt_temp}
    {common_calender_days_temp}
    {for(var m=0;m<displayMonth;m++,month++)}
    {year=(month>12)?year+1:year;month=(month>12)?1:month;}
    <table class="calendar-day month-{month} {if(m<displayMonth-1)}mr5{/if}">
      <thead>
      <tr>
        <th>日</th>
        <th>一</th>
        <th>二</th>
        <th>三</th>
        <th>四</th>
        <th>五</th>
        <th>六</th>
      </tr>
      </thead>
      <tbody>
        {var days=30,fd=new Date(year,month-1,1),tit=year+"-"+(month<10?'0'+month:month);}
        {days=ST.CalenderCal.getDays(year,month)}
        {var ldays=ST.CalenderCal.getDays(year,month,-1)}
        {for(var x=0,xd=fd.getDay();x<xd;x++)}
        <td class="disabled">{ldays-xd+x+1}</td>
        {/for}
        {for(var i=1,dd;dd=(tit+"-"+(i<10?'0'+i:i)),i<=days;i++)}
        {if(mult&&mult.length==2)}
        <td class="{(mult[0]<=dd && dd<=mult[1])?'active':'disabled'}">
          {else}
        <td class="{if(dd==currentDate)}active{else if(dd<mindate || dd>maxdate)}disabled{/if}">
          {/if}
          {if(dd>=mindate && dd<=maxdate)}
          <a href="javascript:;" target="_self" date="{dd}" title="{dd}">{i<10?'&nbsp;'+i:i}</a>
          {else}
          {i<10?'&nbsp;'+i:i}
          {/if}
        </td>
        {if((i+fd.getDay())%7==0)}
        </tr><tr>
          {/if}
          {/for}
          {if((days+fd.getDay())%7>0)}
          {for(var j=0,z=7-(days+fd.getDay())%7;j<z;j++)}
          <td class="disabled">{j+1}</td>
          {/for}
          {/if}
        </tr>
      </tbody>
    </table>
    {/for}
    {/common_calender_days_temp}


    {common_dataselect_roledatalist}
    {if(data&&data.length>0)}
    <ul>
    {for(var i=0,l=data.length,d;i<l,d=data[i];i++)}
      <li><a{if(pars['selected'].getIndex(d[pars['idkey']])>-1)} class="active"{/if} href="javascript:;" data-{pars['idkey']}="{d[pars['idkey']]}" data-cmd="toggle" data-pars="{pars['idkey']}:{d[pars['idkey']]}">{d.name}</a></li>
    {/for}
    </ul>
    {/if}
    {/common_dataselect_roledatalist}

    {common_dataselect_roleselected}
    <div class="dataSelect-search">
          您已选择：
        </div>
        <div class="dataSelect-list">
        {if(data&&data.length>0)}
          <ul>
          {for(var i=0,l=data.length,d;i<l,d=data[i];i++)}
            <li><a href="javascript:;" data-{config.idkey}="{d[config.idkey]}" data-cmd="toggle" data-pars="{config.idkey}:{d[config.idkey]}">{d.name}</a></li>
          {/for}
          </ul>
        {/if}
        </div>
        <div class="dataSelect-pager"></div>
    {/common_dataselect_roleselected}


    {ad_space_list}
      <table class="table table-listing">
        {if(data)}
          {for(var dl in data)}
          <tr>
              <td class="span2">{dl}</td>
              {if (data[dl]&& data[dl].length>0)}
              <td>
                {for(var i=0;i<data[dl].length;i++)}
                  <input type="checkbox" name="ad_space_ids[]" value="{data[dl][i].id}"><span class="pr10">{data[dl][i].name}</span>
                  {/for}
              </td>
              {/if}
          </tr>
          {/for}
        {/if}
      </table>
    {/ad_space_list}
    
    {publish_detail}
      <h3>发布详情</h3>

      <table class="table-voucher mt10">
          <tr>
              <td>发布时间</td>
          </tr>
          {for(var i=0;i<data.length;i++)}
          <tr>
              <td>{data[i].date}</td>
          </tr>
          {/for}
      </table>
    {/publish_detail}
    
    {areaList}
      {for(var cl in clist)}
        <dt>{provs[cl]}</dt>
        <dd class="pl10">
        {for(var i=0;i<clist[cl].length;i++)}
          {clist[cl][i].name} 
        {/for}
        </dd>
      {/for}
    {/areaList}

    -->
</div>