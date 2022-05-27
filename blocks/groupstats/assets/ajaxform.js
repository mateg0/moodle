require(['core/first', 'jquery', 'jqueryui', 'core/ajax'], function (core, $, bootstrap, ajax) {
    $(document).ready(function () {
        //#endregion

        let blankFlag = false;

        /*
        document.getElementById('gs-summary-switch').addEventListener('click', ()=>{
            document.getElementById('gs-summary').classList.toggle('active');
            document.getElementById('gs-cs-form-header').classList.toggle('hide');
            document.getElementById('gs-block-groupstats-holder').classList.toggle('hide');

            let blank = document.getElementById('gs-group-stats-blank');
            if(!blank.classList.contains('hide') && !blankFlag){
                blank.classList.add('hide');
                blankFlag = true;
            } else if(blankFlag){
                blankFlag = false;
                blank.classList.remove('hide');
            }
        });

        */

        let activeCourseElement;
        let activeGroupElement;

        const courceSelect = document.getElementById('gs-cs-gmsc-select');
        const courceList = document.getElementById('gs-cs-gmsc-list');
        const groupSelect = document.getElementById('gs-cs-gms-select');
        const groupList = document.getElementById('gs-cs-gms-list');
        const courceListElements = document.getElementsByClassName('gs-cs-cource-line');
        const groupListElements = document.getElementsByClassName('gs-cs-group-line');
        //const groupListElements = document.getElementsByClassName('gs-group-line');
        const courseSearch = document.getElementById('gs-cource-search');

        /**
         * Is number of part pie students under custom percent of all students
         * @param {number} pieStudents - number of part pie students
         * @param {number} allStudents - number of all students
         * @param {number} customPercent - custom percent for check part pie students percent
         * @return {boolean} under or not part pie students percent
         * */
         function isPartPieUnderCustomPercent(pieStudents, allStudents, customPercent = 20) {
            return ((pieStudents / allStudents) * 100 ) < customPercent;
        }

        /**
         * Return output part pie string
         * @param {string} label - name of part pie label
         * @param {number} students - number of part pie students
         * @return {string} part pie string
         */
        function getOutputPartPieString(label, students) {
            return `${label}\n${students} Чел.`;
        }

        // eslint-disable-next-line require-jsdoc
        function getDataSectionOf(element) {
            return element.getElementsByClassName('gs-cs-data-section')[0];
        }

        function getTextSectionOf(element, className) {
            return element.getElementsByClassName('gs-cs-data-section')[0].getElementsByClassName(className)[0];
        }

        // eslint-disable-next-line require-jsdoc
        function toggleList(list) {
            list.classList.toggle('hide');
            list.classList.toggle('show');
        }

        // eslint-disable-next-line require-jsdoc
        function closeList(select, list) {
            select.classList.remove('active');
            list.classList.remove('show');
            list.classList.add('hide');
        }

        // eslint-disable-next-line require-jsdoc
        function setnewvalue(id) {
            $('input[name = gs-csgm_groupid]').val(id);
        }

        // eslint-disable-next-line require-jsdoc
        function addEventCloseListOnDocumetClick(select, list) {
            document.addEventListener('click', function (e) {
                if (e.target.classList.contains('non-click')) {
                    return;
                }
                closeList(select, list);
            });
            document.addEventListener('focus', function () {
                closeList(select, list);
            });
        }

        function initSearch(search, listElements, className) {
            if (search == null) {
                return;
            }
            search.addEventListener('input', function (e) {
                if (e.keyCode === 13) {
                    return false;
                }
                let clear = false;
                let activeElement;
                if (!search.value) {
                    clear = true;
                }
                for (let i = 0; i < listElements.length; i++) {
                    activeElement = getTextSectionOf(listElements[i], className);
                    if (!clear && !activeElement.innerText.toLowerCase().includes(search.value.toLowerCase())) {
                        if (listElements[i].classList.contains('current-line')) {
                            continue;
                        }
                        listElements[i].style.display = 'none';
                        continue;
                    }
                    listElements[i].style = '';
                }
            });
        }

        // eslint-disable-next-line require-jsdoc
        function initList(select, list, listElements, activeElement) {
            addEventCloseListOnDocumetClick(select, list);
            for (let i = 0; i < listElements.length; i++) {
                if (listElements[i].classList.contains('current-line')) {
                    activeElement = getDataSectionOf(listElements[i]);
                    select.addEventListener('click', function (e) {
                        e.stopPropagation();
                        if (!select.classList.contains('active')) {
                            let event = new Event('focus', { bubbles: true });
                            document.dispatchEvent(event);
                        }
                        select.classList.toggle('active');
                        toggleList(list);
                    });
                } else {
                    listElements[i].addEventListener('click', function (e) {
                        e.stopPropagation();
                        activeElement.innerHTML = getDataSectionOf(e.currentTarget).innerHTML;
                        closeList(select, list);
                        let event = new Event('change', { bubbles: true });
                        select.dispatchEvent(event);
                    });
                }
            }
        }

        function getStyleFromElement(id){
            let styleElement = document.getElementById(id);
            let style = window.getComputedStyle(styleElement);
            return style.getPropertyValue('background-color');
        }

        initList(courceSelect, courceList, courceListElements, activeCourseElement);
        initSearch(courseSearch, courceListElements, 'gs-cs-cource-name');
        initList(groupSelect, groupList, groupListElements, activeGroupElement);

        const currentLine = document.getElementById('gs-cs-gmsc-select');
        let autochange = false;

        $('#gs-cs-gmsc-select').on('change', function () {
            $('#gs-block-groupstats-holder').html('');
            $('#gs-block-groupstats-holder').css({ 'display': 'none' });
            $('#gs-group-stats-blank').css({ 'display': 'block' });
            let selectedcourseline = document.getElementById('gs-cs-data-section').getElementsByClassName('gs-cs-cource-name').item(0);
            let selectedcourseid = selectedcourseline.getAttribute('data-id');
            ajax.call([{
                methodname: 'gs_getgroupsbycourseid',
                args: {
                    'courseid': selectedcourseid
                },
            }])[0].done(function (response) {
                // clear out old values
                $('#gs-cs-groups-holder').html('');
                $('#gs-cs-groups-holder').append(response);

                const newGroupSelect = document.getElementById('gs-cs-gms-select');
                const newGroupList = document.getElementById('gs-cs-gms-list');
                const newGroupListElements = document.getElementsByClassName('gs-cs-group-line');
                const groupSearch = document.getElementById('gs-group-search');

                initList(newGroupSelect, newGroupList, newGroupListElements, activeGroupElement);
                initSearch(groupSearch, newGroupListElements, 'gs-cs-group-name');

                $('#gs-cs-gms-select').on('change', function () {
                    let selectedgroupline = document.getElementById('gs-cs-group-data-section').getElementsByClassName('gs-cs-group-name').item(0);
                    let selectedgroupid = selectedgroupline.getAttribute('data-id');

                    setnewvalue(selectedgroupid);
                    ajax.call([{
                        methodname: 'gs_getgroupstatsbygroupid',
                        args: {
                            'groupid': selectedgroupid
                        },
                    }])[0].done(function (response) {
                        $('#gs-block-groupstats-holder').html('');
                        $('#gs-block-groupstats-holder').append(response);

                        let hoverColor = getStyleFromElement("hover-color");
                        let badSectorColor = getStyleFromElement("bss-color");
                        let okaySectorColor = getStyleFromElement("oss-color");
                        let goodSectorColor = getStyleFromElement("gss-color");
                        let wellSectorColor = getStyleFromElement("wss-color");

                        let currentSlideIndex = 0;
                        let lastSlideIndex = 0;
                        let controllerLeft = document.getElementById('la-switch'); //id here
                        let controllerRight = document.getElementById('ra-switch'); //id here
                        function slideLeft(controller, titles, items) {
                            controller.addEventListener('click', () => {
                                lastSlideIndex = currentSlideIndex;
                                currentSlideIndex--;
                                if (currentSlideIndex < 0) {
                                    currentSlideIndex = 3;
                                }
                                titles[lastSlideIndex].classList.add('hide');
                                titles[currentSlideIndex].classList.remove('hide');
                                items[lastSlideIndex].classList.add('hide');
                                items[currentSlideIndex].classList.remove('hide');
                            });
                        }

                        function slideRight(controller, titles, items) {
                            controller.addEventListener('click', () => {
                                lastSlideIndex = currentSlideIndex;
                                currentSlideIndex++;
                                if (currentSlideIndex > 3) {
                                    currentSlideIndex = 0;
                                }
                                titles[lastSlideIndex].classList.add('hide');
                                titles[currentSlideIndex].classList.remove('hide');
                                items[lastSlideIndex].classList.add('hide');
                                items[currentSlideIndex].classList.remove('hide');
                            });
                        }

                        slideMenuIds = ['wl-stdnts', 'gd-stdnts', 'ok-stdnts', 'bd-stdnts']; //ids here
                        slideMenuItemsIds = ['well-students-list', 'good-students-list', 'okay-students-list', 'bad-students-list']
                        let slideMenuTitles = [];
                        let slideMenuItems = [];
                        for (let i = 0; i < 4; i++) { //diff value here
                            slideMenuTitles.push(document.getElementById(slideMenuIds[i]));
                            slideMenuItems.push(document.getElementById(slideMenuItemsIds[i]));
                        }

                        slideLeft(controllerLeft, slideMenuTitles, slideMenuItems);
                        slideRight(controllerRight, slideMenuTitles, slideMenuItems);



                        //let groupmemberscount = document.getElementById('gs-groupmemberscount');
                        let wellstudentscount = document.getElementById('gs-wellstudentscount').innerHTML;
                        let goodstudentscount = document.getElementById('gs-goodstudentscount').innerHTML;
                        let okaystudentscount = document.getElementById('gs-okaystudentscount').innerHTML;
                        let badstudentscount = document.getElementById('gs-badstudentscount').innerHTML;
                        /*let wellattendingstudentscount = document.getElementById('gs-wellattendingstudentscount').innerHTML;
                        let goodattendingstudentscount = document.getElementById('gs-goodattendingstudentscount').innerHTML;
                        let okayattendingstudentscount = document.getElementById('gs-okayattendingstudentscount').innerHTML;
                        let badattendingstudentscount = document.getElementById('gs-badattendingstudentscount').innerHTML;*/
                        let paystudentscount = document.getElementById('gs-paystudentscount').innerHTML;
                        let didntpaystudentscount = document.getElementById('gs-didntpaystudentscount').innerHTML;

                        const formatPie = (countPartPieStudents, context) => {
                            const countAllStudents = +goodstudentscount + +okaystudentscount
                                + +badstudentscount + +wellstudentscount;

                            const label = context.chart.data.labels[context.dataIndex];

                            let resultString = '';

                            if (countPartPieStudents === 0 || isPartPieUnderCustomPercent(countPartPieStudents, countAllStudents)) {
                                resultString = '';
                            } else {
                                resultString = getOutputPartPieString(label, countPartPieStudents);
                            }
                            
                            return resultString;
                        };

                        const dataLabelsPerformancePieConfig = {
                            anchor: 'end',
                            align: 'start',
                            offset: 0,
                            textAlign : 'center',
                            font: {
                                weight: 'bold'
                            },
                            formatter: formatPie
                        };

                        const dataLabelsPaymentPieConfig = {
                            anchor: 'end',
                            align: 'start',
                            offset: 5,
                            textAlign : 'center',
                            font: {
                                weight: 'bold'
                            },
                            formatter: formatPie
                        };

                        let performanceChart = {
                            plugins: [ChartDataLabels],
                            type: 'pie',
                            data: {
                                labels: ['Хорошисты', 'Успевающие', 'Неуспевающие', 'Отличники'],
                                datasets: [{
                                    data: [goodstudentscount, okaystudentscount, badstudentscount, wellstudentscount],
                                    backgroundColor: [
                                        goodSectorColor,
                                        okaySectorColor,
                                        badSectorColor,
                                        wellSectorColor
                                    ],
                                    borderWidth: 1,
                                    hoverBorderWidth: 3,
                                    hoverBorderColor: hoverColor,
                                }]
                            },
                            options: {
                                plugins: {
                                    legend: {
                                        display: false,
                                    },
                                    datalabels: dataLabelsPerformancePieConfig
                                }
                            }
                        };

                       //console.log ("params: " + goodattendingstudentscount + " " + okayattendingstudentscount + " " + badattendingstudentscount + " " + wellattendingstudentscount);
                        /*let attendanceChart = {
                            plugins: [ChartDataLabels],
                            type: 'pie',
                            data: {
                                labels: ['С 1м прогулом', 'Успевающие', 'Не посещающие занятия', 'Без прогулов'],
                                datasets: [{
                                    data: [goodattendingstudentscount, okayattendingstudentscount, badattendingstudentscount, wellattendingstudentscount],
                                    backgroundColor: [
                                        '#edeab0',
                                        '#d7883f',
                                        '#dca1a1',
                                        '#accca6'
                                    ],
                                    borderWidth: 1,
                                    hoverBorderWidth: 3,
                                    hoverBorderColor: hoverColor
                                }]
                            },
                            options: {
                                plugins: {
                                    legend: {
                                        display: false,
                                    },
                                    datalabels: {
                                        formatter: function (value) {

                                            return value + ' Чел.';
                                        }
                                    }
                                }
                            }
                        };
*/
                        let paymentChart = {
                            plugins: [ChartDataLabels],
                            type: 'pie',
                            data: {
                                labels: ['Не оплатили', 'Оплатили недавно'],
                                datasets: [{
                                    data: [didntpaystudentscount, paystudentscount],
                                    backgroundColor: [
                                        badSectorColor,
                                        okaySectorColor
                                    ],
                                    borderWidth: 1,
                                    hoverBorderWidth: 3,
                                    hoverBorderColor: hoverColor
                                }]
                            },
                            options: {
                                plugins: {
                                    legend: {
                                        display: false,
                                    },
                                    datalabels: dataLabelsPaymentPieConfig
                                }
                            }
                        };

                        //let lastAttendanceList = document.getElementById('well-attending-students-list');
                        let lastPerformanceList = document.getElementById('well-students-list');
                        let lastAttendanceSwitch = document.getElementById('well-attending-students');
                        let lastPerformanceSwitch = document.getElementById('well-students');
                        let lastGroupTab = document.getElementById('GroupPerformanceTab');
                        let lastGroupContentTab = document.getElementById('group-performance');

                        let pieDiagramPerformance = document.getElementById('gst-pie-diagram-performance');
                        //let pieDiagramAttendance = document.getElementById('gst-pie-diagram-attendance');
                        let pieDiagramPayment = document.getElementById('gst-pie-diagram-payment');

                        //let performanceListSwitchIds = ['well-students', 'good-students', 'okay-students', 'bad-students'];
                       // let performanceListIds = ['well-students-list', 'good-students-list', 'okay-students-list', 'bad-students-list'];
                        //let attendanceListIds = ['well-attending-students-list', 'good-attending-students-list', 'okay-attending-students-list', 'bad-attending-students-list'];
                        //let attendanceListSwitchIds = ['well-attending-students', 'good-attending-students', 'okay-attending-students', 'bad-attending-students'];
                        //let groupTabsIds = ['GroupPerformanceTab', 'GroupAttendanceTab', 'GroupPaymentTab'];
                        let groupTabsIds = ['GroupPerformanceTab', 'GroupPaymentTab'];
                        //let groupTabsContentIds = ['group-performance', 'group-attendance', 'group-payment'];
                        let groupTabsContentIds = ['group-performance', 'group-payment'];

                        addTabListeners(groupTabsIds, groupTabsContentIds);
                        //addListListeners(performanceListSwitchIds, performanceListIds, "performance");
                        //addListListeners(attendanceListSwitchIds, attendanceListIds, "attendance");

                        new Chart(pieDiagramPerformance, performanceChart);
                        //new Chart(pieDiagramAttendance, attendanceChart);
                        new Chart(pieDiagramPayment, paymentChart);

                        function setActive(element) {
                            element.classList.add('active');
                        }

                        function setInactive(element) {
                            element.classList.remove('active');
                        }

                        function showTabContent(tabContent) {
                            tabContent.classList.remove('hide');
                            tabContent.classList.add('show');
                        }

                        function hideTabContent(tabContent) {
                            tabContent.classList.remove('show');
                            tabContent.classList.add('hide');
                        }

                        function addTabListeners(tabIds, contentIds) {
                            for (var i = 0; i < tabIds.length; i++) {
                                let element = document.getElementById(tabIds[i]);
                                let content = document.getElementById(contentIds[i]);
                                element.addEventListener('click', function () {
                                    setInactive(lastGroupTab);
                                    hideTabContent(lastGroupContentTab);
                                    setActive(element);
                                    showTabContent(content);
                                    lastGroupTab = element;
                                    lastGroupContentTab = content;
                                });
                            }
                        }

                        /*function addListListeners(switchIds, contentIds, parameter) {
                            for (var i = 0; i < switchIds.length; i++) {
                                let element = document.getElementById(switchIds[i]);
                                let content = document.getElementById(contentIds[i]);
                                element.addEventListener('click', function () {
                                    if (parameter === "performance") {
                                        setInactive(lastPerformanceSwitch);
                                        hideTabContent(lastPerformanceList);
                                        setActive(element);
                                        showTabContent(content);
                                        lastPerformanceSwitch = element;
                                        lastPerformanceList = content;
                                    } else if (parameter === "attendance") {
                                        setInactive(lastAttendanceSwitch);
                                        hideTabContent(lastAttendanceList);
                                        setActive(element);
                                        showTabContent(content);
                                        lastAttendanceSwitch = element;
                                        lastAttendanceList = content;
                                    }
                                });
                            }
                        }*/

                        $('#gs-group-stats-blank').css({ 'display': 'none' });
                        $('#gs-block-groupstats-holder').css({ 'display': 'block' });
                    }).fail(function (err) {
                        console.log(err);
                    });
                });
                if (autochange) {
                    if (newGroupListElements.length === 1) {
                        return;
                    }
                    autochange = false;
                    let istring = document.getElementById('gs-cs-group-data-section');
                    istring.innerHTML = getDataSectionOf(newGroupListElements[1]).innerHTML;

                    //console.log(getDataSectionOf(newGroupListElements[1]).innerHTML);

                    let event = new Event('change', { bubbles: true });
                    newGroupSelect.dispatchEvent(event);
                }
            }).fail(function (err) {
                console.log(err);
                //notification.exception(new Error('Failed to load data'));
                return;
            });
        });
        if (getDataSectionOf(currentLine).getElementsByClassName('gs-cs-checker').length === 0) {
            autochange = true;
            let event = new Event('change', { bubbles: true });
            courceSelect.dispatchEvent(event);
        }
    });
});
