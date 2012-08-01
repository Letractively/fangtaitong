Z.Module.master = Z.Module.master || {};
Z.Module.master.order = Z.Module.master.order || {};

Z.Module.master.order.priceDlist = {
    _lst: null,

    _ext: null,

    _oid: null,

    _rid: null,

    mod: function(idx, ele) {
        ele = $(ele);

        if (ele.is('.price')) {
            this._lst[idx][1] = ele.val()-0;
            this._lst[idx][4] = 1;
        }

        if (ele.is('.brice')) {
            this._lst[idx][2] = ele.val()-0;
            this._lst[idx][5] = 1;
        }

        this.draw();
    },

    shift: function() {
        this._lst.shift();
        this.load();
    },

    pop: function() {
        this._lst.pop();
        this.load();
    },

    unshift: function(size) {
        if (parseInt(size,10) + '' !== size || size > 30 || size < 1) {
            alert('天数值错误，只能为 1 至 30');
            return;
        }

        this.load(0 - size);
    },

    push: function(size) {
        if (parseInt(size,10)+'' !== size || size > 30 || size < 1) {
            alert('天数值错误，只能为 1 至 30');
            return;
        }

        this.load(size - 0);
    },

    load: function(increment) {
        var that = this;
        var push = true;

        var obtm = that._lst[0][0] - 0;
        var oetm = that._lst[that._lst.length - 1][0] - 0;

        if (increment) {
            var tlst = [];
            var i = 0;

            if (increment > 0) {
                for (i = 1; i <= increment; i++) {
                    tlst[oetm + i * 86400 + ''] = 1;
                    // alert(Z.Global.method.date('Y/m/d', (oetm + i * 86400) * 1000));
                }
                oetm += increment * 86400;
            } else if (increment < 0) {
                for (i = -1; i >= increment; i--) { // 取间夜
                    tlst[obtm + i * 86400 + ''] = 0;
                    // alert(Z.Global.method.date('Y/m/d', (obtm + i * 86400) * 1000));
                }
                obtm += increment * 86400;
            }
        }

        if ($('#order-price-list .rprice').length) {
            Z.Global.method.prepare();
        }

        $.getJSON('/master/room/fetch-value', {
            valid: 1,
            oid  : that._oid,
            rid  : that._rid,
            bdate: Z.Global.method.date('Y/m/d', obtm * 1000),
            edate: Z.Global.method.date('Y/m/d', oetm * 1000)
        }, function(data){
            if (data.success) {
                that._ext = data.exttime;

                var price = data.context.price;
                var brice = data.context.brice;
                var saved = data.invalid;

                if (increment) {

                    var time = '';

                    if (increment > 0) {
                        for (time in tlst) {
                            that._lst.push([time, price[time]/100, brice[time]/100, saved[time], 0, 0]);
                        }
                    } else if (increment < 0) {
                        for (time in tlst) {
                            that._lst.unshift([time, price[time]/100, brice[time]/100, saved[time], 0, 0]);
                        }
                    }
                    
                } else {
                    for (var i = 0; i < that._lst.length; i++) {
                        that._lst[i].push(saved[that._lst[i][0]]);
                    }
                }

                that.draw();
                Z.Global.method.closure();
            } else {
                Z.Global.method.respond({forward: false}, data);
            }
        });
    },

    draw: function(list) {
        list = list || this._lst;

        var wrapper = $('#order-price-list');

        var btime = list[0][0] * 1000;
        var etime = list[list.length - 1][0] * 1000 + 86400000;

        var bable = $('#form-update-order-ffmx input[name=bdate]').val(Z.Global.method.date('Y/m/d', btime)).attr('alt') == '1';
        var eable = $('#form-update-order-ffmx input[name=edate]').val(Z.Global.method.date('Y/m/d', etime)).attr('alt') == '1';
        var iable = wrapper.attr('alt').split(',')[1] == '1';

        $('#o-bdate').text(Z.Global.method.date('Y/m/d', btime));
        $('#o-edate').text(Z.Global.method.date('Y/m/d', etime));

        $('#o-btime').text(Z.Global.method.date('H:i', btime + this._ext[0] * 1000)).parent().show();
        $('#o-etime').text(Z.Global.method.date('H:i', etime + this._ext[1] * 1000)).parent().show();

        var html = '<table class="rprice"><thead><tr><th style="width:90px">日期</th><th style="width:80px">成交房费(元)</th><th style="width:80px">账单房费(元)</th><th>';

        if (bable) {
            html += '提前<input type="text" value="1" class="ftt-input-text single-text" />天入住，'
            + '<a href="javascript:;" onclick="Z.Module.master.order.priceDlist.unshift($(this).prev().val());">增加房费</a>';
        } else {
            html += ' - ';
        }

        html += '</th></tr></thead><tbody>';

        var psum = 0;
        var bsum = 0;
        for (var idx = 0; idx < list.length; idx++) {
            psum += list[idx][1];
            bsum += list[idx][2];
            html += '<tr>';
            html += '<td' + (list[idx][3] ? ' class="saved"' : '') + '>' + Z.Global.method.date('Y/m/d', list[idx][0]*1000) + '</td>';
            html += '<td><div class="value"><input type="text" class="price' + (list[idx][4] ? ' changed' : '') + '" alt="' + idx + '" name="price[' + list[idx][0] + ']" value="' + list[idx][1] + '" ' + (iable ? '' : 'disabled="disabled" ') + '/></div></td>';
            html += '<td><div class="value"><input type="text" class="brice' + (list[idx][5] ? ' changed' : '') + '" alt="' + idx + '" name="brice[' + list[idx][0] + ']" value="' + list[idx][2] + '" ' + (iable ? '' : 'disabled="disabled" ') + '/></div></td>';
            html += '<td>';
            if (list.length > 1) {
                if (idx === 0 && bable) {
                    html += '<a href="javascript:;" onclick="Z.Module.master.order.priceDlist.shift();">删除该天</a>';
                }

                if (idx == list.length - 1 && eable) {
                    html += '<a href="javascript:;" onclick="Z.Module.master.order.priceDlist.pop();">删除该天</a>';
                }
            }

            html += '</td></tr>';
        }

        html += '</tbody>';
        html += '<tfoot><tr><td>' + ' 共 ' + list.length + ' 间夜</td><td class="price">' + psum + '</td><td class="brice">' + bsum + '</td><td>';

        if (eable) {
            html += '推迟<input type="text" value="1" class="ftt-input-text single-text" />天退房，'
            + '<a href="javascript:;" onclick="Z.Module.master.order.priceDlist.push($(this).prev().val());">增加房费</a>';
        } else {
            html += ' - ';
        }

        html += '</td></tr></tfoot></table>';

        wrapper.html(html);
    },

    init: function(oid, initPrice, initBrice) {

        this._oid = oid;
        this._rid = $('#order-price-list').attr('alt').split(',')[0];
        this._lst = [];

        for (var time in initPrice) {
            this._lst.push([time, initPrice[time]/100, initBrice[time]/100]);
        }

        this.load();

        var that = this;

        $('.rprice tbody .value input').live('change', function(){
            that.mod($(this).attr('alt') - 0, this);
        });
    }
};

