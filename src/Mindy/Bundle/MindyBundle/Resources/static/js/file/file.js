/* The jQuery UI widget factory, can be omitted if jQuery UI is already included */
require('imports?define=>false&exports=>false!blueimp-file-upload/js/vendor/jquery.ui.widget.js');
/* The Iframe Transport is required for browsers without support for XHR file uploads */
require('imports?define=>false&exports=>false!blueimp-file-upload/js/jquery.iframe-transport.js');
/* The basic File Upload plugin */
require('imports?define=>false&exports=>false!blueimp-file-upload/js/jquery.fileupload.js');
/* The File Upload processing plugin */
require('imports?define=>false&exports=>false!blueimp-file-upload/js/jquery.fileupload-process.js');
/* The File Upload validation plugin */
require('imports?define=>false&exports=>false!blueimp-file-upload/js/jquery.fileupload-validate.js');

$(() => {
    $('.fileupload').fileupload({
        dataType: 'json',
        limitConcurrentUploads: 1,
        sequentialUploads: true,
        dropZone: $('#dropzone'),
        add: function (e, data) {
            $('#progress').removeClass('hide').addClass('visible');
            data.submit();
        },
        progress: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('#progress').find('.progress-state').width(progress + '%').text(progress + '%');
        },
        done: function (data) {
            fetch.get('').then(data => {
                $('.files-container').replaceWith($(data).find('.files-container'));
            });

            var $progress = $('#progress');
            $progress.removeClass('visible').addClass('hide');

            notify({ title: 'Файлы загружены' });
        }
    });

    $(document)
        .on('dragover dragenter', e => {
            var $dropZone = $('#dropzone'),
                timeout = window.dropZoneTimeout;
            if (!timeout) {
                $dropZone.addClass('in');
            } else {
                clearTimeout(timeout);
            }

            var found = false,
                node = e.target;
            do {
                if (node === $dropZone[0]) {
                    found = true;
                    break;
                }
                node = node.parentNode;
            } while (node != null);
            if (found) {
                $dropZone.addClass('hover');
            } else {
                $dropZone.removeClass('hover');
            }

            window.dropZoneTimeout = setTimeout(function () {
                window.dropZoneTimeout = null;
                $dropZone.removeClass('in hover');
            }, 100);
        }) 
        .on('click', '.files-create-directory', e => {
            e.preventDefault();

            var $this = $(e.target).closest('a');
            let value = prompt('Введите имя директории:');
            if (value) {
                fetch.post($this.attr('href'), {}, { directory: value }).then(data => {
                    if (data.status) {
                        notify({ title: 'Директория создана' });

                        fetch.get('').then(data => {
                            $('.files-container').replaceWith($(data).find('.files-container'));
                        });
                    } else {
                        notify({ title: 'Ошибка', message: 'При создании директории возникла ошибка' });
                    }
                });
            }
        })
        .on('click', '.file-manager__table-remove', e => {
            e.preventDefault();

            var $this = $(e.target);
            if (confirm($this.attr('data-confirm-message'))) {
                fetch.post($this.attr('href')).then(data => {
                    $this.closest('tr').remove();

                    notify({ title: 'Файл удален' });
                });
            }
        })
        .on('click', '.file-manager__table-copy', e => {
            e.preventDefault();

            document.querySelector('.file-manager__table-input').select();
            document.execCommand('copy');

            notify({ title: 'Ссылка скопирована' });
        })
        .on('click', '.file-manager__table-paste', e => {
            e.preventDefault();

            var name = $(e.target).attr('data-name'),
                url = $(e.target).attr('data-url'),
                parts = name.split('.'),
                ext = parts[parts.length-1];

            console.log(ext);

            if (ext.toLowerCase() == "png" || ext.toLowerCase() == "jpg" || ext.toLowerCase() == "jpeg") {
                top.tinyMCE.activeEditor.insertContent("<img src='"+url+"'>");
            } else {
                top.tinyMCE.activeEditor.insertContent("<a href='"+url+"'>"+name+"</a>");
            }

            notify({ title: 'Элемент вставлен' });
        })
        .on('click', '.file-manager__table-input', e => {
            document.querySelector('.file-manager__table-input').select();
        });
});