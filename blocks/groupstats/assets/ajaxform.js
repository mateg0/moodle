require(['core/first', 'jquery', 'jqueryui', 'core/ajax'], function (core, $, bootstrap, ajax) {
    $(document).ready(function () {

        let activeCourseElement;
        let activeGroupElement;

        const courceSelect = document.getElementById('gs-cs-gmsc-select');
        const courceList = document.getElementById('gs-cs-gmsc-list');
        const groupSelect = document.getElementById('gs-cs-gms-select');
        const groupList = document.getElementById('gs-cs-gms-list');
        const courceListElements = document.getElementsByClassName('gs-cs-cource-line');
        const groupListElements = document.getElementsByClassName('gs-cs-group-line');

        // eslint-disable-next-line require-jsdoc
        function getDataSectionOf(element) {
            return element.getElementsByClassName('gs-cs-data-section')[0];
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
            document.addEventListener('click', function () {
                closeList(select, list);
            });
            document.addEventListener('focus', function () {
                closeList(select, list);
            });
        }

        // eslint-disable-next-line require-jsdoc
        function initList(select, list, listElements, activeElement) {
            addEventCloseListOnDocumetClick(select, list);
            for (let i = 0; i < listElements.length; i++) {
                if (listElements[i].classList.contains('current-line')) {
                    activeElement = getDataSectionOf(listElements[i]);
                    select.addEventListener('click', function(e){
                        e.stopPropagation();
                        if (!select.classList.contains('active')) {
                            let event = new Event('focus', {bubbles: true});
                            document.dispatchEvent(event);
                        }
                        select.classList.toggle('active');
                        toggleList(list);
                    });
                } else {
                    listElements[i].addEventListener('click', function(e){
                        e.stopPropagation();
                        activeElement.innerHTML = getDataSectionOf(e.currentTarget).innerHTML;
                        closeList(select, list);
                        let event = new Event('change', {bubbles: true});
                        select.dispatchEvent(event);
                    });
                }
            }
        }

        initList(courceSelect, courceList, courceListElements, activeCourseElement);
        initList(groupSelect, groupList, groupListElements, activeGroupElement);

        $('#gs-cs-gmsc-select').on('change', function() {
            $('#gs-block-groupstats-holder').html('');
            $('#gs-block-groupstats-holder').css({'display' : 'none'});
            $('#gs-group-stats-blank').css({'display' : 'block'});
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

                  let newGroupSelect = document.getElementById('gs-cs-gms-select');
                  let newGroupList = document.getElementById('gs-cs-gms-list');
                  let newGroupListElements = document.getElementsByClassName('gs-cs-group-line');
                  initList(newGroupSelect, newGroupList, newGroupListElements, activeGroupElement);

                  $('#gs-cs-gms-select').on('change', function() {
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

                          //let groupmemberscount = document.getElementById('gs-groupmemberscount');
                          let wellstudentscount = document.getElementById('gs-wellstudentscount').innerHTML;
                          let goodstudentscount = document.getElementById('gs-goodstudentscount').innerHTML;
                          let okaystudentscount = document.getElementById('gs-okaystudentscount').innerHTML;
                          let badstudentscount = document.getElementById('gs-badstudentscount').innerHTML;
                          let wellattendingstudentscount = document.getElementById('gs-wellattendingstudentscount').innerHTML;
                          let goodattendingstudentscount = document.getElementById('gs-goodattendingstudentscount').innerHTML;
                          let okayattendingstudentscount = document.getElementById('gs-okayattendingstudentscount').innerHTML;
                          let badattendingstudentscount = document.getElementById('gs-badattendingstudentscount').innerHTML;
                          let paystudentscount = document.getElementById('gs-paystudentscount').innerHTML;
                          let didntpaystudentscount = document.getElementById('gs-didntpaystudentscount').innerHTML;

                          let performanceChart = {
                              type: 'pie',
                              data: {
                                  labels: ['Хорошисты', 'Успевающие', 'Неуспевающие', 'Отличники'],
                                  datasets: [{
                                      data: [goodstudentscount, okaystudentscount, badstudentscount, wellstudentscount],
                                      backgroundColor: [
                                          '#edeab0',
                                          '#d7883f',
                                          '#dca1a1',
                                          '#accca6'
                                      ],
                                      borderWidth: 1,
                                      hoverBorderWidth: 3,
                                      hoverBorderColor: '#599A4E'
                                  }]
                              },
                              options: {
                                  plugins: {
                                      legend: {
                                          display: false,
                                      }
                                  }
                              }
                          };

                          let attendanceChart = {
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
                                      hoverBorderColor: '#599A4E'
                                  }]
                              },
                              options: {
                                  plugins: {
                                      legend: {
                                          display: false,
                                      }
                                  }
                              }
                          };

                          let paymentChart = {
                              type: 'pie',
                              data: {
                                  labels: ['Не оплатили', 'Оплатили недавно'],
                                  datasets: [{
                                      data: [didntpaystudentscount, paystudentscount],
                                      backgroundColor: [
                                          '#dca1a1',
                                          '#accca6'
                                      ],
                                      borderWidth: 1,
                                      hoverBorderWidth: 3,
                                      hoverBorderColor: '#599A4E'
                                  }]
                              },
                              options: {
                                  plugins: {
                                      legend: {
                                          display: false,
                                      }
                                  }
                              }
                          };

                          let lastAttendanceList = document.getElementById('well-attending-students-list');
                          let lastPerformanceList = document.getElementById('well-students-list');
                          let lastAttendanceSwitch = document.getElementById('well-attending-students');
                          let lastPerformanceSwitch = document.getElementById('well-students');
                          let lastGroupTab = document.getElementById('GroupPerformanceTab');
                          let lastGroupContentTab = document.getElementById('group-performance');

                          let pieDiagramPerformance = document.getElementById('gst-pie-diagram-performance');
                          let pieDiagramAttendance = document.getElementById('gst-pie-diagram-attendance');
                          let pieDiagramPayment = document.getElementById('gst-pie-diagram-payment');

                          let performanceListSwitchIds = ['well-students', 'good-students', 'okay-students', 'bad-students'];
                          let performanceListIds = ['well-students-list', 'good-students-list', 'okay-students-list', 'bad-students-list'];
                          let attendanceListIds = ['well-attending-students-list', 'good-attending-students-list', 'okay-attending-students-list', 'bad-attending-students-list'];
                          let attendanceListSwitchIds = ['well-attending-students', 'good-attending-students', 'okay-attending-students', 'bad-attending-students'];
                          let groupTabsIds = ['GroupPerformanceTab', 'GroupAttendanceTab', 'GroupPaymentTab'];
                          let groupTabsContentIds = ['group-performance', 'group-attendance', 'group-payment'];

                          addTabListeners(groupTabsIds, groupTabsContentIds);
                          addListListeners(performanceListSwitchIds, performanceListIds, "performance");
                          addListListeners(attendanceListSwitchIds, attendanceListIds, "attendance");
                          new Chart(pieDiagramPerformance, performanceChart);
                          new Chart(pieDiagramAttendance, attendanceChart);
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
                                  element.addEventListener('click', function(){
                                      setInactive(lastGroupTab);
                                      hideTabContent(lastGroupContentTab);
                                      setActive(element);
                                      showTabContent(content);
                                      lastGroupTab = element;
                                      lastGroupContentTab = content;
                                  });
                              }
                          }

                          function addListListeners(switchIds, contentIds, parameter) {
                              for (var i = 0; i < switchIds.length; i++) {
                                  let element = document.getElementById(switchIds[i]);
                                  let content = document.getElementById(contentIds[i]);
                                  element.addEventListener('click', function(){
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
                          }

                          $('#gs-group-stats-blank').css({'display' : 'none'});
                          $('#gs-block-groupstats-holder').css({'display' : 'block'});
                      }).fail(function (err) {
                          console.log(err);
                      });
                  });

                  return;
              }).fail(function (err) {
                  console.log(err);
                  //notification.exception(new Error('Failed to load data'));
                  return;
              });
        });
    });
});
