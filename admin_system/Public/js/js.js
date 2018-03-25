/**
	js常用函数
*/
var PUBLIC = '',
    DIR = '',
    APP = '',
    ACTION = '',
    GRIDORDER = '',
    PAGE = '',
    LIMIT = '',
    CURRENT = '',
    MENUID = '';
var HTML5 = false;
if (typeof(Worker) !== 'undefined') HTML5 = true; //支持部分html5

function initbody() {}
$(document).ready(function() {
    initbody();
    js.gridinit();
    $("[data-toggle='tooltip']").tooltip(); //加载提示的
    js.setplaceholder();
});

var js = {
    path: '',
    changeid: 0,
    bool: false
}
var isIE = true;
if (!document.all) isIE = false;
var get = function(id) {
    return document.getElementById(id)
};
var strreplace = function(str) {
    return str.replace(/[ ]/gi, '').replace(/\s/gi, '')
}
var form = function(an) {
    return document.myform[an]
}

js.getarr = function(a) {
    var s = '';
    for (var c in a) s += '' + c + '=>' + a[c] + '\n';
    alert(s)
}

js.str = function(o) {
    o.value = strreplace(o.value);
}

//placeholder的属性控制
js.setplaceholder = function() {
    if (HTML5) return;
    $("input[placeholder]").placeholder();
}

/**
	ajax请求地址
*/
js.getajaxurl = function(a, m, can) {
    if (!m) m = DIR;
    if (!can) can = {};
    var url = '' + APP + '' + DIR + '/' + a + '/menuid/' + MENUID + '/rnd/' + Math.random() + '';
    for (var c in can) url += '/' + c + '/' + can[c] + '';
    url += '/ajaxbool/true';
    return url;
}

/**
	获取url参数
*/
js.request = function(name, url) {
    if (!name) return '';
    if (!url) url = location.href;
    if (url.indexOf('\?') < 0) return '';
    neurl = url.split('\?')[1];
    neurl = neurl.split('&');
    var value = ''
    for (i = 0; i < neurl.length; i++) {
        val = neurl[i].split('=');
        if (val[0].toLowerCase() == name.toLowerCase()) {
            value = val[1];
            break;
        }
    }
    if (!value) value = '';
    return value;
}

//获取可见网页总高度
function winHb() {
    var winH = (!isIE) ? window.innerHeight : document.documentElement.offsetHeight;
    return winH;
}

function winWb() {
    var winH = (!isIE) ? window.innerWidth : document.documentElement.offsetWidth;
    return winH;
}

//js版in_array
Array.prototype.in_array = function(e) {
    for (i = 0; i < this.length && this[i] != e; i++);
    return !(i == this.length);
}

/**
	编码
*/
js.decode = function(str) {
    var arr = {
        length: -1
    };

    try {
        arr = new Function('return ' + str + '')();
    } catch (e) {}

    return arr;
}

/**
	获取表单内的值
*/
js.getformdata = function(na) {
    var da = {};
    if (!na) na = 'myform';
    var obj = document[na];
    for (var i = 0; i < obj.length; i++) {
        var type = obj[i].type;
        var val = obj[i].value;
        if (type == 'checkbox') {
            val = '0';
            if (obj[i].checked) val = '1';
        } else if (!HTML5) {
            val = $(obj[i]).placeval();
        }
        da[obj[i].name] = val;
    }
    return da;
}

