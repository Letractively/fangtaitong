Z.Module.master = Z.Module.master || {};
Z.Module.master.stat = Z.Module.master.stat || {};

Z.Module.master.stat.getTimeLine = function()
{
    $('#reports-date, #reports-period').change(function(){
        var tmln = Z.Global.method.getTimeLine($('#reports-date').val(), $('#reports-period').val());

        $('#reports-range').html(
            '<span id="bdate">'
            + Z.Global.method.date('Y-m-d', tmln[0])
            + '</span> 至 <span id="edate">'
            + Z.Global.method.date('Y-m-d', tmln[1])
            + '</span>'
        );
    });
    $('#reports-date').val(Z.Global.method.date('Y-m-d')).change();
};

Z.Module.master.stat.setFormTimeRange = function(maxLength)
{
    var btime = 0;
    var etime = 0;
    var dlgth = 0;

    $('#reports-bdate, #reports-edate').change(function(){
        btime = Date.parse($('#reports-bdate').val().split('-').join('/'));
        etime = Date.parse($('#reports-edate').val().split('-').join('/'));
        dlgth = (etime - btime) / 86400000 + 1;

        $('#reports-range').html(
            '<span id="bdate">'
            + Z.Global.method.date('Y-m-d', btime)
            + '</span> 至 <span id="edate">'
            + Z.Global.method.date('Y-m-d', etime)
            + '</span>'
        );
    });

    $('#form-reports-realtime input[type="submit"]').click(function(){
        if (dlgth < 1 || dlgth > maxLength) {
            alert('选择的日期超出有限范围！');
            return false;
        }
    });
};

Z.Module.master.stat.setLimit = function(maxLength)
{
    $('.reports-query form input[type="submit"]').click(function(){

        if (maxLength) {
            var btime = Date.parse($('#reports-bdate').val().split('-').join('/'));
            var etime = Date.parse($('#reports-edate').val().split('-').join('/'));
            var dlgth = (etime - btime) / 86400000 + 1;

            if (dlgth < 1 || dlgth > maxLength) {
                alert('选择的日期超出有限范围！');
                return false;
            }
        }

        timing(function(){
            $('.reports-query form').submit();
        });

        return false;

    });
};

// 报表导航页

Z.Module.master.stat.index = Z.Module.master.stat.index || {};
Z.Module.master.stat.index.init = function()
{
};

// 入住记录

Z.Module.master.stat.rzjl = Z.Module.master.stat.rzjl || {};
Z.Module.master.stat.rzjl.init = function()
{
    Z.Global.method.tierLook('.report tbody tr');
};

// 退房记录

Z.Module.master.stat.tfjl = Z.Module.master.stat.tfjl || {};
Z.Module.master.stat.tfjl.init = function()
{
    Z.Global.method.tierLook('.report tbody tr');
};

// 应住记录

Z.Module.master.stat.yzjl = Z.Module.master.stat.yzjl || {};
Z.Module.master.stat.yzjl.init = function()
{
    Z.Global.method.tierLook('.report tbody tr', false, 2);
};

// 应退记录

Z.Module.master.stat.ytjl = Z.Module.master.stat.ytjl || {};
Z.Module.master.stat.ytjl.init = function()
{
    Z.Global.method.tierLook('.report tbody tr', false, 2);
};

// 结算-收款流水

Z.Module.master.stat.skls = Z.Module.master.stat.skls || {};
Z.Module.master.stat.skls.init = function()
{
    Z.Global.method.tierLook('.report tbody tr');
};

// 结算-收款渠道

Z.Module.master.stat.jsbbSkqddzmxForm = Z.Module.master.stat.jsbbSkqddzmxForm || {};
Z.Module.master.stat.jsbbSkqddzmxForm.init = function()
{
    Z.Module.master.stat.setFormTimeRange(31);

    var tdtm = Date.parse(Z.Global.method.date('Y/m/d'));
    $('#reports-bdate').val(Z.Global.method.date('Y-m-d', tdtm - 86400000 * 30)).change();
    $('#reports-edate').val(Z.Global.method.date('Y-m-d', tdtm)).change();
};

Z.Module.master.stat.jsbbSkqddzmx = Z.Module.master.stat.jsbbSkqddzmx || {};
Z.Module.master.stat.jsbbSkqddzmx.init = function()
{
    Z.Global.method.tierLook('.report tbody tr');

    Z.Module.master.stat.setLimit(31);

    if (!$('.report .none').size()) {
        $('.report').tablesorter({
            headers: {
                0: {sorter: false}
            },
            widgets: ['zebra']
        });
    }
};

// 结算-预订客人

Z.Module.master.stat.jsbbYdkrdzmxForm = Z.Module.master.stat.jsbbYdkrdzmxForm || {};
Z.Module.master.stat.jsbbYdkrdzmxForm.init = function()
{
    Z.Module.master.stat.setFormTimeRange(31);
    
    var tdtm = Date.parse(Z.Global.method.date('Y/m/d'));
    $('#reports-bdate').val(Z.Global.method.date('Y-m-d', tdtm - 86400000 * 30)).change();
    $('#reports-edate').val(Z.Global.method.date('Y-m-d', tdtm)).change();
};

Z.Module.master.stat.jsbbYdkrdzmx = Z.Module.master.stat.jsbbYdkrdzmx || {};
Z.Module.master.stat.jsbbYdkrdzmx.init = function()
{
    Z.Global.method.tierLook('.report tbody tr');

    Z.Module.master.stat.setLimit(31);

    if (!$('.report .none').size()) {
        $('.report').tablesorter({
            headers: {
                0: {sorter: false}
            },
            widgets: ['zebra']
        });
    }
};

