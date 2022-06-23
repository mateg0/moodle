var windowHeight = window.innerHeight;
var minPos = -500 *( windowHeight  / 996);
let mainContentHeight = document.querySelector('body').offsetHeight - windowHeight;
var bg = document.querySelector('body');
mainContentHeight = document.querySelector('#page-content').offsetHeight - windowHeight;
value = minPos*  (window.pageYOffset / mainContentHeight * 100)/100;
if(document.scrollHeight == document.offsetHeight) value = minPos;
document.documentElement.style.setProperty(`--moveBg`, `${value }px`);
function moveBg(e){
     mainContentHeight = document.querySelector('#page-content').offsetHeight - windowHeight;
      coeff =  (window.pageYOffset / mainContentHeight * 100)/100
      if (coeff > 1){coeff = 1}
      value = minPos*  (window.pageYOffset / mainContentHeight * 100)/100;
     if (value < minPos){ value = minPos;}
     document.documentElement.style.setProperty(`--moveBg`, `${value}px`);
     console.log(value);
}
window.addEventListener('scroll' , moveBg);
window.scrollBy({
            top: offsetPosition,
            behavior: 'smooth'
        });