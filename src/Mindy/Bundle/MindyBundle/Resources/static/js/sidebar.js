function setCookie(name, value) {
    document.cookie = name + "=" + value + "; path=/;";
}

function outClick(e, elem, callback) {
    if (e.target != elem[0] && !elem.has(e.target).length) {
        if (callback) {
            callback();
        }

        return true;
    } else {
        return false;
    }
}

/**
 * Функция добавляет или убирает отступы для header и page
 * @param val
 */
var togglePadding = function (val) {
    let sidebar = $(".b-sidebar");
    if (val) {
        $(".b-header").addClass("b-header_padding");
        $(".b-page").addClass("b-page_padding");
        $(".b-header__burger").addClass("b-header__burger_hide");
        sidebar.removeClass("b-sidebar_shadow");
    } else {
        $(".b-header").removeClass("b-header_padding");
        $(".b-page").removeClass("b-page_padding");
        $(".b-header__burger").removeClass("b-header__burger_hide");
        sidebar.addClass("b-sidebar_shadow");
    }
};

$(document)
    .on('click', ".b-header__burger", function (e) {
        e.preventDefault();

        var padding = $(".b-sidebar-buttons__pin").hasClass("b-sidebar-buttons__pin_checked");
        togglePadding(padding);

        let sidebar = $(".b-sidebar");
        /**
         * Ставим задержку чтобы обработчик скрытия сайдбара
         * не сработал раньше времени
         */
        setTimeout(function () {
            sidebar.addClass("b-sidebar_show");
        }, 10);
    })
    .on("click", ".b-sidebar-buttons__burger", function (e) {
        e.preventDefault();

        let sidebar = $(".b-sidebar");
        sidebar.removeClass("b-sidebar_show");
        togglePadding(false);

    })
    .on("click", ".b-sidebar-buttons__pin", function (e) {
        e.preventDefault();

        $(this).toggleClass("b-sidebar-buttons__pin_checked");

        var padding = $(this).hasClass("b-sidebar-buttons__pin_checked");
        setCookie('__sidebar_padding', padding);
        togglePadding(padding);
    })
    .on('click', ".b-header__user-link", e => {
        e.preventDefault();

        $(this).toggleClass("b-header__user-link_active");
        setTimeout(function () {
            $(".b-user-menu").toggleClass("b-user-menu_show");
        }, 10);
    })
    .on("click", e => {
        let sidebar = $(".b-sidebar");

        /**
         * Здесь скрываем сайдбар при клике вне области сайдбара, если он не закреплен
         */
        if (sidebar.hasClass("b-sidebar_show") && !$(".b-sidebar-buttons__pin").hasClass("b-sidebar-buttons__pin_checked")) {
            // if (e.target != sidebar[0] && !sidebar.has(e.target).length) {
            //     $(".b-sidebar-buttons__burger").trigger("click");
            // }
            outClick(e, sidebar, function () {
                $(".b-sidebar-buttons__burger").trigger("click");
            })
        }

        var userMenu = $(".b-user-menu");

        if (userMenu.hasClass("b-user-menu_show") && outClick(e, userLink)) {
            outClick(e, userMenu, function () {
                userLink.toggleClass("b-header__user-link_active");
                $(".b-user-menu").toggleClass("b-user-menu_show");
            })
        }
    });
