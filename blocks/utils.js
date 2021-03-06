function block_student_involvement_switch(event) {
    block_student_involvement = document.querySelector('section.block_student_involvement');
    student_header_wrapper = document.getElementById('student-header-wrapper');
    student_header_wrapper_mini = document.getElementById('student-header-wrapper-mini');
    horizontal_sections = document.querySelectorAll('#block-region-horizontal > section');

    function switch_to_mini() {
        student_header_wrapper.classList.toggle('hidden', true);
        student_header_wrapper_mini.classList.toggle('hidden', false);
        horizontal_sections.forEach(function(item) {
            item.classList.toggle('fullwidth', true);
        });
    }

    function switch_to_maxi() {
        student_header_wrapper.classList.toggle('hidden', false);
        student_header_wrapper_mini.classList.toggle('hidden', true);
        horizontal_sections.forEach(function(item) {
            item.classList.toggle('fullwidth', false);
        });
    }

    if (document.documentElement.clientWidth > 1810) {
        minimized = document.querySelectorAll('#block-region-horizontal > section div.block-minimize-switch.minimized');
        if (minimized.length > 0) {
            switch_to_mini();
        } else {
            switch_to_maxi();
        }
    } else {
        switch_to_mini();
    }
}

function block_content_minimization_switch(section_id) {
    section_selector = 'section#' + section_id;
    section_content_selector = section_selector + ' > div.card-body ';
    block_section = document.querySelector(section_selector);
    block_header = document.querySelector(section_content_selector + 'h5.card-title');
    block_switcher = document.querySelector(section_content_selector + 'div.block-minimize-switch');
    block_content = document.querySelector(section_content_selector + ' div.card-text.content');
    if (block_section.classList.contains('no-header')) {
        block_header.classList.toggle('hidden');
    }
    if (block_section.classList.contains('transparent') || block_section.classList.contains('was-transparent')) {
        block_section.classList.toggle('transparent');
        block_section.classList.toggle('was-transparent');
    }
    block_switcher.classList.toggle('minimized');
    block_content.classList.toggle('hidden');

    if (!isHideRenderProcess()) {
        if (block_content.classList.contains('hidden')) {
            addSectionToHidden(section_id);
        } else {
            removeSectionFromHidden(section_id);
        }
    }

    if (block_section.parentElement === document.getElementById('block-region-horizontal')) {
        block_student_involvement_switch();
    }
}

function addSectionToHidden(sectionId) {
    const hiddenSections = getHiddenSectionsArray();

    hiddenSections.push(sectionId);
    localStorage.setItem('hiddenSections', hiddenSections);
}

function removeSectionFromHidden(sectionId) {
    const hiddenSections = getHiddenSectionsArray();
    const indexOfSection = hiddenSections.indexOf(sectionId);

    if (indexOfSection !== -1) {
        hiddenSections.splice(indexOfSection, 1);
    }

    localStorage.setItem('hiddenSections', hiddenSections);
}

function getHiddenSectionsArray() {
    const hiddenSections = localStorage.getItem('hiddenSections');

    let hiddenSectionsArray = [];

    if (!hiddenSections) hiddenSectionsArray = [];
    else hiddenSectionsArray = hiddenSections.split(',');

    return hiddenSectionsArray;
}

function hideSectionsAfterRender() {
    const hiddenSections = getHiddenSectionsArray();

    localStorage.setItem('hideProcess', 'true');

    if (hiddenSections.length) {
        hiddenSections.forEach(sectionId => block_content_minimization_switch(sectionId));
    }

    localStorage.removeItem('hideProcess');
}

function isHideRenderProcess() {
    return localStorage.getItem('hideProcess');
}

if (document.querySelector('.student-header-wrapper')) {
    block_student_involvement_switch();
    window.addEventListener('resize', block_student_involvement_switch);
}
document.addEventListener('DOMContentLoaded', hideSectionsAfterRender);