
function getCookie(name) {
    let matches = document.cookie.match(new RegExp(
      "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches ? decodeURIComponent(matches[1]) : undefined;
}

function resetTelegramLocalStorage() {
    localStorage.removeItem('tt-moodle-session');
    dc = localStorage.getItem('dc');
    localStorage.removeItem('dc');
    localStorage.removeItem('dc' + dc + '_auth_key');
    localStorage.removeItem('dc' + dc + '_hash');
    localStorage.removeItem('user_auth');
    localStorage.removeItem('tt-global-state');
    localStorage.removeItem('tt-active-tab');
    for(let key in localStorage) {
        if (key.match(new RegExp("dc\\d+_auth_key")) || key.match(new RegExp("dc\\d+_hash"))) {
            localStorage.removeItem(key);
        }
    }
    if (MoodleSession) {
        localStorage.setItem('tt-moodle-session', MoodleSession);
    }
}

function simulateTelegramSettingsClick() {
    try {
        document.querySelector('div.Menu div.MenuItem[role="button"] i.icon-settings').click();
        setTimeout(simulateTelegramLogoutClick, 200);
    } catch(exception) {
        if (Date.now() - LOGOUT_START < LOGOUT_MAX_TIME) {
            setTimeout(simulateTelegramSettingsClick, 50);
        } else {
            resetTelegramLocalStorage();
        }
    }
}

function simulateTelegramLogoutClick() {
    try {
        document.querySelector('div.Menu.settings-more-menu div.MenuItem[role="button"] i.icon-logout').click();
        setTimeout(simulateTelegramLogoutConfirmClick, 200);
    } catch(exception) {
        if (Date.now() - LOGOUT_START < LOGOUT_MAX_TIME) {
            setTimeout(simulateTelegramLogoutClick, 50);
        } else {
            resetTelegramLocalStorage();
        }
    }
}

function simulateTelegramLogoutConfirmClick() {
    clicked = false;
    try {
        document.querySelectorAll('button.confirm-dialog-button').forEach(function(item) {
            if (item.innerHTML == 'Log Out') {
                item.click();
                clicked = true;
            }
        });
    } finally {
        if (!clicked && (Date.now() - LOGOUT_START < LOGOUT_MAX_TIME)) {
            setTimeout(simulateTelegramLogoutConfirmClick, 50);
        } else {
            resetTelegramLocalStorage();
        }
    }
}

function TelegramLogout() {
    try {
        LOGOUT_START = Date.now();
        setTimeout(simulateTelegramSettingsClick, 200);
    } catch(e) {
        resetTelegramLocalStorage()
    }
}

MoodleSession = getCookie('MoodleSession');
TTMoodleSession = localStorage.getItem('tt-moodle-session');
LOGOUT_START = 0;
LOGOUT_MAX_TIME = 1200;

if (MoodleSession) {
    if (TTMoodleSession != MoodleSession) {
        TelegramLogout();
    }
} else {
    if (TTMoodleSession) {
        TelegramLogout();
    }
}
