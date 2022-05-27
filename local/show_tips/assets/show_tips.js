require(['core/first', 'jquery', 'core/ajax'],
    function (core, $, ajax) {
        $(document).ready(function () {
            function removeOldPreferences() {
                ajax.call([{
                    methodname: 'remove_old_tour_preferences',
                    args: {
                        empty: ''
                    }
                }])[0].done(function (response) {
                    if (response) {
                        location.reload();
                    }
                }).fail(function (err) {
                    console.log(err);
                });
            }

            document.querySelector('.trigger-howto').addEventListener('click', removeOldPreferences);
        });
    }
);