/**
	格式化时间
*/
js.now = function(type, sj) {
    if (!type) type = 'Y-m-d';
    var dt, ymd, Y, m, d, w, W, H, i, s, his;
    if (/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}/.test(sj)) {
        sj = sj.split(' ');
        ymd = sj[0];
        his = sj[1];
        if (!his) his = '00:00:00';
        ymd = ymd.split('-');
        his = his.split(':');;
        dt = new Date(ymd[0], ymd[1] - 1, ymd[2], his[0], his[1], his[2]);
    } else {
        dt = new Date();
    }
    var weekArr = new Array('日', '一', '二', '三', '四', '五', '六');
    Y = dt.getFullYear(); //年
    m = dt.getMonth() + 1;
    if (m < 10) m = '0' + m; //月
    d = dt.getDate();
    if (d < 10) d = '0' + d; //天
    w = dt.getDay(); //星期
    H = dt.getHours();
    if (H < 10) H = '0' + H; //小时
    i = dt.getMinutes();
    if (i < 10) i = '0' + i; //分钟
    s = dt.getSeconds();
    if (s < 10) s = '0' + s; //秒
    W = weekArr[w];
    if (type == 'time') {
        return dt.getTime();
    } else {
        return type.replace('Y', Y).replace('m', m).replace('d', d).replace('H', H).replace('i', i).replace('s', s).replace('w', w).replace('W', W);
    }
}

/**
	提示框
*/
js.msgtotal = 0;
js.msgabc = function(lx, txt, sj) {
    //lx=success/info/warning/danger
    if (!sj) sj = 3;
    js.msgtotal++;
    var id = 'msgshowabc' + js.msgtotal + '';
    var s = '<div id="' + id + '" class="alert alert-' + lx + '" style="width:300px;text-align:center;position: absolute;z-index:' + js.msgtotal + ';top:-100px;left:' + (winWb() - 350) * 0.5 + 'px">' + txt + '</div>';
    $('body').prepend(s);
    setTimeout("$('#" + id + "').animate({top:'-100px',opacity:0},function(){$(this).remove()})", sj * 1000);
    $('#' + id + '').animate({
        top: '3px'
    });
}
js.msg = function(lx, txt, sj) {
    parent.js.msgabc(lx, txt, sj);
}


/**
	提示
*/
js.confirm = function(txt, succefun, evt) {
    $('#confirminfomsg').remove();
    var wm = winWb();
    var x = (wm - 300) * 0.5,
        y = (winHb() - 150) * 0.5;
    if (evt) {
        x = evt.clientX - 150
        y = evt.clientY - 20;
    }
    var jx = x + 310 - wm;
    if (jx > 0) x = wm - 320;
    if (x < 0) x = 1;
    var s = '<div id="confirminfomsg" class="alert alert-danger" style="width:300px;text-align:center;position: absolute;z-index:33;top:' + (y) + 'px;left:' + x + 'px">' + txt + '<br><a href="javascript:" id="confirminfomsgok" >[确定]</a>&nbsp; &nbsp; <a href="javascript:" onclick="$(this).parent().remove()">[取消]</a></div>';
    $('body').prepend(s);

    $('#confirminfomsgok').click(function() {
        if (typeof(succefun) == 'function') {
            succefun();
        } else {
            eval(succefun);
        }
        $(this).parent().remove();
    });
}

//----分页相关js---------
js.gridout = function() {
    return ''
}
js.trclick = function(o) {}
js.gridinit = function() {
    var mobj = $("tr[trbool='true']");
    mobj.click(function() {
        mobj.removeClass();
        var o = $(this);
        o.addClass('selected');
        js.changeid = o.attr('dataid');
        js.trclick(this);
    });

    //判断是否排序的
    var o = $("div[sortable^='true_']");
    for (var i = 0; i < o.length; i++) {
        var o1 = $(o[i]);
        var na = o1.attr('sortable').substr(5);
        var s = '<img onclick="js.gridorder(\'' + na + '\',\'desc\')" title="降序" src="' + PUBLIC + 'images/desc1.gif" class="desc"><img onclick="js.gridorder(\'' + na + '\',\'asc\')" title="升序" src="' + PUBLIC + 'images/asc1.gif" class="asc">';
        if (GRIDORDER.indexOf(na + '-') == 0) {
            var orderlx = GRIDORDER.replace('' + na + '-', '');
            s = '<img src="' + PUBLIC + 'images/' + orderlx + '.gif" align="absmiddle" style="margin-left:5px">';
            orderlx = (orderlx == 'desc') ? 'asc' : 'desc';
            o1.click(new Function("js.gridorder('" + na + "','" + orderlx + "')"));
        }
        o1.append(s);
    }
}
js.gridsoubefore = function(oi) {
        return true
    } //搜索之前判断
