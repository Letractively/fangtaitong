/*
Requires jQuery version: >= 1.2.6

ROSTA
*/
Calendar = (function($) { 
    function Calendar(el, opts) {
        if (typeof(opts) != "object") opts = {};
        $.extend(this, Calendar.DEFAULT_OPTS, opts);

        this.input = $(el);
        this.bindMethodsToObj("show", "selectDate");
        this.build();
        this.show();
    };

    Calendar.DEFAULT_OPTS = {
        month_names: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
        short_month_names: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
        short_day_names: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
        start_of_week: 1,
		change_date_callback: function(){}
    };

    Calendar.prototype = {
        build: function() {
            var monthNav = $('<p class="month_nav">' +
                '<span class="arrow prev">&lt;</span>' +
                ' <span class="month_name"></span> ' +
                '<span class="arrow next">&gt;</span>' +
            '</p>');
            this.monthNameSpan = $(".month_name", monthNav);
            $(".prev", monthNav).click(this.bindToObj(function() { this.moveMonthBy(-1); }));
            $(".next", monthNav).click(this.bindToObj(function() { this.moveMonthBy(1); }));

            var yearNav = $('<p class="year_nav">' +
                '<span class="arrow prev">&lt;&lt;</span>' +
                ' <span class="year_name"></span> ' +
                '<span class="arrow next">&gt;&gt;</span>' +
            '</p>');
            this.yearNameSpan = $(".year_name", yearNav);
            $(".prev", yearNav).click(this.bindToObj(function() { this.moveMonthBy(-12); }));
            $(".next", yearNav).click(this.bindToObj(function() { this.moveMonthBy(12); }));

            var nav = $('<div class="nav"></div>').append(monthNav, yearNav);

            var tableShell = "<table><thead><tr>";
            $(this.adjustDays(this.short_day_names)).each(function() {
                tableShell += "<th>" + this + "</th>";
            });
            tableShell += "</tr></thead><tbody></tbody></table>";

			$('.l_calendar').remove();
			
            this.dateSelector = this.rootLayers = $('<div class="l_calendar"></div>').append(nav, tableShell).appendTo(this.input);

            this.tbody = $("tbody", this.dateSelector);

            this.selectDate();
        },

        selectMonth: function(date) {
            var newMonth = new Date(date.getFullYear(), date.getMonth(), 1);

            if (!this.currentMonth || !(this.currentMonth.getFullYear() == newMonth.getFullYear() &&
            this.currentMonth.getMonth() == newMonth.getMonth())) {

                this.currentMonth = newMonth;

                var rangeStart = this.rangeStart(date), rangeEnd = this.rangeEnd(date);
                var numDays = 41; //this.daysBetween(rangeStart, rangeEnd); 保持每月显示6周
                var dayCells = "";

                for (var i = 0; i <= numDays; i++) {
                    var currentDay = new Date(rangeStart.getFullYear(), rangeStart.getMonth(), rangeStart.getDate() + i, 12, 00);

                    if (this.isFirstDayOfWeek(currentDay)) dayCells += "<tr>";

                    if (currentDay.getMonth() == date.getMonth()) {
                        dayCells += '<td class="selectable_day" date="' + this.dateToString(currentDay) + '">' + currentDay.getDate() + '</td>';
                    } else {
                        dayCells += '<td class="unselected_month" date="' + this.dateToString(currentDay) + '">' + currentDay.getDate() + '</td>';
                    };

                    if (this.isLastDayOfWeek(currentDay)) dayCells += "</tr>";
                };
                this.tbody.empty().append(dayCells);

                this.monthNameSpan.empty().append(this.monthName(date));
                this.yearNameSpan.empty().append(this.currentMonth.getFullYear());

                $(".selectable_day", this.tbody).click(this.bindToObj(function(event) {
                    this.changeDate($(event.target).attr("date"));
                }));

                $("td[date='" + this.dateToString(new Date()) + "']", this.tbody).addClass("today");

                $("td.selectable_day", this.tbody).mouseover(function() { $(this).addClass("hover") });
                $("td.selectable_day", this.tbody).mouseout(function() { $(this).removeClass("hover") });
            };

			this.selectedToHightlight();

        },

        selectDate: function(date) {
            if (typeof(date) == "undefined") {
                date = this.stringToDate(date);
            };
            if (!date) date = new Date();

            this.selectedDate = date;
            this.selectedDateString = this.dateToString(this.selectedDate);
            this.selectMonth(this.selectedDate);
        },

        changeDate: function(dateString) {
            this.input.attr('alt', dateString);
			$('.selected', this.tbody).removeClass("selected");	
			$('td[date="' + dateString + '"]', this.tbody).addClass("selected");
			this.change_date_callback(dateString);
        },

        show: function() {
            this.rootLayers.css("display", "block");
        },

        stringToDate: function(string) {
            var matches;
            if (matches = string.match(/^(\d{1,2}) ([^\s]+) (\d{4,4})$/)) {
                return new Date(matches[3], this.shortMonthNum(matches[2]), matches[1], 12, 00);
            } else {
                return null;
            };
        },

        dateToString: function(date) {
            return date.getDate() + " " + this.short_month_names[date.getMonth()] + " " + date.getFullYear();
        },

        moveMonthBy: function(amount) {
            var newMonth = new Date(this.currentMonth.getFullYear(), this.currentMonth.getMonth() + amount, this.currentMonth.getDate());
            this.selectMonth(newMonth);
        },

        monthName: function(date) {
            return this.month_names[date.getMonth()];
        },

        bindToObj: function(fn) {
            var self = this;
            return function() { return fn.apply(self, arguments) };
        },

        bindMethodsToObj: function() {
            for (var i = 0; i < arguments.length; i++) {
                this[arguments[i]] = this.bindToObj(this[arguments[i]]);
            };
        },

        indexFor: function(array, value) {
            for (var i = 0; i < array.length; i++) {
                if (value == array[i]) return i;
            };
        },

        monthNum: function(month_name) {
            return this.indexFor(this.month_names, month_name);
        },

        shortMonthNum: function(month_name) {
            return this.indexFor(this.short_month_names, month_name);
        },

        shortDayNum: function(day_name) {
            return this.indexFor(this.short_day_names, day_name);
        },

        daysBetween: function(start, end) {
            start = Date.UTC(start.getFullYear(), start.getMonth(), start.getDate());
            end = Date.UTC(end.getFullYear(), end.getMonth(), end.getDate());
            return (end - start) / 86400000;
        },

        changeDayTo: function(dayOfWeek, date, direction) {
            var difference = direction * (Math.abs(date.getDay() - dayOfWeek - (direction * 7)) % 7);
            return new Date(date.getFullYear(), date.getMonth(), date.getDate() + difference);
        },

        rangeStart: function(date) {
            return this.changeDayTo(this.start_of_week, new Date(date.getFullYear(), date.getMonth()), -1);
        },

        rangeEnd: function(date) {
            return this.changeDayTo((this.start_of_week - 1) % 7, new Date(date.getFullYear(), date.getMonth() + 1, 0), 1);
        },

        isFirstDayOfWeek: function(date) {
            return date.getDay() == this.start_of_week;
        },

        isLastDayOfWeek: function(date) {
            return date.getDay() == (this.start_of_week - 1) % 7;
        },

        adjustDays: function(days) {
            var newDays = [];
            for (var i = 0; i < days.length; i++) {
                newDays[i] = days[(i + this.start_of_week) % 7];
            }
            return newDays;
        }
    };

    $.fn.calendar = function(opts) {
        return this.each(function() {new Calendar(this, opts);});
    };
    $.calendar = { init: function(opts) {
        $("#rosta-calendar").calendar(opts);
    }};

    return Calendar;
})(jQuery);