Z.Module.master.order.index = Z.Module.master.order.index || {};
Z.Module.master.order.index.init = function() {
    Z.Global.method.tierLook('.data tbody tr');
};

Z.Module.master.order.create = Z.Module.master.order.create || {};
Z.Module.master.order.create.getPriceTable = function(args) {

    var oidx  = args.oidx;
    var rid   = args.rid;
    var price = args.price;
    var brice = args.brice;
    var saved = args.saved;
    var btime = args.btime / 1000 || 0;
    var etime = args.etime / 1000 || 0;

    

    var table = $('<table class="rprice"></table>');

    var thead = '<thead><tr>' +
                '<th>星期一</th>' +
                '<th>星期二</th>' +
                '<th>星期三</th>' +
                '<th>星期四</th>' +
                '<th>星期五</th>' +
                '<th>星期六</th>' +
                '<th>星期日</th>' +
                '</tr></thead>';

    table.append(thead);

    var tbody = $('<tbody></tbody>');

    var tbtr = '<tr>';

    for (var w = 0; w < 7; w++) {
        tbtr += '<td><div class="empty"></div></td>';
    }

    tbtr += '</tr>';

    tbody.append(tbtr);
    
    var dayCount = 0;
    var priceSum = 0;
    var briceSum = 0;

    var wd = 0;
    var rn = 0;

    for (var time in price) {

        if (btime && etime && (time < btime || time > etime)) {
            continue;
        }

        priceSum += price[time] / 100;
        briceSum += brice[time] / 100;
        dayCount ++;

        wd = Z.Global.method.date('w', time * 1000) - 0;

        if (wd === 1 && dayCount > 1) {
            tbody.append(tbtr);
        }

        tbody.find('tr:last td:eq(' + (wd === 0 ? '6' : wd - 1) + ')').html(
            '<div class="date' + (saved[time] ? ' saved' : '') + '">' + Z.Global.method.date('m月d日', time * 1000) + '</div>' +
            '<div class="value">' +
            '<input type="text" title="点击修改 [ 账单价 ]" class="brice" name="brice[' + oidx + '][' + time + ']" value="' + brice[time] / 100 + '" />' +
            '<input type="text" title="点击修改 [ 成交价 ]" class="price" name="price[' + oidx + '][' + time + ']" value="' + price[time] / 100 + '" />' +
            '</div>'
        );

    }

    table.append(tbody);

    var tfoot = '<tfoot><tr>' +
                '<td class="action">' +
                '<a href="javascript:;" class="discount">批量改价</a>' +
                '</td>' +
                '<td colspan="6" class="total">' +
                '共 <span>' + dayCount + '</span> 间夜，' +
                '成交房费 <span class="price price-sum">' + priceSum + '</span> 元，' +
                '账单房费 <span class="brice brice-sum">' + briceSum + '</span> 元' +
                '</td>' +
                '</tr></tfoot>';

    table.append(tfoot);

    var thisOrder = $('#order-' + oidx);

    thisOrder.find('.price-table').html(table);

    Z.Module.master.order.create.getTotalFee();

    table.find('.discount').click(function(){

        easyDialog.open({
            container: {
                header: '订单 ' + thisOrder.find('.number').html() + ' - 批量修改价格',
                content: '<div class="rprice-dialog discount-dialog" id="discount-dialog">' + $('.discount-tmp').html() + '</div>'
            }
        });

        var thisDialog = $('#discount-dialog');

        thisDialog.find('input[name="discount"]').change(function(){
            thisDialog.find('input.val').attr('disabled', 'disabled');
            thisDialog.find('input.' + this.value).removeAttr('disabled');
        });

        thisDialog.find('.calculate').click(function() {

            var value = thisDialog.find('input.val:enabled').val();
            var type = $(this).attr('alt');
            var chck = thisDialog.find('input[name="discount"]:checked').val();
            var ognl = 0;
            var rslt = 0;
            var sum  = 0;

            switch(chck) {
                case 'yuan':
                    if (/^(\-)?\d+$/.test(value)) { // 正整数、负整数

                        table.find('input[name^="' + type + '"]').each(function() {
                            ognl = $(this).val() - 0;
                            rslt = ognl - value;
                            rslt = rslt < 0 ? 0 : rslt;
                            $(this).val(rslt);
                            sum += rslt;
                        }).change();

                        table.find('.' + type + '-sum').html(sum);

                    }else{

                        alert('输入有误');
                        return false;

                    }
                    break;
                    
                case 'pecent':
                    if (/^\d+(\.\d+)?$/.test(value)) { // 非负数、小数

                        table.find('input[name^="' + type + '"]').each(function() {
                            ognl = $(this).val() - 0;
                            rslt  = Math.round(ognl * (value/100));
                            $(this).val(rslt);
                            sum += rslt;
                        }).change();

                        table.find('.' + type + '-sum').html(sum);

                    }else{

                        alert('输入有误');
                        return false;

                    }
                    break;
            }

            easyDialog.close();

            Z.Module.master.order.create.getTotalFee();

        });

    });

    
    table.find('.value input').change(function(){

        $(this).addClass('changed');

        var prsm = 0;
        var brsm = 0;

        table.find('input[name^="price"]').each(function() {
            prsm += $(this).val() - 0;
        });
        table.find('input[name^="brice"]').each(function() {
            brsm += $(this).val() - 0;
        });

        table.find('.price-sum').html(prsm);
        table.find('.brice-sum').html(brsm);

        Z.Module.master.order.create.getTotalFee();

    });

    if ($.browser.msie && $.browser.version == 6) {
        // alert('fuck ie6');
        if (thisOrder.find('.main-info').outerHeight() > thisOrder.outerHeight()) {
            var h = thisOrder.find('.main-info').outerHeight();
            thisOrder.css('height', h);
            thisOrder.find('.info').css('height', h - 2);
        }
    }

    return priceSum;

};

