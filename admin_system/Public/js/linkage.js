/**
 *　jQuery多级联动扩展
 *
 * @author OuQiang
 */
$.extend({
    linkage : function($selectContainer, config) {
        var linkage = new Linkage($selectContainer, config);
        linkage.bindChangeEvent();
    }
});

/**
 * @constructor
 *
 * @param  object $selectContainer 下拉列表容器jQuery对象
 * @param  object config          配置
 */
function Linkage($selectContainer, config) {
    this.$selectCollection = $($selectContainer).find('select');
    this.config            = config;
    this.setConfig();
}

/**
 * 初始化配置
 */
Linkage.prototype.setConfig = function() {
    this.url                = this.config.url;
    this.paramName          = typeof this.config.paramName          != 'undefined' ? this.config.paramName           : 'option_id';
    this.selectLocationName = typeof this.config.selectLocationName != 'undefined' ? this.config.selectLocationName  : 'select_location';
    this.optionValueName    = typeof this.config.optionValueName    != 'undefined' ? this.config.optionValueName     : 'id';
    this.optionTextName     = typeof this.config.optionTextName     != 'undefined' ? this.config.optionTextName      : 'name';
    this.keepFirstOption    = typeof this.config.keepFirstOption    != 'undefined' ? this.config.keepFirstOption     : true;
}

/**
 * 绑定选项变化事件
 */
Linkage.prototype.bindChangeEvent = function() {
    var linkage = this;
    this.$selectCollection.each(function(index) {
        $(this).change(function() {
            if (!linkage.hasNextSelectSibings($(this))) {
                return;
            }
            var $select        = $(this);
            var selectedIndex  = this.selectedIndex;
            var optionValue    = this.options[selectedIndex].value;
            var selectLocation = index + 2;
            var url            = linkage.url + '/' + linkage.paramName + '/' + optionValue + '/'
                                 + linkage.selectLocationName  + '/' + selectLocation;

            var $nextAllSiblings =  $($select).nextAll();
            linkage.removeOptions($nextAllSiblings);

            $.get(
                url,
                '',
                function(data) {
                    if (data == null) {
                        return;
                    }

                    var $nextSibing      =  $($select).next();
                    linkage.addOptions($nextSibing, data);
                },
                'json'
            );
        });
    });

}

/**
 * 清除选项
 * @param  object $select 下拉列表jQuery对象
 */
Linkage.prototype.removeOptions = function($select) {
    var linkage = this;
    $($select).each(function () {
        if (linkage.keepFirstOption) {
            var firstOption = this.options[0];
            this.options.length = 0;
            this.add(firstOption);
        } else {
            this.options.length = 0;
        }

    });
}

/**
 * 判断是否还有下一个下拉列表
 *
 * @param $select
 * @returns {boolean}
 */
Linkage.prototype.hasNextSelectSibings = function($select) {
    var siblingNum = $($select).nextAll().size();

    return siblingNum > 0 ? true : false;
}

/**
 * 添加选项
 */
Linkage.prototype.addOptions = function($select, options) {
    var html = '';
    for (var i in options) {
        html += '<option value="' + options[i][this.optionValueName ]  + '">'  + options[i][this.optionTextName] + '</option>'
    }

    $($select).append(html);
}

