Z.Module.master = Z.Module.master || {};
Z.Module.master.room = Z.Module.master.room || {};

Z.Module.master.room.index = Z.Module.master.room.index || {};
Z.Module.master.room.index.init = function()
{
    Z.Global.method.tierLook('.data tbody tr');

    $("#room-filter a").click(function(){
        var tr = $("#rooms-list tbody tr");
        var allType = new RegExp("全部");
        var thisType = "#" + $(this).parents("tr").attr("id") + " a";
        var types = [
            'room-type',
            'room-layout',
            'room-view',
            'room-status'
        ];

        $('#nothing').remove();
        if(allType.test($(this).attr("alt"))){
            $(thisType).each(function(){
                if($(this).hasClass('now')){
                    $(this).removeClass("now").html($(this).html().replace(/\sx$/, ''));
                }
            });
            $(this).addClass("now");
        }else{
            $(thisType).filter(".all").removeClass("now");
            if($(this).hasClass('now')){
                $(this).removeClass("now").html($(this).html().replace(/\sx$/, ''));
            }else{
                $(this).addClass("now").append(' x');
            }
        }
        if(!$(thisType).is(".now")){
            $(thisType).filter(".all").addClass("now");
        }

        tr.hide().filter(function(){
            var cur = " ";
            var sum = 0;
            var t = [];
            var m = [];
            var keyword = [];
            var i = 0;
            for(i=0;i<types.length;i++){
                t[i] = 0;
                for(var j=0;j<$("#"+types[i]+" .now").size();j++){
                    keyword[j] = "," + $("#"+types[i]+" .now").eq(j).attr("alt") + ",";
                    cur = "," + $(this).children("."+types[i]).attr("alt") + ",";
                    if(cur.indexOf(keyword[j]) >=0 || allType.test(keyword[j])){
                        t[i]++;
                    }
                }
                m[i] = t[i]>0?1:0;
            }
            for(i=0;i<m.length;i++){
                sum = sum + m[i];
            }
            return sum == m.length?true:false;
        }).show();

        if(!tr.is(':visible')){tr.parent().append('<tr id="nothing"><td colspan="11">没有符合指定要求的房间！</td></tr>');}
        
        Z.Global.method.tierLook(tr);

        return false;
    });
};

Z.Module.master.room.detail = Z.Module.master.room.detail || {};
Z.Module.master.room.detail.init = function()
{
};

Z.Module.master.room.create = Z.Module.master.room.create || {};
Z.Module.master.room.create.init = function()
{
    $('#form-create-room').submit(function(){
        Z.Global.method.ajaxForm(this);
        return false;
    });

    var incr = 'i';
    $('#form-create-room .handle-create').live('click', function(){
        incr+= '0';

        $(this).parent().after(
            '<p>' +
            '    <label for="f-rn-' + incr + '"><em>*</em>房间名</label><input name="names[' + incr + ']" type="text" id="f-rn-' + incr + '" class="long-text ftt-input-text {required:true, maxlength:15}" />' +
            '    <a href="javascript:;" class="handle-create">[增加]</a>' +
            '    <a href="javascript:;" class="handle-remove">[删除]</a>' +
            '</p>'
        );
    });

    $('#form-create-room .handle-remove').live('click', function(){
        $(this).parent().fadeOut(function(){
            $(this).remove();
        });
    });

    // $.trim($('#index-address').html()) == '' || $('#f-address').powerFloat({eventType: 'focus', target: '#index-address'});
    $.trim($('#index-type').html()) === '' || $('#f-type').powerFloat({eventType: 'focus', target: '#index-type'});
    $.trim($('#index-area').html()) === '' || $('#f-area').powerFloat({eventType: 'focus', target: '#index-area'});
    $.trim($('#index-zone').html()) === '' || $('#f-zone').powerFloat({eventType: 'focus', target: '#index-zone'});
    $.trim($('#index-layout').html()) === '' || $('#f-layout').powerFloat({eventType: 'focus', target: '#index-layout'});
};