Z.Module.master.order.create.getTotalFee = function() {

    var totalPrice = 0;
    var totalBrice = 0;

    $('#order-container .order').each(function(){

        totalPrice += $(this).find('.price-sum').html() - 0;
        totalBrice += $(this).find('.brice-sum').html() - 0;

    });

    $('#total-price').html(totalPrice);
    $('#total-brice').html(totalBrice);

};

Z.Module.master.order.create.changeTime = function(hotel, order, dialog) {

    var alt = order.attr('alt');
    var oidx = alt[0];
    var rid  = alt[1];

    var hour = [];
    var minu = [];
    hour[0] = dialog.find('.o-bhour').val();
    hour[1] = dialog.find('.o-ehour').val();
    minu[0] = dialog.find('.o-bminu').val();
    minu[1] = dialog.find('.o-eminu').val();

    var time = [];
    time[0] = order.find('span.btime').attr('alt') - 0;
    time[1] = order.find('span.etime').attr('alt') - 0;

    var num = /^\d{1,2}$/;

    if (!num.test(hour[0]) ||
        !num.test(hour[1]) ||
        !num.test(minu[0]) ||
        !num.test(minu[1])
        ) {

        alert('请输入数字');
        return false;

    } else {

        hour[0] = parseInt(hour[0], 0);
        hour[1] = parseInt(hour[1], 0);
        minu[0] = parseInt(minu[0], 0);
        minu[1] = parseInt(minu[1], 0);

        if (hour[0] > 23 || hour[0] > 23 || hour[0] < 0 || hour[0] < 0 ||
            hour[1] > 23 || hour[1] > 23 || hour[1] < 0 || hour[1] < 0 ||
            minu[0] > 59 || minu[0] > 59 || minu[0] < 0 || minu[0] < 0 ||
            minu[1] > 59 || minu[1] > 59 || minu[1] < 0 || minu[1] < 0
            ) {

            alert('请输入有效的数字');
            return false;

        } else if (hour[0] * 3600000 + minu[0] * 60000 < time[0] ||
                   hour[1] * 3600000 + minu[1] * 60000 > time[1]
                  ) {

            alert('输入的范围不正确');
            return false;

        } else {

            hour[0] = hour[0] < 10 ? '0' + hour[0] : hour[0];
            hour[1] = hour[1] < 10 ? '0' + hour[1] : hour[1];
            minu[0] = minu[0] < 10 ? '0' + minu[0] : minu[0];
            minu[1] = minu[1] < 10 ? '0' + minu[1] : minu[1];

            order.find('.o-bhour').val(hour[0]);
            order.find('.o-ehour').val(hour[1]);
            order.find('.o-bminu').val(minu[0]);
            order.find('.o-eminu').val(minu[1]);
            order.find('span.btime').html(hour[0] + ':' + minu[0]);
            order.find('span.etime').html(hour[1] + ':' + minu[1]);

            Z.Global.method.closure();

        }

    }

};

