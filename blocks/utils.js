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
}