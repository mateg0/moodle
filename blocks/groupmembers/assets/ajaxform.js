require(['core/first', 'jquery', 'jqueryui', 'core/ajax'], function (core, $, bootstrap, ajax) {
    $(document).ready(function () {

        let activeCourseElement;
        let activeGroupElement;

        const courceSelect = document.getElementById('cs-gmsc-select');
        const courceList = document.getElementById('cs-gmsc-list');
        const groupSelect = document.getElementById('cs-gms-select');
        const groupList = document.getElementById('cs-gms-list');
        const courceListElements = document.getElementsByClassName('cs-cource-line');
        const groupListElements = document.getElementsByClassName('cs-group-line');

        // eslint-disable-next-line require-jsdoc
        function getDataSectionOf(element) {
            return element.getElementsByClassName('cs-data-section')[0];
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
            $('input[name = csgm_groupid]').val(id);
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

        $('#cs-gmsc-select').on('change', function() {
            $('#block-groupmembers-holder').html('');
            let selectedcourseline = document.getElementById('cs-data-section').getElementsByClassName('cs-cource-name').item(0);
            let selectedcourseid = selectedcourseline.getAttribute('data-id');
              ajax.call([{
                  methodname: 'getgroupsbycourseid',
                  args: {
                      'courseid': selectedcourseid
                  },
              }])[0].done(function (response) {
                  // clear out old values
                  $('#cs-groups-holder').html('');
                  $('#cs-groups-holder').append(response);

                  let newGroupSelect = document.getElementById('cs-gms-select');
                  let newGroupList = document.getElementById('cs-gms-list');
                  let newGroupListElements = document.getElementsByClassName('cs-group-line');
                  initList(newGroupSelect, newGroupList, newGroupListElements, activeGroupElement);

                  $('#cs-gms-select').on('change', function() {
                      let selectedgroupline = document.getElementById('cs-group-data-section').getElementsByClassName('cs-group-name').item(0);
                      let selectedgroupid = selectedgroupline.getAttribute('data-id');

                      setnewvalue(selectedgroupid);
                      ajax.call([{
                          methodname: 'getgroupmembersbygroupid',
                          args: {
                              'groupid': selectedgroupid
                          },
                      }])[0].done(function (response) {
                          $('#block-groupmembers-holder').html('');
                          $('#block-groupmembers-holder').append(response);
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