Z.Module.master.order.create.changeDate = function(hotel, order, dialog) {

    var alt = dialog.attr('alt').split(',');

    var oidx = alt[0];
    var rid  = alt[1];

    var bdate = dialog.find('.o-bdate').val();
    var edate = dialog.find('.o-edate').val();

    var dateISO = /^\d{4}[\/-]\d{1,2}[\/-]\d{1,2}$/;

    if (!dateISO.test(bdate) || !dateISO.test(edate)) {

        alert('输入的日期格式不正确');
        return false;

    } else {

        var btmp = Date.parse(bdate.split('-').join('/'));
        var etmp = Date.parse(edate.split('-').join('/'));

        if (btmp > etmp) {

            alert('离店时间不能小于入住时间');
            return false;

        } else if(btmp == etmp) {

            alert('离店时间与入住时间不能为同一天');
            return false;

        } else if((etmp - btmp) / 86400000 > hotel.h_order_maxlens) {

            alert('离店时间与入住时间的间隔不能大于' + hotel.h_order_maxlens + '天');
            return false;

        } else if((etmp - btmp) / 86400000 < hotel.h_order_minlens) {

            alert('离店时间与入住时间的间隔不能小于' + hotel.h_order_minlens + '天');
            return false;

        } else if((etmp - (new Date()).getTime()) / 86400000 > hotel.h_order_enddays) {

            alert('不能创建' + hotel.h_order_enddays + '天后的订单');
            return false;

        } else {

            Z.Global.method.prepare();

            var time = [];
            var hour = [];
            var minu = [];

            $.getJSON('/master/room/fetch-value', {
                valid: 1,
                bdate: Z.Global.method.date('Y/m/d', btmp),
                edate: Z.Global.method.date('Y/m/d', etmp - 86400000), // 取间夜价格
                rid  : rid
            }, function(data) {
                if (!data.success) {
                    return Z.Global.method.respond({forward:false}, data);
                }

                Z.Module.master.order.create.getPriceTable({
                    oidx  : oidx,
                    rid   : rid,
                    price : data.context.price,
                    brice : data.context.brice,
                    saved : data.invalid
                });

                order.find('.o-bdate').val(bdate);
                order.find('.o-edate').val(edate);
                order.find('span.bdate').html(bdate).attr('alt', btmp);
                order.find('span.edate').html(edate).attr('alt', etmp);
                
                time[0] = (data.exttime[0] < hotel.h_checkin_time ? hotel.h_checkin_time : data.exttime[0]) * 1000;
                time[1] = (data.exttime[1] > hotel.h_checkout_time ? hotel.h_checkout_time : data.exttime[1]) * 1000;

                hour[0] = Z.Global.method.date('H', btmp + time[0]);
                hour[1] = Z.Global.method.date('H', etmp + time[1]);

                minu[0] = Z.Global.method.date('i', etmp + time[0]);
                minu[1] = Z.Global.method.date('i', etmp + time[1]);

                order.find('.o-bhour').val(hour[0]);
                order.find('.o-ehour').val(hour[1]);
                order.find('.o-bminu').val(minu[0]);
                order.find('.o-eminu').val(minu[1]);
                order.find('span.btime').html(hour[0] + ':' + minu[0]).attr('alt', data.exttime[0] * 1000);
                order.find('span.etime').html(hour[1] + ':' + minu[1]).attr('alt', data.exttime[1] * 1000);

                Z.Global.method.closure();
            });

        }
    }

};

