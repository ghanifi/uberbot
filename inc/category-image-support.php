<?php
/**
 * ══════════════════════════════════════════════════════
 *  KATEGORİ RESİM DESTEĞİ — functions.php'ye ekle
 *  (mevcut functions.php'nin sonuna yapıştır)
 * ══════════════════════════════════════════════════════
 *
 * 1) Admin panelinde Kategori Ekle / Düzenle formuna
 *    "Featured Image" (thumbnail) upload alanı ekler.
 * 2) Seçilen ek görselin ID'sini term meta olarak kaydeder.
 * 3) Kategori Description alanını admin'de görünür bırakır
 *    (varsayılan zaten görünür; ek temizlik yok).
 * 4) Kaydedilen meta, page-cities.php tarafından
 *    get_term_meta( $term_id, 'category_thumbnail_id', true )
 *    ile okunur.
 */

if ( ! function_exists( 'airlinel_category_image_field' ) ) :

    /* ── Yeni Kategori Formu ── */
    function airlinel_category_image_field_add() {
        ?>
        <div class="form-field term-group">
            <label for="category_thumbnail_id"><?php esc_html_e( 'Category Image', 'airlinel-theme' ); ?></label>
            <input type="hidden" id="category_thumbnail_id" name="category_thumbnail_id" value="">
            <div id="category-image-wrapper" style="margin-bottom:8px;"></div>
            <button type="button" class="button airlinel-upload-cat-img"
                    data-target="category_thumbnail_id"
                    data-preview="category-image-wrapper">
                <?php esc_html_e( 'Upload / Choose Image', 'airlinel-theme' ); ?>
            </button>
            <p class="description">
                <?php esc_html_e( 'Recommended: 800 × 600 px, WebP or JPEG.', 'airlinel-theme' ); ?>
            </p>
        </div>
        <?php
    }
    add_action( 'category_add_form_fields', 'airlinel_category_image_field_add' );

    /* ── Mevcut Kategoriyi Düzenleme Formu ── */
    function airlinel_category_image_field( $term ) {
        $thumbnail_id  = get_term_meta( $term->term_id, 'category_thumbnail_id', true );
        $thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url( $thumbnail_id, 'medium' ) : '';
        ?>
        <tr class="form-field term-group-wrap">
            <th scope="row">
                <label for="category_thumbnail_id"><?php esc_html_e( 'Category Image', 'airlinel-theme' ); ?></label>
            </th>
            <td>
                <input type="hidden" id="category_thumbnail_id" name="category_thumbnail_id"
                       value="<?php echo esc_attr( $thumbnail_id ); ?>">
                <div id="category-image-wrapper" style="margin-bottom:10px;">
                    <?php if ( $thumbnail_url ) : ?>
                        <img src="<?php echo esc_url( $thumbnail_url ); ?>" alt=""
                             style="max-width:200px;height:auto;display:block;border-radius:6px;">
                    <?php endif; ?>
                </div>
                <button type="button" class="button airlinel-upload-cat-img"
                        data-target="category_thumbnail_id"
                        data-preview="category-image-wrapper">
                    <?php esc_html_e( 'Upload / Choose Image', 'airlinel-theme' ); ?>
                </button>
                <?php if ( $thumbnail_id ) : ?>
                    <button type="button" class="button airlinel-remove-cat-img"
                            data-target="category_thumbnail_id"
                            data-preview="category-image-wrapper"
                            style="margin-left:6px;color:#d63638;">
                        <?php esc_html_e( 'Remove', 'airlinel-theme' ); ?>
                    </button>
                <?php endif; ?>
                <p class="description">
                    <?php esc_html_e( 'Recommended: 800 × 600 px, WebP or JPEG.', 'airlinel-theme' ); ?>
                </p>
            </td>
        </tr>
        <?php
    }
    add_action( 'category_edit_form_fields', 'airlinel_category_image_field' );

    /* ── Kaydetme (hem Ekle hem Düzenle) ── */
    function airlinel_save_category_image( $term_id ) {
        if ( isset( $_POST['category_thumbnail_id'] ) ) {
            $thumbnail_id = absint( $_POST['category_thumbnail_id'] );
            if ( $thumbnail_id ) {
                update_term_meta( $term_id, 'category_thumbnail_id', $thumbnail_id );
            } else {
                delete_term_meta( $term_id, 'category_thumbnail_id' );
            }
        }
    }
    add_action( 'created_category', 'airlinel_save_category_image' );
    add_action( 'edited_category', 'airlinel_save_category_image' );

    /* ── Admin JS/CSS: Media Uploader ── */
    function airlinel_category_admin_scripts( $hook ) {
        // Sadece kategori sayfalarında yükle
        if ( ! in_array( $hook, array( 'edit-tags.php', 'term.php' ) ) ) {
            return;
        }
        global $taxnow;
        if ( 'category' !== $taxnow && 'category' !== get_current_screen()->taxonomy ) {
            return;
        }

        wp_enqueue_media();
        wp_add_inline_script( 'jquery-core', "
        jQuery(function($) {
            /* ── Upload ── */
            $(document).on('click', '.airlinel-upload-cat-img', function(e) {
                e.preventDefault();
                var btn      = $(this);
                var targetId = btn.data('target');
                var previewId= btn.data('preview');

                var frame = wp.media({
                    title  : 'Select Category Image',
                    button : { text: 'Use this image' },
                    multiple: false
                });
                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    $('#' + targetId).val(attachment.id);
                    $('#' + previewId).html(
                        '<img src=\"' + attachment.url + '\" style=\"max-width:200px;height:auto;display:block;border-radius:6px;margin-bottom:6px;\">'
                    );
                    btn.siblings('.airlinel-remove-cat-img').show();
                });
                frame.open();
            });

            /* ── Remove ── */
            $(document).on('click', '.airlinel-remove-cat-img', function(e) {
                e.preventDefault();
                var btn      = $(this);
                var targetId = btn.data('target');
                var previewId= btn.data('preview');
                $('#' + targetId).val('');
                $('#' + previewId).html('');
                btn.hide();
            });
        });
        " );
    }
    add_action( 'admin_enqueue_scripts', 'airlinel_category_admin_scripts' );

endif; // end function_exists check
