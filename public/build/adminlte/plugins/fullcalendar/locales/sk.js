(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() :
    typeof define === 'function' && define.amd ? define(factory) :
    (global = global || self, (global.FullCalendarLocales = global.FullCalendarLocales || {}, global.FullCalendarLocales.sk = factory()));
}(this, function () { 'use strict';

    var sk = {
        code: "sk",
        week: {
            dow: 1,
            doy: 4 // The week that contains Jan 4th is the first week of the year.
        },
        buttonText: {
            prev: "Predchádzaj&uacute;ci",
            next: "Nasleduj&uacute;ci",
            today: "Dnes",
            month: "Mesiac",
            week: "Týždeň",
            day: "Deň",
            list: "Rozvrh"
        },
        weekLabel: "Ty",
        allDayText: "Celý deň",
        eventLimitText: function (n) {
            return "+ďalšie: " + n;
        },
        noEventsMessage: "Žiadne akcie na zobrazenie"
    };

    return sk;

}));