Z.Module.master.order.create.bindMember = function(checked) {

    var option = $('#o-member');
    var detail = $('.hostel-member .detail');

    easyDialog.open({
        container: {
            header  : '关联会员',
            content : '<div class="member-dialog">' + $('.member-tmp').html() + '</div>',
            yesFn   : function(){
                detail.addClass('waitting');
                var uqno = $('.member-dialog input.uqno').val();
                var sync = $('.member-dialog input.sync').attr('checked');
                $.getJSON('/master/mber/fetch-abled-mber-by-uqno?uqno=' + uqno, function(data){
                    if (!data.success) {
                        Z.Global.method.respond({forward: false}, data);
                        detail.removeClass('waitting');
                        if (!checked) {
                            option.removeAttr('checked');
                        }
                        return false;
                    }
                    data = data.context;
                    option.attr('checked', true);
                    detail.removeClass('waitting');
                    detail.html(
                        '<input type="hidden" name="mno" value="' + data['uqno'] + '" />' +
                        '<li><label>编号：</label><span style="width:110px;">' + data['uqno'] + '</span></li>' +
                        '<li><label>类型：</label><span style="width:80px;">' + data['type'] + '</span></li>' +
                        '<li><label>姓名：</label><span style="width:80px;">' + data['name'] + '</span></li>' +
                        '<li><label>电话：</label><span style="width:120px;">' + data['call'] + '</span></li>' +
                        '<li class="link"><a class="button white medium" id="member-change" href="javascript:;" >关联其他会员</a></li>' +
                        '<li class="link"><a class="button white medium" target="_blank" href="/master/mber/update?mid=' + data['uqid'] + '">会员详情</a></li>'
                    );
                    if (sync) {
                        $('#c-name').val(data['name']).change();
                        $('#c-call').val(data['call']).change();
                    }
                });
            },
            noFn    : true,
            yesText : '关联会员',
            noText  : '关闭窗口'
        },
        callback: function(){
            if (!checked) {
                option.removeAttr('checked');
            }
        },
        drag: false
    });

};

