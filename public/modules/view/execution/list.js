define(function (require) {
    /*点击显示执行单详情*/
    var executionPopTpl = require("tpl/execution/executionPop.html");
    function createTable($el,data){
        /*创建类excel表格*/
        var t=this;

        for(var i=0;i<data.length;i++){
            /*一个htable数据*/
            for(var j=0;j<data[i].length;j++){
                /*一行row数据*/
                for(var k=0;k<data[i][j].length;k++){
                    data[i][j][k] = changeTableValueToHtml(data[i][j][k]);
                }
            }
        }
        new Handsontable($el,{
            data: data[0],
            rowHeaders: true,
            colHeaders: true,
            fixedRowsTop: 2,
            fixedColumnsLeft:2,
            contextMenu: true,
            mergeCells: [
                {row: 0, col: 0, rowspan: 2, colspan: 1},
                {row: 0, col: 1, rowspan: 2, colspan: 1},
                {row: 0, col: 2, rowspan: 1, colspan: 31}
            ],
            colWidths:[200,100,20,20,20,20,20,20,20,20,20,20,20,20,20,20,20,20,20,20,20,20,20,20,20,20,20,20,20,20,20,20,20],
            cells: function (row, col, prop) {
                var cellProperties = {};

                cellProperties.readOnly = true;
                return cellProperties;
            },
            renderer:"html"
        });
    }

    function changeTableValueToHtml(obj){
        var style="",val="",res="",id="";
        if(obj){
            for(var i in obj){
                switch (i){
                    case "weight":
                        if(obj[i]){
                            style = style + ";font-weight:" + obj[i];
                        }
                        break;
                    case "color":
                        if(obj[i]){
                            style = style + ";color:" + obj[i];
                        }
                        break;
                    case "background":
                        if(obj[i]){
                            style = style + ";background-color:" + obj[i];
                        }
                        break;
                    case "align":
                        if(obj[i]){
                            style = style + ";text-align:" + obj[i];
                        }
                        break;
                    case "id":
                        id=obj[i];
                        break;
                    case "value":
                        val=obj[i];
                        break;
                }
            }
        }

        res = "<p  style='"+style+"'  data-id='"+id+"'>"+val+"</p>";
        
        return res;
    }

    $("#show-ad-space").on("click",function(){
        var url = $(this).attr("data-search");
        if(!url){
            return false;
        }

        $.ajax({
            url:url,
            type:"post",
            dataType:"json"
        }).done(function(d){
            if(d.status=="success"&&d.data){
                var data = d.data.table,
                    table = _.template(executionPopTpl,{data:d.data})
                
                $("body").append(table);

                createTable($("#execution-table")[0],data);
                $(".js-execution-popup .js-close").on("click",function(){
                    $(".js-execution-popup").remove();
                });
            }else{
                $.notice({content:d.info});
            }
        }).fail(function(){
            $.notice({content:"操作失败"});
        });
    })
});