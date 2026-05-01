<?php
/**
 * Homepage Content Management — Redesigned Admin Page
 */

if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( __( 'You do not have permission to access this page.', 'airlinel-theme' ) );
}

$homepage_mgr = new Airlinel_Homepage_Manager();
$message      = '';
$message_type = 'updated';

// ── Handle save ──────────────────────────────────────────────────
if ( isset( $_POST['airlinel_homepage_nonce'] ) && wp_verify_nonce( $_POST['airlinel_homepage_nonce'], 'airlinel_homepage_save' ) ) {

    // SEO Block
    update_option( 'airlinel_seo_block_title',   sanitize_text_field( $_POST['airlinel_seo_block_title']   ?? '' ) );
    update_option( 'airlinel_seo_block_content',  wp_kses_post(        $_POST['airlinel_seo_block_content'] ?? '' ) );

    // Section visibility + content
    if ( isset( $_POST['airlinel_sections'] ) && is_array( $_POST['airlinel_sections'] ) ) {
        foreach ( $_POST['airlinel_sections'] as $section_id => $data ) {
            $section_id = sanitize_text_field( $section_id );
            $visible    = ! empty( $data['visible'] );
            $homepage_mgr->set_section_visibility( $section_id, $visible );
            if ( isset( $data['content'] ) ) {
                $homepage_mgr->set_section_content( $section_id, wp_kses_post( $data['content'] ) );
            }
        }
    }

    $message = 'Değişiklikler kaydedildi.';
}

// ── Handle reset ─────────────────────────────────────────────────
if ( isset( $_POST['airlinel_reset_nonce'] ) && wp_verify_nonce( $_POST['airlinel_reset_nonce'], 'airlinel_homepage_reset' ) ) {
    $homepage_mgr->reset_to_defaults();
    $message      = 'Tüm bölümler varsayılan görünürlüğe sıfırlandı.';
    $message_type = 'updated';
}

$all_sections   = $homepage_mgr->get_all_sections();
$seo_title      = get_option( 'airlinel_seo_block_title',   '' );
$seo_content    = get_option( 'airlinel_seo_block_content', '' );
?>