Z.Module.master.order.create.init = function(initHotel, initValue, initSaved) {

    var Hotel = initHotel;

    // 初始化各个订单的价格表

    $('#order-container .order').each(function(){

        var alt = $(this).attr('alt').split(',');

        var oidx = alt[0];
        var rid  = alt[1];

        var fee = Z.Module.master.order.create.getPriceTable({
            oidx  : oidx,
            rid   : rid,
            price : initValue.price[rid],
            brice : initValue.brice[rid],
            saved : initSaved[rid],
            btime : $(this).find('.bdate').attr('alt') - 0,
            etime : $(this).find('.edate').attr('alt') - 0
        });

    });

    // 关联会员

    $('#o-member').removeAttr('checked');

    $('#o-member').click(function(){
        if ($(this).is(':checked')) {
            Z.Module.master.order.create.bindMember(false);
        } else {
            if (confirm("是否取消会员关联？")) {
                $(this).removeAttr('checked');
                $('.hostel-member .detail').html('');
            } else {
                $(this).attr('checked', true);
            }
        }
    });

    // 修改关联会员

    $('#member-change').live('click', function(){
        Z.Module.master.order.create.bindMember(true);
    });

    // 修改订单日期、修改订单时间的窗口弹出

    $('#order-container .change-date, #order-container .change-time').live('click', function(){

        var thisOrder = $(this).parents('li.order');

        var alt = thisOrder.attr('alt').split(',');

        var oidx = alt[0];
        var rid  = alt[1];

        easyDialog.open({
            container: {
                header : '订单 ' + thisOrder.find('.number').html() + ' - 时间调整',
                content: '<div style="height:180px;" class="rprice-dialog" alt="' + oidx + ',' + rid + '"></div>'
            },
            drag: false
        });

        var thisDialog = $('.rprice-dialog[alt="' + oidx + ',' + rid + '"]');

        if ($(this).is('.change-date')) {

            thisDialog.append($('.order-date-tmp').html());

            thisDialog.find('.o-bdate').val(thisOrder.find('span.bdate').html());
            thisDialog.find('.o-edate').val(thisOrder.find('span.edate').html());

            $.datepicker.init();

            thisDialog.find('.get-price').click(function(){
                Z.Module.master.order.create.changeDate(Hotel, thisOrder, thisDialog);
            });

            return;
        }

        if ($(this).is('.change-time')) {

            thisDialog.append($('.order-time-tmp').html());

            var ctime = [];
            ctime[0] = parseInt(thisOrder.find('span.bdate').attr('alt'), 10) + parseInt(thisOrder.find('span.btime').attr('alt'), 10);
            ctime[1] = parseInt(thisOrder.find('span.edate').attr('alt'), 10) + parseInt(thisOrder.find('span.etime').attr('alt'), 10);

            var btime = thisOrder.find('span.btime').html().split(':');
            var etime = thisOrder.find('span.etime').html().split(':');
            
            thisDialog.find('.o-bhour').val(btime[0]);
            thisDialog.find('.o-bminu').val(btime[1]);
            thisDialog.find('.o-ehour').val(etime[0]);
            thisDialog.find('.o-eminu').val(etime[1]);

            thisDialog.find('.btime').html(Z.Global.method.date('H:i', ctime[0]));
            thisDialog.find('.etime').html(Z.Global.method.date('H:i', ctime[1]));

            thisDialog.find('.get-time').click(function(){
                Z.Module.master.order.create.changeTime(Hotel, thisOrder, thisDialog);
            });

            return;
        }

    });

    // 取消创建指定的订单

    $('#order-container .order .close').live('click', function(){
        if ($('#order-container .order').size() == 1) {
            alert('至少包含1个订单！');
            return false;
        } else if (confirm("是否删除该订单？")) {
            $(this).parents('li.order').fadeOut(600, function(){
                $(this).remove();
                Z.Module.master.order.create.getTotalFee();
            });
        }
    });

    // 复制预订人信息至入住人信息

    $('#order-container .copy-guest input[type="checkbox"]').live('change', function() {
        if ($(this).is(':checked')) {
            var gstInfo = $(this).parents('.guest-info');
            gstInfo.find('input.g-name').val($('#c-name').val());
            gstInfo.find('input.g-call').val($('#c-call').val());
        }
    });

    $('#c-name ,#c-call').live('change keyup', function() {
        var val  = this.value;
        var type = this.id.substr(2);
        $('.copy-guest input[type="checkbox"]:checked').each(function(){
            $(this).parents('.guest-info').find('input.g-' + type).val(val);
        });
    });

    // 创建账单 or 关联账单

    $('#order-option input[name="bill"]').live('change', function() {
        if (this.value == '1') {
            $('#o-bid, #get-bill').removeAttr('disabled');
            $('#o-lft input, #o-settlem').attr('disabled', 'disabled');
            $('#o-ldate, #o-lhour, #o-lminu').attr('disabled', 'disabled');
        } else {
            if ($('#o-lft input:checkbox').is(':checked')) {
                $('#o-ldate, #o-lhour, #o-lminu').removeAttr('disabled');
            }
            $('#o-lft input, #o-settlem').removeAttr('disabled');
            $('#o-bid, #get-bill').attr('disabled', 'disabled');
        }
    });

    // 账单过期时间设置

    $('#o-lft input:checkbox').live('change', function() {
        if ($('#o-lft input:checkbox').is(':checked')) {
            $('#o-ldate, #o-lhour, #o-lminu').removeAttr('disabled');
        } else {
            $('#o-ldate, #o-lhour, #o-lminu').attr('disabled', 'disabled');
        }
    });

    // 打开指定账单

    $('#get-bill').click(function(){
        window.open('/master/bill/detail?bid=' + $('#o-bid').val());
    });

    // 添加新选项弹出窗口（预订类型、预订渠道、结算方式）

    $('.handle-option').click(function(){
        var type = $(this).attr('alt');
        var name = $(this).attr('title');

        easyDialog.open({
            container: {
                header  : '添加' + name,
                content : '是否已经成功添加新的 <span style="color:red;">' + name + '</span> 选项？',
                yesFn   : function(){
                    $('select#o-' + type).html('<option>' + Z.Global.string.LOADING + '</option>');
                    $.ajax({
                        url       : '/master/' + type + '/fetch-abled-index',
                        type      : 'POST',
                        dataType  : 'json',
                        success   : function(data){
                            var map = data.context;
                            var max = Z.Global.method.getKeys(map).sort(function(a,b){return b - a;})[0];
                            var slt = $('<select></select>');

                            for(var idx in map) {
                                slt.append('<option value="' + idx + '">' + Z.Global.method.escape(map[idx]) + '</option>');
                            }

                            slt.find('option[value="' + max + '"]').attr('selected', 'selected');

                            $('select#o-' + type).html('').append(slt.children());
                        }
                    });
                },
                noFn    : true,
                yesText : '是的，更新该选项',
                noText  : '取消'
            },
            drag: false
        });
    });

    // 表单ajax

    $('#form-create-order').submit(function() {
        Z.Global.method.ajaxForm(this);
        return false;
    });
};

