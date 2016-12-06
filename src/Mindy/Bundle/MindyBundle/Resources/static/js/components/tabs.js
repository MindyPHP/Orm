$(document)
    .on('click', '.b-tab-header__item:not(.b-tab-header__item_active)', function (e) {
        var $this = $(e.target),
            $header = $this.closest('.b-tab-header');

        if ($this.is('a') && $this.attr('href') === '#') {
            e.preventDefault();
            $this = $this.closest('.b-tab-header__item');
        }

        $header.find('.b-tab-header__item').removeClass('b-tab-header__item_active');
        $this.addClass('b-tab-header__item_active');

        $('.b-tab-content__item')
            .removeClass('b-tab-content__item_active')
            .eq($this.index())
            .addClass('b-tab-content__item_active');
    });