<div class="wrap" id="airlinel-hp-admin">

    <!-- ── Page Header ─────────────────────────────────────────── -->
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
        <div>
            <h1 style="margin:0; font-size:22px; font-weight:700;">Ana Sayfa İçerik Yönetimi</h1>
            <p style="margin:4px 0 0; color:#666; font-size:13px;">SEO bloğunu düzenleyin ve bölümlerin görünürlüğünü yönetin.</p>
        </div>
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank"
           style="display:inline-flex; align-items:center; gap:6px; padding:8px 16px; background:#f0f0f0; border:1px solid #ccc; border-radius:6px; font-size:13px; text-decoration:none; color:#333;">
            <span class="dashicons dashicons-external" style="font-size:16px; width:16px; height:16px;"></span>
            Ana Sayfayı Görüntüle
        </a>
    </div>

    <?php if ( $message ) : ?>
        <div class="notice notice-<?php echo esc_attr( $message_type ); ?> is-dismissible" style="margin-bottom:20px;">
            <p><?php echo esc_html( $message ); ?></p>
        </div>
    <?php endif; ?>

    <form method="post" id="airlinel-hp-form">
        <?php wp_nonce_field( 'airlinel_homepage_save', 'airlinel_homepage_nonce' ); ?>

        <!-- ══════════════════════════════════════════════════════
             CARD 1 — SEO Slide-Up Bloğu
             ══════════════════════════════════════════════════ -->
        <div class="hp-card" style="background:#fff; border:1px solid #e0e0e0; border-radius:10px; margin-bottom:20px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.06);">

            <!-- Card header -->
            <div style="display:flex; align-items:center; justify-content:space-between; padding:18px 22px; border-bottom:1px solid #f0f0f0; background:#fafafa;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <span style="width:38px; height:38px; background:#CC4452; border-radius:8px; display:flex; align-items:center; justify-content:center; color:#fff; font-size:16px;">
                        <span class="dashicons dashicons-editor-expand" style="font-size:18px; width:18px; height:18px; margin-top:1px;"></span>
                    </span>
                    <div>
                        <div style="font-weight:700; font-size:14px; color:#1a1a1a;">SEO Slide-Up İçerik Bloğu</div>
                        <div style="font-size:12px; color:#888; margin-top:2px;">Footer CTA bölümünün üstünde — kullanıcı "Daha Fazla" düğmesine bastığında açılır</div>
                    </div>
                </div>
                <span style="background:#fff0f1; color:#CC4452; border:1px solid #f5b8bc; font-size:11px; font-weight:600; padding:4px 10px; border-radius:20px; white-space:nowrap;">
                    YENİ ALAN
                </span>
            </div>

            <!-- Card body -->
            <div style="padding:22px;">
                <div style="margin-bottom:18px;">
                    <label style="display:block; font-size:12px; font-weight:600; color:#555; text-transform:uppercase; letter-spacing:.05em; margin-bottom:6px;">
                        Başlık (Toggle Düğmesinde Görünür)
                    </label>
                    <input type="text"
                           name="airlinel_seo_block_title"
                           value="<?php echo esc_attr( $seo_title ); ?>"
                           placeholder="Örn: Airlinel Havalimanı Transferleri Hakkında"
                           style="width:100%; max-width:600px; padding:10px 14px; border:1px solid #ddd; border-radius:6px; font-size:14px; color:#333;">
                    <p style="margin:6px 0 0; font-size:12px; color:#999;">Boş bırakırsanız "About Our Services" varsayılan başlığı kullanılır.</p>
                </div>

                <div>
                    <label style="display:block; font-size:12px; font-weight:600; color:#555; text-transform:uppercase; letter-spacing:.05em; margin-bottom:6px;">
                        Gizli İçerik (HTML Destekli)
                    </label>
                    <p style="margin:0 0 10px; font-size:12px; color:#999;">
                        Bu alan <strong>tamamen boş bırakılırsa</strong> blok sayfada hiç görünmez.
                        Uzun SEO metni, bölge bilgileri veya servis detayları için idealdir.
                    </p>
                    <?php
                    wp_editor(
                        $seo_content,
                        'airlinel_seo_block_content_editor',
                        array(
                            'textarea_name' => 'airlinel_seo_block_content',
                            'media_buttons' => false,
                            'textarea_rows' => 10,
                            'teeny'         => false,
                            'quicktags'     => true,
                        )
                    );
                    ?>
                    <div style="margin-top:10px; padding:10px 14px; background:#f0f7ff; border-left:3px solid #3b82f6; border-radius:0 6px 6px 0; font-size:12px; color:#1e40af;">
                        <strong>Önizleme:</strong> Sayfada "<em><?php echo esc_html( $seo_title ?: 'About Our Services' ); ?></em>" başlığı görünür.
                        Ziyaretçi tıkladığında içerik kayarak açılır.
                    </div>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════
             CARD 2 — Bölüm Görünürlükleri
             ══════════════════════════════════════════════════ -->
        <div class="hp-card" style="background:#fff; border:1px solid #e0e0e0; border-radius:10px; margin-bottom:20px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.06);">

            <div style="padding:18px 22px; border-bottom:1px solid #f0f0f0; background:#fafafa;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <span style="width:38px; height:38px; background:#475569; border-radius:8px; display:flex; align-items:center; justify-content:center; color:#fff;">
                        <span class="dashicons dashicons-layout" style="font-size:18px; width:18px; height:18px; margin-top:1px;"></span>
                    </span>
                    <div>
                        <div style="font-weight:700; font-size:14px; color:#1a1a1a;">Ana Sayfa Bölümleri</div>
                        <div style="font-size:12px; color:#888; margin-top:2px;">Hangi bölümlerin gösterileceğini seçin. Özel içerik girmek için bölümü genişletin.</div>
                    </div>
                </div>
            </div>

            <div>
                <?php foreach ( $all_sections as $i => $section ) :
                    $has_content  = ! empty( trim( $section['content'] ) );
                    $row_bg       = $i % 2 === 0 ? '#ffffff' : '#fafafa';
                ?>
                <div class="hp-section-row" style="border-bottom:1px solid #f0f0f0;">

                    <!-- Row header (always visible) -->
                    <div style="display:flex; align-items:center; gap:14px; padding:14px 22px; background:<?php echo $row_bg; ?>;">

                        <!-- Toggle switch -->
                        <label class="hp-toggle" title="Görünürlüğü değiştir" style="flex-shrink:0; cursor:pointer; position:relative; display:inline-block; width:44px; height:24px;">
                            <input type="checkbox"
                                   name="airlinel_sections[<?php echo esc_attr( $section['id'] ); ?>][visible]"
                                   value="1"
                                   <?php checked( $section['visible'] ); ?>
                                   style="opacity:0; width:0; height:0; position:absolute;">
                            <span class="hp-slider"></span>
                        </label>

                        <!-- Label + description -->
                        <div style="flex:1; min-width:0;">
                            <div style="font-weight:600; font-size:13px; color:#1a1a1a;">
                                <?php echo esc_html( $section['label'] ); ?>
                                <?php if ( $has_content ) : ?>
                                    <span style="margin-left:8px; background:#e6f7ee; color:#16a34a; font-size:10px; font-weight:600; padding:2px 8px; border-radius:10px; vertical-align:middle;">ÖZEL İÇERİK</span>
                                <?php endif; ?>
                            </div>
                            <div style="font-size:12px; color:#888; margin-top:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                <?php echo esc_html( $section['description'] ); ?>
                            </div>
                        </div>

                        <!-- Expand button -->
                        <button type="button"
                                class="hp-expand-btn"
                                data-target="section-content-<?php echo esc_attr( $section['id'] ); ?>"
                                style="flex-shrink:0; padding:6px 12px; background:#f0f0f0; border:1px solid #ddd; border-radius:6px; font-size:12px; color:#555; cursor:pointer; display:flex; align-items:center; gap:5px; white-space:nowrap;">
                            <span class="dashicons dashicons-edit" style="font-size:14px; width:14px; height:14px; margin-top:2px;"></span>
                            Özel İçerik
                            <span class="hp-chevron dashicons dashicons-arrow-down-alt2" style="font-size:14px; width:14px; height:14px; margin-top:2px; transition:transform .25s;"></span>
                        </button>
                    </div>

                    <!-- Expandable content editor -->
                    <div id="section-content-<?php echo esc_attr( $section['id'] ); ?>"
                         class="hp-section-content"
                         style="display:<?php echo $has_content ? 'block' : 'none'; ?>; padding:18px 22px; background:#f8f8f8; border-top:1px solid #eee;">
                        <p style="margin:0 0 10px; font-size:12px; color:#888;">
                            Boş bırakırsanız varsayılan tema içeriği kullanılır. HTML desteklenir.
                        </p>
                        <textarea name="airlinel_sections[<?php echo esc_attr( $section['id'] ); ?>][content]"
                                  rows="6"
                                  placeholder="Özel HTML içeriğinizi buraya girin…"
                                  style="width:100%; padding:10px 12px; border:1px solid #ddd; border-radius:6px; font-family:monospace; font-size:13px; resize:vertical; box-sizing:border-box;"><?php echo esc_textarea( $section['content'] ); ?></textarea>
                        <?php if ( $has_content ) : ?>
                        <div style="margin-top:8px; padding:8px 12px; background:#fff3cd; border-left:3px solid #ffc107; border-radius:0 4px 4px 0; font-size:12px; color:#856404;">
                            ⚠️ Bu bölümde özel içerik var — varsayılan tema tasarımı yerine bu içerik gösterilir.
                            Silmek için alanı tamamen boşaltın.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ── Save Button ──────────────────────────────────────── -->
        <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
            <button type="submit"
                    style="padding:12px 28px; background:#CC4452; color:#fff; border:none; border-radius:8px; font-size:14px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:8px;">
                <span class="dashicons dashicons-yes-alt" style="font-size:18px; width:18px; height:18px; margin-top:1px;"></span>
                Değişiklikleri Kaydet
            </button>
            <span style="font-size:12px; color:#999;">Tüm bölümler ve SEO bloğu tek seferde kaydedilir.</span>
        </div>

    </form>

    <!-- ── Reset Section ─────────────────────────────────────── -->
    <div style="margin-top:32px; padding:16px 22px; background:#fffbeb; border:1px solid #fde68a; border-radius:8px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div>
            <div style="font-weight:600; font-size:13px; color:#92400e;">Varsayılanlara Sıfırla</div>
            <div style="font-size:12px; color:#a16207; margin-top:2px;">Tüm bölümler görünür yapılır. Özel içerikler silinmez.</div>
        </div>
        <form method="post" style="margin:0;">
            <?php wp_nonce_field( 'airlinel_homepage_reset', 'airlinel_reset_nonce' ); ?>
            <button type="submit" name="reset_sections"
                    onclick="return confirm('Tüm bölümleri varsayılan görünürlüğe sıfırlamak istediğinizden emin misiniz?');"
                    style="padding:8px 16px; background:#fef3c7; border:1px solid #d97706; border-radius:6px; font-size:13px; color:#92400e; cursor:pointer; font-weight:600;">
                Sıfırla
            </button>
        </form>
    </div>

