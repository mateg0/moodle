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
        CELL_ADD_EVENT_BUTTON_MONTH: 'div.cell-plus-add-event',
        CELL_ADD_EVENT_BUTTON_DAY: 'div.c-d-add-event',
        ADD_EVENT_FORM: '.add-event',
        ADD_EVENT_FORM_DATA: 'form.add-event-form',
        ADD_EVENT_FORM_ADD_BUTTON: 'div.add-event-add-button',
        ADD_EVENT_FORM_DELETE_BUTTON: 'div.add-event-delete-button',
        EVENT_TYPES: 'div.event-types',
        EVENT_TYPE: 'div.event-type',
        SCHEDULE: '.schedule',
        SCHEDULE_CONTENT: 'div.schedule-content',
        SCHEDULE_ADD_EVENT_BUTTON: 'div.add-schedule-event',
        SCHEDULE_LINE: 'div.schedule-line',
        LEGEND_GROUP: '.legend-group',
        LEGEND_GROUP_COLOR: '.legend-group-color',
        SELECT_COURSE: 'select.select-course',
        SELECT_GROUP: 'select.select-group',
        SELECT_COURSE_WRAPPER: 'div.event-select-course',
        SELECT_GROUP_WRAPPER: 'div.event-select-group',
        BACK_BUTTON_FROM_SELECT: 'div.event-back-image',
    };

    const sessionStorageTimestampNewEventFieldName = 'newEventTimestamp';
    const sessionStorageEventTypeFieldName = 'eventType';
    const sessionStorageEventIdFieldName = 'eventId';

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
            const addEventButtonsOnMonthCell = document.querySelectorAll(SELECTORS.CELL_ADD_EVENT_BUTTON_MONTH);
            const addEventButtonsOnDayCell = document.querySelectorAll(SELECTORS.CELL_ADD_EVENT_BUTTON_DAY);
            const addEventButtonOnAddEventForm = document.querySelector(SELECTORS.ADD_EVENT_FORM_ADD_BUTTON);
            const eventTypesOfAddEventForm = document.querySelector(SELECTORS.EVENT_TYPES);

            const addEventForm = document.querySelector(SELECTORS.ADD_EVENT_FORM);
            const addEventFormBackToSelectType = addEventForm
                .querySelector(SELECTORS.SELECT_COURSE_WRAPPER)
                .querySelector(SELECTORS.BACK_BUTTON_FROM_SELECT);
            const addEventFormBackToSelectCourse = addEventForm
                .querySelector(SELECTORS.SELECT_GROUP_WRAPPER)
                .querySelector(SELECTORS.BACK_BUTTON_FROM_SELECT);

            if (addEventButtonsOnMonthCell.length) {
                addEventListenerToAddEventButtonOnCells(addEventButtonsOnMonthCell);
            }

            if (addEventButtonsOnDayCell.length) {
                addEventListenerToAddEventButtonOnCells(addEventButtonsOnDayCell);
            }

            if (eventTypesOfAddEventForm) {
                eventTypesOfAddEventForm.addEventListener(
                    'click',
                    getSelectorOfEventType(eventTypesOfAddEventForm)
                );
            }

            if (addEventButtonOnAddEventForm) {
                addEventButtonOnAddEventForm.addEventListener('click', submitEvent);
            }

            if (addEventFormBackToSelectType) {
                addEventFormBackToSelectType.addEventListener('click', backToSelectType);
            }

            if (addEventFormBackToSelectCourse) {
                addEventFormBackToSelectCourse.addEventListener('click', backToSelectCourse);
            }

            // Bind click events to calendar days.
            root.on('click', SELECTORS.DAY, function (e) {
                // const target = $(e.target);

                if (e.target.closest(SELECTORS.CELL_ADD_EVENT_BUTTON_MONTH)) {
                    return;
                }

                const calendarWrapper = document.querySelector(SELECTORS.CALENDAR_MONTH_WRAPPER);
                const calendarData = calendarWrapper.dataset;

                if (calendarData.view && calendarData.view !== "day") {
                    const dayCell = e.target.closest('td');
                    const dayCellData = dayCell.dataset;

                    sessionStorage.setItem(sessionStorageTimestampNewEventFieldName, dayCellData.newEventTimestamp);

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
                                const schedule = document.querySelector(SELECTORS.SCHEDULE);
                                const scheduleContent = schedule.querySelector(SELECTORS.SCHEDULE_CONTENT);
                                const scheduleAddEventButton = schedule.querySelector(
                                    SELECTORS.SCHEDULE_ADD_EVENT_BUTTON
                                );

                                scheduleContent.innerHTML = '';

                                for (let event of eventsOfDay) {
                                    let groupColor = '#fee7ae';

                                    if (event.groupid) {
                                        const groupId = event.groupid;

                                        document.querySelectorAll(SELECTORS.LEGEND_GROUP).forEach(group => {
                                            if (groupId === +group.dataset.groupId) {
                                                groupColor = group.querySelector(SELECTORS.LEGEND_GROUP_COLOR).style.backgroundColor;
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

                                    if (event.description) {
                                        scheduleEventDescription.append(event.description);
                                        scheduleEventContent.append(scheduleEventDescription);
                                    }

                                    scheduleLine.append(eventTime, scheduleEventContent);

                                    if (event.groupname) {
                                        scheduleEventGroup.append(event.groupname);

                                        scheduleLine.append(scheduleEventGroup);
                                    }

                                    if (event.canedit) {
                                        scheduleLine.classList.add('can-edit');

                                        scheduleLine.addEventListener('click', showChangeEventForm(event));
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
        if (!event.target.closest(SELECTORS.SCHEDULE)) {
            document.querySelector(SELECTORS.SCHEDULE).style.display = 'none';
            document.removeEventListener('click', hideScheduleEvent);
        }
    };

    const showAddEventForm = (event) => {
        const addEventForm = document.querySelector(SELECTORS.ADD_EVENT_FORM);
        const targetCell = event.target.closest('td');
        const shadowDiv = document.createElement('div');

        shadowDiv.className = "shadow";

        if (targetCell) {
            sessionStorage.setItem(sessionStorageTimestampNewEventFieldName, targetCell.dataset.newEventTimestamp);
        }

        clearEventForm(addEventForm);

        addEventForm.style.display = 'block';

        addEventForm.querySelectorAll('input[type=datetime-local]').forEach(inputDate => {
            const addEventDateTimestamp = +sessionStorage.getItem(sessionStorageTimestampNewEventFieldName) * 1000 ;
            const addEventDateJSON = new Date(addEventDateTimestamp).toJSON();
            const splitedAddEventDateJSON = addEventDateJSON.split(':');

            inputDate.value = `${splitedAddEventDateJSON[0]}:${splitedAddEventDateJSON[1]}`;
        });

        document.body.prepend(shadowDiv);

        setTimeout(() => {
            document.addEventListener('click', hideAddEventForm);
        }, 0);
    };

    const showAddEventFormFromDaySchedule = (event) => {
        document.querySelector(SELECTORS.SCHEDULE).style.display = 'none';

        showAddEventForm(event);
    };

    const showChangeEventForm = (calendarEvent) => {
        return (event) => {
            const changeEventForm = document.querySelector(SELECTORS.ADD_EVENT_FORM);
            const eventNameField = changeEventForm.querySelector('input[name=name]');
            const eventTypes = changeEventForm.querySelector(SELECTORS.EVENT_TYPES);
            const selectCourseWrapper = changeEventForm.querySelector(SELECTORS.SELECT_COURSE_WRAPPER);
            const selectCourse = selectCourseWrapper.querySelector(SELECTORS.SELECT_COURSE);
            const selectGroupWrapper = changeEventForm.querySelector(SELECTORS.SELECT_GROUP_WRAPPER);
            const selectGroup = selectGroupWrapper.querySelector(SELECTORS.SELECT_GROUP);
            const timeStartField = changeEventForm.querySelector('input[name=time-start]');
            const timeEndField = changeEventForm.querySelector('input[name=time-end]');
            const descriptionField = changeEventForm.querySelector('textarea[name=description]');
            const locationField = changeEventForm.querySelector('input[name=location]');

            const deleteEventButton = changeEventForm.querySelector(SELECTORS.ADD_EVENT_FORM_DELETE_BUTTON);

            const splitedTimeStartJSON = new Date(calendarEvent.timestart * 1000).toJSON().split(':');
            const splitedTimeEndJSON = new Date((+calendarEvent.timestart + +calendarEvent.timeduration) * 1000)
                .toJSON()
                .split(':');

            const currentCourseOption = document.createElement('option');
            const currentGroupOption = document.createElement('option');

            const shadowDiv = document.createElement('div');

            shadowDiv.className = "shadow";

            clearEventForm(changeEventForm);

            sessionStorage.setItem(sessionStorageEventIdFieldName, calendarEvent.id);
            sessionStorage.setItem(sessionStorageEventTypeFieldName, calendarEvent.eventtype);

            eventNameField.value = calendarEvent.name;
            timeStartField.value = `${splitedTimeStartJSON[0]}:${splitedTimeStartJSON[1]}`;
            timeEndField.value = `${splitedTimeEndJSON[0]}:${splitedTimeEndJSON[1]}`;

            if (calendarEvent.description) {
                descriptionField.value = calendarEvent.description;
            }

            if (calendarEvent.location) {
                locationField.value = calendarEvent.location;
            }

            eventTypes.querySelectorAll(SELECTORS.EVENT_TYPE).forEach(eventType => {
                if (eventType.dataset.eventType === calendarEvent.eventtype) {
                    eventType.classList.add('active');
                }
            });

            if (calendarEvent.course) {
                const currentCourse = calendarEvent.course;

                currentCourseOption.id = currentCourse.id;
                currentCourseOption.text = currentCourse.fullname;
                currentCourseOption.selected = true;

                selectCourse.append(currentCourseOption);
            }

            if (calendarEvent.groupid) {
                currentGroupOption.id = calendarEvent.groupid;
                currentGroupOption.text = calendarEvent.groupname;
                currentGroupOption.selected = true;

                selectGroup.append(currentGroupOption);
            }

            if (calendarEvent.candelete) {
                deleteEventButton.style.display = 'block';
            }

            changeEventForm.style.display = 'block';
            document.body.prepend(shadowDiv);

            setTimeout(() => {
                deleteEventButton.addEventListener('click', deleteEvent);
                document.addEventListener('click', hideChangeEventForm);
            }, 0);
        }
    };

    const clearEventForm = (eventForm) => {
        const eventNameField = eventForm.querySelector('input[name=name]');
        const eventTypes = eventForm.querySelector(SELECTORS.EVENT_TYPES);
        const selectCourseWrapper = eventForm.querySelector(SELECTORS.SELECT_COURSE_WRAPPER);
        const selectCourse = selectCourseWrapper.querySelector(SELECTORS.SELECT_COURSE);
        const selectGroupWrapper = eventForm.querySelector(SELECTORS.SELECT_GROUP_WRAPPER);
        const selectGroup = selectGroupWrapper.querySelector(SELECTORS.SELECT_GROUP);
        const descriptionField = eventForm.querySelector('textarea[name=description]');
        const locationField = eventForm.querySelector('input[name=location]');

        eventNameField.value = '';
        descriptionField.value = '';
        locationField.value = '';
        selectCourse.innerHTML = '';
        selectGroup.innerHTML = '';

        eventTypes.querySelectorAll(SELECTORS.EVENT_TYPE).forEach(eventType => {
            eventType.classList.remove('active');
        });

        selectGroupWrapper.style.display = 'none';
        selectCourseWrapper.style.display = 'none';
        eventTypes.style.display = 'flex';
    }

    const hideChangeEventForm = (event) => {
        if (!event.target.closest(SELECTORS.ADD_EVENT_FORM)) {
            sessionStorage.clear();
        }

        hideAddEventForm(event);
    }

    const hideAddEventForm = (event) => {
        if (!event.target.closest(SELECTORS.ADD_EVENT_FORM)) {
            document.querySelector(SELECTORS.ADD_EVENT_FORM).style.display = 'none';
            document.querySelector('div.shadow').remove();
            document.removeEventListener('click', hideAddEventForm);
            document.removeEventListener('click', hideChangeEventForm);
        }
    }

    const deleteEvent = () => {
        const eventId = sessionStorage.getItem(sessionStorageEventIdFieldName);

        CalendarRepository.deleteEvent(eventId)
            .then(deletedEvent => {
                window.location.reload();
            })
            .catch(console.error);
    };

    const addEventListenerToAddEventButtonOnCells = (addEventButtonsOnCells) => {
        for (let addEventButtonOnCell of addEventButtonsOnCells) {
            addEventButtonOnCell.addEventListener('click', showAddEventForm);
        }
    }

    const getSelectorOfEventType = (eventTypesDiv) => {
        return (clickEvent) => {
            const eventType = clickEvent.target.closest(SELECTORS.EVENT_TYPE);

            if (eventType) {
                const eventTypeValue = eventType.dataset.eventType;

                eventTypesDiv.querySelectorAll(SELECTORS.EVENT_TYPE).forEach(eventTypeDiv => {
                    eventTypeDiv.classList.remove('active');
                });

                eventType.classList.add('active');
                sessionStorage.setItem(sessionStorageEventTypeFieldName, eventTypeValue);

                switch (eventTypeValue) {
                    case 'group': {
                        document
                            .querySelector(SELECTORS.ADD_EVENT_FORM)
                            .querySelector(SELECTORS.SELECT_COURSE_WRAPPER)
                            .querySelector(SELECTORS.SELECT_COURSE)
                            .addEventListener('change', showSelectGroup);
                    }
                    case 'course': {
                        showSelectCourse();
                    }
                }
            }
        };
    };

    const submitEvent = () => {
        const addEventForm = document.querySelector(SELECTORS.ADD_EVENT_FORM_DATA);

        if (addEventForm) {
            const addEventFormData = new FormData(addEventForm);
            const timeStartDate = new Date(addEventFormData.get('time-start'));
            const timeEndDate = new Date(addEventFormData.get('time-end'));
            const nameOfEvent = addEventFormData.get('name');
            const descriptionOfEvent = addEventFormData.get('description');
            const locationOfEvent = addEventFormData.get('location');

            const eventType = sessionStorage.getItem(sessionStorageEventTypeFieldName);
            const eventId = sessionStorage.getItem(sessionStorageEventIdFieldName);

            if (!eventType) {
                alert('Please, select event type');
                return;
            }

            if (!nameOfEvent) {
                alert('Please, type event name');
                return;
            }

            const formData = {
                id: eventId || 0,
                userid: 0,
                modulename: '',
                instance: 0,
                visible: 1,
                mform_showmore_id_general: 1,
                name: nameOfEvent,
                'timestart[year]': timeStartDate.getFullYear(),
                'timestart[month]': timeStartDate.getMonth() + 1,
                'timestart[day]': timeStartDate.getDate(),
                'timestart[hour]': timeStartDate.getHours(),
                'timestart[minute]': timeStartDate.getMinutes(),
                eventtype: eventType,
                'description[text]': descriptionOfEvent || '',
                'description[format]': 1,
                location: locationOfEvent,
            }

            if (eventId) {
                formData['_qf__core_calendar_local_event_forms_update'] = 1;
            } else {
                formData['_qf__core_calendar_local_event_forms_create'] = 1;
            }

            if (timeEndDate - timeStartDate) {
                formData['duration'] = 1;
                formData['timedurationuntil[year]'] = timeEndDate.getFullYear();
                formData['timedurationuntil[month]'] = timeEndDate.getMonth() + 1;
                formData['timedurationuntil[day]'] = timeEndDate.getDate();
                formData['timedurationuntil[hour]'] = timeEndDate.getHours();
                formData['timedurationuntil[minute]'] = timeEndDate.getMinutes();
            } else {
                formData['duration'] = 0;
            }

            switch (eventType) {
                case 'group': {
                    const courseId = addEventFormData.get('courseId');
                    const groupId = addEventFormData.get('groupId');

                    if (!courseId || courseId === '-1') {
                        alert('Please, select group');
                        return;
                    }

                    if (!groupId || groupId === '-1') {
                        alert('Please, select course');
                        return;
                    }

                    formData['groupcourseid'] = courseId;
                    formData['groupid'] = groupId;

                    break;
                }

                case 'course': {
                    const courseId = addEventFormData.get('courseId');

                    if (!courseId || courseId === '-1') {
                        alert('Please, select group');
                        return;
                    }

                    formData['courseid'] = courseId;

                    break;
                }
            }

            let formUrlencodedData = '';

            for (const keyOfField in formData) {
                formUrlencodedData += `${keyOfField}=${formData[keyOfField]}&`;
            }

            formUrlencodedData = encodeURI(formUrlencodedData);

            sessionStorage.clear();

            CalendarRepository.submitCreateUpdateForm(formUrlencodedData)
                .then(newCreatedEvent => {
                    window.location.reload();
                })
                .catch(error => {
                    console.error(error);
                })
        }
    };

    const backToSelectType = (event) => {
        const courseWrapper = event.target.closest(SELECTORS.SELECT_COURSE_WRAPPER);

        courseWrapper
            .querySelector(SELECTORS.SELECT_COURSE)
            .removeEventListener('change', showSelectGroup);

        courseWrapper.style.display = 'none';
        document.querySelector(SELECTORS.EVENT_TYPES).style.display = 'flex';
    };

    const backToSelectCourse = (event) => {
        const courseWrapper = document.querySelector(SELECTORS.SELECT_COURSE_WRAPPER);

        courseWrapper
            .querySelector(SELECTORS.SELECT_COURSE)
            .addEventListener('change', showSelectGroup);

        event.target.closest(SELECTORS.SELECT_GROUP_WRAPPER).style.display = 'none';
        courseWrapper.style.display = 'flex';
    };

    const showSelectCourse = () => {
        CalendarRepository.getUserCoursesWhereUserIsTeacher()
            .then(courses => {
                const formAddEvent = document.querySelector(SELECTORS.ADD_EVENT_FORM);
                const selectCourseWrapper = formAddEvent.querySelector(SELECTORS.SELECT_COURSE_WRAPPER);
                const selectCourse = selectCourseWrapper.querySelector(SELECTORS.SELECT_COURSE);
                const eventTypesDiv = formAddEvent.querySelector(SELECTORS.EVENT_TYPES);

                setDefaultSelect(selectCourse);

                if (courses.length) {
                    appendOptionsToSelect(selectCourse, courses);
                }

                eventTypesDiv.style.display = 'none';
                selectCourseWrapper.style.display = 'flex';
            })
            .catch(console.error);
    }

    const showSelectGroup = (event) => {
        const courseId = event.target.value;

        CalendarRepository.getCourseGroupsData(courseId)
            .then(groups => {
                const formAddEvent = document.querySelector(SELECTORS.ADD_EVENT_FORM);
                const selectCourseWrapper = formAddEvent.querySelector(SELECTORS.SELECT_COURSE_WRAPPER);
                const selectGroupWrapper = formAddEvent.querySelector(SELECTORS.SELECT_GROUP_WRAPPER);
                const selectCourse = selectCourseWrapper.querySelector(SELECTORS.SELECT_COURSE);
                const selectGroup = selectGroupWrapper.querySelector(SELECTORS.SELECT_GROUP);

                setDefaultSelect(selectGroup);

                selectCourse.removeEventListener('change', showSelectGroup);

                if (groups.length) {
                    appendOptionsToSelect(selectGroup, groups);
                }

                selectCourseWrapper.style.display = 'none';
                selectGroupWrapper.style.display = 'flex';
            })
            .catch(console.error);
    };

    const setDefaultSelect = (select) => {
        const emptyOption = document.createElement('option');
        emptyOption.value = '-1';
        emptyOption.text = '...';

        select.innerHTML = '';
        select.append(emptyOption);
    };

    const appendOptionsToSelect = (select, options) => {
        for (const option of options) {
            const selectOption = document.createElement('option');
            selectOption.value = option.id;
            selectOption.text = option.name || option.fullname;

            select.append(selectOption);
        }
    };

    return {
        init: function(root) {
            root = $(root);

            CalendarViewManager.init(root);
            registerEventListeners(root);
        }
    };
});
