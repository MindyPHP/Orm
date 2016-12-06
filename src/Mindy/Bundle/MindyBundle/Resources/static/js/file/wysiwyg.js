import $ from 'jquery';

// Helper function to get parameters from the query string.
let getUrlParam = paramName => {
    let reParam = new RegExp('(?:[\?&]|&)' + paramName + '=([^&]+)', 'i'),
        match = window.location.search.match(reParam);

    return (match && match.length > 1) ? match[1] : null;
};

let ckeditorInitialize = () => {
    $(document).on('click', '.file-manager__table-paste', e => {
        e.preventDefault();
        let $link = $(e.target),
            url = $link.attr('data-url'),
            text = $link.attr('data-text');

        let funcNum = getUrlParam('CKEditorFuncNum');
        window.opener.CKEDITOR.tools.callFunction(funcNum, url);
        window.close();
    });
};

let tinymceInitialize = () => {

};

$(() => {
    let $container = $('[data-filemanager]');

    switch ($container.attr('data-wysiwyg')) {
        case 'ckeditor':
            ckeditorInitialize();
            break;
        case 'tinymce':
            tinymceInitialize();
            break;
        default:
            break;
    }
});