const loginPopupInner = document.getElementsByClassName('login_popup_inner')[0];

if(sessionStorage.getItem('auth-error')){
    const newErrorAlert = document.createElement('div');
    newErrorAlert.className = 'auth-error-alert';
    newErrorAlert.innerText = 'Неверный логин или пароль';

    loginPopupInner.insertAdjacentElement('afterbegin', newErrorAlert);
}