js.gridsou = function(oi) {
    if (!js.gridsoubefore(oi)) return false;
    var url = '' + APP + '' + DIR + '/' + ACTION + '';
    //if (PAGE != '') url += '/page/' + PAGE + '';     
    if (LIMIT != '') url += '/limit/' + LIMIT + '';
    if (MENUID != '') url += '/menuid/' + MENUID + '';
    if (GRIDORDER != '') url += '/gridorder/' + GRIDORDER + '';
    var o = $("select[temp='tbarsou" + oi + "']");
    for (var i = 0; i < o.length; i++) {
        var val = o[i].value;
        if (val != '') url += '/' + o[i].id + '/' + o[i].value + '';
    }
    var o = $("input[temp='tbarsou" + oi + "']");
    for (var i = 0; i < o.length; i++) {
        var no = o[i];
        var val = o[i].value;
        if (o[i].type == 'checkbox') {
            if (!o[i].checked) val = '';
        } else if (!HTML5) {
            val = $(no).placeval(); //由于有的属性不支持placeholder，又使用
        }
        if (val != '') url += '/' + o[i].id + '/' + val + '';
    }
    url += js.gridout(oi);
    location.href = url;
}

js.gridorder = function(na, lx) {
    var url = location.href;
    if (url.indexOf('gridorder') < 0) {
        url += '/gridorder/' + na + '-' + lx + '';
    } else {
        url = url.replace('gridorder/' + GRIDORDER + '', 'gridorder/' + na + '-' + lx + '');
    }
    location.href = url;
}
js.gridlimitpage = function(o, ov, lx) {
        var val = strreplace(o.value);
        if (!val || isNaN(val)) val = ov;
        val = parseInt(val);
        if (val <= 0) val = ov;
        o.value = val;
        if (val == ov) return false;

        var url = location.href;
        if (url.indexOf('' + lx + '/' + ov + '') < 0) {
            url += '/' + lx + '/' + val + '';
        } else {
            url = url.replace('' + lx + '/' + ov + '', '' + lx + '/' + val + '');
        }
        location.href = url;
    }
    //全选
js.sellall = function(o, na) {
    var b = $("input[name='" + na + "']");
    for (var i = 0; i < b.length; i++) {
        b[i].checked = o.checked;
    }
}


//获取多个复选框的值
js.getchecked = function(na) {
    var s = '';
    var o1 = $("input[name='" + na + "']");
    for (var i = 0; i < o1.length; i++) {
        if (o1[i].checked) s += ',' + o1[i].value + '';
    }
    if (s != '') s = s.substr(1);
    return s;
}

js.seldate = function(cid, scan) {
    var can = {
        format: 'yyyy-mm-dd',
        language: 'zh-CN',
        weekStart: 0,
        todayBtn: 1,
        autoclose: 1,
        todayHighlight: 1,
        startView: 2,
        minView: 2,
        forceParse: 1
    }
    can = js.apply(can, scan);
    $("#" + cid + "").datetimepicker(can);
}
js.selmonth = function(cid, scan) {
    var can = {
        format: 'yyyy-mm',
        language: 'zh-CN',
        weekStart: 0,
        //todayBtn:  1,
        autoclose: 1,
        todayHighlight: 1,
        startView: 3, //月开始
        minView: 3,
        forceParse: 1
    }
    can = js.apply(can, scan);
    $("#" + cid + "").datetimepicker(can);
}
js.seldatetime = function(cid, scan) {
    var can = {
        format: 'yyyy-mm-dd hh:ii:00',
        language: 'zh-CN',
        weekStart: 0,
        todayBtn: 1,
        autoclose: 1,
        forceParse: 1
    };
    can = js.apply(can, scan);
    $("#" + cid + "").datetimepicker(can);
}

