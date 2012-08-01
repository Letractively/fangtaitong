Z.Module.master = Z.Module.master || {};
Z.Module.master.account = Z.Module.master.account || {};

Z.Module.master.account.index = Z.Module.master.account.index || {};
Z.Module.master.account.index.init = function()
{
    Z.Global.method.tierLook('.data tbody tr');

    $('a.oper').click(function(){
        Z.Global.method.ajaxLink(this);
        return false;
    });
};

Z.Module.master.account.create = Z.Module.master.account.create || {};
Z.Module.master.account.create.init = function()
{
    $('#form-create-account').validate();
};

Z.Module.master.account.update = Z.Module.master.account.update || {};
Z.Module.master.account.update.init = function()
{
    $('#form-update-account').submit(function(){
        Z.Global.method.ajaxForm(this);
        return false;
    }).validate();

    $('a.oper').click(function(){
        Z.Global.method.ajaxLink(this, true);
        return false;
    });
};
