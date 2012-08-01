Z.Module.hostel = Z.Module.hostel || {};
Z.Module.hostel.order = Z.Module.hostel.order || {};

Z.Module.hostel.order.index = Z.Module.hostel.order.index || {};
Z.Module.hostel.order.index.init = function(initOrder, initPrice, initSaved)
{
    var getPriceTable = function(args) {
        var rid   = args.rid;
        var bdate = args.bdate || Z.Global.method.date('Y/m/d', new Date());
        var edate = args.edate || Z.Global.method.date('Y/m/d', new Date());
        var price = args.price;
        var saved = args.saved;

        var week = {
            '1': '星期一',
            '2': '星期二',
            '3': '星期三',
            '4': '星期四',
            '5': '星期五',
            '6': '星期六',
            '0': '星期日'
        };

        var thead = '<thead><tr>';
        for (var wd in week) {
            thead += '<th>' + week[wd] + '</th>';
        }
        thead += '</tr></thead>';

        var tbody = '<tbody><tr>';

        var fd = Z.Global.method.date('w', new Date(bdate));
        var i = 0;
        if (fd != '0') {
            for (i = 1; i < fd; i++) {
                tbody += '<td><div class="empty"></div></td>';
            }
        } else {
            for (i = 1; i < 7; i++){
                tbody += '<td><div class="empty"></div></td>';
            }
        }
        var dayCount = 0;
        var priceSum = 0;

        for (var date in price) {
            tbody += '<td>'
                  + '<div class="date' + (saved[date] ? ' saved" title="' + Z.Global.method.date('Y年m月d日', new Date(date * 1000)) + '的房间不可用"' : '"') + '>' + Z.Global.method.date('m月d日', new Date(date * 1000)) + '</div>'
                  + '<div class="price">'
                  + '<input type="hidden" name="price[' + rid + '][' + date + ']" alt="' + price[date] / 100 + '" value="' + price[date] / 100 + '" />'
                  + price[date] / 100
                  + '</div>'
                  + '</td>'
                  + (Z.Global.method.date('w', new Date(date * 1000)) == '0' ? '</tr><tr>' : '');
            
            dayCount++;

            priceSum += price[date] - 0;
        }

        var ld = Z.Global.method.date('w', new Date(edate));
        if (ld != '0') {
            for (var j = ld; j < 7; j++) {
                tbody += '<td><div class="empty"></div></td>';
            }
        }

        tbody += '</tr></tbody>';

        var tfoot = '<tfoot><tr><td colspan="7">共 <span>'
                    + dayCount + '</span> 间夜， <span class="price-sum">'
                    + priceSum/100 + '</span> 元</td></tr></tfoot>';

        var table = '<table class="rprice">' + thead + tbody + tfoot + '</table>';

        var thisRoom = $('li#room-' + rid);

        thisRoom.find('.price-table').html(table);

        if ($.browser.msie && $.browser.version == 6) {
            // alert('fuck ie6');
            if (thisRoom.find('.main-info').outerHeight() > thisRoom.outerHeight()) {
                var h = thisRoom.find('.main-info').outerHeight();
                thisRoom.css('height', h);
                thisRoom.find('.info').css('height', h - 2);
            }
        }

        return priceSum/100;
    };

    var totalFee = 0;

    for (var rid in initOrder) {
        var fee = getPriceTable({
            rid  : rid,
            price: initPrice[rid],
            saved: initSaved[rid],
            bdate: initOrder[rid]['date'].split('-').join('/'),
            edate: Z.Global.method.date('Y/m/d', (initOrder[rid]['datm'] + (initOrder[rid]['lgth'] - 1) * 86400) * 1000)
        });
        totalFee += fee;
    }
    $('#total-fee').html(totalFee);

    $('ul#order-container>li.room .close').live('click', function(){
        if ($('ul#order-container>li.room').size() == 1) {
            alert('订单至少包含1个房间！');
            return false;
        } else if (confirm("是否删除该房间？")) {
            var thisRoom = $(this).parents('li.room');
            $('#total-fee').html(totalFee - thisRoom.find('.price-sum').html());
            thisRoom.hide('normal', function(){
                $(this).remove();
            });
        }
    });

    $('.copy-guest input[type="checkbox"]').live('change', function() {
        if ($(this).is(':checked')) {
            var rid = $(this).attr('alt');
            $('input[name="guest[' + rid + '][name]"]').val($('#c-name').val());
            $('input[name="guest[' + rid + '][call]"]').val($('#c-call').val());
            $('input[name="guest[' + rid + '][mail]"]').val($('#c-mail').val());
        }
    });

    $('#c-name ,#c-call ,#c-mail').live('change keyup', function() {
        var val  = this.value;
        var type = this.id.substr(2);
        $('.copy-guest input[type="checkbox"]:checked').each(function(){
            $('input[name="guest[' + $(this).attr('alt') + '][' + type + ']"]').val(val);
        });
    });
    
    $('.reload-captcha').click(function(){
        Z.Global.method.reloadCaptcha('img.captcha');
        $('#form-create-order-captcha').val('');
    });

    $('#form-create-order').submit(function() {
        var kept = $('ul#order-container>li.kept');
        if (kept.size()) {
            var info = '';
            kept.each(function(){
                info += $(this).find('.room-name').val() + ' 不可用，提示信息：'
                      + '<span class="warnning">'
                      + $(this).find('.header .title').attr('title')
                      + '</span><br />';
            });
            easyDialog.open({
                container:{
                    content: info
                },
                drags: false,
                autoClose: 3000
            });
        } else {
            Z.Global.method.ajaxForm(this);
        }
        return false;
    });
};
