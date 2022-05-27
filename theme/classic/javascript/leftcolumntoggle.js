const columnToggle = $(".left-column-toggle");
const leftColumnSwitch = $('#site-left-column-switch');

const hideTelegram = () => {
    columnToggle.parent().removeClass('blocks-pre');
    columnToggle.siblings('.columnleft').hide();
    leftColumnSwitch.addClass('closed');
    columnToggle.addClass('active');
}

const showTelegram = () => {
    columnToggle.parent().addClass('blocks-pre');
    columnToggle.siblings('.columnleft').show();
    leftColumnSwitch.removeClass('closed');
    columnToggle.removeClass('active');
}

columnToggle.click(function(){
    if (!$(this).hasClass('active')) {
        hideTelegram();
        localStorage.setItem('hideTelegram', 'true');
    }
    else {
        showTelegram();
        localStorage.removeItem('hideTelegram');
    }
});

function setTelegramStateAfterRender() {
    const telegramHideState = localStorage.getItem('hideTelegram');

    if (telegramHideState) {
        hideTelegram();
    }
}

document.addEventListener('DOMContentLoaded', setTelegramStateAfterRender);