Z.Module.master.room.update = Z.Module.master.room.update || {};
Z.Module.master.room.update.init = function()
{
    var priceTable = {

        calendar : $('#price-calendar-wrapper'),
        hold     : $('#price-loading-cover'),
        plans    : $('#price-plans'),

        _rid: $('#price-calendar-wrapper').attr('alt'),
        
        wait: function(){
            this.plans.html('');
            this.hold.show();
        },
        
        okay: function(){
            this.hold.hide();
        },
        
        draw: function(args){
            
            var data = args.data;
            var bdate = args.bdate;
            var edate = args.edate;
            var cm = Date.parse(args.cdate);
            
            var week = {
                '1':'星期一',
                '2':'星期二',
                '3':'星期三',
                '4':'星期四',
                '5':'星期五',
                '6':'星期六',
                '0':'星期日'
            };
            
            var thead = '<thead><tr>';
            for(var wd in week){
                thead += '<th>' + week[wd] + '</th>';
            }
            thead += '</tr></thead>';
            
            var tbody = '<tbody><tr>';
            
            var fd = Z.Global.method.date('w', new Date(bdate));
            var i = 0;
            if(fd!='0'){
                for(i = 1; i < fd; i++){
                    tbody += '<td> </td>';
                }
            }else{
                for(i = 1; i < 7; i++){
                    tbody += '<td> </td>';
                }
            }
            
            for(var date in data ){
                tbody += '<td' + (date*1000==cm?' class="chosen"':'') + '>'
                       + '<div class="date">' + Z.Global.method.date('m月d日', new Date(date*1000))  + '</div>'
                       + '<div class="price">' + data[date]/100 + '</div>'
                       + '</td>'
                       + (Z.Global.method.date('w', new Date(date*1000))=='0'?'</tr><tr>':'');
            }
            
            var ld = Z.Global.method.date('w', new Date(edate));
            if(ld!='0'){
                for(var j=ld; j < 7; j++){
                    tbody += '<td> </td>';
                }
            }
            
            
            tbody += '</tr></tbody>';
            
            var table = '<table class="rprice">'+ thead + tbody + '</table>'
                      + '<p id="more-plans"><a alt="' + bdate +','+ edate +'" href="javascript:;">查看价格计划</a></p>';
            
            this.calendar.html(table);
        },

        load: function(cdate){
            
            this.wait();
            this.calendar.html('');
            this.plans.html('');
            
            var bdate = Z.Global.method.date('Y/m/d', Z.Global.method.getTimeLine(cdate, 'M')[0]);
            var edate = Z.Global.method.date('Y/m/d', Z.Global.method.getTimeLine(cdate, 'M')[1]);

            cdate = cdate.split('-').join('/');

            $.getJSON('/master/room/fetch-value', {
                bdate:bdate,
                edate:edate,
                rid:this._rid
            }, function(data){
                if (!data.success) {
                    return Z.Global.method.respond({forward:false}, data);
                }

                priceTable.draw({
                    data: data.context.price,
                    cdate: cdate,
                    bdate: bdate,
                    edate: edate
                });
                
                priceTable.okay();
            });
            
            return;
        },
        
        loadPlans: function(bdate, edate){
            
            $.getJSON('/master/room/fetch-value-plans', {
                bdate:bdate,
                edate:edate,
                rid:this._rid
            }, function(data){
                if (!data.success) {
                    return Z.Global.method.respond({forward:false}, data);
                }

                var plans = '<table class="rprice"><thead><tr><th>开始日期</th><th>结束日期</th><th>价格(元)</th><th>操作人</th><th>操作时间</th></tr></thead><tbody>';
                if(Z.Global.method.getKeys(data.context.plans).length > 0) {
                    for (var idx in data.context.plans) {
                        plans += '<tr><td>'
                                + Z.Global.method.date('Y-m-d', data.context.plans[idx]['rp_btime']*1000)
                                + '</td><td>'
                                + Z.Global.method.date('Y-m-d', data.context.plans[idx]['rp_etime']*1000)
                                + '</td><td>'
                                + data.context.plans[idx]['rp_value']/100
                                + '</td><td>'
                                + data.context.plans[idx]['rp_uname']
                                + '</td><td>'
                                + Z.Global.method.date('Y-m-d H:i:s', data.context.plans[idx]['rp_mtime']*1000)
                                + '</td></tr>';
                    }
                } else {
                    plans += '<tr><td colspan="5">该时间段无价格计划</td></tr>';
                }
                plans += '</tbody></table>';
                priceTable.plans.html(plans);
            });
        
        },

        init: function(){

        }
    };

    // 获取指定日期的房价

    $('#get-price').click(function(){
        priceTable.load($('#price-date').val());
    });

    // 获取当前日期范围的价格计划

    $('#more-plans a').live('click', function(){
        var alt = $(this).attr('alt').split(',');
        priceTable.loadPlans(alt[0], alt[1]);
    });

    // tab切换

    $('.tab-list li').click(function(){
        $(this).addClass("now").siblings().removeClass("now");
        var tab = $(this).attr('alt');
        location.hash = tab;
        $('#block-' + tab).removeClass('hide').siblings().addClass('hide');
        if (tab=='price' && !$('#price-calendar-wrapper .rprice').size()) {
            priceTable.load($('#price-date').val());
        }
    }).hover(function(){
        $(this).addClass("hover");
    },function(){
        $(this).removeClass("hover");
    });

    if (location.hash) {
        $('.tab-list li[alt=' + location.hash.substr(1) + ']').click();
    }

    // 房间基本信息填写参考

    // $.trim($('#index-address').html()) == '' || $('#f-address').powerFloat({eventType: 'focus', target: '#index-address'});
    $.trim($('#index-type').html()) === '' || $('#f-type').powerFloat({eventType: 'focus', target: '#index-type'});
    $.trim($('#index-area').html()) === '' || $('#f-area').powerFloat({eventType: 'focus', target: '#index-area'});
    $.trim($('#index-zone').html()) === '' || $('#f-zone').powerFloat({eventType: 'focus', target: '#index-zone'});
    $.trim($('#index-layout').html()) === '' || $('#f-layout').powerFloat({eventType: 'focus', target: '#index-layout'});

    //停用设置表单控制

    $('#form-retain-room input[name="pause"]').click(function(){
        if ($(this).val() == '1') {
            $('#form-retain-room input[type="text"], #form-retain-room input[type="checkbox"]').removeAttr('disabled');
        } else {
            $('#form-retain-room input[type="text"], #form-retain-room input[type="checkbox"]').attr('disabled', 'disabled');
        }
    });

    //各种表单ajax提交
    
    $('#form-update-basic-price').submit(function(){
        Z.Global.method.ajaxForm(this);
        return false;
    });
    
    $('#form-create-price-plan').submit(function(){
        Z.Global.method.ajaxForm(this);
        return false;
    });

    $('#form-update-room').submit(function(){
        Z.Global.method.ajaxForm(this);
        return false;
    });

    $('#form-update-attrs').submit(function(){
        Z.Global.method.ajaxForm(this);
        return false;
    });

    $('#form-retain-room').submit(function(){
        Z.Global.method.ajaxForm(this);
        return false;
    });

    $('#form-update-equip').submit(function(){
        Z.Global.method.ajaxForm(this);
        return false;
    });
};

