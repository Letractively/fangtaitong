Z.Module.master = Z.Module.master || {};
Z.Module.master.typedef = Z.Module.master.typedef || {};

Z.Module.master.typedef.report = Z.Module.master.typedef.report || {};
Z.Module.master.typedef.report.init = function()
{
    Z.Global.method.tierLook('#history tbody tr');
    Z.Global.method.tierLook('#future tbody tr');

    $(".typedef-tab-menu li").click(function(){
        location.hash = $(this).attr('alt');

        $(this).addClass("now").siblings().removeClass("now");
        $("div.typedef-tab-box > table").eq( $(".typedef-tab-menu li").index(this)).show().siblings().hide();

        Z.Global.method.tierLook('#history tbody tr');
        Z.Global.method.tierLook('#future tbody tr');

    }).hover(function(){
        $(this).addClass("hover");
    },function(){
        $(this).removeClass("hover");
    });

    if (location.hash) {
        $(".typedef-tab-menu li[alt='" + location.hash.substr(1) + "']").click();
    }
};

Z.Module.master.typedef.index = Z.Module.master.typedef.index || {};
Z.Module.master.typedef.index.init = function()
{
    Z.Global.method.tierLook('.data tbody tr');
    $('a.oper').click(function(){
        Z.Global.method.ajaxLink(this);
        return false;
    });
};

Z.Module.master.typedef.create = Z.Module.master.typedef.create || {};
Z.Module.master.typedef.create.init = function()
{
    $('#form-create-typedef').submit(function(){
        Z.Global.method.ajaxForm(this);
        return false;
    }).validate();
};

Z.Module.master.typedef.update = Z.Module.master.typedef.update || {};
Z.Module.master.typedef.update.init = function()
{
    $('#form-update-typedef').submit(function(){
        Z.Global.method.ajaxForm(this);
        return false;
    }).validate();
};
