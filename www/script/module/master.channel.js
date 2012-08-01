Z.Module.master = Z.Module.master || {};
Z.Module.master.channel = Z.Module.master.channel || {};

Z.Module.master.channel.report = Z.Module.master.channel.report || {};
Z.Module.master.channel.report.init = function()
{
    Z.Global.method.tierLook('#history tbody tr');
    Z.Global.method.tierLook('#future tbody tr');

    $(".channel-tab-menu li").click(function(){
        location.hash = $(this).attr('alt');

        $(this).addClass("now").siblings().removeClass("now");
        $("div.channel-tab-box > table").eq( $(".channel-tab-menu li").index(this)).show().siblings().hide();

        Z.Global.method.tierLook('#history tbody tr');
        Z.Global.method.tierLook('#future tbody tr');

    }).hover(function(){
        $(this).addClass("hover");
    },function(){
        $(this).removeClass("hover");
    });

    if (location.hash) {
        $(".channel-tab-menu li[alt='" + location.hash.substr(1) + "']").click();
    }
};

Z.Module.master.channel.index = Z.Module.master.channel.index || {};
Z.Module.master.channel.index.init = function()
{
    Z.Global.method.tierLook('.data tbody tr');
    $('a.oper').click(function(){
        Z.Global.method.ajaxLink(this);
        return false;
    });
};

Z.Module.master.channel.create = Z.Module.master.channel.create || {};
Z.Module.master.channel.create.init = function()
{
    $('#form-create-channel').validate();
};

Z.Module.master.channel.update = Z.Module.master.channel.update || {};
Z.Module.master.channel.update.init = function()
{
    $('#form-update-channel').submit(function(){
        Z.Global.method.ajaxForm(this);
        return false;
    }).validate();
};
