var windowHeight = window.innerHeight;
let speed = 0.5;
let maxPercent = 80;
var bg = document.querySelector('.parallax-scroll');
let scrollTopPercent = -50;
if(windowHeight >= document.querySelector('#page').offsetHeight) { 
     bg.style.cssText =  `transform:translate(0%, -40%)`; 
     speed = 0.25;
}
else{
     scrollTopPercent = window.pageYOffset/ (document.querySelector('#page').offsetHeight - windowHeight) * 100;
     bg.style.cssText =  `transform:translate(0%, -${scrollTopPercent* speed}%)`;
}
function moveBg(){
     if(windowHeight >= document.querySelector('#page').offsetHeight)
     {
          speed = 0.25;
          maxPercent = 160;
     }
     scrollTopPercent =Math.abs( window.pageYOffset/ (document.querySelector('#page').offsetHeight - windowHeight) * 100);
     if (scrollTopPercent > maxPercent) 
     {
          scrollTopPercent = maxPercent;
          
     }
     bg.style.cssText =  `transform:translate(0%, -${scrollTopPercent* speed}%)`;
}
window.addEventListener('scroll' , moveBg);