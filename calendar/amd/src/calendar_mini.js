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
 * @module     core_calendar/calendar_mini
 * @package    core_calendar
 * @copyright  2017 Andrew Nicols <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([
    'jquery',
    'core_calendar/selectors',
    'core_calendar/events',
    'core_calendar/view_manager',
    'core_calendar/repository',
],
function(
    $,
    CalendarSelectors,
    CalendarEvents,
    CalendarViewManager,
    CalendarRepository,
) {

    const courseIdFieldName = 'filterCourseId';
    /**
     * Listen to and handle any calendar events fired by the calendar UI.
     *
     * @method registerCalendarEventListeners
     * @param {object} root The calendar root element
     */
    var registerCalendarEventListeners = function(root) {
        var body = $('body');
        var namespace = '.' + root.attr('id');

        body.on(CalendarEvents.created + namespace, root, reloadMonth);
        body.on(CalendarEvents.deleted + namespace, root, reloadMonth);
        body.on(CalendarEvents.updated + namespace, root, reloadMonth);
        body.on(CalendarEvents.eventMoved + namespace, root, reloadMonth);
    };

    /**
     * Reload the month view in this month.
     *
     * @param {EventFacade} e
     */
    var reloadMonth = function(e) {
        var root = e.data;
        var body = $('body');
        var namespace = '.' + root.attr('id');

        if (root.is(':visible')) {
            CalendarViewManager
                .reloadCurrentMonth(root)
                .then(setEventListeners)
            ;
        } else {
            // The root has been removed.
            // Remove all events in the namespace.
            body.off(CalendarEvents.created + namespace);
            body.off(CalendarEvents.deleted + namespace);
            body.off(CalendarEvents.updated + namespace);
            body.off(CalendarEvents.eventMoved + namespace);
        }
    };

    var registerEventListeners = function(root) {
        $('body').on(CalendarEvents.filterChanged, function(e, data) {
            var daysWithEvent = root.find(CalendarSelectors.eventType[data.type]);

            daysWithEvent.toggleClass('calendar_event_' + data.type, !data.hidden);
        });

        var namespace = '.' + root.attr('id');
        $('body').on('change' + namespace, CalendarSelectors.elements.courseSelector, function() {
            if (root.is(':visible')) {
                var selectElement = $(this);
                var courseId = selectElement.val();
                var categoryId = null;

                CalendarViewManager.reloadCurrentMonth(root, courseId, categoryId);
            } else {
                $('body').off('change' + namespace);
            }
        });
    };

    const setEventListeners = () => {
        setEventListenerToNavLinks();
        setEventListenerToFilterIcon();
    }

    const setEventListenerToFilterIcon = () => {
        document
            .querySelector('.minicalendar-filter-by-course')
            .querySelector('.filter-icon')
            .addEventListener('click', toggleFilterContext);
    };

    const setEventListenerToNavLinks = () => {
        document
            .querySelector('table.minicalendar')
            .querySelectorAll('a.arrow-link')
            .forEach(navLink => {
                navLink.addEventListener('click', changeMonth);
            });
    }

    const toggleFilterContext = (event) => {
        const filterContext = document
            .querySelector('section.minicalendar-filter-by-course')
            .querySelector('div.minicalendar-filter-context');
        const selectCourse = filterContext.querySelector('select.select-course');

        if (filterContext.style.display === 'none') {
            const courseId = sessionStorage.getItem(courseIdFieldName);

            CalendarRepository
                .getUserCourses()
                .then((courses) => {
                    resetSelect(selectCourse);

                    courses.forEach(course => {
                        const courseOption = document.createElement('option');
                        courseOption.text = course.fullname;
                        courseOption.value = course.id;

                        if (course.id === +courseId) {
                            courseOption.selected = true;
                        }

                        selectCourse.append(courseOption);
                    });

                    selectCourse.addEventListener('change', reloadMonthByCourseId);

                    filterContext.style.display = 'block';
                });
        } else {
            selectCourse.removeEventListener('change', reloadMonthByCourseId);

            filterContext.style.display = 'none';
        }
    };

    const resetSelect = (select) => {
        const allCoursesOption = document.createElement('option');
        allCoursesOption.text = '?????? ??????????';
        allCoursesOption.value = '1';

        select.innerHTML = '';

        select.append(allCoursesOption);
    };

    const reloadMonthByCourseId = (event) => {
        const courseId = event.target.value;
        const miniCalendar = document
            .querySelector('table.minicalendar')
            .closest('div.calendarwrapper')
            .parentElement;

        sessionStorage.setItem(courseIdFieldName, courseId);

        CalendarViewManager
            .reloadCurrentMonth($(miniCalendar), courseId)
            .then(setEventListeners)
        ;
    };

    const changeMonth = (event) => {
        const calendarWrapper = document
            .querySelector('table.minicalendar')
            .closest('div.calendarwrapper');
        const calendar = $(calendarWrapper.parentElement);
        const courseId = calendarWrapper.dataset.courseid;
        const categoryId = calendarWrapper.dataset.categoryid;
        const link = event.currentTarget;

        CalendarViewManager
            .changeMonth(
                calendar,
                link.href,
                link.dataset.year,
                link.dataset.month,
                courseId,
                categoryId,
                link.dataset.day
            )
            .then(setEventListeners);
        event.preventDefault();
    }

    return {
        init: function(root, loadOnInit) {
            root = $(root);

            CalendarViewManager.init(root);
            registerEventListeners(root);
            registerCalendarEventListeners(root);

            if (loadOnInit) {
                // The calendar hasn't yet loaded it's events so we
                // should load them as soon as we've initialised.
                CalendarViewManager
                    .reloadCurrentMonth(root)
                    .then(setEventListeners)
                ;
            }

        }
    };
});
