const urlPostRequest = 'https://edu.bonch-ikt.ru/login/index.php';
const urlAfterSuccessfulAuthorization = 'https://edu.bonch-ikt.ru/';
const nameOfAuthError = 'auth-error';

const loginButton = document.getElementsByClassName('btn-login')[0];
const loginSpinner = document.getElementsByClassName('spinner-login')[0];
const loginWrap = document.getElementsByClassName('login_wrap')[0];

loginButton.addEventListener('click', async (event) => {
    event.preventDefault();

    const setAuthenticationErrorToSessionStorage = () => {
        sessionStorage.setItem(nameOfAuthError, 'username');
    };

    const removeAuthenticationErrorFromSessionStorage = () => {
        if(sessionStorage.getItem(nameOfAuthError)){
            sessionStorage.removeItem(nameOfAuthError);
        }
    };

    const setWaitingThemeLoginPage = () => {
        loginButton.disabled = true;
        loginSpinner.classList.add('show-spinner');
        loginWrap.classList.add('brightless-login-wrap');
    };

    setWaitingThemeLoginPage();

    const loginFormData = new FormData(document.getElementsByClassName('loginform')[0]);
    const response = await fetch(urlPostRequest, {
        method: 'POST',
        body: loginFormData
        });
    const responseText = await response.text();

    let redirectUrl = urlAfterSuccessfulAuthorization;

    if(responseText.includes('Личный кабинет')){
        removeAuthenticationErrorFromSessionStorage();
        
        if(window.location.search){
            redirectUrl = window.location.search.split('redirect=')[1];
        }
        window.location.href = redirectUrl;
    } else {
        setAuthenticationErrorToSessionStorage();
        window.location.reload(false);
    }
});