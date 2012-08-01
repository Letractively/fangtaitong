Z.Module.master = Z.Module.master || {};
Z.Module.master.bill = Z.Module.master.bill || {};

Z.Module.master.bill.index = Z.Module.master.bill.index || {};
Z.Module.master.bill.index.init = function()
{
    Z.Global.method.tierLook('.data tbody tr');
};

Z.Module.master.bill.detail = Z.Module.master.bill.detail || {};
Z.Module.master.bill.detail.init = function()
{
    $('.handles a[alt]:not(".link")').click(function(){

        var code = $(this).attr('alt');

        easyDialog.open({
            container : {
                header : $('.dialog-' + code).attr('title'),
                content : $('.dialog-' + code).html()
            }
        });

        if (code == 'sktk') $('input[name="paid[0][date]"]:visible').val(Z.Global.method.date('Y-m-d', new Date()));

        if ($.datepicker) $.datepicker.init();
    });

    $('.handle-option').live('click', function(){

        // $('#closeBtn').click();
        easyDialog.close();

        var type = $(this).attr('alt');
        var name = $(this).attr('title');

        easyDialog.open({
            container: {
                header: '创建' + name,
                content: '<div>' + $('#dialog-form-handle-option').html() + '</div>'
            },
            drag: false
        });

        var thisForm = $('#easyDialogWrapper .form-handler-option');
        thisForm
            .attr('alt', type)
            .attr('action', '/master/' + type + '/do-create?stat=1')
            .find('.ho-name').html('<strong>' + name + '</strong>');

        return false;
    });

    $('.form-handler-option').live('submit', function() {
        var form = $(this);
        var type = form.attr('alt');
        var name = form.find('input[name="name"]').val();
        Z.Global.method.request({
            element: form,
            forward: false,
            prepare: function(){form.find('input[type="submit"]').val(Z.Global.string.WAITING);},
            success: function(opts, data){
                if (data.success) {
                    setTimeout(function(){
                        var optval = data.stacode;
                        var select = $('.b-' + type);
                        select.find('option').removeAttr('selected');

                        var newopt = $('<option value="' + optval + '">' + Z.Global.method.escape(name) + '</option>');
                        select.append(newopt);
                        newopt.attr('selected', 'selected');

                        Z.Global.method.closure();
                        
                        var hmap = {
                            'payment': 'sktk',
                            'settlem': 'jsfs'
                        };
                        $('.handles a[alt="' + hmap[type] + '"]').click();
                    }, 1000);
                } else {
                    Z.Global.method.respond(opts, data);
                }
            }
        });
        
        return false;
    });

    $('.form-bill-handle input[name="gqsj"]').live('change', function(){
        var thisTextInput = $(this).parents('.form-bill-handle').find('input[type="text"]');
        if (this.value == '1') {
            thisTextInput.removeAttr('disabled');
        } else {
            thisTextInput.attr('disabled', 'disabled');
        }
        return false;
    });

    $('.form-bill-handle').live('submit', function(){
        Z.Global.method.ajaxForm(this);
        return false;
    }).validate();
};
