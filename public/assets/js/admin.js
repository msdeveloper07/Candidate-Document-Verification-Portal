/* Admin console behaviour. jQuery, no build step. */
(function ($) {
    'use strict';

    $(function () {

        // ---- Sidebar (mobile) ----
        $('[data-sidebar-toggle]').on('click', function () {
            $('[data-sidebar]').toggleClass('open');
            $('[data-scrim]').toggleClass('show');
        });

        $('[data-scrim]').on('click', function () {
            $('[data-sidebar]').removeClass('open');
            $(this).removeClass('show');
        });

        // ---- Destructive actions ask first ----
        $(document).on('submit', 'form[data-confirm]', function (e) {
            if (!window.confirm($(this).data('confirm'))) {
                e.preventDefault();
            }
        });

        // ---- Show / hide password ----
        $('[data-toggle-password]').on('click', function () {
            var $field = $($(this).data('toggle-password'));
            var isText = $field.attr('type') === 'text';
            $field.attr('type', isText ? 'password' : 'text');
            $(this).attr('aria-label', isText ? 'Show password' : 'Hide password');
        });

        // ---- Copy the invite link ----
        $('[data-copy-trigger]').on('click', function () {
            var $btn = $(this);
            var value = $btn.closest('.input-group').find('[data-copy-source]').val();

            var done = function () {
                var original = $btn.text();
                $btn.text('Copied').prop('disabled', true);
                setTimeout(function () { $btn.text(original).prop('disabled', false); }, 1600);
            };

            if (navigator.clipboard) {
                navigator.clipboard.writeText(value).then(done);
            } else {
                var $tmp = $('<textarea>').val(value).appendTo('body').select();
                document.execCommand('copy');
                $tmp.remove();
                done();
            }
        });

        // ---- Country picker fills the dial code ----
        $('[data-country-select]').on('change', function () {
            var $opt = $(this).find('option:selected');
            if ($opt.data('dial')) {
                $('[data-dial-code]').val($opt.data('dial'));
                $('[data-country-name]').val($opt.data('name'));
            }
        });

        // ---- Select / clear every requested document ----
        $('[data-toggle-all-requirements]').on('click', function () {
            var $boxes = $('input[name="requirements[]"]');
            var allOn = $boxes.length === $boxes.filter(':checked').length;
            $boxes.prop('checked', !allOn);
        });

        // ---- Review modal: which button was pressed decides the outcome ----
        $(document).on('click', '[data-decide]', function () {
            var $form = $(this).closest('form[data-review-form]');
            var decision = $(this).data('decide');

            $form.find('[data-decision]').val(decision);

            if (decision === 'reject' && !$.trim($form.find('textarea[name="remarks"]').val())) {
                window.alert('Add a note telling the candidate what to fix before asking for a re-upload.');
                return false;
            }
        });
    });
})(jQuery);

/* Copy a link straight from a button, e.g. the candidates list row menu. */
$(document).on('click', '[data-copy-value]', function () {
    var $btn  = $(this);
    var value = $btn.data('copy-value');
    var original = $btn.text();

    var done = function () {
        $btn.text('Copied');
        setTimeout(function () { $btn.text(original); }, 1600);
    };

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(value).then(done);
    } else {
        // http://localhost and older browsers have no clipboard API.
        var $tmp = $('<textarea>').val(value).css({position: 'fixed', opacity: 0}).appendTo('body');
        $tmp[0].select();
        try { document.execCommand('copy'); done(); } catch (e) { window.prompt('Copy this link:', value); }
        $tmp.remove();
    }
});

/* Placeholder chips insert at the caret of whichever field was last focused. */
(function () {
    var $lastField = null;

    $(document).on('focus', '[data-tag-target]', function () { $lastField = $(this); });

    $(document).on('click', '[data-tag]', function () {
        var $field = $lastField && $lastField.length ? $lastField : $('[data-tag-target]').first();
        if (!$field.length) { return; }

        var tag   = $(this).data('tag');
        var el    = $field[0];
        var start = el.selectionStart !== undefined ? el.selectionStart : el.value.length;
        var end   = el.selectionEnd !== undefined ? el.selectionEnd : el.value.length;

        el.value = el.value.slice(0, start) + tag + el.value.slice(end);
        el.focus();
        el.selectionStart = el.selectionEnd = start + tag.length;
    });
})();
