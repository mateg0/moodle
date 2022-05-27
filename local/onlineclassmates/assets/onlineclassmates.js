require(['core/first', 'jquery', 'core/ajax'],
    function (core, $, ajax) {
        $(document).ready(function () {
            function updateOnlineClassmates() {
                ajax.call([{
                    methodname: 'getnewonlineclassmates',
                    args: {
                        'id': 0
                    }
                }])[0].done(function (response) {
                    $('#onlineclassmates-block').html('').append(response);
                }).fail(function (err) {
                    console.log(err);
                });
            }

            let updateId = setInterval(updateOnlineClassmates, 30000);
        });
    }
);
