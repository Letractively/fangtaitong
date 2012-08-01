Z.Module.master = Z.Module.master || {};
Z.Module.master.settlem = Z.Module.master.settlem || {};

Z.Module.master.settlem.report = Z.Module.master.settlem.report || {};
Z.Module.master.settlem.report.init = function()
{
    Z.Global.method.tierLook('#history tbody tr');
    Z.Global.method.tierLook('#future tbody tr');

    $(".settlem-tab-menu li").click(function(){
        location.hash = $(this).attr('alt');

        $(this).addClass("now").siblings().removeClass("now");
        $("div.settlem-tab-box > table").eq( $(".settlem-tab-menu li").index(this)).show().siblings().hide();

        Z.Global.method.tierLook('#history tbody tr');
        Z.Global.method.tierLook('#future tbody tr');

    }).hover(function(){
        $(this).addClass("hover");
    },function(){
        $(this).removeClass("hover");
    });

    if (location.hash) {
        $(".settlem-tab-menu li[alt='" + location.hash.substr(1) + "']").click();
    }
};

Z.Module.master.settlem.index = Z.Module.master.settlem.index || {};
Z.Module.master.settlem.index.init = function()
{
    Z.Global.method.tierLook('.data tbody tr');
    $('a.oper').click(function(){
        Z.Global.method.ajaxLink(this);
        return false;
    });
};

Z.Module.master.settlem.create = Z.Module.master.settlem.create || {};
Z.Module.master.settlem.create.init = function()
{
    $('#form-create-settlem').submit(function(){
        Z.Global.method.ajaxForm(this);
        return false;
    }).validate();
};

Z.Module.master.settlem.update = Z.Module.master.settlem.update || {};
Z.Module.master.settlem.update.init = function()
{
    $('#form-update-settlem').submit(function(){
        Z.Global.method.ajaxForm(this);
        return false;
    }).validate();
};