</div><!-- /#airlinel-hp-admin -->

<style>
/* Toggle switch */
.hp-toggle .hp-slider {
    position: absolute; inset: 0;
    background: #ddd;
    border-radius: 24px;
    transition: background .2s;
}
.hp-toggle .hp-slider::before {
    content: '';
    position: absolute;
    width: 18px; height: 18px;
    left: 3px; top: 3px;
    background: #fff;
    border-radius: 50%;
    transition: transform .2s;
    box-shadow: 0 1px 3px rgba(0,0,0,.2);
}
.hp-toggle input:checked + .hp-slider          { background: #22c55e; }
.hp-toggle input:checked + .hp-slider::before  { transform: translateX(20px); }
.hp-toggle input:focus + .hp-slider            { outline: 2px solid #4ade80; outline-offset: 2px; }

/* Expand button active state */
.hp-expand-btn.open { background: #fff0f1; border-color: #CC4452; color: #CC4452; }
.hp-expand-btn.open .hp-chevron { transform: rotate(180deg); }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    /* ── Expand / collapse section content areas ── */
    document.querySelectorAll('.hp-expand-btn').forEach(function (btn) {
        var targetId = btn.getAttribute('data-target');
        var panel    = document.getElementById(targetId);
        if (!panel) return;

        // Mark open if already visible
        if (panel.style.display !== 'none') btn.classList.add('open');

        btn.addEventListener('click', function () {
            var open = panel.style.display !== 'none';
            panel.style.display = open ? 'none' : 'block';
            btn.classList.toggle('open', !open);
        });
    });
});
</script>
