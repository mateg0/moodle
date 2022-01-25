require(['core/first', 'jquery', 'jqueryui', 'core/ajax', 'core_calendar/calendar'], function (core, $, bootstrap, ajax, calendar) {
    $(document).ready(function () {

        let periods = {
            PREVIOUS: 'previousperiod',
            NEXT: 'nextperiod'
        };

        function paintEvents(){
            const dayEvents = document.getElementsByClassName('c-d-event');
            for (let i = 0; i < dayEvents.length; i++) {
                if (dayEvents[i].dataset.groupid) {
                    const groupId = dayEvents[i].dataset.groupid;
                    const queryLegendGroups = '.legend-group';
                    const legendGroups = document.querySelectorAll(queryLegendGroups);

                    legendGroups.forEach(group => {
                        if (groupId === group.dataset.groupId) {
                            const legendGroupColorQuery = '.legend-group-color';

                            dayEvents[i].style.backgroundColor = group.querySelector(legendGroupColorQuery).style.backgroundColor;
                        }
                    });
                } else {
                    const queryLegendGroups = '.legend-group';
                    const legendGroups = document.querySelectorAll(queryLegendGroups);
                    legendGroups.forEach(group => {
                        if (group.dataset.groupId === "-1") {
                            const legendGroupColorQuery = '.legend-group-color';

                            dayEvents[i].style.backgroundColor = group.querySelector(legendGroupColorQuery).style.backgroundColor;
                        }
                    });
                }
            }
        }

        function getPeriod(which) {
            let previous = document.getElementById('calendarlink-' + which);
            let link = previous.dataset.href;
            let searchParams = new URLSearchParams(link);
            return searchParams.get('time');
        }

        function changecalendarPerionOnClick(period, time) {
            $('#calendarlink-'+ period).click(function(){
                ajax.call([{
                    methodname: 'getcalendarperiod',
                    args: {
                        'view': $('#calendar-view-switch').val(),
                        'time': time
                    },
                }])[0].done(function (response) {
                    const mainCalendar = document.querySelector('.maincalendar');
                    let heightContainer;

                    // clear out old values
                    mainCalendar.innerHTML = '';
                    mainCalendar.innerHTML = response;
                    initCalendarViewControl();
                    initCalendarPeriondsControl();

                    heightContainer = mainCalendar.firstChild;

                    calendar.init(heightContainer.firstChild);
                }).fail(function (err) {
                    console.log(err);
                });
            });
        }

        function initCalendarPeriondsControl(){
            let previousperiodtime = getPeriod(periods.PREVIOUS);
            let nextperiodtime = getPeriod(periods.NEXT);
            changecalendarPerionOnClick(periods.PREVIOUS, previousperiodtime);
            changecalendarPerionOnClick(periods.NEXT, nextperiodtime);
        }

        function initCalendarViewControl(){
            let view = $('#calendar-view-switch').val();
            if(view === 'day') {
                paintEvents();
            }
            $('#calendar-view-switch').change(function(){
                view = $(this).val();
                ajax.call([{
                    methodname: 'getcalendarview',
                    args: {
                        'view': view
                    },
                }])[0].done(function (response) {
                    const mainCalendar = document.querySelector('.maincalendar');
                    let heightContainer;

                    // clear out old values
                    mainCalendar.innerHTML = '';
                    mainCalendar.innerHTML = response;
                    if(view === 'day') {
                        paintEvents();
                    }
                    initCalendarViewControl();
                    initCalendarPeriondsControl();
                    heightContainer = mainCalendar.firstChild;

                    calendar.init(heightContainer.firstChild);
                }).fail(function (err) {
                    console.log(err);
                });
            });
        }

        initCalendarViewControl();
        initCalendarPeriondsControl();
    });
});
