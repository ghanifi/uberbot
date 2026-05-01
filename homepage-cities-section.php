<?php
// Homepage Cities Section
// Bu kodu anasayfanın ilgili section'ı ile değiştirin (id="sf560up" olan section)

// Kategorileri çek — uncategorized hariç
$_hp_term_query = new WP_Term_Query( array(
    'taxonomy'   => 'category',
    'hide_empty' => false,
    'orderby'    => 'count',
    'order'      => 'DESC',
    'number'     => 0,
    'fields'     => 'all',
) );
$_hp_all_terms = ! is_wp_error( $_hp_term_query->terms ) ? $_hp_term_query->terms : array();
$_hp_cities    = array_values( array_filter( $_hp_all_terms, function( $t ) {
    return $t->slug !== 'uncategorized';
} ) );

// Thumbnail helper — aynı page-cities.php mantığı
if ( ! function_exists( 'airlinel_cat_thumb' ) ) :
function airlinel_cat_thumb( $category, $index ) {
    $fallbacks = array(
        'theme-image-009.webp','theme-image-005.webp','theme-image-007.webp',
        'theme-image-003.webp','theme-image-011.webp','theme-image-002.webp',
        'theme-image-010.webp','theme-image-012.webp','theme-image-013.webp',
    );
    $id  = get_term_meta( $category->term_id, 'category_thumbnail_id', true );
    $url = $id ? wp_get_attachment_image_url( $id, 'large' ) : '';
    if ( ! $url ) {
        $posts = get_posts( array( 'numberposts' => 1, 'category' => $category->term_id, 'suppress_filters' => false ) );
        if ( $posts && has_post_thumbnail( $posts[0]->ID ) ) {
            $url = get_the_post_thumbnail_url( $posts[0]->ID, 'large' );
        }
    }
    if ( ! $url ) {
        $url = get_template_directory_uri() . '/images/' . $fallbacks[ $index % count( $fallbacks ) ];
    }
    return esc_url( $url );
}
endif;
?>

<style>
/* ── Homepage Cities Section ────────────────────────────────── */
.hp-cities-section {
    position: relative;
    background: #0d0d0d;
    overflow: hidden;
    padding: 80px 0 72px;
}

/* Subtle radial glow top-left */
.hp-cities-section::before {
    content: '';
    position: absolute;
    top: -120px;
    left: -80px;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(204,68,82,0.12) 0%, transparent 70%);
    pointer-events: none;
}

/* Subtle radial glow bottom-right */
.hp-cities-section::after {
    content: '';
    position: absolute;
    bottom: -100px;
    right: -80px;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(204,68,82,0.08) 0%, transparent 70%);
    pointer-events: none;
}

/* Thin diagonal accent lines in background */
.hp-cities-lines {
    position: absolute;
    inset: 0;
    opacity: 0.07;
    pointer-events: none;
}

.hp-cities-inner {
    position: relative;
    z-index: 2;
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 24px;
}

/* Header */
.hp-cities-header {
    text-align: center;
    margin-bottom: 48px;
}
.hp-cities-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: var(--primary-color);
    margin-bottom: 16px;
}
.hp-cities-eyebrow::before,
.hp-cities-eyebrow::after {
    content: '';
    display: block;
    width: 32px;
    height: 1px;
    background: var(--primary-color);
    opacity: 0.5;
}
.hp-cities-title {
    font-family: var(--font-family-heading);
    font-size: clamp(2rem, 4vw, 3.2rem);
    font-weight: 700;
    color: #fff;
    line-height: 1.1;
    margin: 0 0 16px;
}
.hp-cities-title em {
    font-style: normal;
    color: var(--primary-color);
}
.hp-cities-subtitle {
    color: #9ca3af;
    font-size: 1rem;
    max-width: 560px;
    margin: 0 auto;
    line-height: 1.65;
}

/* Grid — same logic as page-cities.php */
.hp-cities-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 48px;
}
@media (max-width: 1100px) {
    .hp-cities-grid { grid-template-columns: repeat(3, 1fr); }
    .hp-city-card.featured { grid-column: span 2; }
}
@media (max-width: 720px) {
    .hp-cities-grid { grid-template-columns: repeat(2, 1fr); }
    .hp-city-card.featured { grid-column: span 2; }
}
@media (max-width: 480px) {
    .hp-cities-grid { grid-template-columns: 1fr; }
    .hp-city-card.featured { grid-column: span 1; }
}

/* Card */
.hp-city-card {
    position: relative;
    border-radius: 14px;
    overflow: hidden;
    display: block;
    text-decoration: none;
    background: #1a1a1a;
    padding-bottom: 72%;
    height: 0;
    cursor: pointer;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: 1px solid rgba(255,255,255,0.06);
}
.hp-city-card.featured {
    padding-bottom: 72%;
}
.hp-city-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 16px 40px rgba(0,0,0,0.35);
    border-color: rgba(204,68,82,0.4);
}
.hp-city-card-bg {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
    transition: transform 0.7s ease;
}
.hp-city-card:hover .hp-city-card-bg {
    transform: scale(1.07);
}
.hp-city-card-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        to top,
        rgba(0,0,0,0.90) 0%,
        rgba(0,0,0,0.50) 50%,
        rgba(0,0,0,0.10) 100%
    );
}
/* Post count badge */
.hp-city-card-badge {
    position: absolute;
    top: 12px;
    right: 12px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 9px;
    background: rgba(0,0,0,0.55);
    border: 1px solid rgba(255,255,255,0.14);
    border-radius: 999px;
    color: #fff;
    font-size: 10px;
    font-weight: 600;
    backdrop-filter: blur(4px);
}
.hp-city-card-badge i { color: var(--primary-color); font-size: 9px; }

