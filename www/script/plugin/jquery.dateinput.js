// jquery.dateinput.js
/*
Date Input 1.2.1
Requires jQuery version: >= 1.2.6

Copyright (c) 2007-2008 Jonathan Leighton & Torchbox Ltd

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.
*/

DateInput = (function($) { 

    function DateInput(el, opts) {
        if (typeof(opts) != "object") opts = {};
        $.extend(this, DateInput.DEFAULT_OPTS, opts);

        this.input = $(el);
        this.bindMethodsToObj("show", "hide", "hideIfClickOutside", "hideIfkeydownTab", "selectDate");

        var that = this;
        this.input.focus(function(){
            if (!that.dateSelector) {
                that.build();
                that.show();
            }
        });
    };
    DateInput.DEFAULT_OPTS = {
        month_names: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
        short_month_names: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
        short_day_names: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
        start_of_week: 1
    };
    DateInput.prototype = {
        build: function() {
            var monthNav = $('<p class="month_nav">' +
                '<span class="arrow prev">&#171;</span>' +
                ' <span class="month_name"></span> ' +
                '<span class="arrow next">&#187;</span>' +
            '</p>');
            this.monthNameSpan = $(".month_name", monthNav);
            $(".prev", monthNav).click(this.bindToObj(function() { this.moveMonthBy(-1); }));
            $(".next", monthNav).click(this.bindToObj(function() { this.moveMonthBy(1); }));

            var yearNav = $('<p class="year_nav">' +
                '<span class="arrow prev">&#171;</span>' +
                ' <span class="year_name"></span> ' +
                '<span class="arrow next">&#187;</span>' +
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

            this.dateSelector = this.rootLayers = $('<div class="date_selector"></div>').append(nav, tableShell).appendTo('body');

            if ($.browser.msie && $.browser.version < 7) {

                this.ieframe = $('<iframe class="date_selector_ieframe" frameborder="0" src="#"></iframe>').insertBefore(this.dateSelector);
                this.rootLayers = this.rootLayers.add(this.ieframe);

                $(".arrow", nav).mouseover(function() { $(this).addClass("hover") });
                $(".arrow", nav).mouseout(function() { $(this).removeClass("hover") });
            };

            this.tbody = $("tbody", this.dateSelector);

            this.input.change(this.bindToObj(function() { this.selectDate(); }));
            this.selectDate();
        },

        selectMonth: function(date) {
            var newMonth = new Date(date.getFullYear(), date.getMonth(), 1);

            if (!this.currentMonth || !(this.currentMonth.getFullYear() == newMonth.getFullYear() &&
            this.currentMonth.getMonth() == newMonth.getMonth())) {

                this.currentMonth = newMonth;

                var rangeStart = this.rangeStart(date), rangeEnd = this.rangeEnd(date);
                var numDays = this.daysBetween(rangeStart, rangeEnd);
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
                    this.changeInput($(event.target).attr("date"));
                }));

                $("td[date='" + this.dateToString(new Date()) + "']", this.tbody).addClass("today");

                $("td.selectable_day", this.tbody).mouseover(function() { $(this).addClass("hover") });
                $("td.selectable_day", this.tbody).mouseout(function() { $(this).removeClass("hover") });
            };

            $('.selected', this.tbody).removeClass("selected");
            $('td[date="' + this.selectedDateString + '"]', this.tbody).addClass("selected");
        },

        selectDate: function(date) {
            if (typeof(date) == "undefined") {
                date = this.stringToDate(this.input.val());
            };
            if (!date) date = new Date();

            this.selectedDate = date;
            this.selectedDateString = this.dateToString(this.selectedDate);
            this.selectMonth(this.selectedDate);
        },

        changeInput: function(dateString) {
            this.input.val(dateString).change();
            this.hide();
        },

        show: function() {
            this.rootLayers.css("display", "block");
            $([window, document.body]).keydown(this.hideIfkeydownTab);
            $([window, document.body]).click(this.hideIfClickOutside);
            this.input.unbind("focus", this.show);
            this.setPosition();
        },

        hide: function() {
            this.rootLayers.css("display", "none");
            $([window, document.body]).unbind('keydown', this.hideIfkeydownTab);
            $([window, document.body]).unbind("click", this.hideIfClickOutside);
            this.input.focus(this.show);
        },

        hideIfkeydownTab: function(event) {
            if (event.keyCode == 9) {
                this.hide();
            }
        },

        hideIfClickOutside: function(event) {
            if (event.target != this.input[0] && !this.insideSelector(event)) {
                this.hide();
            };
        },

        insideSelector: function(event) {
            var offset = this.dateSelector.position();
            offset.right = offset.left + this.dateSelector.outerWidth();
            offset.bottom = offset.top + this.dateSelector.outerHeight();

            return event.pageY < offset.bottom &&
            event.pageY > offset.top &&
            event.pageX < offset.right &&
            event.pageX > offset.left;
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

        setPosition: function() {
            var offset = this.input.offset();
            this.rootLayers.css({
                top: offset.top + this.input.outerHeight(),
                left: offset.left
            });

            if (this.ieframe) {
                this.ieframe.css({
                    width: this.dateSelector.outerWidth(),
                    height: this.dateSelector.outerHeight()
                });
            };
        },

        moveDateBy: function(amount) {
            var newDate = new Date(this.selectedDate.getFullYear(), this.selectedDate.getMonth(), this.selectedDate.getDate() + amount);
            this.selectDate(newDate);
        },

        moveDateMonthBy: function(amount) {
            var newDate = new Date(this.selectedDate.getFullYear(), this.selectedDate.getMonth() + amount, this.selectedDate.getDate());
            if (newDate.getMonth() == this.selectedDate.getMonth() + amount + 1) {

                newDate.setDate(0);
            };
            this.selectDate(newDate);
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
            };
            return newDays;
        }
    };

    $.fn.dateinput = function(opts) {
        return this.each(function() {new DateInput(this, opts);});
    };
    $.datepicker = { init: function(opts) {
        $("input.input-datepicker").dateinput(opts);
    } };

    return DateInput;
})(jQuery); 


jQuery.extend(DateInput.DEFAULT_OPTS, {
    month_names: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"],
    short_month_names: ["一", "二", "三", "四", "五", "六", "七", "八", "九", "十", "十一", "十二"],
    short_day_names: ["日", "一", "二", "三", "四", "五", "六"]
});

jQuery.extend(DateInput.DEFAULT_OPTS, {
    stringToDate: function(string) {
        var matches;
        if (matches = string.match(/^(\d{4,4})-(\d{2,2})-(\d{2,2})$/)) {
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
        return date.getFullYear() + "-" + month + "-" + dom;
    }
});