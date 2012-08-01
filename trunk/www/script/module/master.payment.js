Z.Module.master = Z.Module.master || {};
Z.Module.master.payment = Z.Module.master.payment || {};

Z.Module.master.payment.index = Z.Module.master.payment.index || {};
Z.Module.master.payment.index.init = function()
{
    Z.Global.method.tierLook('.data tbody tr');
    $('a.oper').click(function(){
        Z.Global.method.ajaxLink(this);
        return false;
    });
};

Z.Module.master.payment.create = Z.Module.master.payment.create || {};
Z.Module.master.payment.create.init = function()
{
    $('#form-create-payment').validate();
};

Z.Module.master.payment.update = Z.Module.master.payment.update || {};
Z.Module.master.payment.update.init = function()
{
    $('#form-update-payment').submit(function(){
        Z.Global.method.ajaxForm(this);
        return false;
    }).validate();
};