//保留两位小时
js.float = function(num, w) {
    if (isNaN(num) || num == '' || !num || num == null) num = '0';
    num = parseFloat(num);
    if (!w && w != 0) w = 2;
    var m = num.toFixed(w);
    return m;
}



//移动div
js.move = function(id, event) {
    var _left = 0,
        _top = 0;
    var obj = id;
    if (typeof(id) == 'string') obj = get(id);
    var _Down = function(evt) {
        try {
            var s = '<div id="divmovetemp" style="filter:Alpha(Opacity=0);opacity:0;z-index:99999;width:100%;height:100%;position:absolute;background-color:#000000;left:0px;top:0px;cursor:move"></div>';
            $('body').prepend(s);
            evt = window.event || evt;
            _left = evt.clientX - parseInt(obj.style.left);
            _top = evt.clientY - parseInt(obj.style.top);
            //document.body.style.cursor='move';
            document.onselectstart = function() {
                return false
            }
        } catch (e) {}
    }
    var _Move = function(evt) {
        try { //尝试移动
            var c = get('divmovetemp').innerHTML;
            evt = window.event || evt;
            obj.style.left = evt.clientX - _left + 'px';
            obj.style.top = evt.clientY - _top + 'px';
        } catch (e) {
            _Down(evt)
        }
    }
    var _Up = function() {
        //document.body.style.cursor='';
        document.onmousemove = "";
        document.onselectstart = "" //恢复选择右键document.oncontextmenu
        document.onmouseup = '';
        $('#divmovetemp').remove();
    }
    document.onmousemove = _Move //鼠标移动
    document.onmouseup = _Up; //鼠标释放	
}



/*设置cookie*/
js.cookie = function(name) {
        var str = document.cookie;
        var val = '';
        if (str.length <= 0) return '';
        arr = str.split('; ');
        for (i = 0; i < arr.length; i++) {
            cda = arr[i].split('=');
            if (name.toLowerCase() == cda[0].toLowerCase()) {
                val = cda[1];
                break;
            }
        }
        if (!val) val = '';
        return val;
    }
    //保存
js.savecookie = function(name, value) {
    var expires = new Date();
    var cd = 365;
    if (!value || value == '') {
        value = '';
        cd = -0.5;
    }
    expires.setTime(expires.getTime() + cd * 24 * 60 * 60 * 1000);
    var str = '' + name + '=' + value + ';expires=' + expires.toGMTString() + ';path=/';
    document.cookie = str;
}

//回到顶部
js.backtop = function(toc) {
    if (!toc) toc = 0;
    $('body,html').animate({
        scrollTop: toc
    }, 200);
    return false;
}

js.applyIf = function(a, b) {
    if (!a) a = {};
    if (!b) b = {};
    for (var c in b)
        if (typeof(a[c]) == 'undefined') a[c] = b[c];
    return a;
}

js.apply = function(a, b) {
    if (!a) a = {};
    if (!b) b = {};
    for (var c in b) a[c] = b[c];
    return a;
}

