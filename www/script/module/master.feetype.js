Z.Module.master = Z.Module.master || {};
Z.Module.master.feetype = Z.Module.master.feetype || {};

Z.Module.master.feetype.index = Z.Module.master.feetype.index || {};
Z.Module.master.feetype.index.init = function()
{
    Z.Global.method.tierLook('.data tbody tr');
    $('a.oper').click(function(){
        Z.Global.method.ajaxLink(this);
        return false;
    });
};

Z.Module.master.feetype.create = Z.Module.master.feetype.create || {};
Z.Module.master.feetype.create.init = function()
{
    $('#form-create-feetype').validate();
};

Z.Module.master.feetype.update = Z.Module.master.feetype.update || {};
Z.Module.master.feetype.update.init = function()
{
    $('#form-update-feetype').submit(function(){
        Z.Global.method.ajaxForm(this);
        return false;
    }).validate({errorElement: 'span'});
};