/* Card content */
.hp-city-card-content {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 18px 20px;
}
.hp-city-card.featured .hp-city-card-content {
    padding: 24px 28px;
}
.hp-city-card-name {
    font-family: var(--font-family-heading);
    font-size: 1.3rem;
    font-weight: 700;
    color: #fff;
    margin: 0 0 6px;
    line-height: 1.2;
    text-shadow: 0 1px 8px rgba(0,0,0,0.5);
}
.hp-city-card.featured .hp-city-card-name {
    font-size: 1.9rem;
}
.hp-city-card-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.8rem;
    font-weight: 600;
    color: rgba(255,255,255,0.75);
    transition: color 0.2s;
}
.hp-city-card:hover .hp-city-card-link {
    color: var(--primary-color);
}

/* CTA button */
.hp-cities-cta {
    text-align: center;
}
.hp-cities-cta-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 14px 36px;
    background: transparent;
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 999px;
    color: #fff;
    font-size: 0.9rem;
    font-weight: 600;
    text-decoration: none;
    transition: background 0.2s, border-color 0.2s, color 0.2s;
}
.hp-cities-cta-btn:hover {
    background: var(--primary-color);
    border-color: var(--primary-color);
    color: #fff;
}
.hp-cities-cta-btn i { font-size: 12px; }
</style>

<section class="code-section hp-cities-section" id="sf560up">

    <!-- Subtle SVG lines in BG -->
    <svg class="hp-cities-lines" viewBox="0 0 1280 600" fill="none" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
        <path d="M0 300 Q320 120 640 300 Q960 480 1280 300" stroke="white" stroke-width="1.5" fill="none"/>
        <path d="M0 380 Q320 200 640 380 Q960 560 1280 380" stroke="white" stroke-width="1" fill="none"/>
        <circle cx="180" cy="280" r="6" fill="var(--primary-color)" opacity="0.6"/>
        <circle cx="1100" cy="380" r="6" fill="var(--primary-color)" opacity="0.6"/>
        <line x1="180" y1="280" x2="1100" y2="380" stroke="var(--primary-color)" stroke-width="1" stroke-dasharray="6 10" opacity="0.3"/>
    </svg>

    <div class="hp-cities-inner">

        <!-- Header -->
        <div class="hp-cities-header">
            <div class="hp-cities-eyebrow">
                <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                <?php _e('Service Locations', 'airlinel-theme'); ?>
            </div>
            <h2 class="hp-cities-title">
                <?php _e('Two Continents.', 'airlinel-theme'); ?><br><em><?php _e('One Standard.', 'airlinel-theme'); ?></em>
            </h2>
            <p class="hp-cities-subtitle">
                <?php _e('Airport transfers across the UK and Turkey — professional drivers, fixed rates, real-time flight tracking.', 'airlinel-theme'); ?>
            </p>
        </div>

        <!-- City Grid -->
        <?php if ( ! empty( $_hp_cities ) ) : ?>
        <div class="hp-cities-grid">
            <?php foreach ( $_hp_cities as $index => $category ) :
                $thumb    = airlinel_cat_thumb( $category, $index );
                $link     = get_category_link( $category->term_id );
                $name     = esc_html( $category->name );
                $cnt      = (int) $category->count;
                $is_feat  = ( $index === 0 );
                $class    = 'hp-city-card' . ( $is_feat ? ' featured' : '' );
            ?>
            <a href="<?php echo esc_url( $link ); ?>" class="<?php echo $class; ?>">
                <div class="hp-city-card-bg" style="background-image:url('<?php echo $thumb; ?>')"></div>
                <div class="hp-city-card-overlay"></div>

                <?php if ( $cnt > 0 ) : ?>
                <div class="hp-city-card-badge">
                    <i class="fa-solid fa-book-open" aria-hidden="true"></i>
                    <?php echo $cnt; ?> Guide<?php echo $cnt !== 1 ? 's' : ''; ?>
                </div>
                <?php endif; ?>

                <div class="hp-city-card-content">
                    <div class="hp-city-card-name"><?php echo $name; ?></div>
                    <span class="hp-city-card-link">
                        <?php echo $name; ?> Airport Transfers
                        <i class="fa-solid fa-arrow-right" style="font-size:10px;" aria-hidden="true"></i>
                    </span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- CTA -->
        <div class="hp-cities-cta">
            <a href="<?php echo esc_url( home_url('/cities/') ); ?>" class="hp-cities-cta-btn">
                <?php _e('View All Cities', 'airlinel-theme'); ?>
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </a>
        </div>

    </div>
</section>