js.tanbody1 = function(act, title, w, h, can1) {
    var can = js.applyIf(can1, {
        html: '',
        btn: []
    });
    var l = (winWb() - w - 50) * 0.5,
        t = (winHb() - h - 100) * 0.5 + document.documentElement.scrollTop;
    var s = '';
    var mid = '' + act + '_main';
    $('#' + mid + '').remove();

    s += '<div id="' + mid + '" class="xtborder box" style="position:absolute;background-color:#ffffff;left:' + l + 'px;width:' + w + 'px;top:' + t + 'px;z-index:99;">' +
        '<div class="xttitle" onmousedown="js.move(\'' + mid + '\')" style="-moz-user-select:none;padding:8px"><b id="' + act + '_title">' + title + '</b><span id="' + act + '_spancancel" ><img onclick="js.tanclose(\'' + act + '\')" style="cursor:pointer;" src="' + PUBLIC + 'images/nclose.gif"></span></div>' +
        '<div style="padding:8px;" id="' + act + '_body">' + can.html + '' +
        '</div>';
    s += '<div style="padding:3px 8px;background:#eeeeee" align="right">'; //'+can.btn+'
    s += '<table border="0" width="100%" cellspacing="0" cellpadding="0"><tr><td width="95%" style="-moz-user-select:none;" onmousedown="js.move(\'' + mid + '\')"></td><td nowrap align="right">';
    var btn = can.btn;
    for (var i = 0; i < btn.length; i++) {
        s += '<input class="button button-sml" id="' + act + '_btn' + i + '" onclick="' + btn[i].click + '" type="button" value="' + btn[i].text + '">&nbsp; '
    }
    s += '<input class="button button-sml" id="' + act + '_cancel" onclick="js.tanclose(\'' + act + '\')" type="button" value="取消">';
    s += '</td></tr></table>';
    s += '</div>';

    js.xpbody(can.mode);
    $('body').append(s);
    //没有关闭按钮
    if (can.closed == 'none') {
        $('#' + act + '_cancel').remove();
        $('#' + act + '_spancancel').remove();
    }

}
js.tanbody = function(act, title, w, h, can1) {
    parent.js.tanbody1(act, title, w, h, can1);
}
js.tanbodyhtml = function(act, txt) {
    parent.get('' + act + '_body').innerHTML = txt;
}

js.tanclose = function(act) {
    var mid = '' + act + '_main';
    var t = parseInt(get(mid).style.top);
    $('#' + mid + '').animate({
        top: t + 100,
        opacity: 0
    }, 200, function() {
        $(this).remove();
        js.xpbody('none');
    }); //animate
}

js.xpbody = function(type) {
    if (type == 'none') {
        $('#xpbg_bodydds').remove();
        $('#xpbg_bodyabcdd').remove();
        return;
    }
    if (!get('xpbg_bodydds')) {
        var H = (document.body.clientHeight < winHb()) ? winHb() - 5 : document.body.clientHeight;
        var W = document.documentElement.scrollWidth + document.body.scrollLeft;
        var bs = '';
        if (isIE) bs += '<iframe id=\"xpbg_bodyabcdd\" src="about:blank" frameborder="0" width="' + W + '" height="' + H + '" style="position:absolute;filter:Alpha(Opacity=0);opacity:0;left:0px;top:0px;z-index:9"></iframe>';
        bs += "<div id=\"xpbg_bodydds\" oncontextmenu=\"return false\" style=\"position:absolute;display:;width:" + W + "px;height:" + H + "px;filter:Alpha(Opacity=30);opacity:0.3;left:0px;top:0px;background-color:#000000;z-index:10\"></div>";
        $('body').prepend(bs);
        //$('#xpbg_bodydds').fadeIn(300);//,opacity:''
    }
}







//---------------------树管理的-----------------------
js.treeopenclose = function(o, na) {
    //alert(na);
    var obj = $("tr[temp^='" + na + "_']");
    var src = o.src.toLowerCase();
    if (src.indexOf('folder-open.gif') > 0) {
        src = src.replace('folder-open.gif', 'folder.gif');
        obj.hide();
    } else {
        src = src.replace('folder.gif', 'folder-open.gif');
        obj.show();
    }
    o.src = src;
}

//-----上传插件-----
//上传插件初始化
js.upload = function(id, id2) {
    $('#' + id).fileupload({
        url: APP + "fileUpload/index",
        dataType: 'json',
        done: function(e, data) {
            $.each(data.result.files, function(index, file) {
                var cont = js.uploadCont2(file.name, file.url, id, id2);
                $("#" + id2).append(cont);
            });
        },
        progressall: function(e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('#progress .progress-bar').css(
                'width',
                progress + '%'
            );
        }
    }).prop('disabled', !$.support.fileInput).parent().addClass($.support.fileInput ? undefined : 'disabled');
}

