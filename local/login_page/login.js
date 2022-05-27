const urlAfterSuccessfulAuthorization = `${window.location.protocol}//${window.location.host}`;
const urlPostRequest = `${urlAfterSuccessfulAuthorization}/login/index.php`;
const nameOfAuthError = 'auth-error';

const loginWrapper = document.querySelector('.login-wrapper');
const shadow = document.querySelector('.shadow');
const showLoginFormButton = document.querySelector('button.login');

const loginForm = loginWrapper.querySelector('form.login-form');
const loginButton = loginForm
                        .querySelector('.login-button-wrapper')
                        .querySelector('button');

const authLoad = loginWrapper.querySelector('.auth-load');

const showLoginForm = () => {
    loginWrapper.style.display = 'block';
    shadow.style.display = 'block';
    document.body.style.overflowY = 'hidden';

    setTimeout(() => {
        document.addEventListener('click', hideLoginForm);
    }, 100);
};

const hideLoginForm = (e) => {
    if (!e.target.closest('.login-wrapper')) {
        loginWrapper.style.display = 'none';
        shadow.style.display = 'none';
        document.body.style.overflowY = '';

        document.removeEventListener('click', hideLoginForm);
    }
}

showLoginFormButton.addEventListener('click', showLoginForm);

loginForm.addEventListener('submit', async (event) => {
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

        loginForm.classList.add('during-load');
        authLoad.style.display = 'block';
    };

    const makePrettyHTTPSLink = (HTTPSink) => {
        return HTTPSink
            .replaceAll('%3A', ':')
            .replaceAll('%2F', '/')
            .replaceAll('%3F', '?')
            .replaceAll('%3D', '=');
    };

    setWaitingThemeLoginPage();

    const loginFormData = new FormData(loginForm);
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

        location.href = location.href + '#login';
        location.reload();
    }
});

if (location.href.includes('#login')) showLoginForm(); 