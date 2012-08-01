Z.Module.master = Z.Module.master || {};
Z.Module.master.rosta = Z.Module.master.rosta || {};

Z.Module.master.rosta.index = Z.Module.master.rosta.index || {};
Z.Module.master.rosta.index.init = function(initHotel, initRosta, initRids)
{
    var OATTR_HF = 1;

    var ROSTA_YD = 1;  // 预订
    var ROSTA_BL = 2;  // 保留
    var ROSTA_ZZ = 4;  // 在住
    var ROSTA_JS = 16; // 结束

    var ORDER_LIMIT = 9; //最大订单数

    var FADE_SPEED = 280;

    var Hotel = initHotel;

    var Gantt = {
        title   : $('#rosta .title'),
        navi    : $('#rosta .option .navi'),
        filter  : $('#rosta .option .filter'),
        axis    : $('#rosta .gantt-wrapper .g-axis'),
        chart   : $('#rosta .gantt-wrapper .g-chart'),
        cover   : $('#rosta .gantt-wrapper .g-cover'),
        notice  : $('#rosta .gantt-wrapper .g-notice'),
        range   : $('#rosta .footer .range'),
        calendar: $('#rosta-calendar'),
        cart    : $('#cart'),
        
        status: {
            '1': 'booked',
            '2': 'kept',
            '4': 'living',
            '16': 'completed'
        },
        
        statusCN: {
            '1': '预订',
            '2': '保留',
            '4': '在住',
            '16': '结束'
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

        getLength: function(){
            return this.navi.find('#gantt-view').val();
        },

        getRids: function(){
            return this.axis.find('.room').attr('alt') || '';
        },

        getRostaRoomCount: function(){
            var room, count = 0;
            for(room in this.rosta.room){
                count++;
            }
            return count;
        },

        wait: function(){
            this.cover.show();
            // this.cover.fadeIn('fast');
        },
        
        okay: function(){
            this.cover.hide();
            // this.cover.fadeOut('fast');
            this.notice.hide();
        },

        showNotice : function(){
            var queue = [];

            var timeoutId = 0;

            return function(args){
                if (!queue.length) {
                    queue.push(args);

                    clearTimeout(timeoutId);

                    this.notice.hide().html(args.msg).css(
                        'left', (this.chart.outerWidth() - this.notice.outerWidth()) / 2 + 'px'
                    ).fadeIn(FADE_SPEED);

                    timeoutId = setTimeout(function(){
                        Gantt.notice.fadeOut(FADE_SPEED);
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
                for(var rid in room){
                    if(room[rid].ext[f] && room[rid].ext[f].constructor === Array ){
                        for(var vi in room[rid].ext[f]){
                            fltArr[f].push(room[rid].ext[f][vi]);
                        }
                    }else if(room[rid].ext[f]){
                        fltArr[f].push(room[rid].ext[f]);
                    }
                }
                
                this.filter.find('#'+f).html('<option value="all">全部' + this.filter.find('#'+f).attr('alt') + '</option>');
                
                for(var k in Z.Global.method.uniqArray(fltArr[f])){
                    this.filter.find('#'+f).append('<option value="' + fltArr[f][k] + '">' + fltArr[f][k] + '</option>');
                }
                
                if($.browser.msie && $.browser.version == '6.0'){
                    fixIE();
                }else{
                    this.filter.find('#' + f).val(this.flt[f]);
                }
                    
            }
            
        },
        
        filte: function(rosta){

            var rs = rosta;

            for(var rid in rs.room){
                rs.room[rid].flt = 0;
                for(var f in this.flt){
                    if(((rs.room[rid].ext[f])?rs.room[rid].ext[f].toString().match(this.flt[f]):false) || this.flt[f] == 'all'){
                        rs.room[rid].flt += 1; // 在rosta.room中增加符合筛选条件的个数
                    }
                }
            }

            return rs;
        },

        drawAxis: function(rdata){

            var trs = '';
            var stp = '';

            var today = Date.parse(Z.Global.method.date('Y/m/d', this.rosta.time * 1000));

            for(var i = 0; i < this.lgth; i++){
                stp = this.btime + 86400000 * i;
                trs += '<div' +
                       ' class="' + (stp == today ? 'today' : '') + '"' +
                       ' alt="' + stp + '"' +
                       ' title="' + Z.Global.method.date('Y/m/d', stp) + '"' +
                       ' style="width:' + (this.dayWidth - 1) + 'px;"' + // 1px border
                       '>' +
                       '<div>' + Z.Global.method.date('m/d', stp) + '</div>' +
                       '<div>' + this.week[Z.Global.method.date('w', stp)] + '</div>' +
                       '</div>';
            }

            this.axis.find('.time-line').html(trs);

            if (!rdata){

                this.axis.find('.room').html('<div class="name">房间名称</div>').removeAttr('alt');

            }else{

                this.axis
                    .find('.room')
                    .attr('alt', rdata.rid)
                    .html('<div class="name" title="' +
                            (rdata.ext.rtp ? rdata.ext.rtp : '无房型信息') + ',' +
                            (rdata.ext.rlo ? rdata.ext.rlo : '无户型信息') + ',' +
                            (rdata.ext.rze ? rdata.ext.rze : '无区域信息') + ',' +
                            '[' + (rdata.ext.rvw ? rdata.ext.rvw : '无景观信息') + ']">' +
                            '<a target="_blank" href="/master/room/detail?rid=' + rdata.rid + '">' +
                            (rdata.rnm.length>7 ? rdata.rnm.slice(0,6) + '...' : rdata.rnm) +
                            '</a></div>');

                if (rdata.obn > 0) {
                    this.axis
                        .find('.room')
                        .append('<div class="obn" title="最近72小时内创建的预订订单数：' + rdata.obn + '">*' + rdata.obn + '</div>');
                }

                this.filter.find('.gantt-filter').show().hide();  // 'show()' method is ie6's hack
                this.filter.find('.button-back').show();

            }

            this.title.find('.current').html(Z.Global.method.date('Y/m/d H:i:s',  this.rosta.time * 1000));
            this.range.find('.bdate').html(Z.Global.method.date('Y/m/d', this.btime));
            this.range.find('.edate').html(Z.Global.method.date('Y/m/d', this.etime));
            this.calendar.attr('alt', this.btime + ',' + this.lgth);
        },

        calendarBind: function() {
            $.calendar.init({
                change_date_callback: function(bdate){
                    Gantt.calendar.hide();
                    Gantt.load({
                        date:bdate,
                        lgth:Gantt.getLength(),
                        rids:Gantt.getRids()
                    });
                }
            });
        },

        getLineWidth: function(lvt,rvt,brd){

            var bt = this.btime;
            var et = this.etime;

            return Math.ceil(
                ((rvt>=et?et:rvt)-(lvt<=bt?bt:lvt))/3600000*this.hourWidth-(brd?2:0)
            );

        },
        
        getLineLeft: function(lvt){

            var bt = this.btime;

            return Math.ceil(
                ((lvt<=bt?bt:lvt)-bt)/3600000*this.hourWidth
            );

        },

        getLimitedLine: function(rid){

            var roi = this.rosta.room[rid];
            var limitedLine = $('<div class="limited"></div>');

            limitedLine
                .attr({
                    'alt'  : roi.lvt * 1000 + ',' + roi.rvt * 1000,
                    'title': '房间编号：' + rid + '（已停用）'
                })
                .css({
                    'width': this.getLineWidth(roi.lvt * 1000,roi.rvt * 1000) + 'px',
                    'left' : this.getLineLeft(roi.lvt * 1000) + 'px'
                });

            return limitedLine;

        },

        getOrderLine: function(rid, oid){

            var order = this.rosta.data[rid][oid];
            var orderLine = $('<div class="order-line"></div>');

            orderLine
                .addClass(this.status[order.sta])
                .attr({
                    'alt'  : oid + ',' + order.bid,
                    'title': '入住人：' + order.gst.lvg[0].gnm
                })
                .css({
                    'width': this.getLineWidth(order.lvt * 1000, order.rvt * 1000, true) + 'px',
                    'left' : this.getLineLeft(order.lvt * 1000) + 'px'
                })
                .append('<span class="gnm">' + order.gst.lvg[0].gnm + '</span>');

            if (!(order.ats & OATTR_HF)) {

                orderLine.find('.gnm').addClass('locked');

            }

            return orderLine;

        },

        bindLineEvent: function(clss){
            this.chart.find(clss).mousedown(function(e){
                Gantt.openDialog($(this),e.clientX,e.clientY);
                return false;
            });
        },

        unbindLineEvent: function(clss){
            this.chart.find(clss).unbind('mousedown');
        },

        drawChart: function(rdata) {

            var rosta = this.rosta;

            var tmpChart = $('<div></div>');

            var bkg = '';
            var stp = 0;

            for(var i = 0; i < this.lgth; i++) {
                stp = this.btime + 86400000 * i;
                bkg += '<div class="empty" style="width:' + (this.dayWidth - 1) + 'px;left:' + (i * this.dayWidth) + 'px;" alt="' + stp + '">' +
                       '<div class="l"></div><div class="r"></div>' +
                       '</div>';
                // 1px border
            }

            var tmpRow = '<div class="g-row"><div class="item"></div><div class="wrapper"></div></div>';

            var row = '';
            var cnt = 0;

            var rid = '';
            var oid = '';

            var roi = {};
            var ext = {};

            if (!rdata) {

                for (rid in rosta.room) {

                    roi = rosta.room[rid];

                    if (roi.flt == this.fltCount) { // 匹配是否符合所有筛选条件

                        row = $(tmpRow);

                        row.attr('alt', rid);

                        row.find('.item').append(
                            '<div class="name">' +
                            (roi.rnm.length > 7 ? (roi.rnm.slice(0,6) + '...') : roi.rnm) +
                            '</div>'
                        );

                        ext = roi.ext;

                        row.find('.item').attr(
                            'title',
                            roi.rnm + ': ' +
                            (ext.rtp ? ext.rtp : '无房型信息') + ',' +
                            (ext.rlo ? ext.rlo : '无户型信息') + ',' +
                            (ext.rze ? ext.rze : '无区域信息') + ',' +
                            '[' + (ext.rvw ? ext.rvw : '无景观信息') + ']'
                        );

                        if (roi.obn > 0) {
                            row.find('.item').append(
                                '<div class="obn" title="最近72小时内创建的预订订单数：' + roi.obn + '">*' + roi.obn + '</div>'
                            );
                        }

                        row.find('.wrapper').append('<div class="background">' + bkg + '</div>');

                        for(oid in rosta.data[rid]) {
                            row.find('.wrapper').append(this.getOrderLine(rid, oid));
                        }

                        if(roi.lvt){
                            row.find('.wrapper').append(this.getLimitedLine(rid));
                        }

                        tmpChart.append(row);
                        cnt++;

                    }
                }

            }else{

                rid = rdata.rid;

                for(oid in rosta.data[rid]){

                    row = $(tmpRow);

                    row.attr('alt', rid);

                    row.find('.item').append(
                        '<div class="name"><a href="/master/order/detail?oid=' + oid + '" target="_blank" title="点击查看订单详情">' + oid + '</a></div>'
                    );

                    row.find('.wrapper').append('<div class="background">' + bkg + '</div>');

                    row.find('.wrapper').append(this.getOrderLine(rid, oid));

                    if(rosta.room[rid].lvt){
                        row.find('.wrapper').append(this.getLimitedLine(rid));
                    }
                    
                    tmpChart.append(row);
                    cnt++;
                }

            }

            do{
                row = $(tmpRow);
                
                row.find('.item')
                    .addClass('cr')
                    .append(
                        '<div class="name">' +
                        (rdata ? '' : '<a target="_blank" href="/master/room/create/">创建房间</a>') +
                        '</div>'
                    );

                tmpChart.append(row);
                cnt++;
            }while(cnt < this.lines);

            this.chart.html(tmpChart.html()).find('.g-row:even').addClass('even');

            this.bindChartEvent(rdata);

            if (!rdata) {
                this.cart.find('.order').each(function(){
                    var alt  = $(this).attr('alt').split(',');
                    var rid = alt[0];
                    var lvt = alt[1] - 0;
                    var rvt = alt[2] - 0;

                    if (lvt <= Gantt.etime && rvt >= Gantt.btime) {
                        if (!Gantt.getSelectLine(rid, lvt, rvt)) {
                            Gantt.removeFromCart(rid, lvt, rvt);
                        }
                    }
                });
            }

        },

        bindChartEvent: function(rdata){
            if ($.browser.msie && $.browser.version < 7){
                this.chart.find('.order-line').hover(function(){
                    $(this).addClass('order-line-hover');
                },function(){
                    $(this).removeClass('order-line-hover');
                });
                this.chart.find('.empty > div').hover(function(){
                    $(this).addClass('hover');
                },function(){
                    $(this).removeClass('hover');
                });
                this.chart.find('.item').hover(function(){
                    $(this).addClass('hover');
                }, function() {
                    $(this).removeClass('hover');
                });
            }
            
            this.bindLineEvent('.order-line, .limited');

            if (!rdata) {
                this.chart.find('.item').not('.cr').click(function(){
                    Gantt.fill(Gantt.rosta, $(this).parent().attr('alt'));
                    return false;
                });

                this.chart.find('.empty div').click(function(){
                    var rid   = $(this).parents('.g-row').attr('alt');
                    var ctime = $(this).parent().attr('alt') - 0;

                    if ($(this).is('.l')){
                        ctime = ctime - 86400000;
                    }

                    var lvt = ctime;
                    var rvt = ctime + 86400000;

                    if (!Gantt.combineSelectLine(rid, lvt, rvt)) {
                        if (Gantt.getSelectLine(rid, lvt, rvt, 'select')) {
                            Gantt.addToCart(rid, lvt, rvt);
                        }
                    }

                    return false;
                });
            }
        },

        getSelectLine: function(rid, lvt, rvt, act) {

            var rod = this.rosta.data[rid];
            var roi = this.rosta.room[rid];

            act = act || 'select';

            var room = this.chart.find('.g-row[alt="' + rid + '"] .wrapper');

            if (act == 'select' &&
                       (room.find('.selected-line[alt^="' + rid + ',' + lvt + ',"]').length > 0 ||
                        room.find('.selected-line[alt$=",' + rvt + '"]').length > 0)
                      ) {

                return false; // To check the repetitive select line

            } else {

                var stat = '';

                var odi = {};

                for(var oid in rod) {

                    odi = rod[oid];

                    if (odi.sta != ROSTA_YD && lvt + 86399999 < odi.rvt * 1000 && rvt > odi.lvt * 1000) {

                        stat = 'unavailable';

                    } // 86399999 is the last millisecond of bdate

                }

                if (roi.lvt > 0 && lvt < roi.rvt * 1000 && rvt > roi.lvt * 1000) {

                    stat = 'unavailable';

                }

                if (stat == 'unavailable') {

                    this.showNotice({msg:'>_< 选择的间夜不可用', lft:1});

                    return false;

                } else {

                    var selectedLine = $('<div class="selected-line"></div>');

                    selectedLine
                        .attr('alt', rid + ',' + lvt + ',' + rvt)
                        .css({
                            'width': (lvt < this.btime ? this.dayWidth : 0) + this.getLineWidth(lvt, rvt, true) + 'px',
                            'left' : (lvt < this.btime ? 0 - this.dayWidth / 2 : this.getLineLeft(lvt) + this.dayWidth / 2) + 'px'
                        });

                    if (lvt >= this.btime) {

                        selectedLine
                            .append('<div class="btn l"></div>')
                            .find('.l')
                            .click(function(){
                                Gantt.selectPrev(rid, lvt, rvt);
                                return false;
                            });

                    }

                    if (rvt <= this.etime) {

                        selectedLine
                            .append('<div class="btn r"></div>')
                            .find('.r')
                            .css({
                                'left' : selectedLine.outerWidth() - 16 + 'px'
                            })
                            .click(function(){
                                Gantt.selectNext(rid, lvt, rvt);
                                return false;
                            });

                    }

                    var btm = this.btime - 86400000;
                    var etm = Date.parse(Z.Global.method.date('Y/m/d',this.etime + 86400000));

                    btm = lvt < btm ? btm : lvt;
                    etm = rvt > etm ? etm : rvt;

                    var ront = (etm - btm) / 86400000;
                    var html = '<div class="btn split"></div>';

                    for (var i = 0; i < ront; i++) {
                        selectedLine
                            .append(html)
                            .find('.split:last')
                            .attr({
                                'alt'   : (btm + i * 86400000),
                                'title' : '点击取消 ' + Z.Global.method.date('Y-m-d', btm + i * 86400000)
                            })
                            .css({
                                'left' : (this.dayWidth * i + this.dayWidth / 2 - 10) + 'px'
                            });
                    }

                    selectedLine.find('.split').click(function(e) {
                        var ctime = $(this).attr('alt') - 0;
                        Gantt.splitSelectLine(rid, lvt, rvt, ctime);
                        return false;
                    });

                    room.append(selectedLine);

                    selectedLine.click(function(e){
                        Gantt.openDialog(selectedLine, e.clientX, e.clientY);
                        return false;
                    });

                    return true;

                }

            }
            
        },

        combineSelectLine: function(rid, sbtm, setm) {

            var line = [[rid, sbtm, setm]];

            this.cart.find('.order[alt^="' + rid + ',"]').each(function(){

                var alt = $(this).attr('alt').split(',');

                if (parseInt(alt[2], 10) == sbtm) {
                    line[0][1] = parseInt(alt[1], 10);
                    line.push(alt);
                }

                if (parseInt(alt[1], 10) == setm) {
                    line[0][2] = parseInt(alt[2], 10);
                    line.push(alt);
                }

            });

            if (line.length > 1 && Gantt.getSelectLine(rid, line[0][1], line[0][2], 'replace')) {

                Gantt.addToCart(rid, line[0][1], line[0][2]);
                
                for (var i = 1; i <= line.length - 1; i++) {
                    Gantt.removeFromCart(line[i][0], line[i][1], line[i][2]);
                }
                    
                Gantt.removeFromCart(rid, sbtm, setm);

                return line.length;

            } else {

                return false;

            }
        },

        splitSelectLine: function(rid, sbtm, setm, ctime) {

            var sl = [];

            var btm = sbtm;
            var etm = setm - 86400000;

            if (ctime == btm && ctime == etm) {

                this.removeFromCart(rid, sbtm, setm);

            } else {

                var act = 'replace';
            
                if (ctime == btm && ctime != etm) {
                    sl.push({
                        lvt : sbtm + 86400000,
                        rvt : setm
                    });
                } else if (ctime != btm && ctime == etm) {
                    sl.push({
                        lvt : sbtm,
                        rvt : setm - 86400000
                    });
                } else if (ctime > btm && ctime < etm) {
                    sl.push({
                        lvt : sbtm,
                        rvt : ctime
                    });
                    sl.push({
                        lvt : ctime + 86400000,
                        rvt : setm
                    });
                    act = 'split';
                }

                var sta = false;

                for (var i = 0; i < sl.length; i++) {
                    if (this.getSelectLine(rid, sl[i].lvt, sl[i].rvt, act)) {
                        sta = true;
                        this.addToCart(rid, sl[i].lvt, sl[i].rvt);
                    }
                }

                if (sta) {
                    this.removeFromCart(rid, sbtm, setm);
                } else {
                    for (var j = 0; j < sl.length; j++) {
                        this.removeFromCart(rid, sl[j].lvt, sl[j].rvt);
                    }
                }

            }
        },

        selectPrev: function(rid, sbtm, setm) {

            var lvt = sbtm - 86400000;
            var rvt = setm;

            if (Gantt.combineSelectLine(rid, lvt, rvt)) {
                Gantt.removeFromCart(rid, sbtm, setm);
            } else if (Gantt.getSelectLine(rid, lvt, rvt, 'replace')) {
                Gantt.addToCart(rid, lvt, rvt);
                Gantt.removeFromCart(rid, sbtm, setm);
            }
        },

        selectNext: function(rid, sbtm, setm) {

            var lvt = sbtm;
            var rvt = setm + 86400000;

            if (Gantt.combineSelectLine(rid, lvt, rvt)) {
                Gantt.removeFromCart(rid, sbtm, setm);
            } else if (Gantt.getSelectLine(rid, lvt, rvt, 'replace')) {
                Gantt.addToCart(rid, lvt, rvt);
                Gantt.removeFromCart(rid, sbtm, setm);
            }
        },

        addToCart: function(rid, sbtm, setm) {

            var ront = (setm - sbtm) / 86400000;

            var order = $('<li class="order"><table><thead></thead><tbody></tbody></table></li>');

            order.attr('alt', rid + ',' + sbtm + ',' + setm);

            order.find('thead').append(
                '<tr><th>' +
                '<input type="hidden" class="form-date"' +
                ' name="order[' + rid + ',' + sbtm + ',' + setm + '][room]"' +
                ' value="' +rid + '" />' +
                '<input type="hidden" class="form-date"' +
                ' name="order[' + rid + ',' + sbtm + ',' + setm + '][date]"' +
                ' value="' +Z.Global.method.date('Y-m-d', sbtm) + '" />' +
                '<input type="hidden" class="form-lgth"' +
                ' name="order[' + rid + ',' + sbtm + ',' + setm + '][lgth]"' +
                ' value="' + ront + '" />' +
                (this.rosta.room[rid].rnm.length > 7 ?
                    this.rosta.room[rid].rnm.slice(0,6) + '...'
                    :
                    this.rosta.room[rid].rnm) +
                '</th><th class="action"><a class="delete" href="javascript:;">删除</a></th></tr>'
            );

            order.find('tbody').append(
                '<tr class="bdate"><td>入住日期：<span class="bdate">' +
                Z.Global.method.date('Y-m-d', sbtm) +
                '</span></td>' +
                '<td></td></tr>' +
                '<tr class="edate"><td>离店日期：<span class="edate">' +
                Z.Global.method.date('Y-m-d', setm) +
                '</span></td>' +
                '<td class="action"> ' +
                '<span title="房态规则中设置了每订单至少要达到 ' + Hotel.h_order_minlens + ' 间夜，' +
                '最多不超过 ' + Hotel.h_order_maxlens + ' 间夜" class="ront">' +
                ront +
                '</span> 间夜' +
                '</td></tr>'
            );

            if (ront < Hotel.h_order_minlens) {

                order.find('.ront')
                     .addClass('below')
                     .attr('title', '房态规则中设置了每订单至少要达到 ' + Hotel.h_order_minlens + ' 间夜');

            } else if ( ront > Hotel.h_order_maxlens) {

                order.find('.ront')
                     .addClass('beyond')
                     .attr('title', '房态规则中设置了每订单最多不超过 ' + Hotel.h_order_maxlens + ' 间夜');

            }

            var exptime = Date.parse(Z.Global.method.date('Y/m/d', new Date())) + Hotel.h_order_enddays * 86400000;

            if (sbtm > exptime) {

                order.find('span.bdate')
                     .addClass('invalid')
                     .attr('title', '房态规则中设置了不能创建 ' + Hotel.h_order_enddays + ' 以后的订单');

            }

            if (setm > exptime) {

                order.find('span.edate')
                     .addClass('invalid')
                     .attr('title', '房态规则中设置了不能创建 ' + Hotel.h_order_enddays + ' 以后的订单');

            }

            order.find('.delete').click(function(){
                Gantt.removeFromCart(rid, sbtm, setm);
            });

            this.cart.find('.items').prepend(order);

            this.countCart();

        },

        removeFromCart: function(rid, sbtm, setm) {

            this.chart.find('.selected-line[alt="' + rid + ',' + sbtm + ',' + setm + '"]').remove();
            this.cart.find('.order[alt="' + rid + ',' + sbtm + ',' + setm + '"]').remove();

            this.countCart();

        },

        countCart: function() {

            var count = this.cart.find('.order').size();

            this.cart.find('.total-order').html(count);

            if (count > ORDER_LIMIT) {
                this.cart.find('.total-order').addClass('warnning');
            } else {
                this.cart.find('.total-order').removeClass('warnning');
            }

        },

        getLimitedDialog: function(rid) {

            var room = this.rosta.room[rid];

            var content =
                '<table><tbody>' +
                '<tr><td class="first">停用房间：</td><td>' + room.rnm + '</td></tr>' +
                '<tr><td class="first">开始时间：</td><td class="date">' + Z.Global.method.date('Y/m/d', room.lvt * 1000) + '</td></tr>' +
                '<tr><td class="first">结束时间：</td><td class="date">' + (room.rvt===0?'永久':Z.Global.method.date('Y/m/d', room.rvt * 1000)) + '</td></tr>' +
                '</tbody></table>' +
                '<p class="actions">' +
                '<a target="_blank" href="/master/room/update?rid=' + rid + '#pause">停用设置</a>' +
                '<a target="_blank" href="/master/order/?type=room&name='+ encodeURIComponent(room.rnm) +'&rtsta[0]=128">查看冲突</a>' +
                '</p>';

            return content;

        },

        getSelectDialog: function(rid, sbtm, setm) {

            var content =
                '<table><tbody>' +
                '<tr><td class="first">房间名称：</td><td>' + this.rosta.room[rid].rnm + '</td></tr>' +
                '<tr><td class="first">入住日期：</td><td class="date">' + Z.Global.method.date('Y/m/d', sbtm) + '</td></tr>' +
                '<tr><td class="first">离店日期：</td><td class="date">' + Z.Global.method.date('Y/m/d', setm) + '</td></tr>' +
                '<tr><td class="first">入住间夜：</td><td>' + (setm - sbtm) / 86400000 + '</td></tr>' +
                '</tbody></table>' +
                '<p class="actions">' +
                '<a class="delete-selected" href="javascript:;">取消选择</a>' +
                '</p>';

            return content;

        },

        getOrderDialog: function(rid, oid) {

            var order = this.rosta.data[rid][oid];
            var btime = Z.Global.method.date('Y/m/d H:i', order.lvt * 1000);
            var etime = Z.Global.method.date('Y/m/d H:i', order.rvt * 1000);
            var bdate = Z.Global.method.date('Y/m/d', order.lvt * 1000);
            var edate = Z.Global.method.date('Y/m/d', order.rvt * 1000);

            var content =
                    '<table><tbody>' +
                    '<tr><td class="first">订单编号：</td><td>' + order.oid + '<a target="_blank" href="/master/order/detail/?oid=' + order.oid + '" > [ 看订单 ] </a></td></tr>' +
                    '<tr><td class="first">账单编号：</td><td>' + order.bid + '<a target="_blank" href="/master/bill/detail/?bid=' + order.bid + '" > [ 看账单 ] </a></td></tr>' +
                    '<tr><td class="first">订单状态：</td><td>' + Gantt.statusCN[order.sta] + '</td></tr>' +
                    '<tr><td class="first">预计入住：</td><td class="date">' + btime + '</td></tr>' +
                    '<tr><td class="first">预计离店：</td><td class="date">' + etime + '</td></tr>' +
                    '<tr><td class="first">入住间夜：</td><td>' + ((Date.parse(edate) - Date.parse(bdate)) / 86400000) + '</td></tr>';
                    
            for(var bkgidx in order.gst.bkg){
                content += '<tr><td class="first">' + (bkgidx=='0'?'预订人：':'') + '</td><td>'
                         + order.gst.bkg[bkgidx].gnm  + ' (' + order.gst.bkg[bkgidx].tel  + ')</td></tr>';
            }
            
            for(var lvgidx in order.gst.lvg){
                content += '<tr><td class="first">' + (lvgidx=='0'?'入住人：':'') + '</td><td>'
                         + order.gst.lvg[lvgidx].gnm  + ' (' + order.gst.lvg[lvgidx].tel  + ')</td></tr>';
            }

            content += '<tr><td class="first">备注内容：</td><td class="memo">' + (order.tip === undefined ? '' : order.tip) + '</td></tr>';
            
            content += '</tbody></table>';
            
            content += '<p class="actions">';
                
            for(var act in order.act){
                content += '<a target="_blank" href="/master/order/handle/?oid=' + order.oid
                         + '&sta=' + order.sta
                         + '&act=' + order.act[act].code + '" '
                         + 'alt="' + order.act[act].code + '">'
                         + order.act[act].name + '</a>';
            }

            if ((order.sta == ROSTA_YD || order.sta == ROSTA_BL || order.sta == ROSTA_ZZ) && (order.ats & OATTR_HF)) {
                content += '<a alt="blhf" href="javascript:;">办理换房</a>';
            }
            
            content += '</p>';

            return content;

        },
        
        openDialog: function(obj, x, y) {

            if (this.chart.find('.g-dialog').size()) {
                this.closeDialog();
            }

            var type = '';

            var dialog = $('<div class="g-dialog"><div class="content"><a href="javascript:;" class="close">×</a></div></div>');

            var rid = obj.parents('.g-row').attr('alt');

            var alt = obj.attr('alt').split(',');

            if (obj.is('.selected-line')) {

                type = 'selected';

                var sbtm = alt[1] - 0;
                var setm = alt[2] - 0;

                dialog.find('.content').append(this.getSelectDialog(rid, sbtm, setm));

                this.cart.find('.order[alt="' + rid + ',' + sbtm + ',' + setm + '"]').addClass('highlight');

                dialog.find('.delete-selected').click(function(){
                    Gantt.closeDialog();
                    Gantt.removeFromCart(rid, sbtm, setm);
                });

            } else if(obj.is('.limited')){

                type = 'limited';

                dialog.find('.content').append(this.getLimitedDialog(rid));

            }else if(obj.is('.order-line')){

                type = 'order';

                var oid = alt[0];

                dialog.find('.content').append(this.getOrderDialog(rid, oid)).attr('alt', oid);

                if (this.rosta.data[rid][oid].tip === undefined) {
                    $.ajax({
                        url       : '/master/order/fetch-memo',
                        type      : 'POST',
                        data      : {oid: oid},
                        dataType  : 'json',
                        beforeSend: function(){
                            dialog.find('.memo').addClass('wait');
                        },
                        success   : function(data){
                            var memo = Z.Global.method.escape(data.context);
                            Gantt.rosta.data[rid][oid].tip = memo;
                            dialog.find('.memo').removeClass('wait').html(memo);
                        }
                    });
                }

                var bid = alt[1];

                this.chart.find('.order-line[alt$=",' + bid + '"]').addClass('bill-mark');

                dialog.find('.actions a').click(function(){

                    Gantt.handleOrder({
                        rid : rid,
                        oid : oid,
                        act : $(this).attr('alt')
                    });

                    return false;
                });
                    
            }
            
            this.chart.append(dialog);

            var po = Z.Global.method.getPageOffset();
            var ow = obj.outerWidth();
            var oh = obj.parents('.g-row').outerHeight();
            var ol = x - obj.parents('.g-row').offset().left + po.x;
            var ot = obj.parents('.g-row').index() * oh;
            var gw = Gantt.chart.width() - 17; //17px scroller
            var mw = dialog.outerWidth();
            var mh = dialog.outerHeight();
            var ml = ol + mw > gw ? ol - mw + 20 : ol - 22;
            var mt = 0;

            if(mh < ot){

                dialog.append('<div class="trgl-b"></div>');
                mt = ot - mh + 7; //7px offset

            }else{

                dialog.prepend('<div class="trgl-t"></div>');
                mt = ot + oh - 7; //7px offset

            }

            dialog
                .css({
                    'top' : mt + 'px',
                    'left' : ml + 'px'
                })
                .addClass(ol + mw > gw ? 'right-side' : '')
                .addClass(type);
                // .hide().fadeIn(FADE_SPEED);

            dialog.find('.close').click(function(){Gantt.closeDialog();});

            Gantt.bindDialogEvent();
            
        },
        
        closeDialog: function() {
            var dialog = this.chart.find('.g-dialog');

            if (dialog.hasClass('selected')) {
                this.cart.find('.highlight').removeClass('highlight');
            }

            if (dialog.hasClass('order')) {
                this.chart.find('.bill-mark').removeClass('bill-mark');
            }

            dialog.remove();

            // dialog.fadeOut(FADE_SPEED, function(){$(this).remove();});
        },
        
        bindDialogEvent: function() {
            this.bindBodyEvent();
            this.chart.find('.g-dialog').hover(function(){
                Gantt.unbindBodyEvent();
            },function(){
                Gantt.bindBodyEvent();
            });
        },
        
        unbindDialogEvent: function() {
            this.chart.find('.g-dialog').unbind('mouseenter').unbind('mouseleave');
        },

        bindBodyEvent: function() {
            $('body').bind('mousedown', function(){Gantt.closeDialog();});
        },

        unbindBodyEvent: function() {
            $('body').unbind('mousedown');
        },

        handleOrder: function(args) {

            var rid = args.rid;
            var oid = args.oid;
            var act = args.act;

            var order = this.rosta.data[rid][oid];

            var sta = order.sta;
            var key = order.key;

            if (act != 'blhf') {

                this.unbindDialogEvent();

                easyDialog.open({
                    container: {
                        header : order.act[act].name,
                        content: '<label for="dialog-order-memo" id="dialog-order-memo-label">添加备注</label><textarea id="dialog-order-memo"></textarea>',
                        yesFn  : function(){
                            $.ajax({
                                url       : '/master/order/do-action',
                                type      : 'POST',
                                data      : {
                                            oid : oid,
                                            act : act,
                                            sta : sta,
                                            key : key,
                                            memo: $('#dialog-order-memo').val()
                                            },
                                dataType  : 'json',
                                beforeSend: function(){
                                    Z.Global.method.prepare();
                                },
                                success   : function(data){
                                    if (data.success) {
                                        if (!Gantt.getRids()) {
                                            Gantt.closeDialog();
                                            Gantt.reloadRoomLine(rid);
                                        } else {
                                            Gantt.title.find('.button-refresh').click();
                                        }
                                    } else {
                                        Z.Global.method.respond({forward:false}, data);
                                    }
                                }
                            });
                        },
                        yesText: order.act[act].name
                    },
                    callback : function(){Gantt.bindDialogEvent();}
                });

            } else {

                var rosta = this.rosta;

                var select = this.cart.find('.order');

                if (select.size() < 1) {
                    alert('请先选中要换的间夜！ >_<');
                    return false;
                }

                /*if (this.getRids()) {
                    alert('请在多房间房态页进行换房操作！ >_<');
                    return false;
                }*/

                var olvt = Date.parse(Z.Global.method.date('Y/m/d', rosta.data[rid][oid].lvt * 1000)) - 0;
                var orvt = Date.parse(Z.Global.method.date('Y/m/d', rosta.data[rid][oid].rvt * 1000)) - 0;
                var ornt = (orvt - olvt) / 86400000;

                var sltArr = [];
                var sltMap = {};

                select.each(function(){
                    sltArr.push($(this).attr('alt').split(','));
                });

                if (sltArr.length > ORDER_LIMIT) {
                    alert('拆分后的订单数不能大于' + ORDER_LIMIT + '！');
                    return false;
                }

                var srid = '';
                var rids = [];

                var slvt = 0;
                var srvt = 0;

                var srnt = 0;
                var srntt = 0;

                var stat = '';

                var tmsp = '';

                for (var i = sltArr.length - 1; i >= 0; i--) {

                    srid = sltArr[i][0];
                    slvt = sltArr[i][1] - 0;
                    srvt = sltArr[i][2] - 0;

                    rids.push(srid);

                    if (slvt >= olvt && srvt <= orvt) {
                        stat += '+';
                    }

                    srnt = (srvt - slvt) / 86400000;

                    sltArr[i].push(srnt);

                    srntt += srnt;

                    // array to map

                    for (var sn = 0; sn < srnt; sn++) {

                        tmsp = slvt + sn * 86400000 + '';

                        if (sltMap[tmsp] === undefined) {
                            sltMap[tmsp] = srid;
                        } else {
                            alert('同一天不能选中多个房间！');
                            return false;
                        }

                    }
                }

                rids = Z.Global.method.uniqArray(rids);

                if (stat.length != sltArr.length) {
                    alert('选择的间夜必须在订单日期范围内！');
                    return false;
                }

                var table = $('<table class="blhf"></table>');

                table.append('<thead><tr><th>日期</th><th>新房间</th><th>换房后房费</th></tr></thead><tbody></tbody>');

                var rnm = '';

                for (tmsp in sltMap) {
                    rnm = rosta.room[sltMap[tmsp]].rnm;
                    table.find('tbody').append(
                        '<tr alt="' + tmsp + '">' +
                        '<td class="date">' + Z.Global.method.date('Y/m/d', tmsp - 0) + '</td>' +
                        '<td class="room wait">' +
                        '<a target="_blank" href="/master/room/update?rid=' + sltMap[tmsp] + '" title="' + rnm + '">' +
                        (rnm.length > 7 ? rnm.slice(0,6) + '...' : rnm) +
                        '</a>' +
                        ' (<span class="price" title="成交价">---</span>/<span class="brice" title="账单价">---</span>)' +
                        '</td>' +
                        '<td class="value wait">' +
                        '<input type="text" class="price" title="成交价" alt="' + tmsp + '"/>' +
                        '<input type="text" class="brice" title="账单价" alt="' + tmsp + '" />' +
                        '</td>' +
                        '</tr>'
                    );
                }

                table.append('<tfoot><tr><td colspan="3">注：绿色为账单价，黄色为成交价，输入框内默认为原订单对应日期的房费。</td></tr></tfoot>');

                this.unbindDialogEvent();

                easyDialog.open({
                    container: {
                        header : '办理换房',
                        content: '<div id="blhf-dialog" style="height:' + (srntt * 38 + 33 + 22 + 1) + 'px;"></div>',
                        yesFn  : function(){

                            qn = 0; // 取消读取价格队列

                            var args = {};
                            args.oid   = oid;
                            args.sta = order.sta,
                            args.key = order.key,

                            args.order = [];
                            args.price = [];
                            args.brice = [];

                            var srid = '';
                            var slvt = 0;
                            var srvt = 0;
                            var srnt = 0;

                            var bswap = {};
                            var pswap = {};
                            var temp  = '';

                            for (var i = sltArr.length - 1; i >= 0; i--) {
                                srid = sltArr[i][0];
                                slvt = sltArr[i][1] - 0;
                                srvt = sltArr[i][2] - 0;
                                srnt = sltArr[i][3];

                                args.order.push({
                                    room: srid,
                                    date: Z.Global.method.date('Y/m/d', slvt),
                                    lgth: srnt
                                });

                                for (var j = 0; j < srnt; j++) {
                                    temp = slvt + j * 86400000 + '';
                                    bswap[temp/1000] = dialog.find('.brice[alt="' + temp + '"]').val();
                                    pswap[temp/1000] = dialog.find('.price[alt="' + temp + '"]').val();
                                }
                                args.brice.push(bswap);
                                args.price.push(pswap);

                                bswap = {};
                                pswap = {};
                            }

                            $.ajax({
                                url       : '/master/order/do-modify',
                                type      : 'POST',
                                data      : args,
                                dataType  : 'json',
                                beforeSend: function(){
                                    Z.Global.method.prepare();
                                },
                                success   : function(data){
                                    if (data.success) {
                                        Gantt.closeDialog();
                                        Gantt.cart.find('.order').remove();
                                        Gantt.cart.find('.total-order').html('0');
                                        Gantt.reloadRoomLine(rid);
                                        for (var i = rids.length - 1; i >= 0; i--) {
                                            Gantt.reloadRoomLine(rids[i]);
                                        }
                                    } else {
                                        Z.Global.method.respond({forward:false}, data);
                                    }
                                    
                                }
                            });
                        },
                        yesText: '办理换房'
                    },
                    callback : function(){Gantt.bindDialogEvent();}
                });

                var dialog = $('#blhf-dialog');

                dialog.append(table);

                var qn = sltArr.length - 1;

                var loadRoomValueCallback = function(data){
                    var time = '';
                    var line = '';

                    for(time in data.price) {
                        line = dialog.find('tr[alt="' + time * 1000 + '"]');
                        line.find('span.brice').html(data.brice[time] / 100);
                        line.find('span.price').html(data.price[time] / 100);
                        line.find('.room').removeClass('wait');
                    }

                    if (--qn >= 0) {
                        Gantt.loadRoomValue(sltArr[qn], loadRoomValueCallback);
                    }
                };

                this.loadOrderValue(oid, function(data){
                    var time = '';
                    var line = '';

                    for(time in data.price) {
                        line = dialog.find('tr[alt="' + time * 1000 + '"]');
                        line.find('input.brice').val(data.brice[time] / 100);
                        line.find('input.price').val(data.price[time] / 100);
                        line.find('.value').removeClass('wait');
                    }
                    
                    Gantt.loadRoomValue(sltArr[qn], loadRoomValueCallback);
                });
            }

        },

        reloadRoomLine: function(rid) {

            var room = this.chart.find('.g-row[alt="' + rid + '"]');

            room.find('.wrapper').prepend('<div class="wait-row"></div>');

            $.getJSON('/master/rosta', {
                date: this.range.find('.bdate').html(),
                lgth: this.getLength(),
                rids: rid
            }, function(data){

                var rosta = data.context;

                var roi = rosta.room[rid];
                var rod = rosta.data[rid];

                Gantt.rosta.room[rid] = roi;
                Gantt.rosta.data[rid] = rod;

                room.find('.obn, .order-line, .selected-line').remove();

                room.find('.item').attr(
                    'title',
                    (roi.ext.rtp ? roi.ext.rtp : '无房型信息') + ',' +
                    (roi.ext.rlo ? roi.ext.rlo : '无户型信息') + ',' +
                    (roi.ext.rze ? roi.ext.rze : '无区域信息') + ',' +
                    '[' + (roi.ext.rvw ? roi.ext.rvw : '无景观信息') + ']'
                );

                if (roi.obn > 0) {
                    room.find('.item').append(
                        '<div class="obn" title="最近72小时内创建的预订订单数：' + roi.obn + '">*' + roi.obn + '</div>'
                    );
                }

                for(var oid in rod) {
                    room.find('.wrapper').append(Gantt.getOrderLine(rid, oid));
                }

                if(roi.lvt){
                    room.find('.wrapper').append(Gantt.getLimitedLine(rid));
                }

                Gantt.cart.find('.order[alt^="' + rid + ',"]').each(function(){
                    var alt  = $(this).attr('alt').split(',');
                    var rid = alt[0];
                    var lvt = alt[1] - 0;
                    var rvt = alt[2] - 0;

                    if (lvt <= Gantt.etime && rvt >= Gantt.btime) {
                        if (!Gantt.getSelectLine(rid, lvt, rvt)) {
                            Gantt.removeFromCart(rid, lvt, rvt);
                        }
                    }
                });

                room.find('.wait-row').fadeOut(FADE_SPEED, function(){$(this).remove();});

                Gantt.bindLineEvent('.order-line, .limited');

            });

        },

        loadOrderValue: function(oid, fn){
            $.getJSON('/master/order/fetch-value', {oid:oid}, function(data){
                if (data.success) {
                    return fn(data.context);
                }
            });
        },

        loadRoomValue: function(arr, fn){
            $.getJSON('/master/room/fetch-value', {
                rid  : arr[0],
                bdate: Z.Global.method.date('Y/m/d', arr[1] - 0),
                edate: Z.Global.method.date('Y/m/d', arr[2] - 86400000)
            }, function(data){
                if (data.success) {
                    return fn(data.context);
                }
            });
        },

        load: function(args){
        
            var queue = [];

            return function(args){
                if (!queue.length) {
                    queue.push(args);
                    Gantt.wait();

                    $.getJSON('/master/rosta', queue[0], function(data){
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
                                Gantt.fill(Gantt.rosta = data, args.rids);

                                if(Gantt.chart.find('.order-line').size() === 0 && queue[0].skip == 1){
                                    Gantt.showNotice({msg:'该方向没有更多的订单了', lft:3});
                                }

                                queue.shift();
                            },1000);
                            return;
                        }

                        Gantt.fill(Gantt.rosta = data, args.rids);

                        if(Gantt.chart.find('.order-line').size() === 0 && queue[0].skip == 1){
                            Gantt.showNotice({msg:'该方向没有更多的订单了', lft:3});
                        }

                        queue.shift();
                    });
                }
            };
        },

        fill: function(rs, rids){

            rids = (rids || '').split(',');

            this.setFilter(rs.room);
            this.rosta = this.filte(rs);

            var rdata = null;

            if (rids.length==1){
                rdata = this.rosta.room[rids[0]];
            }

            Gantt.lgth = this.getLength();
            Gantt.dayWidth = this.width / this.lgth;
            Gantt.hourWidth = this.dayWidth / 24;

            Gantt.btime = this.rosta.line[0] * 1000;
            Gantt.etime = this.btime + this.lgth * 86400000 - 1;

            this.drawAxis(rdata);

            this.drawChart(rdata);
            
            this.calendarBind();

            this.okay();
        },

        init: function() {

            this.load = this.load();
            this.showNotice = this.showNotice();

            this.navi.find('#gantt-view').val('8'); //firefox to refresh's bug or feature, '8' is default
            
            this.fill(initRosta,initRids);
            
            this.navi.find('a').click(function(){

                var date = '';
                var lgth = Gantt.getLength();
                var skip = 0;
                var rids = Gantt.getRids();

                switch($(this).attr('class')){
                    case 'pre':
                        date = Gantt.range.find('.bdate').html();
                        lgth = -lgth;
                        break;
                    case 'next':
                        date =  Gantt.range.find('.edate').html();
                        break;
                    case 'pre-s':
                        date =  Gantt.range.find('.bdate').html();
                        lgth = -lgth;
                        skip = 1;
                        break;
                    case 'next-s':
                        date =  Gantt.range.find('.edate').html();
                        skip = 1;
                        break;
                }
                
                Gantt.load({date:date, lgth:lgth, skip:skip, rids:rids});

            });

            this.title.find('.button-calendar').click(function(){
                if (Gantt.calendar.css('display')!='none') {
                    Gantt.calendar.hide();
                    // Gantt.calendar.fadeOut(FADE_SPEED);
                }else{
                    Gantt.calendar.show();
                    // Gantt.calendar.fadeIn(FADE_SPEED);
                    // Gantt.unbindLineEvent('.order-line, .limited');

                    var bf = function() {
                        Gantt.calendar.hide();
                        // Gantt.calendar.fadeOut(FADE_SPEED);
                        // Gantt.bindLineEvent('.order-line, .limited');
                    };

                    Gantt.calendar.hover(function() {
                        $('body').unbind('mouseup', bf);
                    }, function() {
                        $('body').bind('mouseup', bf);
                    });

                    $(this).hover(function() {
                        $('body').unbind('mouseup', bf);
                    }, function() {
                        $('body').bind('mouseup', bf);
                    });
                }
            });

            this.title.find('.button-today').click(function(){
                Gantt.load({
                    date: Z.Global.method.date('Y/m/d', (new Date()).getTime() - 86400000),
                    lgth: Gantt.getLength(),
                    rids: Gantt.getRids()
                });
            });
            
            this.title.find('.button-refresh').click(function(){
                Gantt.load({
                    date: Z.Global.method.date('Y/m/d', Gantt.btime),
                    lgth: Gantt.getLength(),
                    rids: Gantt.getRids()
                });
            });

            this.filter.find('.gantt-filter').live('change',function(){
                Gantt.flt[$(this).attr('id')] = $(this).val();
                Gantt.fill(Gantt.rosta);
            });
            
            this.navi.find('#gantt-view').live('change',function(){
                Gantt.title.find('.button-refresh').click();
            });

            this.chart.find('.room-row .room').live('click', function(){
                Gantt.fill(Gantt.rosta, $(this).parent().attr('alt'));
            });
            
            this.filter.find('.button-back').live('click', function(){
                $(this).hide();
                Gantt.filter.find('.gantt-filter').show();

                if (Gantt.getRostaRoomCount() > 1){
                    Gantt.fill(Gantt.rosta);
                }else{
                    Gantt.load({
                        date: Gantt.range.find('.bdate').html(),
                        lgth: Gantt.getLength()
                    });
                }
            });

            this.cart.find('#form-cart').submit(function(){
                if(!Gantt.cart.find('.order').size()){
                    alert('您还没选择任何房间！');
                    return false;
                }
                if(parseInt(Gantt.cart.find('.total-order').html(), 10) > ORDER_LIMIT){
                    alert('您选择的订单不能超过 ' + ORDER_LIMIT + ' 个！');
                    return false;
                }
                if(Gantt.cart.find('.invalid').size()){
                    alert('您选择的订单的日期超出可用范围！');
                    return false;
                }
                if(Gantt.cart.find('.below').size()){
                    alert('您选择的房间房晚不够！');
                    return false;
                }
                if(Gantt.cart.find('.beyond').size()){
                    alert('您选择的房间房晚太多！');
                    return false;
                }
            });

            this.cart.find('#empty-orders').click(function(){
                if (Gantt.cart.find('.order').length) {
                    Gantt.cart.find('.order').remove();
                    Gantt.cart.find('.total-order').html('0').removeClass('warnning');
                    Gantt.title.find('.button-refresh').click();
                }
            });
        }
    };
    
    Gantt.init();

    var task = {

        list: $('#tasks .items'),

        done: function(code){
            var list = code ? this.list.find('li a[alt="' + code + '"]').parent().addClass('done') : this.list.find('li.done');
            list.appendTo(
                list.parent()
            ).addClass('done');
        },

        load: function(){
            $.getJSON('/master/task?hash=' + (new Date()).getTime(), null, function(ret){
                if (!ret.success){
                    Z.Global.method.respond({forward: false}, ret);
                    setTimeout(task.load, 60000);
                    return false;
                }

                ret = ret.context;
                
                $('#tasks .current').html(Z.Global.method.date('Y/m/d H:i', new Date(ret.time*1000)))
                    .attr('title','每' + ret.life/60 + '分钟刷新一次。');

                var list = '';

                for (var key in ret.data){
                    list += '<li class="' + (ret.data[key].done ? 'done' : '') + '">'
                    + '<p>' + ret.data[key].info + '</p>'
                    + '<a target="_blank" href="' + ret.data[key].href + '" alt="' + key + '">去处理</a>'
                    + '</li>';
                }

                if (list !== ''){
                    task.list.html(list);
                    task.done();
                }

                setTimeout(task.load, ret.life * 1000);
            });
        },

        init: function(){
            this.list.find('li a').live('click', function(){
                $.get('/master/task/done.json', {'code': $(this).attr('alt')});
                task.done($(this).attr('alt'));
                //task.load();
            });
            this.load();
        }
    };

    task.init();
};
