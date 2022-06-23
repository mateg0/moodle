var windowHeight = window.innerHeight;
var minPos = -500 *( windowHeight  / 996);
var bg = document.querySelector('body');
let mainContentHeight = document.querySelector('#page-content').offsetHeight - windowHeight;

if(document.querySelector('#page-content').offsetHeight <= windowHeight) value = minPos;
else value = minPos*  (window.pageYOffset / mainContentHeight * 100)/100;
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