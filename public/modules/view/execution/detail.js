define(function (require) {
    var Base = require("base");
    var tpl = require("tpl/execution/detail.html");
    var M = require("model/execution/detail");
    var com = require("lang/common");

    var View = Base.View.extend({
        //template: tpl,
        initialize: function (pars) {
            var t = this;
            
            t.model = new M();
            t.model.on("sync",function(){
                t.render(pars);
            });
            t.model.fetch();
        },
        render:function(pars){
            var t=this,
                data = t.model.get("data");

            if(pars&&!pars.tableOnly){
                t.$el.html(_.template(tpl,{data:data}));
            }
            
            t.tableRender();
        },
        tableRender:function(){
            var t=this,
                data = t.model.convertTableData();

            for(var table_i=0;table_i<data.length;table_i++){
                t.$el.append($("<div class='js-dtable mt20'></div>"));
                t.createTable(t.$el.find(".js-dtable").eq(table_i)[0],data[table_i]);
            }
        },
        createTable:function($el,data){
            /*创建类excel表格*/
            var t=this;
            new Handsontable($el,{
                data: data,
                rowHeaders: true,
                colHeaders: true,
                fixedRowsTop: 2,
                contextMenu: true,
                mergeCells: [
                    {row: 0, col: 0, rowspan: 2, colspan: 1},
                    {row: 0, col: 1, rowspan: 2, colspan: 1},
                    {row: 0, col: 2, rowspan: 2, colspan: 1},
                    {row: 0, col: 3, rowspan: 1, colspan: 31}
                ],
                colWidths:[150,200,100,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30],
                cells: function (row, col, prop) {
                    var cellProperties = {};

                    cellProperties.readOnly = true;
                    return cellProperties;
                },
                renderer:"html"
            });
        }
    });
    return function (options) {
        return new View(options);
    }
});