//已上传文件列表
js.uploadCont = function(str, id, id2) {
    var ar = str.split("|||");
    var cont = "";

    $.each(ar, function(i, str2) {
        if (str2) {
            var ar2 = str2.split("###");
            var name = ar2[0];
            var url = ar2[1];
            url = APP + url;
            cont += '<div><a href="javascript:void(0)" title="删除" style="color:red;" onclick="js.uploadDel(this, \'' + encodeURIComponent(url) + '\', \'' + id + '\')">X</a>&nbsp;&nbsp;<a href="' + url + '" target="_blank">' + name + '</a></div>';
        }
    });
    $("#" + id2).append(cont);
}

//已上传文件列表（ajax返回时用）
js.uploadCont2 = function(name, url, id, id2) {
    var cont = '<div><a href="javascript:void(0)" title="删除" style="color:red;" onclick="js.uploadDel(this, \'' + url + '\', \'' + id + '\')">X</a>&nbsp;&nbsp;<a href="' + decodeURIComponent(url) + '" target="_blank">' + name + '</a></div>';
    var url2 = decodeURIComponent(url);
    url2 = url2.replace(APP, "");
    js.uploadHide(name + "###" + url2 + "|||", id, id2);
    return cont;
}

//已上传文件隐藏标签
js.uploadHide = function(str, id, id2) {
    if ($("#" + id + "_hidden").length > 0) {
        //存在则追加
        var val = $("#" + id + "_hidden").val();
        $("#" + id + "_hidden").val(val + str);
    } else {
        //不存在则创建hidden标签
        var inputHid = '<input type="hidden" name="' + id + '_hidden" id="' + id + '_hidden" value="' + str + '"/>';
        $("#" + id2).append(inputHid);
    }
}

//上传文件删除
js.uploadDel = function(o, file, id) {
    if (confirm("是否确定删除该文件？")) {
        $.post(APP + "fileUpload/delete", {
            file: file
        }, function(data) {
            if (data == 'success') {
                var next = $(o).next();
                var cont = next.text() + "###" + next.attr("href").replace(APP, "") + "|||";
                var val = $("#" + id + "_hidden").val();
                val = val.replace(cont, "");
                $("#" + id + "_hidden").val(val);
                $(o).parent().remove();
            } else {
                alert("删除失败");
            }
        });
    }
}

js.open = function(url, w, h, can) {
    if (!w) w = 600;
    if (!h) h = 500;
    if (!can) can = 'resizable=yes,scrollbars=yes';
    var l = (screen.width - w) * 0.5;
    var t = (screen.height - h) * 0.5;
    var opar = window.open(url, '', 'width=' + w + 'px,height=' + h + 'px,left=' + l + 'px,top=' + t + 'px,' + can + '');
    return false;
}

//打开上传窗口
js.openupload = function(call, can) {
    if (!call) call = '';
    if (!can) can = {};
    var url = '' + APP + 'Xyupload/index/menuid/' + MENUID + '';
    if (call != '') url += '/callback/' + call + '';
    for (var a in can) url += '/' + a + '/' + can[a] + '';
    js.open(url, 550, 320);
    return false;
}


//-----选择卡-----
//新增选择卡
function addtabs(arr) {
        try {
            parent.tabs.add(js.applyIf(arr, {
                id: MENUID
            }));
        } catch (e) {}
        return false;
    }
    //关闭选择卡
function closetabs(num) {
        setTimeout(function() {
            try {
                parent.tabs.closetabs(num);
            } catch (e) {}
        }, 50);
    }
    //刷新选择卡
function reloadtabs(num) {
    try {
        parent.tabs.reload(num);
    } catch (e) {}
}