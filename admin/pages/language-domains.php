<?php
/**
 * Language Domains Admin Page
 * Manage language-to-domain mappings
 */

if (!defined('ABSPATH')) {
    exit;
}

// Check permission
if (!current_user_can('manage_options')) {
    wp_die('Unauthorized');
}

require_once(dirname(__FILE__) . '/../../includes/class-language-domains.php');
$language_domains = new Airlinel_Language_Domains();

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['airlinel_language_domain_nonce'])) {
    if (!wp_verify_nonce($_POST['airlinel_language_domain_nonce'], 'airlinel_language_domain_action')) {
        wp_die('Security check failed');
    }

    $action = sanitize_text_field($_POST['action'] ?? '');

    if ($action === 'save_domain') {
        $result = $language_domains->save_domain($_POST);
        $message = $result ? 'Dil domain\'i başarıyla kaydedildi.' : 'Hata oluştu. Lütfen tekrar deneyin.';
    } elseif ($action === 'delete_domain') {
        $language_code = sanitize_text_field($_POST['language_code'] ?? '');
        if ($language_code !== 'en_US') { // Don't delete main language
            $result = $language_domains->delete_domain($language_code);
            $message = $result ? 'Dil domain\'i başarıyla silindi.' : 'Hata oluştu.';
        } else {
            $message = 'Ana dili silemezsiniz.';
        }
    }
}

$all_domains = $language_domains->get_all_domains(false);
?>

<div class="wrap">
    <h1>Dil Domainleri</h1>

    <?php if ($message): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html($message); ?></p>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0;">
        <!-- Add/Edit Form -->
        <div>
            <h2>Yeni Dil Ekle / Düzenle</h2>
            <form method="POST" action="">
                <?php wp_nonce_field('airlinel_language_domain_action', 'airlinel_language_domain_nonce'); ?>
                <input type="hidden" name="action" value="save_domain">

                <table class="form-table">
                    <tr>
                        <th><label for="language_code">Dil Kodu *</label></th>
                        <td>
                            <input type="text" name="language_code" id="language_code"
                                   placeholder="tr_TR, de_DE, es_ES"
                                   class="regular-text" required>
                            <p class="description">Örnek: tr_TR, de_DE, en_US</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="language_name">Dil Adı *</label></th>
                        <td>
                            <input type="text" name="language_name" id="language_name"
                                   placeholder="Türkçe, Deutsch, English"
                                   class="regular-text" required>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="domain_url">Domain URL</label></th>
                        <td>
                            <input type="text" name="domain_url" id="domain_url"
                                   placeholder="havalimanitransfer.com (protocol olmadan)"
                                   class="regular-text">
                            <p class="description">Protocol (https://) olmadan domain yazın</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="flag">Bayrak Kodu</label></th>
                        <td>
                            <input type="text" name="flag" id="flag"
                                   placeholder="TR, DE, EN"
                                   class="regular-text" maxlength="2">
                            <p class="description">2 karakterli ülke kodu</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="display_order">Sıra</label></th>
                        <td>
                            <input type="number" name="display_order" id="display_order"
                                   value="0" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="is_active">Aktif</label></th>
                        <td>
                            <input type="checkbox" name="is_active" id="is_active"
                                   value="1" checked>
                            <label for="is_active">Bu dili etkinleştir</label>
                        </td>
                    </tr>
                </table>

                <?php submit_button('Kaydet', 'primary', 'submit', true); ?>
            </form>
        </div>

        <!-- Domains List -->
        <div>
            <h2>Mevcut Dil Domainleri</h2>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>Dil Kodu</th>
                        <th>Adı</th>
                        <th>Domain</th>
                        <th>Durum</th>
                        <th>İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($all_domains): ?>
                        <?php foreach ($all_domains as $domain): ?>
                            <tr>
                                <td><code><?php echo esc_html($domain->language_code); ?></code></td>
                                <td><?php echo esc_html($domain->language_name); ?></td>
                                <td>
                                    <?php if ($domain->domain_url): ?>
                                        <a href="https://<?php echo esc_attr($domain->domain_url); ?>"
                                           target="_blank" rel="noopener">
                                            <?php echo esc_html($domain->domain_url); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="dashicons dashicons-dismiss"
                                              style="color: #dc3545;"></span> Yok
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($domain->is_active): ?>
                                        <span class="dashicons dashicons-yes"
                                              style="color: #28a745;"></span> Aktif
                                    <?php else: ?>
                                        <span class="dashicons dashicons-no-alt"
                                              style="color: #dc3545;"></span> İnaktif
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" action="" style="display: inline;">
                                        <?php wp_nonce_field('airlinel_language_domain_action', 'airlinel_language_domain_nonce'); ?>
                                        <input type="hidden" name="action" value="delete_domain">
                                        <input type="hidden" name="language_code"
                                               value="<?php echo esc_attr($domain->language_code); ?>">
                                        <?php if ($domain->language_code !== 'en_US'): ?>
                                            <?php submit_button('Sil', 'delete', 'submit', false,
                                                array('onclick' => 'return confirm("Emin misiniz?")')); ?>
                                        <?php endif; ?>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">Hiçbir dil tanımlı değil.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .form-table th { width: 200px; }
    .form-table input[type="text"],
    .form-table input[type="number"] { width: 100%; }
</style>
