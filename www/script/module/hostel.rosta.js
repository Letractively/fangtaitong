Z.Module.hostel = Z.Module.hostel || {};
Z.Module.hostel.rosta = Z.Module.hostel.rosta || {};

Z.Module.hostel.rosta.index = Z.Module.hostel.rosta.index || {};
Z.Module.hostel.rosta.index.init = function(initHotel, initRosta)
{
    var ROSTA_TS = 0; // 特殊
    var ROSTA_YD = 3; // 预订
    var ROSTA_BL = 4; // 保留

    var ORDER_LK = 4; // 旅客

    var ORDER_LIMIT = 3; //最大订单数

    var Hotel = initHotel;

    var Gantt = {
    
        top     : $('.chart-top'),
        navi    : $('.chart-header .navi'),
        filter  : $('.chart-header .filter'),
        axis    : $('.gantt-wrapper .time-row'),
        chart   : $('.gantt-wrapper .room-rows'),
        hold    : $('.gantt-wrapper .loading-cover'),
        notice  : $('.gantt-wrapper .notice'),
        range   : $('.chart-footer .range'),
        calendar: $('#rosta-calendar'),
        cart    : $('#cart'),
        
        status: {
            '0': 'empty',
            '3': 'alternative',
            '4': 'unavailable'
        },
        
        statusCN: {
            '0': '可用房源',
            '3': '房源紧张',
            '4': '店家保留'
        },

        week: {
            '0':'周日',
            '1':'周一',
            '2':'周二',
            '3':'周三',
            '4':'周四',
            '5':'周五',
            '6':'周六'
        },

        flt: {
            'rtp':'all',
            'rlo':'all',
            'rze':'all',
            'rvw':'all'
        },  // 筛选值
    
        fltCount: 4, // 筛选条件数
        
        lines: 20, //一屏的房间行数
        
        width: 576, //时间轴长度

        length: 8,

        getLength: function(){
            return this.navi.find('#gantt-view').val();
        },

        getRostaRoomCount: function(){
            var room, count = 0;
            for(room in this.rosta.room){
                count++;
            }
            return count;
        },

        wait: function(){
            this.hold.show();
            this.notice.hide().html('');
        },
        
        okay: function(){
            this.hold.hide();
            this.notice.hide().html('');
        },

        showNotice : function(){
            var queue = [];

            var timeoutId = 0;

            return function(args){
                if (!queue.length) {
                    queue.push(args);

                    clearTimeout(timeoutId);

                    this.notice.hide().html(args.msg).css(
                        'left',
                        (this.chart.outerWidth() - this.notice.outerWidth()) / 2 + 'px'
                    ).fadeIn('fast');

                    timeoutId = setTimeout(function(){
                        Gantt.notice.fadeOut('fast'/*, function(){
                            $(this).html('');
                        }*/);
                    }, args.lft * 1000);

                    queue.shift();
                }
            };
        },
        
        setFilter: function(room){

            var fixIE = function(){
                setTimeout(function(){
                    for(var f in Gantt.flt){Gantt.filter.find('#'+f).val(Gantt.flt[f]);}
                },1);
            }; // select element bug fix

            var fltArr = {};
            
            for(var f in this.flt){
                fltArr[f] = [];
                for(var ri in room){
                    if(room[ri].ext[f] && room[ri].ext[f].constructor === Array ){
                        for(var vi in room[ri].ext[f]){
                            fltArr[f].push(room[ri].ext[f][vi]);
                        }
                    }else{
                        if(room[ri].ext[f])fltArr[f].push(room[ri].ext[f]);
                    }
                }
                
                this.filter.find('#'+f).html('<option value="all">全部' + this.filter.find('#'+f).attr('alt') + '</option>');
                
                for(var k in Z.Global.method.uniqArray(fltArr[f])){
                    this.filter.find('#'+f).append('<option value="' + fltArr[f][k] + '">' + fltArr[f][k] + '</option>');
                }
                
                if($.browser.msie && $.browser.version == '6.0'){
                    fixIE();
                }else{
                    this.filter.find('#'+f).val(this.flt[f]);
                }
                    
            }
            
        },
        
        filte: function(rs,id,value){
            var rf = rs;
            for(var ri in rf.room){
                rf.room[ri].filter = 0;
                for(var f in this.flt){
                    if(((rf.room[ri].ext[f])?rf.room[ri].ext[f].toString().match(this.flt[f]):false) || this.flt[f] == 'all'){
                        rf.room[ri].filter += 1;
                    }
                }
            }
            return rf;
        },

        drawAxis: function(){

            var html = '';
            var stp = '';

            var today = Date.parse(Z.Global.method.date('Y/m/d', this.rosta.time * 1000));

            for(var i = 0; i < Gantt.lgth; i++){
                stp = Gantt.btime + 86400000 * i;
                html += '<div class="' + (stp == today ? 'today' : '')
                    + '" alt="' + stp
                    + '" title="' + Z.Global.method.date('Y/m/d', stp)
                    + '" style="width:' + (Gantt.dwth - 1) + 'px;">' // 1px border
                    + '<div>' + Z.Global.method.date('m/d', stp) + '</div>'
                    + '<div>' + Gantt.week[Z.Global.method.date('w', stp)] + '</div>'
                    + '</div>';
            }

            this.axis.find('.timer-shaft').html(html);

            this.top.find('.current').html(Z.Global.method.date('Y/m/d H:i:s',  this.rosta.time * 1000));
            this.range.find('.data-bdate').html(Z.Global.method.date('Y/m/d', this.btime));
            this.range.find('.data-edate').html(Z.Global.method.date('Y/m/d', this.etime));
            this.calendar.attr('alt', this.btime + ',' + this.lgth);
        },

        drawChart: function(){

            var getWidth = function(l,r){
                var b = Gantt.btime/1000;
                var e = Gantt.etime/1000;
                return Math.ceil(
                    ((r>=e?e:r)-(l<=b?b:l))/3600*Gantt.hwth
                );
            };
            
            var getLeft = function(l){
                var b = Gantt.btime/1000;
                return Math.ceil(
                    ((l<=b?b:l)-b)/3600*Gantt.hwth
                );
            };

            var decode = function(data,lgth){
                var rslt = [];
                var tmp  = [];

                for(var key in data){
                    var rng = key.split(',');
                    if(rng[1]){
                        for(var i = rng[0]-0; i <= rng[1]-0; i++){
                            tmp[i] = data[key];
                        }
                    }else{
                        tmp[key] = data[key];
                    }
                }

                for(var j = 0; j <= lgth; j++){
                    rslt[j] = tmp[j] ? tmp[j] : 0;
                }

                return rslt;
            };

            var rosta = Gantt.rosta;
            
            var count = 0;
            var html = '';
            var alt = '';

            for(var ri in rosta.data){
                if(rosta.room[ri].filter == this.fltCount || rosta.room[ri].filter === null){
                    html += '<div class="room-row" alt="' + rosta.room[ri].rid  + '"><div class="room"><span class="name" title="'
                        + rosta.room[ri].rnm + ','
                        + (rosta.room[ri].ext.rtp !== '' ? rosta.room[ri].ext.rtp : '无房型信息') + ','
                        + (rosta.room[ri].ext.rlo !== '' ? rosta.room[ri].ext.rlo : '无户型信息') + ','
                        + (rosta.room[ri].ext.rze !== '' ? rosta.room[ri].ext.rze : '无区域信息') + ',['
                        + (rosta.room[ri].ext.rvw !== '' ? rosta.room[ri].ext.rvw : '无景观信息') + ']'
                        + '">' + (rosta.room[ri].rnm.length > 10 ? (rosta.room[ri].rnm.slice(0,9) + '...') : rosta.room[ri].rnm)
                        + '</span><span class="profile">' + rosta.room[ri].ext.rtp + '</span></div><div class="room-status">';

                    var data = decode(rosta.data[ri], Gantt.lgth);
                    var rent = decode(rosta.rent[ri], Gantt.lgth);

                    var ocls = this.cart.find('table.room[alt=' + ri + ']').size() ? 'order' : '';
                    var obtm = ocls ? Date.parse(this.cart.find('table.room[alt=' + ri + '] tbody span.bdate').text().split('-').join('/')) : '';
                    var oetm = ocls ? Date.parse(this.cart.find('table.room[alt=' + ri + '] tbody span.edate').text().split('-').join('/')) : '';

                    for(var i in data){
                        var time = this.btime + 86400000 * i;

                        html += '<div class="block"'
                              + 'style="width:' + (Gantt.dwth - 1) + 'px;left:' + (i * Gantt.dwth) + 'px;"'
                              + 'alt="' + ri + ',' + rent[i]/100 + ',' + time + '">'
                              + '<div class="inner '
                              + (
                                data[i] == ROSTA_BL ?
                                    Gantt.status[data[i]] + (ocls === '' || time < obtm || time >= oetm?'':' order bad" title="该日期已被店家保留') + '">'
                                    :
                                    Gantt.status[data[i]] + (ocls === '' || time < obtm || time >= oetm?'':' order') + '">' + rent[i]/100
                                ) // 判断当前是否已被选中
                              + '</div></div>';
                    }

                    html += '</div></div>';
                    count++;
                }
            }

            this.chart.html(html).find('.room-row:even').addClass('even');

            this.chartEventBind();
        },

        chartEventBind: function(){
            this.chart.find('.block').hover(function(){
                $(this).addClass('over').parents('.room-row').find('.room').addClass('over');
                $('.timer-shaft>div:eq(' + $(this).index() + ')').addClass('over');
            },function(){
                $('.over').removeClass('over');
            }).click(function(e){
                if($(this).children().hasClass(Gantt.status[ROSTA_BL])){
                    e.preventDefault();
                }else if (!(ORDER_LK & Hotel.h_order_enabled)) {
                    Gantt.cart.fadeOut('fast', function(){Gantt.cart.fadeIn('fast');});
                }else{
                    var alt = $(this).attr('alt').split(',');
                    Gantt.addToCart({rid:alt[0],price:alt[1],date:alt[2]});
                }
                e.preventDefault();
            });

            this.chart.find('.room-row .room').hover(function() {
                $(this).addClass('over');
            }, function() {
                $(this).removeClass('over');
            });
        },

        calendarBind: function(){
            $.calendar.init({
                change_date_callback: function(bdate){
                    Gantt.calendar.hide();
                    Gantt.load({
                        date:bdate,
                        lgth:Gantt.getLength()
                    });
                }
            });
        },

        addToCart: function(args){
            var rid   = args.rid;
            var date  = args.date - 0;
            var price = args.price - 0;

            if(date > Date.parse(Z.Global.method.date('Y-m-d', new Date())) + Hotel.h_order_enddays *86400000){
                this.showNotice({
                    msg:'>_< 旅店不允许创建 <span class="focal">' + Hotel.h_order_enddays + '</span> 天以后的订单',
                    lft:2
                });
                return false;
            }

            var lgth = 0;

            if(!this.cart.find('table.room[alt=' + rid + ']').size()){
                if(this.cart.find('table.room').size() >= ORDER_LIMIT){
                    this.showNotice({
                        msg:'>_< 选择的房间数不能超过 <span class="focal">' + ORDER_LIMIT + '</span>',
                        lft:2
                    });
                    return false;
                }else{
                    lgth = 1;
                    var html = '<table class="room" alt="' + rid + '">'
                             + '<thead><tr>'
                             + '<th>'
                             + '<input type="hidden" class="form-date" name="order[' + rid + '][date]" value="'
                             + Z.Global.method.date('Y-m-d', date) + '" />'
                             + '<input type="hidden" class="form-lgth" name="order[' + rid + '][lgth]" value="'
                             + lgth + '" />'
                             + (
                                this.rosta.room[rid].rnm.length > 7 ?
                                    this.rosta.room[rid].rnm.slice(0,6) + '...'
                                    :
                                    this.rosta.room[rid].rnm
                               )
                             + '</th>'
                             + '<th class="action"><a class="delete" href="javascript:;">删除</a></th>'
                             + '</tr></thead>'
                             + '<tbody>'
                             + '<tr><td class="bdate" colspan="2">入住日期：<span class="bdate">'
                             + Z.Global.method.date('Y-m-d', date)
                             + '</span></td></tr>'
                             + '<tr><td class="edate" colspan="2">离店日期：<span class="edate">'
                             + Z.Global.method.date('Y-m-d', date + 86400000)
                             + '</span></td></tr>'
                             + '</tbody>'
                             + '<tfoot><tr>'
                             + '<td><span title="每个房间最少要达到 ' + Hotel.h_order_minlens + ' 间夜，'
                             + '每个房间最多不超过 ' + Hotel.h_order_maxlens + ' 间夜" class="ront'
                             + (lgth < Hotel.h_order_minlens ? ' below' : '') + '">'
                             + lgth + '</span> 间夜</td>'
                             + '<td class="action"><span class="sum">' + price + '</span> 元</td>'
                             + '</tr></tfoot></table>';
                    this.cart.find('.content').prepend(html).find('.none').hide();
                    this.cart.find('table.footer').show();
                }
            }else{
                var room = this.cart.find('table.room[alt=' + rid + ']');

                lgth = room.find('input.form-lgth').val() - 0;

                if(lgth < Hotel.h_order_maxlens){ // order max length limit
                    var btime  = Date.parse(room.find('input.form-date').val().split('-').join('/'));
                    var select = '';

                    if(date == btime - 86400000){
                        select = 'prev';
                    } // selected the prev one

                    if(date == btime + lgth * 86400000){
                        select = 'next';
                    } // selected the next one

                    if(select){
                        lgth = lgth + 1;
                        room.find('span.ront').html(lgth);
                        room.find('input.form-lgth').val(lgth);
                        if(lgth >= Hotel.h_order_minlens)room.find('span.ront').removeClass('below');
                        switch(select){
                            case 'prev':
                                var bdate = Z.Global.method.date('Y-m-d', date);
                                room.find('input.form-date').val(bdate);
                                room.find('span.bdate').html(bdate);
                                room.find('span.edate').html(Z.Global.method.date('Y-m-d', date + lgth * 86400000));
                                break;
                            case 'next':
                                room.find('span.edate').html(Z.Global.method.date('Y-m-d', btime + lgth * 86400000));
                                break;
                        }
                        var sum  = room.find('span.sum').html() - 0;
                        room.find('span.sum').html(sum + price);
                    }else{
                        if(date >= btime && date < btime + lgth * 86400000){ // selected the selected one
                            this.showNotice({msg:'-_- 该房间日期已经添加', lft:2});
                            return false;
                        }else{ // selected other
                            this.showNotice({msg:'>_< 只能请选择连续的日期。', lft:2});
                            return false;
                        }
                    }
                }else{
                    this.showNotice({
                        msg:'>_< 每个房间最多不超过 <span class="focal">' + Hotel.h_order_maxlens + '</span> 间夜',
                        lft:2
                    });
                    return false;
                }
            }

            var total = 0; // order
            this.cart.find('table.room span.sum').each(function(){
                total += $(this).html() - 0;
            });
            this.cart.find('span#price-total').html(total);

            // this.drawChart();
            this.chart
                .find('.room-row[alt="'+ rid + '"] .block[alt="'+ rid + ',' + price + ',' + date + '"] .inner')
                .addClass('order');
            
            this.okay();

            if(lgth < Hotel.h_order_minlens){
                this.showNotice({
                    msg : '-_- 每个房间最少要达到 <span class="focal">' + Hotel.h_order_minlens + '</span> 间夜，请继续添加',
                    lft : 1
                });
            }
        },

        removeFromCart: function(rid){
            this.cart.find('table.room[alt=' + rid + ']').remove();
            this.cart.find('').remove();

            var total = 0; // order
            this.cart.find('table.room .sum').each(function(){
                total += $(this).html() - 0;
            });
            this.cart.find('#price-total').html(total);

            if(!this.cart.find('table.room').size()){
                this.cart.find('.none').show();
                this.cart.find('table.footer').hide();
            }

            // this.drawChart();
            this.chart.find('.room-row[alt="'+ rid + '"] .block[alt=^"'+ rid + ',"] .inner').removeClass('bad order');

            this.showNotice({msg:'^_^ 成功删除 ' + this.rosta.room[rid].rnm, lft:1});
        },

        load: function(){
        
            var queue = [];

            return function(args){

                args.hid = Hotel.h_id;

                var d = Date.parse(args.date);
                var t = Date.parse(Z.Global.method.date('Y/m/d', new Date()));

                if(d < t){
                    Gantt.showNotice({msg:'>_< 不能查看今天以前的房态', lft:3});
                    return false;
                }

                if(d + 86400000 * args.lgth < t){
                    args.lgth = Math.abs(args.lgth);
                    args.date = Z.Global.method.date('Y/m/d', new Date());
                }

                if (!queue.length) {
                    queue.push(args);
                    Gantt.wait();

                    $.getJSON('/hostel/rosta', queue[0], function(data){
                        if (!data.success)
                        {
                            Gantt.okay();
                            queue.shift();
                            
                            Z.Global.method.respond({forward: false}, data);
                            return false;
                        }

                        data = data.context;
                        
                        if(data.time - Gantt.rosta.time < 2){
                            setTimeout(function(){
                                Gantt.fill(data);
                                queue.shift();
                            },1000);
                            return;
                        }

                        Gantt.fill(data);

                        queue.shift();
                    });
                }
            };
        },

        fill: function(rs){

            if(rs.success === 0){
                alert(rs.message);
                //window.location = '../';
            }

            this.setFilter(rs.room);
            Gantt.rosta = this.filte(rs);

            Gantt.lgth = this.getLength();
            Gantt.dwth = this.width / this.lgth;
            Gantt.hwth = this.dwth / 24;

            Gantt.btime = this.rosta.line[0] * 1000;
            Gantt.etime = this.btime + this.lgth * 86400000 - 1;

            this.drawAxis();

            this.drawChart();
            
            this.calendarBind();

            this.okay();
        },

        init: function(){

            this.load = this.load();
            this.showNotice = this.showNotice();

            this.navi.find('#gantt-view').val('8'); //firefox to refresh's bug or feature, '8' is default
            
            this.fill(initRosta);
            
            this.navi.find('a:not(.pick)').click(function(){

                var date = '';
                var lgth = Gantt.getLength();

                switch($(this).attr('class')){
                    case 'today':
                        date = Z.Global.method.date('Y/m/d', new Date());
                        break;
                    case 'pre':
                        if(Gantt.range.find('.data-bdate').html() == Z.Global.method.date('Y/m/d', new Date())){
                            Gantt.showNotice({msg:'>_< 不能查看今天以前的房态', lft:3});
                            return false;
                        }
                        lgth = -lgth;
                        date = Z.Global.method.date('Y/m/d', Date.parse(Gantt.range.find('.data-bdate').html()) - 86400000);
                        break;
                    case 'next':
                        date = Z.Global.method.date('Y/m/d', Date.parse(Gantt.range.find('.data-edate').html()) + 86400000);
                        break;
                }
                
                Gantt.load({date:date, lgth:lgth});

            });

            this.navi.find('a.pick').click(function(){
                Gantt.calendar.show();

                var bf = function(){
                    Gantt.calendar.hide();
                };

                $('body').bind('mousedown', bf);

                Gantt.calendar.hover(function() {
                    $('body').unbind('mousedown', bf);
                }, function() {
                    $('body').bind('mousedown', bf);
                });
            });

            this.top.find('.button-refresh').click(function(){
                Gantt.load({
                    date: Z.Global.method.date('Y/m/d', Gantt.btime),
                    lgth: Gantt.getLength()
                });
            });
            
            this.navi.find('#gantt-view').live('change',function(){
                Gantt.top.find('.button-refresh').click();
            });

            this.filter.find('.gantt-filter').live('change',function(){
                Gantt.flt[$(this).attr('id')] = $(this).val();
                Gantt.fill(Gantt.rosta);
            });

            this.cart.find('a.delete').live('click', function(){
                Gantt.removeFromCart($(this).parents('table').attr('alt'));
            });
        }
    };
    
    Gantt.init();

    $('#form-cart').submit(function(){
        if(!Gantt.cart.find('table.room').size()){
            alert('您还没选择任何房间！');
            return false;
        }
        if(Gantt.cart.find('.below').size()){
            alert('您选择的房间房晚不够！');
            return false;
        }
    });
};
