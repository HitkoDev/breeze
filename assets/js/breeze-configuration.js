jQuery(document).ready(function ($) {
    // database clean tabs
    $('input[name="all_control"]').click(function () {
        var checked = $(this).is(':checked');
        if (checked == true) {
            $(".clean-data").prop("checked", true);
        } else {
            $(".clean-data").prop("checked", false);
        }
    });

    $('.clean-data').click(function () {
        var checked = $(this).is(':checked');
        if (checked == false) {
            $('input[name="all_control"]').prop('checked', false);
        }
    });

    function initRemoveBtn() {
        $('.breeze-input-group span.item-remove').unbind('click').click(function () {
            var inputURL = $(this).closest('.breeze-input-group');
            inputURL.fadeOut(300, function () {
                inputURL.remove();
                validateMoveButtons();
            });
        });
    }

    initRemoveBtn();

    function initSortableHandle() {
        $('.breeze-list-url').sortable({
            handle: $('span.sort-handle'),
            stop: validateMoveButtons
        });
    }

    initSortableHandle();

    function initMoveButtons() {
        $('.sort-handle span').unbind('click').click(function (e) {
            var inputGroup = $(this).parents('.breeze-input-group');
            if ($(this).hasClass('moveUp')) {
                inputGroup.insertBefore(inputGroup.prev());
            } else {
                inputGroup.insertAfter(inputGroup.next());
            }

            validateMoveButtons();
        });
    }

    initMoveButtons();

    function validateMoveButtons() {
        var listURL = $('.breeze-list-url');
        listURL.find('.breeze-input-group').find('.sort-handle').find('span').removeClass('blur');
        listURL.find('.breeze-input-group:first-child').find('.moveUp').addClass('blur');
        listURL.find('.breeze-input-group:last-child').find('.moveDown').addClass('blur');
    }

    validateMoveButtons();

    $('button.add-url').unbind('click').click(function () {
        var defer = $(this).attr('id').indexOf('defer') > -1;
        var listURL = $(this).closest('td').find('.breeze-list-url');
        var html = '';
        var listInput = listURL.find('.breeze-input-group');
        var emptyInput = false;

        listInput.each(function () {
            var thisInput = $(this).find('.breeze-input-url');
            if (thisInput.val().trim() === '') {
                thisInput.focus();
                emptyInput = true;
                return false;
            }
        });

        if (emptyInput) return false;

        html += '<div class="breeze-input-group">';
        html += '   <span class="sort-handle">';
        html += '       <span class="dashicons dashicons-arrow-up moveUp"></span>';
        html += '       <span class="dashicons dashicons-arrow-down moveDown"></span>';
        html += '   </span>';
        html += '   <input type="text" size="98"';
        html += 'class="breeze-input-url"';
        if (!defer) {
            html += 'name="move-to-footer-js[]"';
        } else {
            html += 'name="defer-js[]"';
        }
        html += 'placeholder="Enter URL..."';
        html += 'value="" />';
        html += '       <span class="dashicons dashicons-no item-remove" title="Remove"></span>';
        html += '</div>';

        listURL.append(html);
        initRemoveBtn();
        initSortableHandle();
        initMoveButtons();
        validateMoveButtons();
    });

    // Change tab
    $("#breeze-tabs .nav-tab").click(function (e) {
        e.preventDefault();
        $("#breeze-tabs .nav-tab").removeClass('active');
        $(e.target).addClass('active');
        id_tab = $(this).data('tab-id');
        $("#tab-" + id_tab).addClass('active');
        $("#breeze-tabs-content .tab-pane").removeClass('active');
        $("#tab-content-" + id_tab).addClass('active');
        document.cookie = 'breeze_active_tab=' + id_tab;

        // Toggle right-side content
        if (id_tab === 'faq') {
            $('#breeze-and-cloudways').hide();
            $('#faq-content').accordion({
                collapsible: true,
                animate: 200,
                header: '.faq-question',
                heightStyle: 'content'
            });
        } else {
            $('#breeze-and-cloudways').show();
        }
    });

    // Cookie do
    function setTabFromCookie() {
        active_tab = getCookie('breeze_active_tab');
        if (!active_tab) {
            active_tab = 'basic';
        }

        if ($("#tab-" + active_tab).length === 0) { // Tab not found (multisite case)
            firstTab = $('#breeze-tabs').find('a:first-child');
            tabType = firstTab.attr('id').replace('tab-', '');
            firstTab.addClass('active');
            $("#tab-content-" + tabType).addClass('active');
        } else {
            $("#tab-" + active_tab).addClass('active');
            $("#tab-content-" + active_tab).addClass('active');
        }

        // Toggle right-side content
        if (active_tab === 'faq') {
            $('#breeze-and-cloudways').hide();
            $('#faq-content').accordion({
                collapsible: true,
                animate: 200,
                header: '.faq-question',
                heightStyle: 'content'
            });
        } else {
            $('#breeze-and-cloudways').show();
        }
    }

    function getCookie(cname) {
        var name = cname + "=";
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') c = c.substring(1);
            if (c.indexOf(name) == 0) return c.substring(name.length, c.length);
        }
        return "";
    }

    setTabFromCookie();

    // Sub-site settings toggle.
    var global_tabs = [
        'faq'
    ];
    var save_settings_inherit_form_on_submit = true;
    var settings_inherit_form_did_change = false;
    var $settings_inherit_form = $('#breeze-inherit-settings-toggle');
    if ($settings_inherit_form.length) {
        $('input', $settings_inherit_form).on('change', function () {
            var inherit = $(this).val() == '1';

            $('#breeze-tabs').toggleClass('tabs-hidden', inherit);
            $('#breeze-tabs-content').toggleClass('tabs-hidden', inherit);

            $('#breeze-tabs .nav-tab').each(function () {
                var tab_id = $(this).data('tab-id');

                if ($.inArray(tab_id, global_tabs) === -1) {
                    $(this).toggleClass('inactive', inherit);
                    $('#breeze-tabs-content #tab-content-' + tab_id).toggleClass('inactive', inherit);
                }
            });

            settings_inherit_form_did_change = !$(this).parents('.radio-field').hasClass('active');

            $('p.disclaimer', $settings_inherit_form).toggle(settings_inherit_form_did_change);
        });

        $('#breeze-tabs-content form').on('submit', function (event) {
            var $form = $(this);

            if (save_settings_inherit_form_on_submit && settings_inherit_form_did_change) {
                event.preventDefault();

                $.ajax({
                    url: window.location,
                    method: 'post',
                    data: $settings_inherit_form.serializeArray(),

                    beforeSend: function () {
                        $settings_inherit_form.addClass('loading');
                    },

                    complete: function () {
                        $settings_inherit_form.removeClass('loading');

                        // Continue form submit.
                        settings_inherit_form_did_change = false;
                        $form.submit();
                    },

                    success: function () {
                        $('input:checked', $settings_inherit_form).parents('.radio-field').addClass('active').siblings().removeClass('active');
                    }
                });
            } else {
                return;
            }
        });
    }

    // Database optimization.
    $('#breeze-database-optimize').on('click', function (event) {
        save_settings_inherit_form_on_submit = false;
    });
    $('#tab-content-database .submit input').on('click', function (event) {
        $('#tab-content-database input[type=checkbox]').attr('checked', false);
    });

    function remove_query_arg(url, arg) {
        var urlparts = url.split('?');
        if (urlparts.length >= 2) {
            var prefix = encodeURIComponent(arg) + '=';
            var pars = urlparts[1].split(/[&;]/g);

            for (var i = pars.length; i-- > 0;) {
                if (pars[i].lastIndexOf(prefix, 0) !== -1) {
                    pars.splice(i, 1);
                }
            }

            return urlparts[0] + (pars.length > 0 ? '?' + pars.join('&') : '');
        }
        return url;
    }

    // Remove notice query args from URL.
    if (window.history && typeof window.history.pushState === 'function') {
        var clean_url = remove_query_arg(window.location.href, 'save-settings');
        clean_url = remove_query_arg(clean_url, 'database-cleanup');
        window.history.pushState(null, null, clean_url);
    }
});