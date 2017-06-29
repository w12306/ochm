define(function (require) {
    var Base = require("base");
    var tpl = require("tpl/execution/adPopup.html");
    var M = require("model/execution/schedule");
    var com = require("lang/common");

    var View = Base.View.extend({
        //template: tpl,
        initialize: function (pars) {
            var t = this;

            t.model = new M();
            t.model.on("sync",function(){
                t.render();
                t.afterRender();
                t.search=true;
            });
            //t.model.fetch();
            $(".js-get-schedule").on("click",function(){
                /*同时传递查询参数*/
                var par={},ad_type,status,date;
                par["ad_type"]=[];
                $(".js-ad-type:checked").each(function(){
                    par["ad_type"].push($(this).val());
                })
                par["ad_type"] = par["ad_type"].join(",");
                par["status"] = $(".js-status:checked").val();
                par["date"] = $(".js-month").val();

                t.model.setPars(par);

                if(t.search){
                    t.search=false;
                    t.model.fetch(par);
                }
            })
            $(".js-filter").click(function () {
              $(".js-filter-container").toggle();
              var isHidden = $(".js-filter-container").is(":hidden");
              $(".js-filter").html(isHidden?"高级筛选":"简易筛选")
              var filterHeight = $(".js-filter-container").height()+10
              var height = !isHidden? -filterHeight : 0
              t.tableRender(height);
            })

            $(window).resize(function () {
              t.tableRender();
            })

        },
        search:true,
        render:function(){
            var t=this,
                data = t.model.get("data");
            t.tableRender();
            $("#downLoadXls").click(function(){
                var exportPlugin  = t.table.getPlugin('exportFile');
                exportPlugin.downloadFile('csv', {filename: 'MyFile'});
            })

        },
        tableRender:function(height){
            var t=this,data = t.model.convertTableData();
            $("#js-container").html("")
            if(data){
              for(var table_i=0;table_i<data.length;table_i++){
                  $("#js-container").append($("<div class='js-table'></div>"));
                  t.createTable(t.$el.find(".js-table").eq(table_i)[0],data[table_i],height);
              }
            }

        },
        createTable:function($el,data,filterHeight){
            /*创建类excel表格*/
            var t=this,columns = ['<p>广告位</p>'];
            for (var i = 0; i <= 31; i++) {
                columns.push((i+1))
            }
            data.shift();
            this.table = new Handsontable($el,{
                data: data,
                rowHeaders: false,
                columnHeaderHeight:26,
                // height:(data.length+2)*31,
                height:function () {
                  var height = $("body").height() - $(".s-menu-container").height() - $(".s-menu-container").offset().top - 40
                  height = filterHeight?height+filterHeight:height
                  return height;
                },
                colHeaders: columns,
                colWidths:[97,35,35,35,35,35,35,35,35,35,35,35,35,35,35,35,35,35,35,35,35,35,35,35,35,35,35,35,35,35,35,35],
                cells: function (row, col, prop) {
                    return {readOnly : true}
                },
                renderer:"html"
            });

        },
        afterRender:function(){
            var t=this,n,timer;
            t.$el.off("mouseenter")
            t.$el.on("mouseenter","p.js-ad",function(e){
                var id = $(this).data("id");
                if(!id) return;
                timer = setTimeout(function(){

                    var x,y;
                    x = e.pageX;
                    y = e.pageY;

                    if(x>$("body").width() - 200){
                      x = $("body").width() - 300
                    }

                    if(y>$("body").height() - 300){
                      y = $("body").height() - 280
                    }


                    $(".js-ad-popup").remove();
                    $.ajax({
                        type:"post",
                        url:ST.ACTION.adToop,
                        data:{id:id},
                        dataType:"json"
                    }).done(function(d){
                        if(d.status=="success"&&d.data){
                            content=_.template(tpl,{data:d.data,x:x,y:y});
                            //console.log(x,y,content)
                            $("body").append(content);
                        }
                    }).fail(function(e){
                        console.log("get adtoop error",e)
                    })

                },1000);
            });
            t.$el.off("mouseout")
            t.$el.on("mouseout","p.js-ad",function(){
                clearTimeout(timer);
                $(".js-ad-popup").remove();
            });

        }
    });
    return function (options) {
        return new View(options);
    }
});
