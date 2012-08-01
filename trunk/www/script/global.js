// Global namespace Zyon!
window.Z = window.Z || {};
Z.Global = Z.Global || {};
Z.Module = Z.Module || {};

Z.Global.config = Z.Global.config || {};
Z.Global.method = Z.Global.method || {};
Z.Global.source = Z.Global.source || {};
Z.Global.string = Z.Global.string || {};

Z.Global.string.SUCCESS = '操作成功！';
Z.Global.string.FAILURE = '操作失败！';
Z.Global.string.WAITING = '处理中，请稍侯...';
Z.Global.string.LOADING = '加载中，请稍侯...';
Z.Global.string.POSTING = '数据发送中，请稍侯...';

Z.Global.config.REQUESTS_INTERVAL = 5; // s

// getPageScroll() by quirksmode.com
Z.Global.method.getPageScroll = function()
{
    var xScroll, yScroll;
    if (self.pageYOffset) {
        yScroll = self.pageYOffset;
        xScroll = self.pageXOffset;
    } else if (document.documentElement && document.documentElement.scrollTop) {     // Explorer 6 Strict
        yScroll = document.documentElement.scrollTop;
        xScroll = document.documentElement.scrollLeft;
    } else if (document.body) {// all other Explorers
        yScroll = document.body.scrollTop;
        xScroll = document.body.scrollLeft;
    }
    return new Array(xScroll,yScroll);
};

// Adapted from getPageSize() by quirksmode.com
Z.Global.method.getPageHeight = function()
{
    var windowHeight;
    if (self.innerHeight) { // all except Explorer
        windowHeight = self.innerHeight;
    } else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
        windowHeight = document.documentElement.clientHeight;
    } else if (document.body) { // other Explorers
        windowHeight = document.body.clientHeight;
    }
    return windowHeight;
};

Z.Global.method.getPageOffset = function(){

    var offset = {x:0, y:0};
    
    // 如果浏览器支持 pageYOffset, 通过 pageXOffset 和 pageYOffset 获取页面和视窗之间的距离
    if(typeof window.pageYOffset != 'undefined') {
        offset.x = window.pageXOffset;
        offset.y = window.pageYOffset;
    }
    // 如果浏览器支持 compatMode, 并且指定了 DOCTYPE, 通过 documentElement 获取滚动距离作为页面和视窗间的距离
    // IE 中, 当页面指定 DOCTYPE, compatMode 的值是 CSS1Compat, 否则 compatMode 的值是 BackCompat
    else if(typeof document.compatMode != 'undefined' && document.compatMode != 'BackCompat') {
        offset.x = document.documentElement.scrollLeft;
        offset.y = document.documentElement.scrollTop;
    }
    // 如果浏览器支持 document.body, 可以通过 document.body 来获取滚动高度
    else if(typeof document.body != 'undefined') {
        offset.x = document.body.scrollLeft;
        offset.y = document.body.scrollTop;
    }
    
    return offset;
};

//删除数组中重复的元素
Z.Global.method.uniqArray = function(arr){
    arr = arr || [];
    var a = {};

    for (var i = 0; i < arr.length; i++){
        var v = arr[i];
        if (typeof(a[v]) == 'undefined'){
            a[v] = 1;
        }
    }

    arr.length = 0;

    for (var j in a){
        arr[arr.length] = j;
    }
    return arr;
};

// Fetch keys from map
Z.Global.method.getKeys = function(map)
{
    var ret = [];
    if (map) {
        for (var key in map) {
            ret.push(key);
        }
    }

    return ret;
};

