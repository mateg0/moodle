const urlPostRequest = 'https://edu.bonch-ikt.ru/login/index.php';
const urlAfterCompletedAuthorization = 'https://edu.bonch-ikt.ru/my';

document.getElementsByClassName('btn-login')[0].addEventListener('click', async (event) => {
    event.preventDefault();

    const loginFormData = new FormData(document.getElementsByClassName('loginform')[0]);
    const response = await fetch(urlPostRequest, {
        method: 'POST',
        body: loginFormData
        });
    const responseText = await response.text();

    if(responseText.includes('Личный кабинет')){
        window.location.href = urlAfterCompletedAuthorization;
    }
});