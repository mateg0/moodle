/*Timer data structure
* hour - value of passed hours
* minute - value of passed minutes
* seconds - value of passed seconds
* lastUpdate - Date object of last change
* */

require(['core/first', 'jquery'],
    function (core, $) {
        $(document).ready(function () {
            let secondsChangeInterval;

            let AFKTimeout;
            let mouseMoveThrottle;

            let timerData = JSON.parse(sessionStorage.getItem('timer'));

            const miniTimer = document.querySelector('#mini-timer');
            const miniMinutes = miniTimer.querySelector('#mini-minutes');
            const miniHours = miniTimer.querySelector('#mini-hours');
            const miniTimerAnimates = miniTimer.querySelectorAll('animate');

            const timer = document.querySelector('#timer');
            const minutes = timer.querySelector('#minutes');
            const hours = timer.querySelector('#hours');
            const timerAnimate = timer.querySelector('animate');
            const timerAnimateTransform = timer.querySelector('animateTransform');

            const second = 1_000;
            const minute = 60 * second;
            const hour = minute * 60;

            const checkTimerDataDay = (timerData) => {
                const currentDate = new Date();
                const timerDate = new Date(timerData.lastUpdate);

                return currentDate.getDate() === timerDate.getDate();
            }

            const clearTimerData = () => {
                timerData = {
                    hour: 0,
                    minute: 0,
                    second: 0,
                    lastUpdate: new Date()
                };
            };

            const updateTimerData = () => sessionStorage.setItem('timer', JSON.stringify(timerData));

            const startTimerAnimation = () => {
                timerAnimate.beginElement();
                timerAnimateTransform.beginElement();
                miniTimerAnimates.forEach(animate => {
                    animate.beginElement();
                });
            }

            const transformValueUnderTen = (value) => value < 10 ? '0' + value : '' + value;

            const updateSeconds = () => {
                let valueOfSeconds = +timerData.second;

                if (valueOfSeconds >= 59) {
                    updateMinutes();
                    startTimerAnimation();

                    valueOfSeconds = -1;
                }

                valueOfSeconds++;

                timerData.second = valueOfSeconds;
                timerData.lastUpdate = new Date();
                updateTimerData();
            };

            const updateMinutes = () => {
                let valueOfMinutes = +minutes.innerHTML;

                if (valueOfMinutes >= 59) {
                    updateHours();

                    valueOfMinutes = -1;
                }

                valueOfMinutes++;

                timerData.minute = valueOfMinutes;
                updateTimerData();

                valueOfMinutes = transformValueUnderTen(valueOfMinutes)

                minutes.innerHTML = valueOfMinutes;
                miniMinutes.innerHTML = valueOfMinutes;
            };

            const updateHours = () => {
                let valueOfHours = +hours.innerHTML;

                valueOfHours++;

                timerData.hour = valueOfHours;
                updateTimerData();

                valueOfHours = transformValueUnderTen(valueOfHours);

                hours.innerHTML = valueOfHours;
                miniHours.innerHTML = valueOfHours;
            };

            const startChangeInterval = () => {
                secondsChangeInterval = setInterval(updateSeconds, second);
            }

            const startChangeIntervalsAfterAFK = () => {
                document.addEventListener('mousemove', startChangeInterval, {once: true});
            }

            const clearChangeInterval = () => {
                clearInterval(secondsChangeInterval);
            }

            const setAFKTimeout = () => {
                document.addEventListener('mousemove', () => {
                    if (mouseMoveThrottle) return;

                    mouseMoveThrottle = true;

                    setTimeout(() => {
                        if (AFKTimeout) {
                            clearTimeout(AFKTimeout);
                        }

                        AFKTimeout = setTimeout(() => {
                            clearChangeInterval();
                            clearTimerData();
                            updateTimerData();

                            timerAnimate.endElement();
                            timerAnimateTransform.endElement();
                            miniTimerAnimates.forEach(animate => {
                                animate.endElement();
                            });

                            startChangeIntervalsAfterAFK()
                        }, hour * 2);

                        mouseMoveThrottle = false;
                    }, 10000);
                });
            }

            const startStopwatch = () => {
                startChangeInterval();
                setAFKTimeout();
                startTimerAnimation();
            }

            const startStopwatchAfterTimeout = () => {
                clearChangeInterval();

                startStopwatch();
            }

            if (timerData) {
                if (checkTimerDataDay(timerData)) {
                    minutes.innerHTML = transformValueUnderTen(timerData.minute);
                    hours.innerHTML = transformValueUnderTen(timerData.hour);

                    miniMinutes.innerHTML = transformValueUnderTen(timerData.minute);
                    miniHours.innerHTML = transformValueUnderTen(timerData.hour);
                } else {
                    clearTimerData();
                    updateTimerData();
                }
            } else {
                clearTimerData();
                updateTimerData();
            }

            if(timerData.second) {
                startChangeInterval();

                setTimeout(startStopwatchAfterTimeout, (60 - timerData.second) * second);
            } else {
                startStopwatch();
            }
        });
    });
