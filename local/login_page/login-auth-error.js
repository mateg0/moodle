const authError = document.querySelector('.auth-error');

if(sessionStorage.getItem('auth-error')){
    authError.style.display = 'block';
}