/**
* Format date time
* @param string a Format
* @param int    s Timestamp
*/
Z.Global.method.date = function(a, s)
{
    var d = s ? new Date(s) : new Date(), f = d.getTime();
    return ('' + a).replace(/a|A|d|D|F|g|G|h|H|i|I|j|l|L|m|M|n|s|S|t|T|U|w|y|Y|z|Z/g, function(a) {
        switch (a) {
        case 'a' : return d.getHours() > 11 ? 'pm' : 'am';
        case 'A' : return d.getHours() > 11 ? 'PM' : 'AM';
        case 'd' : return ('0' + d.getDate()).slice(-2);
        case 'D' : return ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'][d.getDay()];
        case 'F' : return ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'][d.getMonth()];
        case 'g' : return (s = (d.getHours() || 12)) > 12 ? s - 12 : s;
        case 'G' : return d.getHours();
        case 'h' : return ('0' + ((s = d.getHours() || 12) > 12 ? s - 12 : s)).slice(-2);
        case 'H' : return ('0' + d.getHours()).slice(-2);
        case 'i' : return ('0' + d.getMinutes()).slice(-2);
        case 'I' : return (function(){d.setDate(1); d.setMonth(0); s = [d.getTimezoneOffset()]; d.setMonth(6); s[1] = d.getTimezoneOffset(); d.setTime(f); return s[0] == s[1] ? 0 : 1;})();
        case 'j' : return d.getDate();
        case 'l' : return ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][d.getDay()];
        case 'L' : return (s = d.getFullYear()) % 4 === 0 && (s % 100 !== 0 || s % 400 === 0) ? 1 : 0;
        case 'm' : return ('0' + (d.getMonth() + 1)).slice(-2);
        case 'M' : return ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'][d.getMonth()];
        case 'n' : return d.getMonth() + 1;
        case 's' : return ('0' + d.getSeconds()).slice(-2);
        case 'S' : return ['th', 'st', 'nd', 'rd'][(s = d.getDate()) < 4 ? s : 0];
        case 't' : return (function(){d.setDate(32); s = 32 - d.getDate(); d.setTime(f); return s;})();
        case 'T' : return 'UTC';
        case 'U' : return ('' + f).slice(0, -3);
        case 'w' : return d.getDay();
        case 'y' : return ('' + d.getFullYear()).slice(-2);
        case 'Y' : return d.getFullYear();
        case 'z' : return (function(){d.setMonth(0); return d.setTime(f - d.setDate(1)) / 86400000;})();
        default  : return -d.getTimezoneOffset() * 60;
        }
    });
};

/**
 * @param string str
 */
Z.Global.method.escape = function(str)
{
    return $(document.createElement("div")).text(str).html();
};

/**
* @param int    sta 状态码，1为success，0为failure
* @param string msg 提示信息
* @param int    lft (optional) 生命周期，单位为秒，值为0则不过期
*/
Z.Global.method.notice = function (sta, msg, lft)
{
    lft = lft || 0;
    var tip = $('div.tip');

    if (tip.length)
    {
        if (tip.attr('alt'))
        {
            clearTimeout(tip.attr('alt'));
        }
    }
    else
    {
        $('body').append('<div class="tip" alt="0" style="display:none"></div>');
        tip = $('div.tip');

        $(window).scroll(function(){
            tip.css('top', Z.Global.method.getPageScroll()[1] + 'px');
        });
    }

    tip.empty().html('<div class="tip-' + (sta ? 'success' : 'failure') + '">' + msg + '</div>');

    tip.css({
        top : Z.Global.method.getPageScroll()[1] + 'px',
        left: (document.documentElement.clientWidth / 2) - (msg.length * 13 / 2) + 'px'
    }).fadeIn('fast');

    if (lft)
    {
        tip.attr('alt', setTimeout(function(){tip.attr('alt', '0');tip.fadeOut();}, (lft * 1000)));
    }
};

