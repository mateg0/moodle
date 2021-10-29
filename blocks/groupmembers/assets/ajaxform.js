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
        const courseSearch = document.getElementById('cs-cource-search');


        // eslint-disable-next-line require-jsdoc
        function getDataSectionOf(element) {
            return element.getElementsByClassName('cs-data-section')[0];
        }

        function getTextSectionOf(element, className) {
            return element.getElementsByClassName('cs-data-section')[0].getElementsByClassName(className)[0];
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

            document.addEventListener('click', function (e) {
                if(e.target.classList.contains('non-click')){
                    return;
                }
                closeList(select, list);
            });
            document.addEventListener('focus', function () {
                closeList(select, list);
            });
        }

        function initSearch(search, listElements, className){
            if(search == null) {
                return;
            }
            search.addEventListener('input', function(e){
                if(e.keyCode === 13){
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
                        if (listElements[i].classList.contains('current-line')){
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
        initSearch(courseSearch, courceListElements, 'cs-cource-name');
        initList(groupSelect, groupList, groupListElements, activeGroupElement);

        const currentLine = document.getElementById('cs-gmsc-select');
        let autochange = false;

        $('#cs-gmsc-select').on('change', function() {
            $('#block-groupmembers-holder').html('');
            $('#gm-cource-group-members-blank').css({'display' : 'block'});
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
                  $('#gm-addgroup-button').removeClass('disabled-mouse-events');
                  const newGroupSelect = document.getElementById('cs-gms-select');
                  const newGroupList = document.getElementById('cs-gms-list');
                  const newGroupListElements = document.getElementsByClassName('cs-group-line');
                  const groupSearch = document.getElementById('cs-group-search');

                  initList(newGroupSelect, newGroupList, newGroupListElements, activeGroupElement);
                  initSearch(groupSearch, newGroupListElements, 'cs-group-name');

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
                          $('#gm-cource-group-members-blank').css({'display' : 'none'});
                          $('#block-groupmembers-holder').html('');
                          $('#block-groupmembers-holder').append(response);
                      }).fail(function (err) {
                          console.log(err);
                      });
                  });

                  if(autochange){
                      if(newGroupListElements.length === 1){
                          return;
                      }
                      autochange = false;
                      let istring = document.getElementById('cs-group-data-section');
                      istring.innerHTML = getDataSectionOf(newGroupListElements[1]).innerHTML;

                      //console.log(getDataSectionOf(newGroupListElements[1]).innerHTML);

                      let event = new Event('change', {bubbles: true});
                      newGroupSelect.dispatchEvent(event);
                  }

              }).fail(function (err) {
                  console.log(err);
              });
        });

        if(getDataSectionOf(currentLine).getElementsByClassName('cs-checker').length === 0) {
            autochange = true;
            let event = new Event('change', {bubbles: true});
            courceSelect.dispatchEvent(event);
        }
    });
});
