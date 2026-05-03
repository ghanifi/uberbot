<?php
/**
 * Price Table Admin Page
 * View, import (JSON/CSV), and manage price comparison data.
 */

if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Permission denied.' );
}

if ( ! class_exists( 'Airlinel_Price_Table' ) ) {
    require_once get_template_directory() . '/includes/class-price-table.php';
}

// ── Ensure table exists ──────────────────────────────────────────
Airlinel_Price_Table::create_table();

$message = '';
$msg_type = 'success';

// ── Handle: JSON Import ──────────────────────────────────────────
if ( isset( $_POST['airlinel_pt_import_nonce'] ) &&
     wp_verify_nonce( $_POST['airlinel_pt_import_nonce'], 'airlinel_pt_import' ) ) {

    $source  = sanitize_text_field( $_POST['import_source'] ?? 'airlinel' );
    $raw     = stripslashes( $_POST['import_json'] ?? '' );
    $decoded = json_decode( $raw, true );

    if ( json_last_error() !== JSON_ERROR_NONE ) {
        $message  = 'JSON parse hatası: ' . json_last_error_msg();
        $msg_type = 'error';
    } else {
        // Accept both flat array and wrapped { entries: [...] }
        $entries = isset( $decoded['entries'] ) ? $decoded['entries'] : $decoded;
        if ( ! is_array( $entries ) ) {
            $message  = 'Geçersiz format. Dizi veya {"entries":[...]} bekleniyor.';
            $msg_type = 'error';
        } else {
            foreach ( $entries as &$e ) { $e['source'] = $source; }
            unset( $e );
            $result   = Airlinel_Price_Table::bulk_insert( $entries );
            $message  = sprintf( '%d kayıt içe aktarıldı, %d hata.', $result['inserted'], $result['errors'] );
        }
    }
}

// ── Handle: Delete single row ────────────────────────────────────
if ( isset( $_POST['airlinel_pt_delete_nonce'] ) &&
     wp_verify_nonce( $_POST['airlinel_pt_delete_nonce'], 'airlinel_pt_delete' ) ) {
    $id = (int) ( $_POST['delete_id'] ?? 0 );
    Airlinel_Price_Table::delete( $id );
    $message = "Kayıt #{$id} silindi.";
}

// ── Handle: Delete all by source ────────────────────────────────
if ( isset( $_POST['airlinel_pt_purge_nonce'] ) &&
     wp_verify_nonce( $_POST['airlinel_pt_purge_nonce'], 'airlinel_pt_purge' ) ) {
    $src = sanitize_text_field( $_POST['purge_source'] ?? '' );
    if ( $src ) {
        $n = Airlinel_Price_Table::delete_by_source( $src );
        $message = sprintf( '"%s" kaynağından %d kayıt silindi.', esc_html( $src ), $n );
    }
}

// ── Filters ──────────────────────────────────────────────────────
$f_source  = sanitize_text_field( $_GET['f_source']  ?? '' );
$f_pickup  = sanitize_text_field( $_GET['f_pickup']  ?? '' );
$f_dropoff = sanitize_text_field( $_GET['f_dropoff'] ?? '' );
$f_cur     = sanitize_text_field( $_GET['f_cur']     ?? '' );
$page_num  = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
$per_page  = 50;

$rows    = Airlinel_Price_Table::query( array(
    'source'   => $f_source,
    'pickup'   => $f_pickup,
    'dropoff'  => $f_dropoff,
    'currency' => $f_cur,
    'limit'    => $per_page,
    'offset'   => ( $page_num - 1 ) * $per_page,
    'order_by' => 'recorded_at',
    'order'    => 'DESC',
) );
$sources = Airlinel_Price_Table::get_sources();
$counts  = Airlinel_Price_Table::count_by_source();
$total   = Airlinel_Price_Table::total_count();

$api_key   = \Airlinel_Settings_Manager::get( 'airlinel_api_key', '' );
$site_url  = get_site_url();
?>

