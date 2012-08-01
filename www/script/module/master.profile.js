Z.Module.master = Z.Module.master || {};
Z.Module.master.profile = Z.Module.master.profile || {};

Z.Module.master.profile.updateSelf = Z.Module.master.profile.updateSelf || {};
Z.Module.master.profile.updateSelf.init = function()
{
    $('#form-update-self').submit(function(){
        Z.Global.method.ajaxForm(this);
        return false;
    }).validate();

    $('#form-update-self-password').submit(function(){
        Z.Global.method.ajaxForm(this);
        return false;
    }).validate();
};
