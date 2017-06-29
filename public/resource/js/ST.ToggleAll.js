/**
 * 全选插件
 * created & last edited by zhangshu 2013-11-27
 *
 * @class ToggleAll
 * @namespace ST
 * @requires jquery ST
 */
var ST = ST || {};
ST.ToggleAll = function (id, ops) {
  //默认配置
  var _default = {
    type: '',                         //类型（'toggleAll'|'checkAll'|'uncheckAll'|'invertAll'）
    targets: '.js-toggleall-target',  //目标checkbox
    sync: true,                        //是否同步数据（若此项设为true，则目标checkbox选中数量将影响全选按钮选中状态）
    onChange: null                     //当选中值发生改变时（@params v：当前选中值 n：触发节点）
  };
  //私有成员
  var selectedValue = [];            //初始选中值

  //绑定点击事件
  function addEvents($elm, action) {
    $elm.on('click.toggleall', action);
  }

  //更新数据&同步状态
  function update(data,trigger){
    var t = this, c = t.config;
    if(c.sync){
      var isCheckbox = t.Jid.is(':checkbox');
      var $toggleAll = isCheckbox? t.Jid:t.Jid.find(':checkbox').filter('[data-cmd=toggleAll]');
      if ($toggleAll.length>0) {
        var $targets = t.getTargets();
        var $selected = t.getSelectedOptions();
        var f = $selected.length == $targets.length;
        $toggleAll.attr('checked', f);
      }
    }
    if (data.join(',') != selectedValue.join(',')) {
      selectedValue = data.slice(0);
      c.onChange && c.onChange.call(t, selectedValue, trigger);
    }
  }

  return{
    init: function (id, conf) {
      var t = this;
      t.config = $.extend({}, _default);
      $.extend(t.config, conf);
      var c = t.config;
      t.Jid = $('#' + id);
      if (t.Jid.length == 0) return;

      var $targets = t.getTargets();
      //设置选中值
      var $checked = $targets.filter(':checked'),
          n = $checked.length,
          isToggleAll = (n && n == $targets.length);
      $checked.each(function () {
        selectedValue.push($(this).val());
      });
      //初始化事件
      addEvents.call(t, $targets, function (e) {
        t.toggle.call(t, e.target);
      });
      var type = c.type;
      if (t.Jid.is(':checkbox')) {
        type = "toggleAll";
        if(isToggleAll){
            t.Jid.attr('checked',true);
        }
      } else {
        if(isToggleAll){
            t.Jid.find('[data-cmd="toggleAll"]').attr('checked',true);
        }
        t.Jid.evProx({
          'click': {
            '*': function () {
              var cmd = $(this).data('cmd');
              if (cmd && t[cmd]) {
                t[cmd].call(t, this);
              }
            }
          }
        });
      }
      if (type) {
        addEvents.call(t, t.Jid, function (e) {
          t[type].call(t, e.target);
        });
      }
      delete t.init;
      return t;
    },
    //选项切换
    toggle: function (n) {
      var t = this, c = t.config;
      var f = !!$(n).attr('checked');
      f ? t.check(n) : t.uncheck(n);
      return t;
    },
    //切换
    toggleAll: function (n) {
      var t = this, c = t.config;
      var f = !!$(n).attr('checked');
      f ? t.checkAll(n) : t.uncheckAll(n);
      return t;
    },
    //全选
    checkAll: function (n) {
      var t = this, c = t.config;
      var tmp = selectedValue.slice(0);
      var $targets = t.getTargets();
      $targets.attr('checked', true);
      $targets.each(function () {
        var val = $(this).val();
        if (tmp.getIndex(val) == -1) {
          tmp.push(val);
        }
      });
      update.call(t,tmp,n);
      return t;
    },
    //取消全选
    uncheckAll: function (n) {
      var t = this, c = t.config;
      var $targets = t.getTargets();
      $targets.attr('checked', false);
      update.call(t,[],n);
      return t;
    },
    //反选
    invertAll: function (n) {
      var t = this, c = t.config;
      var $targets = t.getTargets();
      var tmp = selectedValue.slice(0);
      $targets.each(function () {
        var f = !!$(this).attr('checked'), val = $(this).val();
        $(this).attr('checked', !f);
        tmp[f ? 'remove' : 'push'](val);
      });
      update.call(t,tmp,n);
      return t;
    },
    //选择
    check: function (n) {
      var t = this, c = t.config;
      var val = $(n).val(), tmp = selectedValue.slice(0);
      if (tmp.getIndex(val) == -1) {
        tmp.push(val);
      }
      update.call(t,tmp,n);
      return t;
    },
    //取消选择
    uncheck: function (n) {
      var t = this, c = t.config;
      var val = $(n).val(),tmp = selectedValue.slice(0);
      tmp.remove(val);
      update.call(t,tmp,n);
      return t;
    },
    //获取选中数据
    getSelectedValue: function () {
      return selectedValue;
    },
    //获取已选选项
    getSelectedOptions: function () {
      var $targets = this.getTargets();
      return $targets.filter(':checked');
    },
    //获取模板选项
    getTargets: function () {
      return $(this.config.targets).filter(':checkbox');
    }
  }.init(id, ops);
};