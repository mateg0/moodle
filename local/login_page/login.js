const urlPostRequest = 'https://edu.bonch-ikt.ru/login/index.php';
const urlAfterSuccessfulAuthorization = 'https://edu.bonch-ikt.ru/';
const nameOfAuthError = 'auth-error';

const loginButton = document.getElementsByClassName('btn-login')[0];
const loginForm = document.getElementsByClassName('loginform')[0];
const loginLogo = document.getElementsByClassName('potentialidps')[0].firstElementChild;

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
        loginForm.classList.add('brightless-login-form');
        loginLogo.classList.add('logo-pulse');
    };

    const makePrettyHTTPSLink = (HTTPSink) => {
        return HTTPSink
            .replaceAll('%3A', ':')
            .replaceAll('%2F', '/')
            .replaceAll('%3F', '?')
            .replaceAll('%3D', '=');
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
        
        if(window.location.search.includes('redirect')){
            redirectUrl = makePrettyHTTPSLink(window.location.search.split('redirect=')[1]);
        }
        window.location.href = redirectUrl;
    } else {
        setAuthenticationErrorToSessionStorage();
        window.location.reload(false);
    }
});