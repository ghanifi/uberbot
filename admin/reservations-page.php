<?php
/**
 * Airlinel Reservations Admin Page
 * Display and manage all reservations with filtering and bulk actions
 */

if (!defined('ABSPATH')) {
    exit;
}

// Check permissions (also verified in callback)
if (!current_user_can('manage_options')) {
    wp_die('You do not have permission to manage reservations.');
}

$mgr = new Airlinel_Reservation_Manager();

    // Get filter values from GET/POST
    $status = isset($_GET['filter_status']) ? sanitize_text_field($_GET['filter_status']) : '';
    $payment_status = isset($_GET['filter_payment_status']) ? sanitize_text_field($_GET['filter_payment_status']) : '';
    $country = isset($_GET['filter_country']) ? sanitize_text_field($_GET['filter_country']) : '';
    $date_from = isset($_GET['filter_date_from']) ? sanitize_text_field($_GET['filter_date_from']) : '';
    $date_to = isset($_GET['filter_date_to']) ? sanitize_text_field($_GET['filter_date_to']) : '';
    $search = isset($_GET['filter_search']) ? sanitize_text_field($_GET['filter_search']) : '';
    $paged = isset($_GET['paged']) ? intval($_GET['paged']) : 1;

    // Get reservations with filters
    $result = $mgr->get_reservations(array(
        'status' => $status,
        'payment_status' => $payment_status,
        'country' => $country,
        'date_from' => $date_from,
        'date_to' => $date_to,
        'search' => $search,
        'page' => $paged,
        'per_page' => 20,
    ));

    $reservations = $result['reservations'];
    $total = $result['total'];
    $pages = $result['pages'];

    // Nonce for AJAX actions
    $nonce = wp_create_nonce('airlinel_nonce');
    ?>

    <div class="wrap airlinel-reservations-wrap">
        <h1>Reservations</h1>

        <!-- Filters Form -->
        <div class="airlinel-filters" style="background: #f1f1f1; padding: 15px; margin: 20px 0; border-radius: 4px;">
            <form method="get">
                <input type="hidden" name="page" value="airlinel-reservations">

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px;">
                    <!-- Status Filter -->
                    <div>
                        <label for="filter_status">Status:</label>
                        <select name="filter_status" id="filter_status" style="width: 100%;">
                            <option value="">All Statuses</option>
                            <option value="pending" <?php selected($status, 'pending'); ?>>Pending</option>
                            <option value="processing" <?php selected($status, 'processing'); ?>>Processing</option>
                            <option value="completed" <?php selected($status, 'completed'); ?>>Completed</option>
                            <option value="cancelled" <?php selected($status, 'cancelled'); ?>>Cancelled</option>
                        </select>
                    </div>

                    <!-- Payment Status Filter -->
                    <div>
                        <label for="filter_payment_status">Payment Status:</label>
                        <select name="filter_payment_status" id="filter_payment_status" style="width: 100%;">
                            <option value="">All Payment Status</option>
                            <option value="pending" <?php selected($payment_status, 'pending'); ?>>Pending</option>
                            <option value="completed" <?php selected($payment_status, 'completed'); ?>>Completed</option>
                            <option value="failed" <?php selected($payment_status, 'failed'); ?>>Failed</option>
                        </select>
                    </div>

                    <!-- Country Filter -->
                    <div>
                        <label for="filter_country">Country:</label>
                        <select name="filter_country" id="filter_country" style="width: 100%;">
                            <option value="">All Countries</option>
                            <option value="UK" <?php selected($country, 'UK'); ?>>UK</option>
                            <option value="TR" <?php selected($country, 'TR'); ?>>Turkey</option>
                        </select>
                    </div>

                    <!-- Date From Filter -->
                    <div>
                        <label for="filter_date_from">From Date:</label>
                        <input type="date" name="filter_date_from" id="filter_date_from" value="<?php echo esc_attr($date_from); ?>" style="width: 100%; padding: 5px;">
                    </div>

                    <!-- Date To Filter -->
                    <div>
                        <label for="filter_date_to">To Date:</label>
                        <input type="date" name="filter_date_to" id="filter_date_to" value="<?php echo esc_attr($date_to); ?>" style="width: 100%; padding: 5px;">
                    </div>

                    <!-- Search Filter -->
                    <div>
                        <label for="filter_search">Search (Name/Email):</label>
                        <input type="text" name="filter_search" id="filter_search" value="<?php echo esc_attr($search); ?>" style="width: 100%; padding: 5px;">
                    </div>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="button button-primary">Apply Filters</button>
                    <a href="?page=airlinel-reservations" class="button">Reset Filters</a>
                    <button type="button" id="export-csv-btn" class="button" style="margin-left: auto;">Export as CSV</button>
                </div>
            </form>
        </div>

        <!-- Results Summary -->
        <div style="margin-bottom: 15px;">
            <p>Showing <strong><?php echo count($reservations); ?></strong> of <strong><?php echo $total; ?></strong> reservations</p>
        </div>

        <!-- Bulk Actions -->
        <div id="bulk-actions" style="margin-bottom: 15px; display: flex; gap: 10px;">
            <select id="bulk-action-select" style="padding: 5px;">
                <option value="">-- Bulk Actions --</option>
                <option value="completed">Mark as Completed</option>
                <option value="cancelled">Mark as Cancelled</option>
            </select>
            <button type="button" id="apply-bulk-action" class="button">Apply</button>
            <span id="bulk-count" style="align-self: center;"></span>
        </div>

        <!-- Reservations Table -->
        <table class="widefat striped" id="reservations-table">
            <thead>
                <tr>
                    <th style="width: 40px;"><input type="checkbox" id="select-all"></th>
                    <th style="width: 60px;">ID</th>
                    <th>Customer Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Pickup</th>
                    <th>Dropoff</th>
                    <th>Transfer Date</th>
                    <th style="width: 100px;">Total Price (£)</th>
                    <th style="width: 120px;">Status</th>
                    <th style="width: 120px;">Payment Status</th>
                    <th style="width: 100px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reservations)) : ?>
                    <tr>
                        <td colspan="12" style="text-align: center; padding: 30px;">No reservations found.</td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($reservations as $res) : ?>
                        <tr>
                            <td><input type="checkbox" class="reservation-checkbox" value="<?php echo intval($res['id']); ?>"></td>
                            <td><?php echo intval($res['id']); ?></td>
                            <td><?php echo esc_html($res['customer_name']); ?></td>
                            <td><?php echo esc_html($res['email']); ?></td>
                            <td><?php echo esc_html($res['phone']); ?></td>
                            <td><?php echo esc_html($res['pickup_location']); ?></td>
                            <td><?php echo esc_html($res['dropoff_location']); ?></td>
                            <td><?php echo !empty($res['transfer_date']) ? esc_html(date('Y-m-d', strtotime($res['transfer_date']))) : ''; ?></td>
                            <td>£<?php echo esc_html(number_format($res['total_price_gbp'], 2)); ?></td>
                            <td>
                                <select class="status-dropdown" data-id="<?php echo intval($res['id']); ?>" data-nonce="<?php echo esc_attr($nonce); ?>">
                                    <?php foreach ($mgr->get_statuses() as $s) : ?>
                                        <option value="<?php echo esc_attr($s); ?>" <?php selected($res['status'], $s); ?>><?php echo ucfirst($s); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><?php echo esc_html(ucfirst($res['payment_status'])); ?></td>
                            <td>
                                <button type="button" class="button button-small view-details" data-id="<?php echo intval($res['id']); ?>">Details</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($pages > 1) : ?>
            <div class="pagination" style="margin-top: 20px;">
                <?php
                $current_url = remove_query_arg('paged');
                for ($i = 1; $i <= $pages; $i++) {
                    if ($i === $paged) {
                        echo '<span class="page-numbers current">' . $i . '</span>';
                    } else {
                        echo '<a class="page-numbers" href="' . esc_url(add_query_arg('paged', $i, $current_url)) . '">' . $i . '</a>';
                    }
                }
                ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Detail Modal -->
    <div id="reservation-modal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6);">
        <div style="background-color: #fefefe; margin: 5% auto; padding: 20px; border-radius: 4px; width: 90%; max-width: 700px; max-height: 90vh; overflow-y: auto;">
            <span class="close-modal" style="color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; line-height: 1;">&times;</span>
            <h2 id="modal-title">Reservation Details</h2>
            <div id="modal-content"></div>
        </div>
    </div>

    <!-- Styles for this page -->
    <style>
        .airlinel-reservations-wrap {
            margin-top: 20px;
        }

        .airlinel-filters {
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .airlinel-filters input[type="text"],
        .airlinel-filters input[type="date"],
        .airlinel-filters select {
            border: 1px solid #ddd;
            border-radius: 3px;
            padding: 5px;
            font-size: 14px;
        }

        .status-dropdown {
            padding: 5px;
            font-size: 13px;
            border: 1px solid #ddd;
            border-radius: 3px;
            width: 100%;
        }

        #reservations-table {
            margin-top: 20px;
        }

        .view-details {
            white-space: nowrap;
        }

        .close-modal {
            line-height: 20px;
        }

        .close-modal:hover,
        .close-modal:focus {
            color: black;
        }

        .modal-section {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .modal-section:last-child {
            border-bottom: none;
        }

        .modal-section h3 {
            margin-top: 0;
            color: #333;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .modal-field {
            display: grid;
            grid-template-columns: 150px 1fr;
            gap: 15px;
            margin-bottom: 12px;
            font-size: 13px;
        }

        .modal-field label {
            font-weight: 600;
            color: #666;
        }

        .modal-field value {
            color: #333;
            word-break: break-word;
        }

        #notes-field {
            width: 100%;
            min-height: 100px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-family: monospace;
            font-size: 12px;
        }

        .save-notes-btn {
            margin-top: 10px;
        }

        .pagination .page-numbers {
            padding: 5px 8px;
            margin-right: 5px;
            border: 1px solid #ddd;
            border-radius: 3px;
            text-decoration: none;
            color: #0073aa;
        }

        .pagination .page-numbers.current {
            background-color: #0073aa;
            color: white;
            border-color: #0073aa;
        }

        .pagination .page-numbers:hover {
            border-color: #0073aa;
        }
    </style>

    <!-- JavaScript for interactions -->
    <script>
    (function($) {
        // Select all checkboxes
        $('#select-all').on('change', function() {
            $('.reservation-checkbox').prop('checked', this.checked);
            updateBulkCount();
        });

        // Update bulk count when individual checkboxes change
        $(document).on('change', '.reservation-checkbox', function() {
            updateBulkCount();
        });

        function updateBulkCount() {
            var count = $('.reservation-checkbox:checked').length;
            if (count > 0) {
                $('#bulk-count').text('(' + count + ' selected)');
                $('#bulk-actions').show();
            } else {
                $('#bulk-count').text('');
            }
        }

        // Apply bulk action
        $('#apply-bulk-action').on('click', function() {
            var action = $('#bulk-action-select').val();
            var ids = [];

            $('.reservation-checkbox:checked').each(function() {
                ids.push($(this).val());
            });

            if (!action || ids.length === 0) {
                alert('Please select an action and reservations.');
                return;
            }

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'airlinel_bulk_update_reservations',
                    nonce: '<?php echo esc_js($nonce); ?>',
                    ids: ids,
                    status: action,
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message || 'Updated ' + response.data.updated + ' reservations');
                        location.reload();
                    } else {
                        var errorMsg = response.data && response.data.message ? response.data.message : 'Error processing action';
                        alert('Error: ' + errorMsg);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', status, error, xhr.responseText);
                    alert('Error processing bulk action: ' + (error || 'Unknown error'));
                }
            });
        });

        // Status dropdown change
        $(document).on('change', '.status-dropdown', function() {
            var id = $(this).data('id');
            var status = $(this).val();
            var nonce = $(this).data('nonce');
            var $select = $(this);

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'airlinel_update_reservation_status',
                    nonce: nonce,
                    id: id,
                    status: status,
                },
                success: function(response) {
                    if (response.success) {
                        $select.closest('tr').fadeOut(100).fadeIn(100);
                    } else {
                        var errorMsg = response.data && response.data.message ? response.data.message : 'Error updating reservation status';
                        alert('Error: ' + errorMsg);
                        location.reload();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', status, error, xhr.responseText);
                    alert('Error updating reservation status: ' + (error || 'Unknown error'));
                    location.reload();
                }
            });
        });

        // View details modal
        $(document).on('click', '.view-details', function() {
            var id = $(this).data('id');
            var $modal = $('#reservation-modal');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'airlinel_get_reservation_details',
                    nonce: '<?php echo esc_js($nonce); ?>',
                    id: id,
                },
                success: function(response) {
                    if (response.success) {
                        $('#modal-content').html(response.data.html);
                        $modal.show();
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('Error loading reservation details');
                }
            });
        });

        // Close modal
        $('.close-modal, #reservation-modal').on('click', function(e) {
            if (e.target === this || $(e.target).hasClass('close-modal')) {
                $('#reservation-modal').hide();
            }
        });

        // Save notes
        $(document).on('click', '.save-notes-btn', function() {
            var id = $(this).data('id');
            var notes = $('#notes-field').val();
            var nonce = '<?php echo esc_js($nonce); ?>';

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'airlinel_update_reservation_notes',
                    nonce: nonce,
                    id: id,
                    notes: notes,
                },
                success: function(response) {
                    if (response.success) {
                        alert('Notes saved successfully');
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('Error saving notes');
                }
            });
        });

        // Export CSV
        $('#export-csv-btn').on('click', function() {
            // Get current filter parameters
            var params = new URLSearchParams(window.location.search);
            params.set('action', 'airlinel_export_reservations_csv');
            params.set('nonce', '<?php echo esc_js($nonce); ?>');

            // Download CSV
            window.location.href = ajaxurl + '?' + params.toString();
        });
    })(jQuery);
    </script>

    <?php