<div class="wrap" id="airlinel-pt-admin">

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
        <div>
            <h1 style="margin:0;font-size:22px;font-weight:700;">Fiyat Karşılaştırma Tablosu</h1>
            <p style="margin:4px 0 0;color:#666;font-size:13px;">
                Toplam <strong><?php echo number_format( $total ); ?></strong> kayıt
                <?php foreach ( $counts as $c ) : ?>
                    &nbsp;·&nbsp; <strong><?php echo esc_html( $c['source'] ); ?></strong>: <?php echo (int) $c['cnt']; ?>
                <?php endforeach; ?>
            </p>
        </div>
        <button onclick="document.getElementById('pt-import-box').style.display = document.getElementById('pt-import-box').style.display === 'none' ? 'block' : 'none';"
                style="padding:9px 18px;background:#CC4452;color:#fff;border:none;border-radius:6px;font-weight:700;cursor:pointer;font-size:13px;">
            + Veri İçe Aktar
        </button>
    </div>

    <?php if ( $message ) : ?>
        <div class="notice notice-<?php echo $msg_type === 'error' ? 'error' : 'success'; ?> is-dismissible" style="margin-bottom:16px;">
            <p><?php echo esc_html( $message ); ?></p>
        </div>
    <?php endif; ?>

    <!-- ── Import Box ─────────────────────────────────────────── -->
    <div id="pt-import-box" style="display:none;background:#fff;border:1px solid #e0e0e0;border-radius:10px;padding:24px;margin-bottom:24px;box-shadow:0 1px 4px rgba(0,0,0,.06);">
        <h2 style="margin:0 0 16px;font-size:15px;font-weight:700;">JSON İçe Aktar</h2>

        <!-- API info -->
        <div style="background:#f0f7ff;border-left:3px solid #3b82f6;padding:12px 16px;border-radius:0 6px 6px 0;margin-bottom:20px;font-size:12px;color:#1e40af;">
            <strong>REST API Endpoint:</strong><br>
            <code style="background:#dbeafe;padding:2px 6px;border-radius:4px;font-size:11px;">
                POST <?php echo esc_html( $site_url ); ?>/wp-json/airlinel/v1/price-table/import
            </code><br>
            Header: <code style="background:#dbeafe;padding:2px 6px;border-radius:4px;font-size:11px;">X-Api-Key: YOUR_API_KEY</code><br>
            Body: <code style="background:#dbeafe;padding:2px 6px;border-radius:4px;font-size:11px;">{"source":"airlinel","entries":[{...},{...}]}</code>
        </div>

        <form method="post">
            <?php wp_nonce_field( 'airlinel_pt_import', 'airlinel_pt_import_nonce' ); ?>
            <div style="display:flex;gap:12px;margin-bottom:12px;flex-wrap:wrap;">
                <div style="flex:0 0 200px;">
                    <label style="display:block;font-size:12px;font-weight:600;color:#555;text-transform:uppercase;margin-bottom:4px;">Kaynak</label>
                    <input type="text" name="import_source" value="airlinel"
                           style="width:100%;padding:8px 12px;border:1px solid #ddd;border-radius:6px;font-size:13px;"
                           placeholder="airlinel veya rakip adı">
                </div>
            </div>
            <div style="margin-bottom:12px;">
                <label style="display:block;font-size:12px;font-weight:600;color:#555;text-transform:uppercase;margin-bottom:4px;">
                    JSON Verisi
                </label>
                <textarea name="import_json" rows="10"
                          style="width:100%;font-family:monospace;font-size:12px;padding:10px 12px;border:1px solid #ddd;border-radius:6px;resize:vertical;box-sizing:border-box;"
                          placeholder='[{"pickup":"Heathrow Airport","dropoff":"London City","name":"Business Class","price_value":85.00,"currency":"GBP","classification":"business","capacity":4,"is_available":1,"has_promo":0}]'></textarea>
                <p style="margin:4px 0 0;font-size:11px;color:#888;">
                    Düz dizi <code>[ {...}, {...} ]</code> veya sarmalı <code>{"entries": [ {...} ]}</code> format desteklenir.
                    Alan adları: pickup, dropoff, pickup_resolved, dropoff_resolved, pickup_lat, pickup_lng, dropoff_lat, dropoff_lng,
                    name, classification, price_value, currency, eta_min, trip_min, capacity, is_available, has_promo, timestamp
                </p>
            </div>
            <button type="submit" style="padding:10px 22px;background:#22c55e;color:#fff;border:none;border-radius:6px;font-weight:700;cursor:pointer;font-size:13px;">
                İçe Aktar
            </button>
        </form>
    </div>

    <!-- ── Source Purge ───────────────────────────────────────── -->
    <?php if ( ! empty( $sources ) ) : ?>
    <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:14px 20px;margin-bottom:20px;display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
        <span style="font-size:13px;font-weight:600;color:#92400e;">Kaynağa göre tümünü sil:</span>
        <form method="post" style="display:flex;align-items:center;gap:8px;margin:0;">
            <?php wp_nonce_field( 'airlinel_pt_purge', 'airlinel_pt_purge_nonce' ); ?>
            <select name="purge_source" style="padding:6px 10px;border:1px solid #d97706;border-radius:6px;font-size:13px;">
                <?php foreach ( $sources as $s ) : ?>
                    <option value="<?php echo esc_attr($s); ?>"><?php echo esc_html($s); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit"
                    onclick="return confirm('Bu kaynağa ait tüm veriler silinecek. Devam?');"
                    style="padding:6px 14px;background:#ef4444;color:#fff;border:none;border-radius:6px;font-weight:700;cursor:pointer;font-size:12px;">
                Kaynağı Temizle
            </button>
        </form>
    </div>
    <?php endif; ?>

    <!-- ── Filters ────────────────────────────────────────────── -->
    <form method="get" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;align-items:flex-end;">
        <input type="hidden" name="page" value="airlinel-price-table">
        <div>
            <label style="display:block;font-size:11px;font-weight:600;color:#666;margin-bottom:3px;">KAYNAK</label>
            <select name="f_source" style="padding:7px 10px;border:1px solid #ddd;border-radius:6px;font-size:13px;">
                <option value="">Tümü</option>
                <?php foreach ( $sources as $s ) : ?>
                    <option value="<?php echo esc_attr($s); ?>" <?php selected( $f_source, $s ); ?>><?php echo esc_html($s); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label style="display:block;font-size:11px;font-weight:600;color:#666;margin-bottom:3px;">PICKUP</label>
            <input type="text" name="f_pickup" value="<?php echo esc_attr($f_pickup); ?>"
                   style="padding:7px 10px;border:1px solid #ddd;border-radius:6px;font-size:13px;width:180px;" placeholder="Ara…">
        </div>
        <div>
            <label style="display:block;font-size:11px;font-weight:600;color:#666;margin-bottom:3px;">DROPOFF</label>
            <input type="text" name="f_dropoff" value="<?php echo esc_attr($f_dropoff); ?>"
                   style="padding:7px 10px;border:1px solid #ddd;border-radius:6px;font-size:13px;width:180px;" placeholder="Ara…">
        </div>
        <div>
            <label style="display:block;font-size:11px;font-weight:600;color:#666;margin-bottom:3px;">PARA BİRİMİ</label>
            <select name="f_cur" style="padding:7px 10px;border:1px solid #ddd;border-radius:6px;font-size:13px;">
                <option value="">Tümü</option>
                <?php foreach ( array('GBP','EUR','TRY','USD') as $c ) : ?>
                    <option value="<?php echo $c; ?>" <?php selected( $f_cur, $c ); ?>><?php echo $c; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" style="padding:7px 16px;background:#475569;color:#fff;border:none;border-radius:6px;font-size:13px;cursor:pointer;font-weight:600;">
            Filtrele
        </button>
        <?php if ( $f_source || $f_pickup || $f_dropoff || $f_cur ) : ?>
            <a href="?page=airlinel-price-table" style="padding:7px 14px;background:#f0f0f0;color:#333;border:1px solid #ccc;border-radius:6px;font-size:13px;text-decoration:none;">Temizle</a>
        <?php endif; ?>
    </form>

    <!-- ── Table ──────────────────────────────────────────────── -->
    <?php if ( empty( $rows ) ) : ?>
        <div style="padding:40px;text-align:center;background:#f9f9f9;border:1px dashed #ddd;border-radius:8px;color:#888;">
            Kayıt bulunamadı. Yukarıdaki "Veri İçe Aktar" düğmesini kullanarak veri yükleyin.
        </div>
    <?php else : ?>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:12px;background:#fff;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;">
            <thead>
                <tr style="background:#f8fafc;border-bottom:2px solid #e5e7eb;text-align:left;">
                    <th style="padding:10px 12px;font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;white-space:nowrap;">ID</th>
                    <th style="padding:10px 12px;font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;white-space:nowrap;">Kaynak</th>
                    <th style="padding:10px 12px;font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;">Pickup</th>
                    <th style="padding:10px 12px;font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;">Dropoff</th>
                    <th style="padding:10px 12px;font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;">Araç / Sınıf</th>
                    <th style="padding:10px 12px;font-size:11px;font-weight:700;color:#CC4452;text-transform:uppercase;text-align:right;">Fiyat</th>
                    <th style="padding:10px 12px;font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;text-align:center;">Kap.</th>
                    <th style="padding:10px 12px;font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;text-align:center;">Süre</th>
                    <th style="padding:10px 12px;font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;text-align:center;">Durum</th>
                    <th style="padding:10px 12px;font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;white-space:nowrap;">Kayıt Tarihi</th>
                    <th style="padding:10px 12px;"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $rows as $i => $row ) : ?>
                <tr style="border-bottom:1px solid #f1f5f9;background:<?php echo $i%2===0?'#fff':'#fafafa'; ?>;transition:background .1s;" onmouseover="this.style.background='#f0f7ff'" onmouseout="this.style.background='<?php echo $i%2===0?'#fff':'#fafafa'; ?>'">
                    <td style="padding:9px 12px;color:#9ca3af;"><?php echo (int)$row['id']; ?></td>
                    <td style="padding:9px 12px;">
                        <span style="padding:2px 8px;border-radius:12px;font-size:11px;font-weight:600;background:<?php echo $row['source']==='airlinel'?'#dcfce7;color:#16a34a':'#fef3c7;color:#b45309'; ?>;">
                            <?php echo esc_html( $row['source'] ); ?>
                        </span>
                    </td>
                    <td style="padding:9px 12px;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?php echo esc_attr($row['pickup_resolved'] ?: $row['pickup']); ?>">
                        <?php echo esc_html( $row['pickup'] ); ?>
                    </td>
                    <td style="padding:9px 12px;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?php echo esc_attr($row['dropoff_resolved'] ?: $row['dropoff']); ?>">
                        <?php echo esc_html( $row['dropoff'] ); ?>
                    </td>
                    <td style="padding:9px 12px;">
                        <div style="font-weight:600;color:#1a1a1a;"><?php echo esc_html( $row['name'] ); ?></div>
                        <?php if ( $row['classification'] ) : ?>
                            <div style="font-size:10px;color:#9ca3af;text-transform:uppercase;"><?php echo esc_html($row['classification']); ?></div>
                        <?php endif; ?>
                        <?php if ( $row['has_promo'] ) : ?>
                            <span style="font-size:9px;background:#fce7f3;color:#be185d;padding:1px 6px;border-radius:10px;font-weight:700;">PROMO</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding:9px 12px;text-align:right;font-weight:700;font-size:14px;color:#CC4452;white-space:nowrap;">
                        <?php echo number_format((float)$row['price_value'], 2); ?>
                        <span style="font-size:10px;font-weight:400;color:#9ca3af;"><?php echo esc_html($row['currency']); ?></span>
                    </td>
                    <td style="padding:9px 12px;text-align:center;color:#475569;"><?php echo $row['capacity'] ?? '–'; ?></td>
                    <td style="padding:9px 12px;text-align:center;color:#475569;white-space:nowrap;"><?php echo $row['trip_min'] ? $row['trip_min'].' dk' : '–'; ?></td>
                    <td style="padding:9px 12px;text-align:center;">
                        <?php if ( $row['is_available'] ) : ?>
                            <span style="color:#16a34a;font-size:16px;">●</span>
                        <?php else : ?>
                            <span style="color:#dc2626;font-size:16px;">●</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding:9px 12px;color:#9ca3af;white-space:nowrap;font-size:11px;">
                        <?php echo esc_html( date('d.m.y H:i', strtotime($row['recorded_at'])) ); ?>
                    </td>
                    <td style="padding:9px 12px;">
                        <form method="post" style="margin:0;" onsubmit="return confirm('Bu kaydı silmek istediğinizden emin misiniz?');">
                            <?php wp_nonce_field( 'airlinel_pt_delete', 'airlinel_pt_delete_nonce' ); ?>
                            <input type="hidden" name="delete_id" value="<?php echo (int)$row['id']; ?>">
                            <button type="submit" style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:14px;padding:2px 6px;" title="Sil">✕</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ( $total > $per_page ) : ?>
    <div style="margin-top:14px;display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
        <?php
        $total_pages = ceil( $total / $per_page );
        $base_url = admin_url( 'admin.php?page=airlinel-price-table'
            . ( $f_source  ? '&f_source='  . urlencode($f_source)  : '' )
            . ( $f_pickup  ? '&f_pickup='  . urlencode($f_pickup)  : '' )
            . ( $f_dropoff ? '&f_dropoff=' . urlencode($f_dropoff) : '' )
            . ( $f_cur     ? '&f_cur='     . urlencode($f_cur)     : '' )
        );
        for ( $p = 1; $p <= min( $total_pages, 20 ); $p++ ) :
            $active = $p === $page_num;
        ?>
        <a href="<?php echo esc_url( $base_url . '&paged=' . $p ); ?>"
           style="padding:5px 10px;border-radius:5px;font-size:12px;text-decoration:none;<?php echo $active ? 'background:#CC4452;color:#fff;font-weight:700;' : 'background:#f0f0f0;color:#333;'; ?>">
            <?php echo $p; ?>
        </a>
        <?php endfor; ?>
        <span style="font-size:11px;color:#888;margin-left:8px;">Toplam <?php echo number_format($total); ?> kayıt</span>
    </div>
    <?php endif; ?>
    <?php endif; ?>

</div><!-- /#airlinel-pt-admin -->