Z.Module.master.order.detail = Z.Module.master.order.detail || {};
Z.Module.master.order.detail.init = function(initOid, initPrice, initBrice) {

    //订单可用操作表单显示

    if ($('.order-tip').size()) {
        $('.order-tip').fadeIn('slow');
    }

    // tab 切换

    $('.tab-list li').click(function(){
        $(this).addClass("now").siblings().removeClass("now");
        var tab = $(this).attr('alt');
        location.hash = tab;
        $('#block-' + tab).removeClass('hide').siblings().addClass('hide');
        if (tab=='ffmx' && !$('#order-price-list .rprice').size()) {
            Z.Module.master.order.priceDlist.init(initOid, initPrice, initBrice);
        }
    }).hover(function(){
        $(this).addClass("hover");
    },function(){
        $(this).removeClass("hover");
    });

    if (location.hash) {
        $('.tab-list li[alt=' + location.hash.substr(1) + ']').click();
    }

    // 修改订单日期切换

    $('#change-date-link').click(function(){
        $('.tab-list li[alt=ffmx]').click();
    });

    // 初始化价格列表

    // Z.Module.master.order.priceDlist.init(initOid, initPrice);

    // 弹出修改销售人员，预订渠道，预订类型窗口

    $('.handle-option').click(function(){
        var alt = $(this).attr('alt');

        if($('.' + alt + '-select').html() === ''){
            Z.Global.method.prepare();
            $.getJSON('/master/' + alt + '/fetch-abled-index', function(data){
                if (!data.success) {
                    Z.Global.method.respond({forward: false}, data);
                    return;
                }
                data = data.context;

                var html = '';
                for(var i in data){
                    html += '<option value="' + i + '">' + data[i]+ '</option>';
                }
                $('.' + alt + '-select').html(html);
                easyDialog.open({
                    container : {
                        header : $('.dialog-' + alt).attr('title'),
                        content : '<div class="order-dialog-wrapper">' + $('.dialog-' + alt).html() + '</div>'
                    }
                });
            });
        }else{
            easyDialog.open({
                container : {
                    header : $('.dialog-' + alt).attr('title'),
                    content : '<div class="order-dialog-wrapper">' + $('.dialog-' + alt).html() + '</div>'
                }
            });
        }
    });

    // 修改关联会员
    
    $('#change-member').click(function(){
        easyDialog.open({
            container: {
                header : $('.dialog-member').attr('title'),
                content : '<div class="order-dialog-wrapper">' + $('.dialog-member').html() + '</div>'
            }
        });
    });

    $('.form-handle-member').live('submit', function(){

        if ($(this).find('input[name="mno"]').val() !== '') {
            Z.Global.method.ajaxForm(this);
        } else if ($(this).find('input[name="gbk"]').is(':checked') || $(this).find('input[name="glv"]').is(':checked')) {
            if (confirm('取消关联会员不会更新订单的客人信息！')) {
                Z.Global.method.ajaxForm(this);
            }
        } else {
            Z.Global.method.ajaxForm(this);
        }

        return false;
    });

    // 订单可用操作

    $('.handles a[alt]:not(".link")').click(function(){
        var code = $(this).attr('alt');
        easyDialog.open({
            container : {
                header : $('.dialog-' + code).attr('title'),
                content : '<div class="order-dialog-wrapper">' + $('.dialog-' + code).html() + '</div>'
            }
        });
        if($.datepicker)$.datepicker.init();
    });

    // ajax 切换换房属性

    $('#change-room-toggle').click(function(){
        Z.Global.method.ajaxLink(this);
        return false;
    });

    $('.form-handle-option, '        // ajax 销售人员，预订渠道，预订类型
      + '.form-handle-order, '       // ajax 订单操作
      + '#form-update-order-jbxx, '  // ajax 基本信息
      + '#form-update-order-ffmx, '  // ajax 房费明细
      + '#form-update-order-krxx'    // ajax 客人信息
     ).live('submit', function(){
        Z.Global.method.ajaxForm(this);
        return false;
    });
};
