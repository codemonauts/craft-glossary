import tippy from 'tippy.js';

tippy('.glossary', {
    content(reference) {
        const id = reference.getAttribute('data-glossary-term');
        const template = document.getElementById(id);
        return template.innerHTML;
    },
    theme: 'light',
    allowHTML: true,
});