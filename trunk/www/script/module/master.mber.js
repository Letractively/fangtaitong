Z.Module.master = Z.Module.master || {};
Z.Module.master.mber = Z.Module.master.mber || {};

Z.Module.master.mber.index = Z.Module.master.mber.index || {};
Z.Module.master.mber.index.init = function()
{
    Z.Global.method.tierLook('.data tbody tr');
};

Z.Module.master.mber.create = Z.Module.master.mber.create || {};
Z.Module.master.mber.create.init = function()
{
    $.trim($('#index-types').html()) === '' || $('#m-type').powerFloat({eventType: 'focus', target: '#index-types'});

    $('#form-create-mber').submit(function(){
        Z.Global.method.ajaxForm(this);
        return false;
    }).validate();
};

Z.Module.master.mber.update = Z.Module.master.mber.update || {};
Z.Module.master.mber.update.init = function()
{
    $.trim($('#index-types').html()) === '' || $('#m-type').powerFloat({eventType: 'focus', target: '#index-types'});
    
    $('#form-update-mber').submit(function(){
        Z.Global.method.ajaxForm(this);
        return false;
    }).validate();
};