/**
* Usage:
* Z.Global.method.tierLook('.data tbody tr');
*/
Z.Global.method.tierLook = function(obj, cls, row)
{
    obj = $(obj);
    cls = cls || {over: 'over', even: 'even'};
    row = row || 1;

    if (cls.even)
    {
        $(obj.selector).removeClass(cls.even);
        $(obj.selector + ':visible').addClass(function(index){
            if (index % (row*2) >= row) {
                return cls.even;
            }
        });
    }

    if (cls.over)
    {
        obj.each(function(){
            $(this).hover(
                function(){
                    var min = parseInt($(obj.selector + ':visible').index(this)/row, 10)*row;
                    var max = min + row - 1;

                    $(obj.selector + ':visible').each(function(idx){
                        if (idx >= min && idx <= max) {
                            $(this).addClass(cls.over);
                        }
                    });
                },
                function(){
                    var min = parseInt($(obj.selector + ':visible').index(this)/row, 10)*row;
                    var max = min + row - 1;

                    $(obj.selector + ':visible').each(function(idx){
                        if (idx >= min && idx <= max) {
                            $(this).removeClass(cls.over);
                        }
                    });
                }
            );
        });
    }
};

Z.Global.method.reloadCaptcha = function(captcha)
{
    captcha = $(captcha);

    // Work in IE6, preload image.
    var newCaptcha = new Image();
    newCaptcha.onload = function(){captcha.attr('src', $(this).attr('src'));};
    newCaptcha.src = captcha.attr('src').replace(/&o=[\d.]+/, '&o=' + (new Date()).getTime());
};

/**
* 准备操作
*/
Z.Global.method.prepare = function(options)
{
    var setting = {
        message: Z.Global.string.WAITING
    };
    $.extend(setting, options || {});

    easyDialog.open({
        container : {content: '<div class="ajax-form-loading"></div><p class="align-center">' + setting.message + '</p>'},
        drag : false
    });
};

/**
* 操作结束
*/
Z.Global.method.closure = function()
{
    if (window.easyDialog && $('#easyDialogBox').length) {
        easyDialog.close();
    }
};

/**
* 提交客户端请求
*/
Z.Global.method.request = function(options)
{
    var setting = {
        element: null,
        forward: true,
        singlet: true,

        prepare: Z.Global.method.prepare,
        success: Z.Global.method.respond,
        failure: null  // *Unsupported!
    };

    if (options && !options.element) {
        setting.element = options;
    } else {
        $.extend(setting, options || {});
    }

    if (!setting.element) {
        return false;
    }

    setting.element = $(setting.element);

    if (setting.singlet && setting.element[0].__waiting) {
        return false;
    }

    if (setting.element.is('form')) {
        var form = setting.element;
        if (!form.valid()) {
            return false;
        }

        $.ajax({
            url       : form.attr('action'),
            type      : form.attr('method'),
            data      : form.serialize(),
            dataType  : 'json',
            beforeSend: function(){
                setting.prepare(setting);
            },
            success   : function(message){
                setting.success(setting, message);
            }
        });
    } else {
        var link = setting.element;

        $.ajax({
            url       : link.attr('href') || link.attr('alt'),
            type      : 'get',
            dataType  : 'json',
            beforeSend: function(){
                setting.prepare(setting);
            },
            success   : function(message){
                setting.success(setting, message);
            }
        });
    }

    if (setting.singlet) {
        setting.element[0].__waiting = 1;
    }

    return false;
};

