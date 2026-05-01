<?php
/**
 * Generate .mo files from .po files using WordPress built-in POMO classes
 * Access: Airlinel Dashboard → 🌐 Çeviri Oluştur
 */

if (!defined('ABSPATH')) exit;
if (!current_user_can('manage_options')) wp_die('Unauthorized');

// Load WordPress POMO classes
require_once ABSPATH . 'wp-includes/pomo/po.php';
require_once ABSPATH . 'wp-includes/pomo/mo.php';
require_once ABSPATH . 'wp-includes/pomo/entry.php';

$languages_dir = get_template_directory() . '/languages/';
$results = [];

if (isset($_POST['generate_mo'])) {
    check_admin_referer('generate_mo_nonce');

    $po_files = glob($languages_dir . '*.po');

    foreach ($po_files as $po_file) {
        $mo_file = str_replace('.po', '.mo', $po_file);
        $lang    = basename($po_file, '.po');

        // Use WordPress built-in PO parser
        $po = new PO();
        if (!$po->import_from_file($po_file)) {
            $results[] = ['lang' => $lang, 'success' => false, 'error' => 'PO parse failed'];
            continue;
        }

        // Export to MO
        $mo = new MO();
        $mo->headers = $po->headers;
        $mo->entries = $po->entries;

        $ok = $mo->export_to_file($mo_file);
        $results[] = [
            'lang'     => $lang,
            'success'  => $ok,
            'entries'  => count($po->entries),
            'mo_size'  => file_exists($mo_file) ? filesize($mo_file) : 0
        ];
    }
}

// Also verify current site locale is loading correctly
$current_locale = get_locale();
$current_mo     = $languages_dir . 'airlinel-theme-' . $current_locale . '.mo';
$mo_loaded      = is_textdomain_loaded('airlinel-theme');

$po_files = glob($languages_dir . '*.po');
?>
<div class="wrap">
    <h1>🌐 Çeviri (.mo) Dosyaları</h1>
    <p>WordPress <strong>Settings → General → Site Language</strong> ayarına göre tema statik yazılarını çevirir.</p>

    <!-- Current Status -->
    <div style="background:<?php echo $mo_loaded ? '#edf7ed' : '#fdf3cd'; ?>;border:1px solid <?php echo $mo_loaded ? '#4caf50' : '#ffc107'; ?>;padding:15px;border-radius:8px;max-width:700px;margin:15px 0">
        <h3 style="margin:0 0 10px">📊 Mevcut Durum</h3>
        <table style="width:100%;border-collapse:collapse">
            <tr><td style="padding:4px 0"><strong>Site Locale:</strong></td><td><code><?php echo esc_html($current_locale); ?></code></td></tr>
            <tr><td style="padding:4px 0"><strong>Beklenen .mo:</strong></td><td><code>airlinel-theme-<?php echo esc_html($current_locale); ?>.mo</code></td></tr>
            <tr><td style="padding:4px 0"><strong>Dosya mevcut:</strong></td><td><?php echo file_exists($current_mo) ? '✅ Var' : '❌ Yok - Aşağıdan oluştur!'; ?></td></tr>
            <tr><td style="padding:4px 0"><strong>Textdomain yüklü:</strong></td><td><?php echo $mo_loaded ? '✅ Yüklü' : '⚠️ Yüklenmedi (sayfayı yenile)'; ?></td></tr>
        </table>
    </div>

    <!-- Results -->
    <?php if (!empty($results)): ?>
    <div class="notice notice-success is-dismissible"><p><strong>✅ İşlem tamamlandı!</strong></p>
    <table class="widefat" style="max-width:700px;margin-top:10px">
        <thead><tr><th>Dil Dosyası</th><th>Durum</th><th>Çeviri Sayısı</th><th>MO Boyutu</th></tr></thead>
        <tbody>
        <?php foreach ($results as $r): ?>
        <tr>
            <td><code><?php echo esc_html($r['lang']); ?></code></td>
            <td><?php echo ($r['success'] ?? false) ? '✅ Başarılı' : ('❌ ' . esc_html($r['error'] ?? 'Hata')); ?></td>
            <td><?php echo isset($r['entries']) ? number_format($r['entries']) . ' adet' : '—'; ?></td>
            <td><?php echo isset($r['mo_size']) ? number_format($r['mo_size']) . ' bytes' : '—'; ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <p><strong>Şimdi siteyi ziyaret edin</strong> — statik metinler seçili dilde görünmeli.</p>
    </div>
    <?php endif; ?>

    <!-- Generate Form -->
    <div style="background:#fff;border:1px solid #ddd;padding:20px;border-radius:8px;max-width:700px;margin:20px 0">
        <h2>Mevcut .po Dosyaları</h2>
        <table class="widefat">
            <thead><tr><th>Dil</th><th>.po</th><th>.mo</th><th>Durum</th><th>Çeviri Sayısı</th></tr></thead>
            <tbody>
            <?php foreach ($po_files as $po_file):
                $lang    = basename($po_file, '.po');
                $mo_file = str_replace('.po', '.mo', $po_file);
                $mo_exists   = file_exists($mo_file);
                $mo_outdated = $mo_exists && filemtime($mo_file) < filemtime($po_file);
                $is_current  = str_ends_with($lang, $current_locale);

                $po = new PO();
                $entry_count = $po->import_from_file($po_file) ? count($po->entries) : '?';
            ?>
            <tr style="<?php echo $is_current ? 'background:#f0f7ff;font-weight:bold' : ''; ?>">
                <td><code><?php echo esc_html($lang); ?></code>
                    <?php if ($is_current) echo ' ← <em>Aktif</em>'; ?>
                </td>
                <td>✅</td>
                <td><?php echo $mo_exists ? '✅' : '❌'; ?></td>
                <td>
                    <?php if (!$mo_exists): ?>
                        <span style="color:red">⚠️ Oluşturulmalı</span>
                    <?php elseif ($mo_outdated): ?>
                        <span style="color:orange">⚠️ Güncellenmeli</span>
                    <?php else: ?>
                        <span style="color:green">✅ Güncel</span>
                    <?php endif; ?>
                </td>
                <td><?php echo $entry_count; ?> çeviri</td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <form method="POST" style="margin-top:20px">
            <?php wp_nonce_field('generate_mo_nonce'); ?>
            <input type="submit" name="generate_mo" class="button button-primary button-large"
                   value="🔄 Tüm .mo Dosyalarını Oluştur / Güncelle">
        </form>
    </div>

    <!-- Instructions -->
    <div style="background:#e8f4fd;border:1px solid #b8d9f3;padding:15px;border-radius:8px;max-width:700px">
        <h3>📋 Kullanım Adımları</h3>
        <ol>
            <li><strong>Settings → General → Site Language</strong> → dil seç (örn: Türkçe)</li>
            <li>Bu sayfaya geri dön</li>
            <li><strong>"Tüm .mo Dosyalarını Oluştur"</strong> butonuna tıkla</li>
            <li>Siteye geri dön → Yazılar seçilen dilde görünür ✅</li>
        </ol>
        <p style="margin:0"><strong>⚠️ Not:</strong> .po dosyalarına yeni çeviri ekledikten sonra bu işlemi tekrar çalıştır.</p>
    </div>
</div>
