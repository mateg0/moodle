function block_content_minimization_switch(id) {
    section_selector = 'section#inst' + id;
    block_section = document.querySelector(section_selector);
    block_header = document.querySelector(section_selector + ' > div.card-body > h5.card-title');
    block_switcher = document.querySelector(section_selector + ' > div.card-body > div.block-minimize-switch');
    block_content = document.querySelector(section_selector + ' > div.card-body > div.card-text.content');
    if (block_section.classList.contains('no-header')) {
        block_header.classList.toggle('hidden');
    }
    block_switcher.classList.toggle('minimized');
    block_content.classList.toggle('hidden');
}