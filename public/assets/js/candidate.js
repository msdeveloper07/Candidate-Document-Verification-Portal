/* Candidate portal behaviour. jQuery, no build step. */
(function ($) {
    'use strict';

    // ---------------------------------------------------------
    // OTP: six boxes that behave like one field
    // ---------------------------------------------------------
    function wireOtpInputs() {
        var $wrap = $('[data-otp-inputs]');
        if (!$wrap.length) { return; }

        var $boxes = $wrap.find('input');

        $boxes.on('input', function () {
            var $this = $(this);
            $this.val($this.val().replace(/\D/g, '').slice(0, 1));
            $this.toggleClass('filled', $this.val() !== '');

            if ($this.val()) {
                $this.next('input').trigger('focus');
            }
        });

        $boxes.on('keydown', function (e) {
            var $this = $(this);
            if (e.key === 'Backspace' && !$this.val()) {
                $this.prev('input').trigger('focus').val('').removeClass('filled');
            }
            if (e.key === 'ArrowLeft') { $this.prev('input').trigger('focus'); }
            if (e.key === 'ArrowRight') { $this.next('input').trigger('focus'); }
        });

        // Pasting the whole code fills every box.
        $boxes.on('paste', function (e) {
            var text = (e.originalEvent.clipboardData || window.clipboardData).getData('text');
            var digits = text.replace(/\D/g, '').split('');
            if (!digits.length) { return; }

            e.preventDefault();
            $boxes.each(function (i) {
                $(this).val(digits[i] || '').toggleClass('filled', !!digits[i]);
            });
            $boxes.eq(Math.min(digits.length, $boxes.length - 1)).trigger('focus');
        });

        // Submit as soon as the last box is filled.
        $('[data-otp-form]').on('input', function () {
            var complete = $boxes.filter(function () { return $(this).val() !== ''; }).length === $boxes.length;
            if (complete) { $(this).trigger('submit'); }
        });
    }

    // ---------------------------------------------------------
    // Resend cooldown
    // ---------------------------------------------------------
    function wireResendTimer() {
        var $btn = $('[data-resend-button]');
        if (!$btn.length) { return; }

        var remaining = parseInt($btn.data('wait'), 10) || 0;
        var label = $btn.text().trim();

        if (remaining <= 0) { return; }

        var tick = setInterval(function () {
            remaining -= 1;
            if (remaining <= 0) {
                clearInterval(tick);
                $btn.prop('disabled', false).text(label);
            } else {
                $btn.text('Send a new code in ' + remaining + 's');
            }
        }, 1000);
    }

    // ---------------------------------------------------------
    // Sleeves open and close
    // ---------------------------------------------------------
    function wireSleeves() {
        $(document).on('click', '[data-sleeve-toggle]', function () {
            var $sleeve = $(this).closest('[data-sleeve]');
            $sleeve.toggleClass('open');
            $sleeve.find('.sleeve-body').attr('hidden', !$sleeve.hasClass('open'));
        });
    }

    // ---------------------------------------------------------
    // Uploads
    // ---------------------------------------------------------
    function humanSize(bytes) {
        if (bytes >= 1048576) { return (bytes / 1048576).toFixed(1) + ' MB'; }
        if (bytes >= 1024) { return Math.round(bytes / 1024) + ' KB'; }
        return bytes + ' B';
    }

    function stampMarkup(tone, text) {
        return '<span class="stamp stamp-' + (tone || 'warn') + '">' + text + '</span>';
    }

    function showError($form, message) {
        $form.find('[data-upload-error]').removeClass('d-none').text(message);
        $form.find('[data-progress-track]').addClass('d-none');
    }

    function refreshProgress(summary, canSubmit) {
        // References are optional, so only documents count towards clearance.
        var done  = summary.approved;
        var left  = summary.missing;

        $('[data-count-approved]').text(done);
        $('[data-count-total]').text(summary.total);
        $('[data-bar-done]').text(done);

        // The card itself is the gauge — keep a sliver visible at zero.
        $('[data-clearance-fill]').css('height', Math.max(summary.percent, 4) + '%');

        var cleared = canSubmit && summary.approved === summary.total;
        $('[data-progress-panel]').toggleClass('is-cleared', cleared);

        var headline, sub;
        if (cleared) {
            headline = 'Cleared for assignment';
            sub = 'Everything has been checked and accepted. Nothing more is needed from you.';
        } else if (summary.rejected > 0) {
            headline = summary.rejected + ' item' + (summary.rejected === 1 ? '' : 's') + ' need a new copy';
            sub = 'Open the items marked below — each one says what to change.';
        } else if (left > 0) {
            headline = 'Still to send';
            sub = 'You can stop and come back; anything you have sent is saved.';
        } else {
            headline = 'With our team now';
            sub = 'We are checking your files. You will get an email as each one is decided.';
        }
        $('[data-progress-headline]').text(headline);
        $('.clearance-sub').text(sub);

        $('[data-bar-hint]').text(
            canSubmit ? 'Ready to send'
                      : (summary.rejected > 0 ? summary.rejected + ' to replace'
                                              : left + ' left to do')
        );

        $('[data-submit-button]').prop('disabled', !canSubmit);
        $('[data-submit-hint]').text(canSubmit
            ? 'Everything requested is in.'
            : 'Every document must be uploaded first.');

        refreshNextUp();
    }

    /* Recompute the "next up" card from the DOM after an upload. */
    function refreshNextUp() {
        var $card = $('.nextup');
        if (!$card.length) { return; }

        var $target = $('.sleeve').filter(function () {
            return $(this).hasClass('is-empty') || $(this).hasClass('is-rejected');
        }).first();

        if (!$target.length) {
            $card.remove();
            return;
        }

        var rejected = $target.hasClass('is-rejected');
        $card.attr('data-jump-to', $target.attr('id'));
        $card.find('.lead').text(rejected ? 'Needs a new copy' : 'Next up');
        $card.find('.what').text($target.find('.sleeve-head .title').first().text().trim());
        $card.find('.why').text(rejected
            ? 'The reviewer left a note'
            : $target.find('.sleeve-head .desc').first().text().trim());
    }

    function uploadFile($form, file) {
        var maxKb = parseInt($form.data('max-kb'), 10);
        var typeId = $form.data('type-id');
        var accept = String($form.data('accept') || '').split(',');
        var ext = '.' + file.name.split('.').pop().toLowerCase();

        $form.find('[data-upload-error]').addClass('d-none').text('');

        if (accept.length && accept.indexOf(ext) === -1) {
            showError($form, 'Use one of these formats: ' + accept.join(', ') + '.');
            return;
        }

        if (file.size > maxKb * 1024) {
            showError($form, 'That file is ' + humanSize(file.size) + '. Keep it under ' + Math.round(maxKb / 1024) + ' MB.');
            return;
        }

        var data = new FormData();
        data.append('document_type_id', typeId);
        data.append('file', file);

        var $track = $form.find('[data-progress-track]').removeClass('d-none');
        var $fill = $form.find('[data-progress-fill]').css('width', '0%');

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: data,
            processData: false,
            contentType: false,
            dataType: 'json',
            xhr: function () {
                var xhr = $.ajaxSettings.xhr();
                if (xhr.upload) {
                    xhr.upload.addEventListener('progress', function (e) {
                        if (e.lengthComputable) {
                            $fill.css('width', Math.round((e.loaded / e.total) * 100) + '%');
                        }
                    });
                }
                return xhr;
            }
        }).done(function (response) {
            var $sleeve = $form.closest('[data-sleeve]');
            var doc = response.document;

            $fill.css('width', '100%');
            setTimeout(function () { $track.addClass('d-none'); }, 500);

            // Swap in the uploaded file row.
            var $current = $sleeve.find('[data-current-file]').removeClass('d-none');
            if (!$current.find('.file-row').length) {
                $current.html(
                    '<div class="file-row">' +
                    '<span class="glyph"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg></span>' +
                    '<div class="flex-grow-1 min-width-0"><div class="fname" data-file-name></div>' +
                    '<div class="fmeta" data-file-meta></div><div class="fmeta" data-file-state></div></div>' +
                    '<div class="d-flex gap-1 flex-shrink-0">' +
                    '<a class="btn btn-quiet btn-sm" target="_blank" rel="noopener" data-file-link>View</a>' +
                    '<a class="btn btn-quiet btn-sm" data-file-save>Save</a>' +
                    '<button type="button" class="btn btn-quiet btn-sm" data-replace-trigger>Replace</button>' +
                    '</div>' +
                    '</div>'
                );
            }
            $current.find('[data-file-name]').text(doc.name);
            $current.find('[data-file-meta]').text(doc.size + ' · uploaded ' + doc.uploadedAt);
            $current.find('[data-file-state]').text(doc.statusHint || '');
            $current.find('[data-file-link]').attr('href', doc.previewUrl);
            $current.find('[data-file-save]').attr('href', doc.downloadUrl);

            // Update the stamp, the sleeve tint and the meter notch.
            $sleeve.find('[data-sleeve-stamp]').html(stampMarkup(doc.statusTone, doc.statusText));
            $sleeve.removeClass('is-empty is-pending is-uploaded is-under_review is-approved is-rejected')
                   .addClass('is-' + doc.status);
            $sleeve.find('.reviewer-note').remove();

            $('[data-notch-for="' + $sleeve.data('type-id') + '"]')
                .removeClass('done redo review')
                .addClass(doc.status === 'approved' ? 'done' : (doc.status === 'rejected' ? 'redo' : 'review'));

            $form.find('.dropzone .primary').text('Choose a replacement file');
            $form.find('[data-file-input]').val('');

            refreshProgress(response.summary, response.canSubmit);

        }).fail(function (xhr) {
            var message = 'Upload failed. Check your connection and try again.';
            if (xhr.responseJSON) {
                if (xhr.responseJSON.errors && xhr.responseJSON.errors.file) {
                    message = xhr.responseJSON.errors.file[0];
                } else if (xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
            }
            showError($form, message);
        });
    }

    function wireDropzones() {
        $(document).on('change', '[data-file-input]', function () {
            if (this.files && this.files.length) {
                uploadFile($(this).closest('form[data-upload-form]'), this.files[0]);
            }
        });

        $(document).on('dragover dragenter', '[data-dropzone]', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('dragging');
        });

        $(document).on('dragleave dragend drop', '[data-dropzone]', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('dragging');
        });

        $(document).on('drop', '[data-dropzone]', function (e) {
            var files = e.originalEvent.dataTransfer.files;
            if (files && files.length) {
                uploadFile($(this).closest('form[data-upload-form]'), files[0]);
            }
        });

        // Keyboard users get the same affordance.
        $(document).on('keydown', '[data-dropzone]', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                $(this).find('[data-file-input]').trigger('click');
            }
        });
    }

    $(function () {
        wireOtpInputs();
        wireResendTimer();
        wireSleeves();
        wireDropzones();
    });
})(jQuery);

/* "Replace" on an existing file just opens that sleeve's file picker. */
$(document).on('click', '[data-replace-trigger]', function () {
    $(this).closest('.sleeve').find('[data-file-input]').trigger('click');
});

/* "Next up" scrolls to the item and opens it. */
$(document).on('click', '[data-jump-to]', function () {
    var $target = $('#' + $(this).data('jump-to'));
    if (!$target.length) { return; }

    if ($target.hasClass('sleeve') && !$target.hasClass('open')) {
        $target.addClass('open').find('.sleeve-body').removeAttr('hidden');
    }

    var top = $target.offset().top - 16;
    if ('scrollBehavior' in document.documentElement.style) {
        window.scrollTo({ top: top, behavior: 'smooth' });
    } else {
        window.scrollTo(0, top);
    }
});
