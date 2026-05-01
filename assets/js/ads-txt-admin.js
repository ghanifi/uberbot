(function($) {
    'use strict';

    var currentEditId = null;

    $(document).ready(function() {
        // Add entry button
        $('#airlinel-add-entry').on('click', function(e) {
            e.preventDefault();
            addEntry();
        });

        // Edit entry buttons
        $(document).on('click', '.airlinel-edit-entry', function() {
            openEditModal($(this).data('entry-id'));
        });

        // Delete entry buttons
        $(document).on('click', '.airlinel-delete-entry', function() {
            deleteEntry($(this).data('entry-id'));
        });

        // Modal buttons
        $('#airlinel-modal-save').on('click', function() {
            updateEntry();
        });

        $('#airlinel-modal-cancel').on('click', function() {
            closeEditModal();
        });

        // Regenerate file button
        $('#airlinel-regenerate-file').on('click', function() {
            regenerateFile();
        });

        // Enter key support
        $('.airlinel-form-field').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                addEntry();
                return false;
            }
        });

        // Escape key support for modal
        $(document).on('keydown', function(e) {
            if (e.keyCode === 27 && $('#airlinel-edit-modal').hasClass('visible')) {
                closeEditModal();
            }
        });
    });

    function addEntry() {
        var domain = $.trim($('#ads-txt-domain').val());
        var pubId = $.trim($('#ads-txt-pub-id').val());
        var relationship = $.trim($('#ads-txt-relationship').val());
        var certId = $.trim($('#ads-txt-cert-id').val());

        // Clear message
        $('#airlinel-add-message').removeClass('error success').html('');

        // Validate
        if (!domain || !pubId || !relationship) {
            showMessage('#airlinel-add-message', 'Please fill in all required fields.', 'error');
            return;
        }

        var $btn = $('#airlinel-add-entry');
        $btn.prop('disabled', true).text('Adding...');

        $.ajax({
            url: airlinel_ads_txt.ajax_url,
            type: 'POST',
            data: {
                action: 'airlinel_add_ads_txt_entry',
                nonce: airlinel_ads_txt.nonce,
                domain: domain,
                pub_id: pubId,
                relationship: relationship,
                cert_id: certId,
            },
            success: function(response) {
                if (response.success) {
                    showMessage('#airlinel-add-message', 'Entry added successfully!', 'success');
                    $('#ads-txt-domain').val('');
                    $('#ads-txt-pub-id').val('');
                    $('#ads-txt-relationship').val('');
                    $('#ads-txt-cert-id').val('');

                    // Refresh table after 1 second
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    showMessage('#airlinel-add-message', response.data.message || 'Error adding entry.', 'error');
                }
            },
            error: function() {
                showMessage('#airlinel-add-message', 'Error communicating with server.', 'error');
            },
            complete: function() {
                $btn.prop('disabled', false).text('Add Entry');
            }
        });
    }

    function openEditModal(entryId) {
        currentEditId = entryId;

        // Find the row and get data from data attributes
        var $row = $('[data-entry-id="' + entryId + '"]').closest('tr');
        var data = {
            id: $row.data('entry-id'),
            domain: $row.data('domain'),
            pubId: $row.data('pubid'),
            relationship: $row.data('relationship'),
            certId: $row.data('certid')
        };

        $('#modal-domain').val(data.domain);
        $('#modal-pub-id').val(data.pubId);
        $('#modal-relationship').val(data.relationship);
        $('#modal-cert-id').val(data.certId || '');
        $('#airlinel-modal-message').removeClass('error success').html('');

        $('#airlinel-edit-modal').addClass('visible').attr('aria-hidden', 'false');
    }

    function closeEditModal() {
        $('#airlinel-edit-modal').removeClass('visible').attr('aria-hidden', 'true');
        currentEditId = null;
    }

    function updateEntry() {
        if (!currentEditId) return;

        var domain = $.trim($('#modal-domain').val());
        var pubId = $.trim($('#modal-pub-id').val());
        var relationship = $.trim($('#modal-relationship').val());
        var certId = $.trim($('#modal-cert-id').val());

        if (!domain || !pubId || !relationship) {
            showMessage('#airlinel-modal-message', 'Please fill in all required fields.', 'error');
            return;
        }

        var $btn = $('#airlinel-modal-save');
        $btn.prop('disabled', true).text('Saving...');

        $.ajax({
            url: airlinel_ads_txt.ajax_url,
            type: 'POST',
            data: {
                action: 'airlinel_update_ads_txt_entry',
                nonce: airlinel_ads_txt.nonce,
                id: currentEditId,
                domain: domain,
                pub_id: pubId,
                relationship: relationship,
                cert_id: certId,
            },
            success: function(response) {
                if (response.success) {
                    showMessage('#airlinel-modal-message', 'Entry updated successfully!', 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    showMessage('#airlinel-modal-message', response.data.message || 'Error updating entry.', 'error');
                }
            },
            error: function() {
                showMessage('#airlinel-modal-message', 'Error communicating with server.', 'error');
            },
            complete: function() {
                $btn.prop('disabled', false).text('Save Changes');
            }
        });
    }

    function deleteEntry(entryId) {
        if (!confirm('Are you sure you want to delete this entry? This will regenerate the ads.txt file.')) {
            return;
        }

        $.ajax({
            url: airlinel_ads_txt.ajax_url,
            type: 'POST',
            data: {
                action: 'airlinel_delete_ads_txt_entry',
                nonce: airlinel_ads_txt.nonce,
                id: entryId,
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    showError(response.data.message || 'Error deleting entry.');
                }
            },
            error: function() {
                showError('Error communicating with server.');
            }
        });
    }

    function regenerateFile() {
        var $btn = $('#airlinel-regenerate-file');
        $btn.prop('disabled', true).text('Regenerating...');

        $.ajax({
            url: airlinel_ads_txt.ajax_url,
            type: 'POST',
            data: {
                action: 'airlinel_regenerate_ads_txt_file',
                nonce: airlinel_ads_txt.nonce,
            },
            success: function(response) {
                if (response.success) {
                    showMessage('#airlinel-add-message', 'File regenerated successfully!', 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    showError(response.data.message || 'Error regenerating file.');
                }
            },
            error: function() {
                showError('Error communicating with server.');
            },
            complete: function() {
                $btn.prop('disabled', false).text('Regenerate File');
            }
        });
    }

    function showError(message) {
        showMessage('#airlinel-add-message', message, 'error');
    }

    function showMessage(selector, message, type) {
        var $msg = $(selector);
        $msg.removeClass('error success').addClass(type).html(message);
        if (type === 'success') {
            $msg.css('color', 'green');
        } else {
            $msg.css('color', 'red');
        }
    }

})(jQuery);
