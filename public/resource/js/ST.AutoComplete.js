/*
    自动完成类_开发版
*/
ST.AutoComplete=$.createClass({},{
    /*
        获取数据的服务器端默认页面路径
    */
    server:'404.html',
    /*
        返回的数据没有内容时默认提示语
    */
    emptyMsg:'没有匹配内容',
    /*
        获取数据出错时的显示的内容
    */
    errorMsg:'服务器忙',
    /*
        普通信息
    */
    commonMsg:'请输入内容',
    /*
        容器模板id
    */
    cntTempId:'common_ac_cnt_temp',
    /*
        列表模板id
    */
    listTempId:'common_ac_list_temp',
    /*
        加载数据提示语
    */
    loadingData:'正下载数据...',
    /*
        提示div样式
    */
    Css:'dropdown',
	/*
        提示hover
    */
	hoverCss:'',
    /*
        查询字符串格式
    */
    queryFormat:'w=$q&type=$p',
	/*
        查询类型
    */
	queryType:'-1', 
    /*
        查询延时，优化使用
    */
    queryDelay:500,
    /*
        列表项tagName
    */
    listItemTagName:'li',
	/*
		使用静态数据
	*/
	useStaticData:false,
	/*
		搜索匹配的字段
	*/
	regField:"name",
	highlight:"true",
	/*
	最大显示个数
	*/
	itemHeight:21,
	maxDisplay:10,
	tongji:false,
    /****************
	*以下是私有变量区
	****************/
    /*
        历史值
    */
    _lastVal:'',
    /*
        定时器对象
    */
    _timer:null,
    /*
        历史选中索引
    */
    _lastIdx:-1,
    /*
        历史鼠标悬停对象
    */
    _lastMoverObj:null,
    /*
        清空历史变量信息
    */
	_clear:function () {
		var a=this;
		a._lastMoverObj&&$(a._lastMoverObj).toggleClass(a.hoverCss||a.Css+"_hover",false);
		a._lastMoverObj=null;
		a._lastIdx=-1;
		a._timer&&clearTimeout(a._timer);
		a._timer=null
	},
	_isNodeContains:function (a,b) {
		return a.contains?a!=b&&a.contains(b):!!(a.compareDocumentPosition(b)&16)
		//compareDocumentPosition 未定义
	},
	_getNode:function (a,b) {
		for(;a!=b;) {
			if(a.tagName)if(a.tagName.toLowerCase()!=this.listItemTagName) {
				a=a.parentNode;
				continue
			}else return a;
			return null
		}
	},
	_buildTipUI:function (a) {
		var b=this,aa=document.getElementById(a),e=$.getBound(b.cid);
		if(!aa){
			c=$("<div>").attr({"id":a,"class":b.Css}).css({
				position:"absolute",
				width:e.w-2+"px",
				left:e.x+"px",
				top:e.y+e.h-1+"px"
			});
			aa=c[0];
		}
		ST.JTE.fetch(b.cntTempId).toFill(c,{
			controlId:b.cid,commonMsg: b.commonMsg
		});		
		c.appendTo("body");
		b.$dMousedown=function (d) {
			$.stopEvent(d);
			d=d.target;
			if(b._isNodeContains(aa,d))if(d=b._getNode(d,aa))if(d.tagName.c(new RegExp("^"+b.listItemTagName+"$","i"))) {
				var _v1=$("#"+b.cid+"_item_"+b._lastIdx).text();
				$("#"+b.cid).val(_v1);
				c.hide();			
				var f={};
				f.target=d;
				b.onFill(b,f);
				b.onClickFill(b,f);		
				if(b._lastVal!=_v1) b.$data=b.onValueChanged(_v1);
				b._lastVal=_v1;
			}
		};
		b.$dMousemove=function (d) {
			d=d.target;
			var f;
			if(b._isNodeContains(aa,d))if(d=b._getNode(d,aa))if(d.tagName.c(new RegExp("^"+b.listItemTagName+"$","i"))) {
				f=parseInt($(d).attr("idx"));
				if(b._lastIdx!=f) {
					b._lastMoverObj&&
					$(b._lastMoverObj).toggleClass(b.hoverCss||b.Css+"_hover",false);
					b._lastMoverObj=d;
					b._lastIdx=f;
					$(d).toggleClass(b.hoverCss||b.Css+"_hover",true);
					b.onMouseover(b,{target:d});
				}
			}
		};
		b.$dMouseout=function () {
			b._clear()
		};
		c.bind("mousedown",function(e){b.$dMousedown(e)})
		 .bind("mousemove",function(e){b.$dMousemove(e)})
		 .bind("mouseout",function(e){b.$dMouseout(e)});
		
		return c;
	},
	_buildContent:function (a,b) {
		var e=this;
	    a=ST.JTE.fetch(e.listTempId).getFilled({controlId:e.cid,list:a,key:b,regFiled:e.regField,maxDisplay:e.maxDisplay,itemHeight:e.itemHeight});
		return a
	},
	_loadData:function (a,k) {
		var b=this,e=b.cid+"_datalist",c=b.cid+"_tipmsg",d=b.cid+"_container";
		if(b.$data) {
			if(b.$data[0]&&b.$data[0].total) {
				$("#"+c).hide();
				$("#"+e).html(b._buildContent($.Lang.isArray(b.regField)?b.$data:b.$data[0][b.regField],k)).show();
			}else {
				$("#"+e).html("").hide();
				$("#"+c).html(b.emptyMsg).show();
			}
			$("#"+d).show();
		}else {
			$("#"+c).html(b.loadingData).show();
			ST.getJSON(b.server,b.queryFormat.r("$q",a.u()).r("$p",b.queryType),function(f){
				if(f.data) {
					if($.Lang.isArray(f.data)&&f.data.length) {
						$("#"+c).hide();
						$("#"+e).html(b._buildContent(f.data,a)).show();
					}else {
						$("#"+e).html("").hide();
						$("#"+c).html(b.emptyMsg).show();
					}
					$("#"+d).show();
					b.$reqData=f.data
				}else {
					$("#"+e).html("").hide();
					$("#"+c).html(f.message||b.errorMsg).show();
				}			
				b._clear();
			});
		}
	},
	_fetchData:function () {
		var a=this,b=$("#"+a.cid).val(),e=a.cid+"_datalist",c=a.cid+"_tipmsg",d=a.cid+"_container";
		if(b&&b!=a._lastVal) {
			$("#"+d).size()||a._buildTipUI(d,true);
			a._lastVal=b;
			//201
			if(a.useStaticData){
				if(!a.$$data) return;
				b=b.replace(/[`~!@#$%^&*()+=|{}':;',.<>/?~！@#￥%……&*（）——+|{}【】'；：""'。，、？]/g,"");
				a.$data=a.onValueChanged(b);
				a._loadData(a,b);
			}else{
				a._timer&&clearTimeout(a._timer);
				a._timer=window.setTimeout($.Lang.bind(a._loadData,a,b),a.queryDelay);
			}
		}else if(!b) {
			a._clear();
			$("#"+c).html(a.commonMsg).show();		
			$("#"+e).html("").hide();
			$("#"+d).show();
			a._lastVal=""
		}
	},
	//事件监听
	_eventListen:function () {
		var a=this,b=a.cid+"_container",bb=$("#"+b),e;
		a.$cFocus=function () {
			if(!($.Lang.isArray(a.$data)&&!a.$data.length)) {
				if(bb.size()>0) {
					e=$.getBound(a.cid);
					bb.css({
						left:e.x+"px",
						top:e.y+e.h-1+"px"
					}).show();
					a._fetchData();
				}else {
					bb=a._buildTipUI(b);
				}
			}
		};
		a.$cBlur =function(){
			bb.hide();
		},
		a.$cKeyup=function (c) {			
			/^(?:37|38|39|40|13)$/.test(c.keyCode)||a._fetchData()
		};
		a.$cMousedown=function () {
			//a.jid.trigger('focus');
			$.Lang.isArray(a.$data)&&!a.$data.length||bb.size()&&bb.show();
		};
		a.$cKeydown=function (c,d) {
			if(bb.css('display')!="none") {
				a._lastMoverObj&&$(a._lastMoverObj).css({
					backgroundColor:"",
					color:""
				});
				if(c.keyCode==13) {
					bb.hide();
					var _v1;
					if(~a._lastIdx){
						_v1=$("#"+a.cid+"_item_"+a._lastIdx).text();
						a.jid.val(_v1);
					}else{
						_v1=a.jid.val();
					}
					if(a._lastVal!=_v1) a.$data=a.onValueChanged(_v1);
					
					a._lastVal=_v1;		
					a.onEnter(a,{target:a._lastMoverObj});
					a._clear();
					$.stopEvent(c);
				}else {
					if(c.keyCode==40) {
						a._lastIdx++;
						document.getElementById(a.cid+"_item_"+a._lastIdx)||a._lastIdx--
					}else if(c.keyCode==38) {
						a._lastIdx--;
						if(a._lastIdx<0)a._lastIdx=0
					}else return ;
					
					d=d||$("#"+a.cid+"_item_"+a._lastIdx);
					if(c=d[0]) {
						$(a._lastMoverObj).toggleClass(a.hoverCss||a.Css+"_hover",false);
						for(;c.tagName.toLowerCase()!=a.listItemTagName.toLowerCase();)c=c.parentNode;if(a._lastMoverObj=c) {
							$(c).toggleClass(a.hoverCss||a.Css+"_hover",true);
							c={};
							c.target=a._lastMoverObj;
							a.onFill(a,c)
						}
					}
				}
			}
		};
		$("body").bind("mousedown.ac",function(){
			bb.hide();
			a._clear();
		});
		a.jid.bind("focus",a.$cFocus)
			 .bind("keyup",a.$cKeyup)
			 .bind("mousedown",function(e){a.$cMousedown();})
			 .bind("keydown",function(e){a.$cKeydown(e)})
	},
	init:function (a , b) {
		var t=this;
		t.cid=a;
		t.jid=$("#"+a);
		t.jid[0].autocomplete="off";
		if($("#"+a+"_type") && b && b.length>0){
			t.typelist=ST.ddList(a+'_type',b,function(o){
				t.queryType=o.value;
			}).selByValue(b[0].value);
		}
	},
	dispose:function () {
		var a=this,b=$("#"+a.cid+"_container");
		a.jid.unbind();//移除事件
		b.unbind();//移除事件
		$(document).unbind("mousedown.ac");
	},
	setup:function () {
		var a=this;
		a._eventListen()
	},
	hide:function () {
		this.$cBlur();
	},
	setData:function (a) {
		this.$$data=this.$data=a
	},
	getData:function(){
		var t=this,data=t.$reqData || t.$data;
		if($.Lang.isArray(t.regField) && data.length>0){
			var a=new Array();
			for(var i=0,dl,arr1;i<t.regField.length,dl=t.regField[i];i++){
				a=a.concat(data[0][dl]);
			}
			return a;
		}else{
			return data;
		}
	},
	getObjectCount:function(o){  
	   var n, count = 0;
	   if(!$.Lang.isObject(o)) return count;
       for(n in o){  
          if(o.hasOwnProperty(n)) count++;  
       }  
       return count;  
    },  
	//仅支持2级数据嵌套
	onValueChanged:function(val){
		if(!val || val=="\\") return [];
		var t=this,arr=[],obj={},total=0,reg=new RegExp("("+val+")","ig");
		var Fn=function(item){
			if(!item.highlight) {item.highlight={};}
			if($.Lang.isArray(t.regField)){
				for(var i=0,dl,macthed=0,arr1;i<t.regField.length,dl=t.regField[i];i++){
					if(!obj[dl]) obj[dl]=[];
					arr1=obj[dl]; //引用
					if(!item[dl]) continue;
					if(item[dl].match(reg)){
						if(t.highlight){
							item.highlight[dl]=item[dl].replace(reg,"<span style='color:red'>$1</span>");
						}
						if(!macthed){
							arr1.push(item);
							total++;
						};
						macthed=true;
					}else{
						if(t.highlight) item.highlight[dl]="";
					}
				}
			}else{
				if(!obj[t.regField]) obj[t.regField]=[];
				if(!item[t.regField]) throw new Error("no field has find");
				if(item[t.regField].match(reg)){
					if(t.highlight){
						if(!item.highlight) item.highlight={};
						item.highlight[t.regField]=item[t.regField].replace(reg,"<span style='color:red'>$1</span>");
					}
					obj[t.regField].push(item);
					total++;
				}else{
					if(t.highlight) item.highlight[t.regField]="";
				}
			}
		};
		if(t.useStaticData){
			var l=t.$$data.length,isobj=$.Lang.isObject(t.$$data),n;
			if(!l&&isobj) l=t.getObjectCount(t.$$data);
			if(l>10000){ST.tipMsg('超过10000条大数据，建议使用服务端搜索'); return arr};
			if(isobj){
				for(n in t.$$data){
					n=t.$$data[n];
					if($.Lang.isObject(n)){
						Fn(item1);
					}else if($.Lang.isArray(n)){
						for(var j=0,ll=n.length;j<ll,item=n[j];j++){
							Fn(item);
						}
					}
				}
			}else{
				for(var i=0,item1,item;i<l,item1=t.$$data[i];i++){
					if($.Lang.isObject(item1)){
						Fn(item1);
					}else if($.Lang.isArray(item1)){
						for(var j=0,ll=item1.length;j<ll,item=item1[j];j++){
							Fn(item);
						}
					}
				}
			}
		}else{
			//server code
			
		}
		obj.total=total;
		arr.push(obj);
		return arr;
	},
	onFill:ST.emptyFn,
	onClickFill:ST.emptyFn,
	onEnter:ST.emptyFn,
	onBlurFill:ST.emptyFn,
	onMouseover:ST.emptyFn
});