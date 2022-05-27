function changeButtonStateToHideAll () {
    topicsInteractButton.classList.remove('show-topics');
    topicsInteractButton.innerText = 'Свернуть всё';

    topicsInteractButton.removeEventListener('click', showTopics);
    topicsInteractButton.addEventListener('click', hideTopics);
}

function changeButtonStateToShowAll () {
    topicsInteractButton.classList.add('show-topics');
    topicsInteractButton.innerText = 'Развернуть всё';

    topicsInteractButton.removeEventListener('click', hideTopics);
    topicsInteractButton.addEventListener('click', showTopics);
}

function hideTopics () {
    details.forEach(details => {
        details.removeAttribute('open');
    });
};

function showTopics () {
    details.forEach(details => {
        details.setAttribute('open', true);
    });
}

function mutationsInteract () {
    for (const detail of details) {
        if (detail.open) {
            changeButtonStateToHideAll();
            localStorage.removeItem('hideTopics');
            return;
        }
    }

    localStorage.setItem('hideTopics', 'true');
    changeButtonStateToShowAll();
}

function hideTopicsDuringRender () {
    const isHideTopics = localStorage.getItem('hideTopics');

    if (isHideTopics) {
        hideTopics();
    }
}

const details = document.querySelectorAll('details');
const topicsInteractButton = document.querySelector('button.hide-topics');
const observer = new MutationObserver(mutationsInteract);

topicsInteractButton.addEventListener('click', hideTopics);

details.forEach(detail => {
    observer.observe(detail, {attributes: true});
});

if (location.href.includes('my')) {
    hideTopicsDuringRender();
}