if (!window.location.href.includes('my')) {
    let minuteChangeInterval;
    let hourChangeInterval;

    let AFKTimeout;
    let mouseMoveThrottle;
    
    let timerData = JSON.parse(sessionStorage.getItem('timer'));

    const minute = 60_000;
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
            lastUpdate: new Date()
        };
    };

    const updateTimerData = () => sessionStorage.setItem('timer', JSON.stringify(timerData));

    const updateMinutes = () => {
        let valueOfMinutes = timerData.minute;

        if (valueOfMinutes >= 59) {
            valueOfMinutes = -1;
        }

        valueOfMinutes++;

        timerData.minute = valueOfMinutes;
        timerData.lastUpdate = new Date();
        updateTimerData();
    };

    const updateHours = () => {
        let valueOfHours = timerData.hour;

        valueOfHours++;

        timerData.hour = valueOfHours;
        timerData.lastUpdate = new Date();
        updateTimerData();
    };

    const startChangeIntervals = () => {
        minuteChangeInterval = setInterval(updateMinutes, minute);
        hourChangeInterval = setInterval(updateHours, hour);
    }

    const startChangeIntervalsAfterAFK = () => {
        document.addEventListener('mousemove', startChangeIntervals, {once: true});
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
                    clearInterval(hourChangeInterval);
                    clearInterval(minuteChangeInterval);
                    clearTimerData();
                    updateTimerData();

                    startChangeIntervalsAfterAFK()
                }, hour * 2);

                mouseMoveThrottle = false;
            }, 10000);
        });
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

    startChangeIntervals();
    setAFKTimeout();
}