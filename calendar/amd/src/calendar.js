// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This module is the highest level module for the calendar. It is
 * responsible for initialising all of the components required for
 * the calendar to run. It also coordinates the interaction between
 * components by listening for and responding to different events
 * triggered within the calendar UI.
 *
 * @module     core_calendar/calendar
 * @package    core_calendar
 * @copyright  2017 Simey Lameze <simey@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([
            'jquery',
            'core/ajax',
            'core/str',
            'core/templates',
            'core/notification',
            'core/custom_interaction_events',
            'core/modal_events',
            'core/modal_factory',
            'core_calendar/modal_event_form',
            'core_calendar/summary_modal',
            'core_calendar/repository',
            'core_calendar/events',
            'core_calendar/view_manager',
            'core_calendar/crud',
            'core_calendar/selectors',
        ],
        function(
            $,
            Ajax,
            Str,
            Templates,
            Notification,
            CustomEvents,
            ModalEvents,
            ModalFactory,
            ModalEventForm,
            SummaryModal,
            CalendarRepository,
            CalendarEvents,
            CalendarViewManager,
            CalendarCrud,
            CalendarSelectors
        ) {

    var SELECTORS = {
        ROOT: "[data-region='calendar']",
        DAY: "[data-region='day']",
        NEW_EVENT_BUTTON: "[data-action='new-event-button']",
        DAY_CONTENT: "[data-region='day-content']",
        LOADING_ICON: '.loading-icon',
        VIEW_DAY_LINK: "[data-action='view-day-link']",
        CALENDAR_MONTH_WRAPPER: ".calendarwrapper",
        TODAY: '.today',
    };

    /**
     * Handler for the drag and drop move event. Provides a loading indicator
     * while the request is sent to the server to update the event start date.
     *
     * Triggers a eventMoved calendar javascript event if the event was successfully
     * updated.
     *
     * @param {event} e The calendar move event
     * @param {int} eventId The event id being moved
     * @param {object|null} originElement The jQuery element for where the event is moving from
     * @param {object} destinationElement The jQuery element for where the event is moving to
     */
    var handleMoveEvent = function(e, eventId, originElement, destinationElement) {
        var originTimestamp = null;
        var destinationTimestamp = destinationElement.attr('data-day-timestamp');

        if (originElement) {
            originTimestamp = originElement.attr('data-day-timestamp');
        }

        // If the event has actually changed day.
        if (!originElement || originTimestamp != destinationTimestamp) {
            Templates.render('core/loading', {})
                .then(function(html, js) {
                    // First we show some loading icons in each of the days being affected.
                    destinationElement.find(SELECTORS.DAY_CONTENT).addClass('hidden');
                    Templates.appendNodeContents(destinationElement, html, js);

                    if (originElement) {
                        originElement.find(SELECTORS.DAY_CONTENT).addClass('hidden');
                        Templates.appendNodeContents(originElement, html, js);
                    }
                    return;
                })
                .then(function() {
                    // Send a request to the server to make the change.
                    return CalendarRepository.updateEventStartDay(eventId, destinationTimestamp);
                })
                .then(function() {
                    // If the update was successful then broadcast an event letting the calendar
                    // know that an event has been moved.
                    $('body').trigger(CalendarEvents.eventMoved, [eventId, originElement, destinationElement]);
                    return;
                })
                .always(function() {
                    // Always remove the loading icons regardless of whether the update
                    // request was successful or not.
                    var destinationLoadingElement = destinationElement.find(SELECTORS.LOADING_ICON);
                    destinationElement.find(SELECTORS.DAY_CONTENT).removeClass('hidden');
                    Templates.replaceNode(destinationLoadingElement, '', '');

                    if (originElement) {
                        var originLoadingElement = originElement.find(SELECTORS.LOADING_ICON);
                        originElement.find(SELECTORS.DAY_CONTENT).removeClass('hidden');
                        Templates.replaceNode(originLoadingElement, '', '');
                    }
                    return;
                })
                .fail(Notification.exception);
        }
    };

    /**
     * Listen to and handle any calendar events fired by the calendar UI.
     *
     * @method registerCalendarEventListeners
     * @param {object} root The calendar root element
     * @param {object} eventFormModalPromise A promise reolved with the event form modal
     */
    var registerCalendarEventListeners = function(root, eventFormModalPromise) {
        var body = $('body');

        body.on(CalendarEvents.created, function() {
            CalendarViewManager.reloadCurrentMonth(root);
        });
        body.on(CalendarEvents.deleted, function() {
            CalendarViewManager.reloadCurrentMonth(root);
        });
        body.on(CalendarEvents.updated, function() {
            CalendarViewManager.reloadCurrentMonth(root);
        });
        body.on(CalendarEvents.editActionEvent, function(e, url) {
            // Action events needs to be edit directly on the course module.
            window.location.assign(url);
        });
        // Handle the event fired by the drag and drop code.
        body.on(CalendarEvents.moveEvent, handleMoveEvent);
        // When an event is successfully moved we should updated the UI.
        body.on(CalendarEvents.eventMoved, function() {
            CalendarViewManager.reloadCurrentMonth(root);
        });

        CalendarCrud.registerEditListeners(root, eventFormModalPromise);
    };

    /**
     * Register event listeners for the module.
     *
     * @param {object} root The calendar root element
     */
    var registerEventListeners = function(root) {
        // Listen the click on the day link to render the day view.
        root.on('click', SELECTORS.VIEW_DAY_LINK, function(e) {
            var dayLink = $(e.target);
            var year = dayLink.data('year'),
                month = dayLink.data('month'),
                day = dayLink.data('day'),
                courseId = dayLink.data('courseid'),
                categoryId = dayLink.data('categoryid');
            CalendarViewManager.refreshDayContent(root, year, month, day, courseId, categoryId, root,
                    'core_calendar/calendar_day').then(function() {
                e.preventDefault();
                var url = '?view=day&time=' + dayLink.data('timestamp');
                return window.history.pushState({}, '', url);
            }).fail(Notification.exception);
        });

        root.on('change', CalendarSelectors.elements.courseSelector, function() {
            var selectElement = $(this);
            var courseId = selectElement.val();
            CalendarViewManager.reloadCurrentMonth(root, courseId, null)
                .then(function() {
                    // We need to get the selector again because the content has changed.
                    return root.find(CalendarSelectors.elements.courseSelector).val(courseId);
                })
                .fail(Notification.exception);
        });

        var eventFormPromise = CalendarCrud.registerEventFormModal(root),
            contextId = $(SELECTORS.CALENDAR_MONTH_WRAPPER).data('context-id');
        registerCalendarEventListeners(root, eventFormPromise);

        if (contextId) {
            const addEventButtonsOnCell = document.querySelectorAll('div.cell-plus-add-event');

            const getDiv = (className) => {
                const newDiv = document.createElement('div');

                newDiv.className = className || '';

                return newDiv;
            }

            const getDivWithStyles = (className, styles) => {
                const newDiv = getDiv(className);

                for (let style in styles) {
                    newDiv.style.setProperty(style, styles[style]);
                }

                return newDiv;
            };

            const getScheduleLine = (groupColor) => {
                return getDivWithStyles('schedule-line', {
                    borderColor: groupColor,
                    background: '#EFFAFF'
                });
            };

            const getScheduleEventContent = () => {
                return getDiv('schedule-event-content');
            };

            const getScheduleEventName = () => {
                return getDiv('schedule-event-name');
            };

            const getScheduleEventDescription = () => {
                return getDiv('schedule-event-description');
            };

            const getScheduleEventGroup = (groupColor) => {
                return getDivWithStyles('schedule-event-group', {
                    background: groupColor
                });
            };

            const getEventTime = (groupColor) => {
                return getDivWithStyles('event-time', {
                    background: groupColor
                });
            };

            const getEventTimeStart = () => {
                return getDiv('event-time-start');
            };

            const getEventTimeEnd = () => {
                return getDiv('event-time-end');
            };

            const getHoursAndMinutesByTimestamp = (timestamp) => {
                const fixDateIfUnderTen = (time) => {
                    return time < 10 ? '0' + time : time;
                }

                const dateByTimestamp = new Date(timestamp);

                let dateHours = fixDateIfUnderTen(dateByTimestamp.getHours());
                let dateMinutes = fixDateIfUnderTen(dateByTimestamp.getMinutes());

                return `${dateHours}:${dateMinutes}`;
            };

            const hideScheduleEvent = (event) => {
                const scheduleQuery = '.schedule';

                if (!event.target.closest(scheduleQuery)) {
                    document.querySelector(scheduleQuery).style.display = 'none';
                    document.removeEventListener('click', hideScheduleEvent);
                }
            };

            const showAddEventForm = () => {
                const addEventFormQuery = 'div.add-event';

                document.querySelector(addEventFormQuery).style.display = 'block';

                setTimeout(() => {
                    document.addEventListener('click', hideAddEventForm);
                }, 0);
            }

            const showAddEventFormFromDaySchedule = () => {
                const scheduleQuery = '.schedule';

                document.querySelector(scheduleQuery).style.display = 'none';

                showAddEventForm();
            };

            const hideAddEventForm = (event) => {
                const addEventFormQuery = 'div.add-event';

                if (!event.target.closest(addEventFormQuery)) {
                    document.querySelector(addEventFormQuery).style.display = 'none';
                    document.removeEventListener('click', hideAddEventForm);
                }
            }

            for (let addEventButtonOnCell of addEventButtonsOnCell) {
                addEventButtonOnCell.addEventListener('click', showAddEventForm);
            }

            // Bind click events to calendar days.
            root.on('click', SELECTORS.DAY, function (e) {
                // const target = $(e.target);

                const addEventFormQuery = 'div.add-event';

                if (e.target.closest(addEventFormQuery)) {
                    return;
                }

                const dayCell = e.target.closest('td');
                const calendarWrapper = document.querySelector(SELECTORS.CALENDAR_MONTH_WRAPPER);

                const dayCellData = dayCell.dataset;
                const calendarData = calendarWrapper.dataset;

                if (calendarData.view && calendarData.view !== "day") {
                    CalendarRepository.getCalendarDayData(
                        calendarData.year,
                        calendarData.month,
                        dayCellData.day,
                        calendarData.courseid,
                        0
                    )
                        .then(dayData => {
                            const eventsOfDay = dayData.events;

                            if (eventsOfDay.length) {
                                const schedule = document.querySelector('.schedule');
                                const scheduleContent = schedule.querySelector('div.schedule-content');
                                const scheduleAddEventButton = schedule.querySelector('div.add-schedule-event');

                                scheduleContent.innerHTML = '';

                                for (let event of eventsOfDay) {
                                    let groupColor = '#fee7ae';

                                    if (event.groupid) {
                                        const groupId = event.groupid;
                                        const queryLegendGroups = '.legend-group';

                                        document.querySelectorAll(queryLegendGroups).forEach(group => {
                                            if (groupId === +group.dataset.groupId) {
                                                const legendGroupColorQuery = '.legend-group-color';
                                                groupColor = group.querySelector(legendGroupColorQuery).style.backgroundColor;
                                            }
                                        });
                                    }

                                    const scheduleLine = getScheduleLine(groupColor);
                                    const scheduleEventContent = getScheduleEventContent();
                                    const scheduleEventName = getScheduleEventName();
                                    const scheduleEventDescription = getScheduleEventDescription();
                                    const scheduleEventGroup = getScheduleEventGroup(groupColor);

                                    const eventTime = getEventTime(groupColor);
                                    const eventTimeStart = getEventTimeStart();
                                    const eventTimeEnd = getEventTimeEnd();

                                    //To fix moodle's timestamp
                                    const timestampStartEvent = event.timestart * 1000;
                                    const timestampEndEvent = ( event.timestart + event.timeduration ) * 1000;

                                    const timeStartEvent = getHoursAndMinutesByTimestamp(timestampStartEvent);
                                    const timeEndEvent = getHoursAndMinutesByTimestamp(timestampEndEvent);

                                    eventTimeStart.append(timeStartEvent);
                                    eventTimeEnd.append(timeEndEvent);

                                    eventTime.append(eventTimeStart, eventTimeEnd);

                                    scheduleEventName.append(event.popupname);

                                    scheduleEventContent.append(scheduleEventName);

                                    scheduleLine.append(eventTime, scheduleEventContent);

                                    if (event.groupname) {
                                        scheduleEventGroup.append(event.groupname);

                                        scheduleLine.append(scheduleEventGroup);
                                    }

                                    scheduleContent.append(scheduleLine);
                                }

                                schedule.style.display = 'block';

                                document.addEventListener('click', hideScheduleEvent);
                                scheduleAddEventButton.addEventListener('click', showAddEventFormFromDaySchedule);
                            }
                        })
                        .catch(error => {
                            console.error('GET day data', error);

                            alert('GET day data ERROR. Please, report administrator')
                        });
                }

                /*if (!target.is(SELECTORS.VIEW_DAY_LINK)) {
                    var startTime = $(this).attr('data-new-event-timestamp');
                    eventFormPromise.then(function (modal) {
                        var wrapper = target.closest(CalendarSelectors.wrapper);
                        modal.setCourseId(wrapper.data('courseid'));

                        var categoryId = wrapper.data('categoryid');
                        if (typeof categoryId !== 'undefined') {
                            modal.setCategoryId(categoryId);
                        }

                        modal.setContextId(wrapper.data('contextId'));
                        modal.setStartTime(startTime);
                        modal.show();
                        return;
                    })
                    .fail(Notification.exception);

                    e.preventDefault();
                }*/
            });
        }
    };

    return {
        init: function(root) {
            root = $(root);

            console.log(root);

            CalendarViewManager.init(root);
            registerEventListeners(root);
        }
    };
});