Z.Module.master.room.updateRule = Z.Module.master.room.updateRule || {};
Z.Module.master.room.updateRule.init = function()
{
    Z.Global.method.tierLook('#rooms-list-all tbody tr', {over: 'over', even: ''});

    $('table#rooms-list-all').tablesorter({
        headers: {
            0: {sorter: false},
            7: {sorter: false}
        },
        widgets: ['zebra']
    });
    
    $('#chk-all').change(function(){
        $('input[name="rids[]"]').attr('checked', ($(this).attr('checked')?true:false));
    });
    
    $('#form-update-basic-prices, #form-create-price-plans').live('submit', function(){
        var thisForm = $(this);
        $('input[name="rids[]"]:checked').each(function(){
            thisForm.append('<input type="hidden" name="rids[]" value="'+ this.value + '" />');
        });
        Z.Global.method.ajaxForm(this);
        return false;
    });
    
    var ubp = $('#div-update-basic-prices');
    var cpp = $('#div-create-price-plans');
    
    $('.price-button, .plan-button').click(function(){
        if($('input[name="rids[]"]:checked').size()>0){
            easyDialog.open({
                container : {
                    header: '房间价格规则批量操作',
                    content: '<div class="price-dialog"></div>'
                }
            });
            if($(this).is('.price-button')){
                ubp.appendTo('.price-dialog');
            }else if($(this).is('.plan-button')){
                cpp.appendTo('.price-dialog');
            }
        }else{
            easyDialog.open({
                container : {
                    header: '房间价格规则批量操作',
                    content: '<div class="price-dialog"><span style="color:red;">未选中任何房间。</span></div>'
                },
                autoClose : 3000
            });
        }
    });
};

