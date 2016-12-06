import $ from "expose?$!expose?jQuery!jquery";
import "expose?fetch!./fetch";
import "expose?notify!./notify";

import "jquery-ui/ui/widget.js";
import "jquery-ui/ui/widgets/sortable.js";
// todo fixme
import 'checkboxes.js/src/jquery.checkboxes.js';

import './sidebar';
import './file/file';
import './file/wysiwyg';

$(document)
    .on('click', '.b-filter-button', e => {
        e.preventDefault();
        $(e.target).toggleClass('b-filter-button_active');
        $('.b-filter-form').toggleClass('b-filter-form_active');
    })
    .on('click', '.b-flash', e => {
        e.preventDefault();
        $(e.target).closest('.b-flash').remove();
    });