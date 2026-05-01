<?php
/**
 * Airlinel Ads.txt Admin Page
 * Manage ad network publisher IDs and ads.txt file
 */

if (!defined('ABSPATH')) {
    exit;
}

// Check permissions (also verified in callback)
if (!current_user_can('manage_options')) {
    wp_die('You do not have permission to manage ads.txt.');
}

$mgr = new Airlinel_Ads_Txt_Manager();
    $entries = $mgr->get_entries();
    $file_status = $mgr->get_file_status();
    $file_content = $mgr->get_file_content();

    // Get pagination info
    $per_page = 10;
    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $total_entries = count($entries);
    $total_pages = ceil($total_entries / $per_page);

    // Validate page number doesn't exceed total pages
    if ($current_page > $total_pages && $total_pages > 0) {
        $current_page = $total_pages;
    }

    $start = ($current_page - 1) * $per_page;
    $paginated_entries = array_slice($entries, $start, $per_page);
    ?>
    <div class="wrap airlinel-ads-txt-wrap">
        <h1>ads.txt Management</h1>
        <p>Manage ad network publisher IDs for your site's ads.txt file.</p>

        <!-- File Status Section -->
        <div class="card">
            <h2>ads.txt File Status</h2>
            <table class="form-table">
                <tr>
                    <th>File Path</th>
                    <td><code><?php echo esc_html($file_status['path']); ?></code></td>
                </tr>
                <tr>
                    <th>Exists</th>
                    <td>
                        <?php if ($file_status['exists']) : ?>
                            <span class="status-success">Yes</span>
                        <?php else : ?>
                            <span class="status-warning">No (will be created on first entry)</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>Readable</th>
                    <td>
                        <?php if ($file_status['readable']) : ?>
                            <span class="status-success">Yes</span>
                        <?php else : ?>
                            <span class="status-error">No</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>Writable</th>
                    <td>
                        <?php if ($file_status['writable']) : ?>
                            <span class="status-success">Yes</span>
                        <?php else : ?>
                            <span class="status-error">No - File cannot be written</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php if ($file_status['last_modified']) : ?>
                    <tr>
                        <th>Last Updated</th>
                        <td><?php echo esc_html(date('Y-m-d H:i:s', $file_status['last_modified'])); ?></td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <th>File Size</th>
                    <td><?php echo esc_html(number_format($file_status['size']) . ' bytes'); ?></td>
                </tr>
            </table>

            <div class="button-group">
                <button class="button button-secondary" id="airlinel-regenerate-file">
                    Regenerate File
                </button>
            </div>
        </div>

        <!-- Current File Content Section -->
        <?php if (!empty($file_content)) : ?>
            <div class="card">
                <h2>Current ads.txt Content</h2>
                <textarea class="ads-txt-content" readonly><?php echo esc_textarea($file_content); ?></textarea>
            </div>
        <?php endif; ?>

        <!-- Add New Entry Form -->
        <div class="card">
            <h2>Add New Publisher Entry</h2>
            <table class="form-table">
                <tr>
                    <th><label for="ads-txt-domain">Network Domain *</label></th>
                    <td>
                        <input type="text" id="ads-txt-domain" class="airlinel-form-field" placeholder="e.g., google.com">
                        <p class="description">Domain of the ad network (e.g., google.com, appnexus.com)</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="ads-txt-pub-id">Publisher ID *</label></th>
                    <td>
                        <input type="text" id="ads-txt-pub-id" class="airlinel-form-field" placeholder="e.g., pub-1234567890123456">
                        <p class="description">Your publisher ID from the ad network</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="ads-txt-relationship">Relationship Type *</label></th>
                    <td>
                        <select id="ads-txt-relationship" class="airlinel-form-field">
                            <option value="">-- Select --</option>
                            <option value="DIRECT">DIRECT</option>
                            <option value="RESELLER">RESELLER</option>
                        </select>
                        <p class="description">Is this a direct relationship or reseller?</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="ads-txt-cert-id">Certification ID (Optional)</label></th>
                    <td>
                        <input type="text" id="ads-txt-cert-id" class="airlinel-form-field" placeholder="e.g., f08c47fec0942fa0">
                        <p class="description">Certification ID from ad network (if applicable)</p>
                    </td>
                </tr>
            </table>
            <div class="button-group">
                <button class="button button-primary" id="airlinel-add-entry">Add Entry</button>
                <span id="airlinel-add-message" class="form-message"></span>
            </div>
        </div>

        <!-- Entries Table -->
        <div class="card">
            <h2 style="margin-top: 0;">Publisher Entries (<?php echo esc_html(count($entries)); ?>)</h2>

            <?php if (empty($entries)) : ?>
                <p class="no-entries-message">No publisher entries configured yet.</p>
            <?php else : ?>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th>Network Domain</th>
                            <th>Publisher ID</th>
                            <th>Relationship</th>
                            <th>Certification ID</th>
                            <th>Added</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="airlinel-entries-tbody">
                        <?php foreach ($paginated_entries as $entry) : ?>
                            <tr class="airlinel-entry-row"
                                data-entry-id="<?php echo esc_attr($entry['id']); ?>"
                                data-domain="<?php echo esc_attr($entry['domain']); ?>"
                                data-pubid="<?php echo esc_attr($entry['pub_id']); ?>"
                                data-relationship="<?php echo esc_attr($entry['relationship']); ?>"
                                data-certid="<?php echo esc_attr($entry['cert_id']); ?>">
                                <td><?php echo esc_html($entry['domain']); ?></td>
                                <td><code><?php echo esc_html($entry['pub_id']); ?></code></td>
                                <td><span class="badge <?php echo strtolower($entry['relationship']); ?>"><?php echo esc_html($entry['relationship']); ?></span></td>
                                <td><?php echo !empty($entry['cert_id']) ? esc_html($entry['cert_id']) : '—'; ?></td>
                                <td><?php echo esc_html(date('Y-m-d', strtotime($entry['added_at']))); ?></td>
                                <td>
                                    <button class="button button-small airlinel-edit-entry" data-entry-id="<?php echo esc_attr($entry['id']); ?>">Edit</button>
                                    <button class="button button-small button-link-delete airlinel-delete-entry" data-entry-id="<?php echo esc_attr($entry['id']); ?>">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if ($total_pages > 1) : ?>
                    <div class="tablenav bottom">
                        <div class="tablenav-pages">
                            <span class="displaying-num"><?php echo esc_html($total_entries); ?> items</span>
                            <span class="pagination-links">
                                <?php if ($current_page > 1) : ?>
                                    <a class="prev-page" href="<?php echo esc_url(admin_url('admin.php?page=airlinel-ads-txt&paged=1')); ?>">&laquo;</a>
                                    <a class="prev-page" href="<?php echo esc_url(admin_url('admin.php?page=airlinel-ads-txt&paged=' . ($current_page - 1))); ?>">&lsaquo;</a>
                                <?php endif; ?>

                                <span class="paging-input">
                                    <span class="tablenav-paging-text"><?php echo esc_html($current_page); ?> of <span class="total-pages"><?php echo esc_html($total_pages); ?></span></span>
                                </span>

                                <?php if ($current_page < $total_pages) : ?>
                                    <a class="next-page" href="<?php echo esc_url(admin_url('admin.php?page=airlinel-ads-txt&paged=' . ($current_page + 1))); ?>">&rsaquo;</a>
                                    <a class="next-page" href="<?php echo esc_url(admin_url('admin.php?page=airlinel-ads-txt&paged=' . $total_pages)); ?>">&raquo;</a>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="airlinel-edit-modal" role="dialog" aria-labelledby="modal-title" aria-hidden="true">
        <div>
            <h2 id="modal-title">Edit Entry</h2>
            <table class="form-table">
                <tr>
                    <th><label for="modal-domain">Network Domain *</label></th>
                    <td>
                        <input type="text" id="modal-domain" class="widefat">
                    </td>
                </tr>
                <tr>
                    <th><label for="modal-pub-id">Publisher ID *</label></th>
                    <td>
                        <input type="text" id="modal-pub-id" class="widefat">
                    </td>
                </tr>
                <tr>
                    <th><label for="modal-relationship">Relationship Type *</label></th>
                    <td>
                        <select id="modal-relationship" class="widefat">
                            <option value="">-- Select --</option>
                            <option value="DIRECT">DIRECT</option>
                            <option value="RESELLER">RESELLER</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="modal-cert-id">Certification ID (Optional)</label></th>
                    <td>
                        <input type="text" id="modal-cert-id" class="widefat">
                    </td>
                </tr>
            </table>
            <div class="modal-actions">
                <button class="button button-primary" id="airlinel-modal-save">Save Changes</button>
                <button class="button" id="airlinel-modal-cancel">Cancel</button>
            </div>
            <span id="airlinel-modal-message" class="form-message"></span>
        </div>
    </div>
    <?php