/**
* 处理服务器端响应数据
*/
Z.Global.method.respond = function(opts, data)
{
    var args = {forward: true, element: [{}]};
    $.extend(args, opts || {});

    if (data.success) {
        easyDialog.open({
            container : {
                content : ('<div>' + Z.Global.string.SUCCESS + (data.message===null ? '' : data.message) + '</div>')
                + (data.content===null ? '' : '<div style="margin-top:8px"><ul><li>' + (typeof data.content == 'string' ? data.content : data.content.join('</li><li>')) + '</li></ul></div>')
            }
        });

        if (args.forward && data.forward) {
            if (data.forward == 'javascript:history.go(-1)') {
                setTimeout(function(){location.reload();}, (data.timeout || 2)*1000);
            } else {
                setTimeout(function(){location.href = data.forward;}, (data.timeout || 2)*1000);
            }

            return;
        } else {
            setTimeout(function(){easyDialog.close();if (args.singlet) {args.element[0].__waiting = 0;}}, (data.timeout || 2)*1000);

            if ($('.reload-captcha').length) {
                $('.reload-captcha').click();
            }
        }
    } else {
        easyDialog.open({
            container : {
                header : Z.Global.string.FAILURE,
                content : (data.message===null ? '' : '<div style="color:red">' + data.message + '</div>')
                + (data.content===null ? '' : '<div style="margin-top:8px"><ul><li>' + (typeof data.content == 'string' ? data.content : data.content.join('</li><li>')) + '</li></ul></div>')
            },
            autoClose : data.forward ? (data.timeout || 5)*1000 : 0,
            callback: function(){ if (args.singlet) { args.element[0].__waiting = 0; } }
        });

        if ($('.reload-captcha').length) {
            $('.reload-captcha').click();
        }
    }
};

Z.Global.method.ajaxForm = function(form, stay)
{
    Z.Global.method.request({element: form, forward: !stay});
    return false;
};

Z.Global.method.ajaxLink = function(link, stay)
{
    Z.Global.method.request({element: link, forward: !stay});
    return false;
};

Z.Global.method.adRandomLoop = function(cls)
{
    var len = (cls = $(cls)).length;

    if (len)
    {
        var rnd = function(len)
        {
            var idx = Math.floor(Math.random() * (len * (len + 1) / 2)) + 1;
            var sum = 0;
            for(var i = len; i > 0; i--)
            {
                sum += len;
                if(idx <= sum)
                {
                    return (len - i);
                }
            }
        };

        var pos = rnd(len);

        var ads = function()
        {
            cls.eq(pos).fadeIn(500).delay(4000).slideUp(500, function(){
                pos = pos == len - 1 ? 0 : pos + 1;
                if ($(cls).parent().is(':visible')) ads();
            });
        };

        cls.hide().hover(
            function(){$(this).stop(true, true);},
            function(){ads();}
        );

        ads();
    }
    else
    {
        return false;
    }
};

/**
* 指定时间点长度获取时间轴
* lght : [D, W, M, S, Y]
*/
Z.Global.method.getTimeLine = function(date, lght)
{
    var datm = Date.parse(date.split('-').join('/'));
    var line = [];

    switch(lght) {
    case 'D':
        line.push(datm);
        line.push(datm + 86399000);

        break;
    case 'W':
        line.push(datm - ((Z.Global.method.date('w', datm) || 7) - 1)*86400000);
        line.push(line[0] + 86400000*6+86399000);

        break;
    case 'M':
        line.push(Date.parse(Z.Global.method.date('Y/m/01', datm)));
        line.push(Date.parse(Z.Global.method.date('Y/m/', datm) + Z.Global.method.date('t', datm))+86399000);

        break;
    case 'S':
        var mon = parseInt(Z.Global.method.date('n', datm), 10);
        var ses = {3:31, 6:30, 9:30, 12:31};
        for (var key in ses)
        {
            if (mon <= key)
            {
                line.push(Date.parse(Z.Global.method.date('Y/' + (key-2) + '/01', datm)));
                line.push(Date.parse(Z.Global.method.date('Y/' + key + '/' + ses[key], datm))+86399000);
                break;
            }
        }

        break;
    case 'Y':
        line.push(Date.parse(Z.Global.method.date('Y/01/01', datm)));
        line.push(Date.parse(Z.Global.method.date('Y/12/31', datm)) + 86399000);

        break;
    default:
        if ((parseInt(lght, 10)+'').length != (lght+'').length) {
            return false;
        }

        line.push(datm);
        line.push(datm + (lght-1)*86400000 + 86399000);
    }

    return line;
};

$(function(){
    if($.datepicker) $.datepicker.init();

    $(window).unload(function(){
        Z.Global.method.closure();
    });
});
