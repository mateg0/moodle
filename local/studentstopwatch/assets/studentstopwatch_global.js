if (!window.location.href.includes('my')) {
    let secondsChangeInterval;

    let AFKTimeout;
    let mouseMoveThrottle;
    
    let timerData = JSON.parse(sessionStorage.getItem('timer'));

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

    const updateSeconds = () => {
        let valueOfSeconds = +timerData.second;

        if (valueOfSeconds >= 59) {
            updateMinutes();

            valueOfSeconds = -1;
        }

        valueOfSeconds++;

        timerData.second = valueOfSeconds;
        timerData.lastUpdate = new Date();
        updateTimerData();
    };

    const updateMinutes = () => {
        let valueOfMinutes = timerData.minute;

        if (valueOfMinutes >= 59) {
            updateHours();

            valueOfMinutes = -1;
        }

        valueOfMinutes++;

        timerData.minute = valueOfMinutes;
        updateTimerData();
    };

    const updateHours = () => {
        let valueOfHours = timerData.hour;

        valueOfHours++;

        timerData.hour = valueOfHours;
        updateTimerData();
    };

    const startChangeIntervals = () => {
        secondsChangeInterval = setInterval(updateSeconds, second);
    }

    const startChangeIntervalsAfterAFK = () => {
        document.addEventListener('mousemove', startChangeIntervals, {once: true});
    }

    const clearChangeIntervals = () => {
        clearInterval(secondsChangeInterval);
        clearInterval(minuteChangeInterval);
        clearInterval(hourChangeInterval);
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
                    clearChangeIntervals();
                    clearTimerData();
                    updateTimerData();

                    startChangeIntervalsAfterAFK()
                }, hour * 2);

                mouseMoveThrottle = false;
            }, 10000);
        });
    }

    const startStopwatch = () => {
        startChangeIntervals();
        setAFKTimeout();
    }

    if (timerData) {
        if (!checkTimerDataDay(timerData)) {
            clearTimerData();
            updateTimerData();
        }
    } else {
        clearTimerData();
        updateTimerData();
    }

    startStopwatch();
}