ST.Tabs=function(ops){
    return{
        //相关默认配置
        init:function(ops){
            var t=this,d=t.defaults={
                cnode:0,
                el:'',
                ts:'',
                curClass:'',
                oldClass:'',
                sfun:'',
				evType:"click"
            };
            for(var n in d){
                d[n]=ops[n]||d[n];//获取参数
            }
            if(t.jid=$(d.ts),t.jid.size()==0) return;
            t.tabs=t.jid.find(d.el); 
			t.tabs.each(function(i,v){
				$(this).bind(d.evType,function(){
					t.show(this);
				});
				if(d.cnode==i && d.cnode!=0)
					t.show(this);
			});

			delete init;
            return t;
        },
        //显示方法 @node 指定的节点index 和datas数组下标一一对应
        show:function(node){
            var t=this,d=t.defaults;
            if(d.cnode==node) return;
            for(var i=0,l=t.tabs.length;i<l;i++){
                if(node==t.tabs[i]){
                    t.swapCN(t.tabs[i],d.oldClass,d.curClass);
                    d.cnode=node;
                    if(d.sfun) d.sfun(t.tabs[i],i);
                }else{
                    t.swapCN(t.tabs[i],d.curClass,d.oldClass);
                }
            }
        },
        swapCN:function(o,c1,c2){
           $(o).removeClass(c1).addClass(c2);
        }
    }.init(ops)
};