jQuery.extend(Calendar.DEFAULT_OPTS, {
    month_names: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"],
    short_month_names: ["一", "二", "三", "四", "五", "六", "七", "八", "九", "十", "十一", "十二"],
    short_day_names: ["日", "一", "二", "三", "四", "五", "六"],
	start_of_week: 1,
	change_date_callback: function(){}
});

jQuery.extend(Calendar.DEFAULT_OPTS, {
    stringToDate: function(string) {
        var matches;
        if (matches = string.match(/^(\d{4,4})\/(\d{2,2})\/(\d{2,2})$/)) {
            return new Date(matches[1], matches[2] - 1, matches[3]);
        } else {
            return null;
        };
    },

    dateToString: function(date) {
        var month = (date.getMonth() + 1).toString();
        var dom = date.getDate().toString();
        if (month.length == 1) month = "0" + month;
        if (dom.length == 1) dom = "0" + dom;
        return date.getFullYear() + "/" + month + "/" + dom;
    },
	
	changeDate: function(dateString) {
		this.change_date_callback(dateString);
    },
	
	selectDate: function(date) {
		if (!date) date = new Date(this.input.attr('alt').split(',')[0]-0 || this.dateToString(new Date()));
		this.selectedDate = date;
		this.selectedDateString = this.dateToString(this.selectedDate);
		this.selectMonth(this.selectedDate);
	},
	
	selectedToHightlight: function(){
		$('.selected', this.tbody).removeClass("selected");
		var btime = this.input.attr('alt').split(',')[0] - 0;
		var length = this.input.attr('alt').split(',')[1];
		if(length){
			for(var i=0; i<length; i++){
				$('td[date="' + this.dateToString(new Date(btime+86400000*i)) + '"]', this.tbody).addClass("selected");
			}
		}else{
			btime && $('td[date="' + this.dateToString(new Date(btime)) + '"]', this.tbody).addClass("selected");
		}
		
	}
	
});