// 结算-销售人员

Z.Module.master.stat.jsbbXsrydzmxForm = Z.Module.master.stat.jsbbXsrydzmxForm || {};
Z.Module.master.stat.jsbbXsrydzmxForm.init = function()
{
    Z.Module.master.stat.setFormTimeRange(31);
    
    var tdtm = Date.parse(Z.Global.method.date('Y/m/d'));
    $('#reports-bdate').val(Z.Global.method.date('Y-m-d', tdtm - 86400000 * 30)).change();
    $('#reports-edate').val(Z.Global.method.date('Y-m-d', tdtm)).change();
};

Z.Module.master.stat.jsbbXsrydzmx = Z.Module.master.stat.jsbbXsrydzmx || {};
Z.Module.master.stat.jsbbXsrydzmx.init = function()
{
    Z.Global.method.tierLook('.report tbody tr');

    Z.Module.master.stat.setLimit(31);

    if (!$('.report .none').size()) {
        $('.report').tablesorter({
            headers: {
                0: {sorter: false}
            },
            widgets: ['zebra']
        });
    }
};

// 结算-预订渠道

Z.Module.master.stat.jsbbYdqddzmxForm = Z.Module.master.stat.jsbbYdqddzmxForm || {};
Z.Module.master.stat.jsbbYdqddzmxForm.init = function()
{
    Z.Module.master.stat.setFormTimeRange(31);
    
    var tdtm = Date.parse(Z.Global.method.date('Y/m/d'));
    $('#reports-bdate').val(Z.Global.method.date('Y-m-d', tdtm - 86400000 * 30)).change();
    $('#reports-edate').val(Z.Global.method.date('Y-m-d', tdtm)).change();
};

Z.Module.master.stat.jsbbYdqddzmx = Z.Module.master.stat.jsbbYdqddzmx || {};
Z.Module.master.stat.jsbbYdqddzmx.init = function()
{
    Z.Global.method.tierLook('.report tbody tr');

    Z.Module.master.stat.setLimit(31);

    if (!$('.report .none').size()) {
        $('.report').tablesorter({
            headers: {
                0: {sorter: false}
            },
            widgets: ['zebra']
        });
    }
};

// 销售-房间

Z.Module.master.stat.xsbbSyfjForm = Z.Module.master.stat.xsbbSyfjForm || {};
Z.Module.master.stat.xsbbSyfjForm.init = function()
{
    Z.Module.master.stat.getTimeLine();
};

Z.Module.master.stat.xsbbSyfj = Z.Module.master.stat.xsbbSyfj || {};
Z.Module.master.stat.xsbbSyfj.init = function()
{
    Z.Global.method.tierLook('.report tbody tr', {over: 'over', even: ''});


    if (!$('.report .none').size()) {
        $('.report').tablesorter({
            headers: {
                0: {sorter: false}
            },
            widgets: ['zebra']
        });
    }
};

// 销售-销售人员

Z.Module.master.stat.xsbbXsryForm = Z.Module.master.stat.xsbbXsryForm || {};
Z.Module.master.stat.xsbbXsryForm.init = function()
{
    Z.Module.master.stat.getTimeLine();
};

Z.Module.master.stat.xsbbXsry = Z.Module.master.stat.xsbbXsry || {};
Z.Module.master.stat.xsbbXsry.init = function()
{
    Z.Global.method.tierLook('.report tbody tr', {over: 'over', even: ''});


    if (!$('.report .none').size()) {
        $('.report').tablesorter({
            headers: {
                0: {sorter: false}
            },
            widgets: ['zebra']
        });
    }
};

// 销售-预订类型

Z.Module.master.stat.xsbbYdlxForm = Z.Module.master.stat.xsbbYdlxForm || {};
Z.Module.master.stat.xsbbYdlxForm.init = function()
{
    Z.Module.master.stat.getTimeLine();
};

Z.Module.master.stat.xsbbYdlx = Z.Module.master.stat.xsbbYdlx || {};
Z.Module.master.stat.xsbbYdlx.init = function()
{
    Z.Global.method.tierLook('.report tbody tr', {over: 'over', even: ''});


    if (!$('.report .none').size()) {
        $('.report').tablesorter({
            headers: {
                0: {sorter: false}
            },
            widgets: ['zebra']
        });
    }
};

// 销售-预订渠道

Z.Module.master.stat.xsbbYdqdForm = Z.Module.master.stat.xsbbYdqdForm || {};
Z.Module.master.stat.xsbbYdqdForm.init = function()
{
    Z.Module.master.stat.getTimeLine();
};

Z.Module.master.stat.xsbbYdqd = Z.Module.master.stat.xsbbYdqd || {};
Z.Module.master.stat.xsbbYdqd.init = function()
{
    Z.Global.method.tierLook('.report tbody tr', {over: 'over', even: ''});


    if (!$('.report .none').size()) {
        $('.report').tablesorter({
            headers: {
                0: {sorter: false}
            },
            widgets: ['zebra']
        });
    }
};

// 销售-其他费用

Z.Module.master.stat.xsbbQtfyForm = Z.Module.master.stat.xsbbQtfyForm || {};
Z.Module.master.stat.xsbbQtfyForm.init = function()
{
    Z.Module.master.stat.getTimeLine();
};

Z.Module.master.stat.xsbbQtfy = Z.Module.master.stat.xsbbQtfy || {};
Z.Module.master.stat.xsbbQtfy.init = function()
{
    Z.Global.method.tierLook('.report tbody tr', {over: 'over', even: ''});


    if (!$('.report .none').size()) {
        $('.report').tablesorter({
            headers: {
                0: {sorter: false}
            },
            widgets: ['zebra']
        });
    }
};
