Z.Module.master = Z.Module.master || {};
Z.Module.master.hostel = Z.Module.master.hostel || {};

Z.Module.master.hostel.index = Z.Module.master.hostel.index || {};
Z.Module.master.hostel.index.init = function()
{
};

Z.Module.master.hostel.invite = Z.Module.master.hostel.invite || {};
Z.Module.master.hostel.invite.init = function()
{
    $('#form-invite-refer').hover(function(){
        $(this).select();
    });
};

Z.Module.master.hostel.update = Z.Module.master.hostel.update || {};
Z.Module.master.hostel.update.init = function()
{
    var sel = new LinkageSel({
        data  : Z.Global.source.district,
        level : 2,
        select: ['#form-update-sel-province', '#form-update-sel-city']
    });

    // 点击重置按钮时恢复原省市信息
    $('#form-update').find('input[type=reset]').click(function(){
        setTimeout(function(){sel.changeValues(Z.Global.config.area);}, 1); // IE6 Done
    }).click();

    $('#form-update').submit(function(){
        Z.Global.method.ajaxForm(this, true);
        return false;
    }).validate();
};

Z.Module.master.hostel.updateRule = Z.Module.master.hostel.updateRule || {};
Z.Module.master.hostel.updateRule.init = function()
{
    $('#form-update-rule').submit(function(){
        Z.Global.method.ajaxForm(this, true);
        return false;
    }).validate();
};
