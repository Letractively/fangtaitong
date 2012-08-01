Z.Module.master = Z.Module.master || {};
Z.Module.master.index  = Z.Module.master.index || {};

Z.Module.master.index.signin = Z.Module.master.index.signin || {};
Z.Module.master.index.signin.init = function()
{
    var html_captcha = '<p><label for="form-signin-captcha">验证码</label><img class="captcha" src="/com/captcha/image?a=/master/index/do-signin&o=' + (new Date).getTime()
    + '" alt="点击刷新" /><input tabindex="3" type="text" name="captcha" id="form-signin-captcha" class="text tulin required" /><a class="reload-captcha" href="javascript:;">换一张</a></p>'
    
    $('.reload-captcha').live('click', function() {
        Z.Global.method.reloadCaptcha('.captcha');
        $('#form-signin-captcha').val('');
    });

    $('#form-signin').submit(function(){
        var form = $(this);
        Z.Global.method.request({element: form, forward: true,
            prepare: function(opts, data){
                form.find('input[type=submit]').val('登录中...').removeClass('orange').addClass('green');
            },
            success: function(opts, data){
                if (data.stacode == 1) {
                    setTimeout(function(){location.href = data.forward}, data.timeout*1000);
                    return;
                }

                if (data.stacode == -1 && !$('.reload-captcha').length) {
                    form.find('p.submit').before(html_captcha);
                }

                Z.Global.method.respond(opts, data);
                form.find('input[type=submit]').val('登录').removeClass('green').addClass('orange');
            }
        });

        return false;
    });
};

Z.Module.master.index.apply = Z.Module.master.index.apply || {};
Z.Module.master.index.apply.init = function()
{
    $('.reload-captcha').click(function(){
        Z.Global.method.reloadCaptcha('.captcha');
        $('#form-apply-captcha').val('');
    });

    $('#form-apply').find('input').hint();

    $('#form-apply').submit(function(){
        Z.Global.method.ajaxForm(this);
        return false;
    });
};

Z.Module.master.index.start = Z.Module.master.index.start || {};
Z.Module.master.index.start.init = function()
{
    $('#form-start').submit(function(){
        Z.Global.method.ajaxForm(this);
        return false;
    });

    $('.reload-captcha').click(function(){
        Z.Global.method.reloadCaptcha('.captcha');
        $('#form-start-captcha').val('');
    });
};

Z.Module.master.index.signup = Z.Module.master.index.signup || {};
Z.Module.master.index.signup.init = function()
{
    var sel = new LinkageSel({
        data     : Z.Global.source.district,
        level    : 2,
        minWidth : 80,
        maxWidth : 80,
        select   : ['#form-signup-sel-province', '#form-signup-sel-city']
    });

    $('.folding .caption .note').click(function(){
        $(this).parent().parent().removeClass('folding');
        $(this).remove();
    });

    $('.reload-captcha').click(function(){
        Z.Global.method.reloadCaptcha('.captcha');
        $('#form-signup-captcha').val('');
    });

    $('#form-signup').submit(function(){
        Z.Global.method.ajaxForm(this);
        return false;
    });
};

Z.Module.master.index.passwordRecovery = Z.Module.master.index.passwordRecovery || {};
Z.Module.master.index.passwordRecovery.init = function()
{
    $('#form-password-recovery').submit(function(){
        Z.Global.method.ajaxForm(this);
        return false;
    });

    $('.reload-captcha').click(function(){
        Z.Global.method.reloadCaptcha('.captcha');
        $('#form-password-recovery-captcha').val('');
    });
};

Z.Module.master.index.resetPassword = Z.Module.master.index.resetPassword || {};
Z.Module.master.index.resetPassword.init = function()
{
    $('#form-reset-password').submit(function(){
        Z.Global.method.ajaxForm(this);
        return false;
    });
};

Z.Module.master.index.activateAccount = Z.Module.master.index.activateAccount || {};
Z.Module.master.index.activateAccount.init = function()
{
    $('#form-activate-account').submit(function(){
        Z.Global.method.ajaxForm(this);
        return false;
    });
};

Z.Module.master.index.sendActivateMail = Z.Module.master.index.sendActivateMail || {};
Z.Module.master.index.sendActivateMail.init = function()
{
    $('#form-send-activate-mail').submit(function(){
        Z.Global.method.ajaxForm(this);
        return false;
    });
    
    $('.reload-captcha').click(function(){
        Z.Global.method.reloadCaptcha('.captcha');
        $('#form-send-activate-captcha').val('');
    });
};
