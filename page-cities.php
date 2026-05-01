<?php
/**
 * Template Name: Cities / Blog Categories Grid
 */
get_header();

// Tüm kategorileri çek — sadece 'uncategorized' slug'ını hariç tut
$term_query = new WP_Term_Query( array(
    'taxonomy'   => 'category',
    'hide_empty' => false,
    'orderby'    => 'name',
    'order'      => 'ASC',
    'number'     => 0,
    'fields'     => 'all',
) );
$all_terms = ! is_wp_error( $term_query->terms ) ? $term_query->terms : array();

// Sadece 'uncategorized' slug'ını çıkar — ID'ye göre değil
$cities = array_filter( $all_terms, function( $term ) {
    return $term->slug !== 'uncategorized';
} );
$cities     = array_values( $cities ); // index'leri sıfırla
$city_count = count( $cities );

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
?>

<style>
/* Cities page — all styles scoped here, zero Tailwind arbitrary values */
.cities-hero {
    position: relative;
    padding-top: 80px; /* header height */
    background-color: #0d0d0d;
    overflow: hidden;
    min-height: 580px;
    display: flex;
    align-items: stretch;
}

/* Full-bleed background photo — darkened */
.cities-hero-bg {
    position: absolute;
    inset: 0;
    background-image: url('https://airlinel.com/wp-content/uploads/2026/03/london-airport-transfer-1024x572.webp');
    background-size: cover;
    background-position: center 30%;
    opacity: 0.22;
}

/* Left-to-right gradient so left text is readable, right shows the photo card */
.cities-hero-fade {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        to right,
        rgba(13,13,13,0.97) 0%,
        rgba(13,13,13,0.85) 40%,
        rgba(13,13,13,0.30) 70%,
        rgba(13,13,13,0.05) 100%
    );
}

.cities-hero-inner {
    position: relative;
    z-index: 10;
    width: 100%;
    max-width: 1280px;
    margin: 0 auto;
    padding: 80px 40px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    align-items: center;
}
@media (max-width: 860px) {
    .cities-hero-inner {
        grid-template-columns: 1fr;
        padding: 60px 24px;
    }
    .cities-hero-photo { display: none; }
    .cities-hero-bg { opacity: 0.12; }
}

/* Left text column */
.cities-hero-text { max-width: 560px; }

.cities-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 24px;
}
.cities-eyebrow span.line {
    display: block;
    width: 28px;
    height: 2px;
    background: var(--primary-color);
    border-radius: 2px;
}
.cities-eyebrow span.label {
    color: var(--primary-color);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.18em;
    text-transform: uppercase;
}
.cities-h1 {
    font-family: var(--font-family-heading);
    font-size: clamp(2.4rem, 4.5vw, 3.8rem);
    font-weight: 700;
    color: #fff;
    line-height: 1.08;
    margin: 0 0 20px;
}
.cities-h1 em {
    font-style: normal;
    color: var(--primary-color);
}
.cities-subtitle {
    color: #9ca3af;
    font-size: 1rem;
    line-height: 1.7;
    max-width: 460px;
    margin: 0 0 40px;
}

/* Stats row */
.cities-stats {
    display: flex;
    gap: 0;
    border: 1px solid #2a2a2a;
    border-radius: 14px;
    overflow: hidden;
    width: fit-content;
    background: rgba(255,255,255,0.03);
}
.cities-stat {
    padding: 18px 28px;
    text-align: center;
    border-right: 1px solid #2a2a2a;
}
.cities-stat:last-child { border-right: none; }
.cities-stat-num {
    font-family: var(--font-family-heading);
    font-size: 1.8rem;
    font-weight: 700;
    color: #fff;
    display: block;
    line-height: 1;
}
.cities-stat-num.accent { color: var(--primary-color); }
.cities-stat-label {
    display: block;
    font-size: 9px;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #4b5563;
    margin-top: 6px;
}

/* Right photo card */
.cities-hero-photo {
    position: relative;
    border-radius: 20px;
    overflow: hidden;
    aspect-ratio: 4/3;
    box-shadow: 0 32px 80px rgba(0,0,0,0.6), 0 0 0 1px rgba(255,255,255,0.06);
}
.cities-hero-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
/* Badge on photo card */
.cities-hero-photo-badge {
    position: absolute;
    bottom: 20px;
    left: 20px;
    right: 20px;
    background: rgba(0,0,0,0.75);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 12px;
    padding: 14px 18px;
    display: flex;
    align-items: center;
    gap: 14px;
}
.cities-hero-photo-badge-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: var(--primary-color);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: #fff;
    font-size: 14px;
}
.cities-hero-photo-badge-text strong {
    display: block;
    color: #fff;
    font-size: 0.85rem;
    font-weight: 700;
}
.cities-hero-photo-badge-text span {
    color: #9ca3af;
    font-size: 0.75rem;
}

.cities-divider { height: 1px; background: #1a1a1a; }

/* Grid */
.cities-section {
    padding: 80px 0;
    background: #f9fafb;
}
.cities-grid-wrap {
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 24px;
}
.cities-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
}
@media (max-width: 1100px) {
    .cities-grid { grid-template-columns: repeat(3, 1fr); }
    .city-card.featured { grid-column: span 2; }
}
@media (max-width: 720px) {
    .cities-grid { grid-template-columns: repeat(2, 1fr); }
    .city-card.featured { grid-column: span 2; }
}
@media (max-width: 480px) {
    .cities-grid { grid-template-columns: 1fr; }
    .city-card.featured { grid-column: span 1; }
}

/* Card */
.city-card {
    position: relative;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.12);
    transition: box-shadow 0.3s ease, transform 0.3s ease;
    cursor: pointer;
    display: block;
    text-decoration: none;
    background: #1a1a1a;
    /* Fixed height via padding-bottom trick */
    padding-bottom: 75%;
    height: 0;
}
.city-card.featured {
    padding-bottom: 75%; /* same ratio as regular cards — just wider due to col-span-2 */
}
.city-card:hover {
    box-shadow: 0 12px 32px rgba(0,0,0,0.22);
    transform: translateY(-4px);
}
.city-card-bg {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
    transition: transform 0.7s ease;
}
.city-card:hover .city-card-bg {
    transform: scale(1.07);
}
.city-card-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.88) 0%, rgba(0,0,0,0.45) 55%, rgba(0,0,0,0.08) 100%);
}
.city-card-badge {
    position: absolute;
    top: 14px;
    right: 14px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    background: rgba(0,0,0,0.6);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 999px;
    color: #fff;
    font-size: 11px;
    font-weight: 600;
    backdrop-filter: blur(4px);
}
.city-card-badge i { color: var(--primary-color); font-size: 9px; }
.city-card-content {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 20px;
}
.city-card.featured .city-card-content { padding: 28px; }
.city-card-title {
    font-family: var(--font-family-heading);
    font-size: 1.35rem;
    font-weight: 700;
    color: #fff;
    margin: 0 0 6px;
    line-height: 1.2;
    text-shadow: 0 1px 8px rgba(0,0,0,0.6);
}
.city-card.featured .city-card-title { font-size: 2rem; }
.city-card-desc {
    color: rgba(255,255,255,0.75);
    font-size: 0.82rem;
    line-height: 1.5;
    margin: 0 0 10px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.city-card.featured .city-card-desc {
    -webkit-line-clamp: 3;
    max-width: 420px;
}
.city-card-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.82rem;
    font-weight: 600;
    color: rgba(255,255,255,0.85);
    transition: color 0.2s;
    text-shadow: 0 1px 4px rgba(0,0,0,0.5);
}
.city-card:hover .city-card-link { color: var(--primary-color); }

/* Trust strip */
.cities-trust {
    padding: 56px 0;
    background: #fff;
    border-top: 1px solid var(--light-border-color);
}
.cities-trust-inner {
    max-width: 1100px;
    margin: 0 auto;
    padding: 0 24px;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 32px;
}
@media (max-width: 768px) { .cities-trust-inner { grid-template-columns: repeat(2, 1fr); } }
.trust-item { display: flex; gap: 14px; align-items: flex-start; }
.trust-icon {
    flex-shrink: 0;
    width: 42px; height: 42px;
    border-radius: 10px;
    background: var(--light-background-color);
    display: flex; align-items: center; justify-content: center;
    color: var(--primary-color);
}
.trust-title {
    font-family: var(--font-family-heading);
    font-weight: 700;
    font-size: 0.88rem;
    color: var(--dark-text-color);
    margin: 0 0 4px;
}
.trust-body { font-size: 0.78rem; color: #6b7280; line-height: 1.5; margin: 0; }

/* CTA */
.cities-cta {
    padding: 96px 24px;
    background: linear-gradient(to right, var(--dark-background-color), #000);
    text-align: center;
}
.cities-cta h2 {
    font-family: var(--font-family-heading);
    font-size: clamp(2rem, 5vw, 3.2rem);
    font-weight: 700;
    color: #fff;
    margin: 0 0 16px;
}
.cities-cta p {
    color: #9ca3af;
    font-size: 1.05rem;
    max-width: 440px;
    margin: 0 auto 36px;
    line-height: 1.6;
}
.cities-cta-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 18px 40px;
    background: var(--primary-color);
    color: #fff;
    font-weight: 700;
    font-size: 1rem;
    border-radius: 999px;
    text-decoration: none;
    transition: background 0.25s, box-shadow 0.25s, transform 0.25s;
}
.cities-cta-btn:hover {
    background: var(--primary-button-hover-bg-color);
    box-shadow: 0 12px 32px rgba(204,68,82,0.35);
    transform: translateY(-2px);
    color: #fff;
}
</style>

<!-- HERO -->
<section class="cities-hero">
    <!-- subtle full-bleed photo bg -->
    <div class="cities-hero-bg"></div>
    <div class="cities-hero-fade"></div>

    <div class="cities-hero-inner">

        <!-- LEFT: text -->
        <div class="cities-hero-text">
            <div class="cities-eyebrow">
                <span class="line"></span>
                <span class="label">London · Manchester · Istanbul · Antalya</span>
            </div>

            <?php if ( have_posts() ) : the_post(); ?>
            <h1 class="cities-h1"><?php _e('Airport Transfers', 'airlinel-theme'); ?><br><?php esc_html_e('by', 'airlinel-theme'); ?> <em><?php esc_html_e('City', 'airlinel-theme'); ?></em></h1>
            <?php if ( get_the_content() ) : ?>
                <p class="cities-subtitle"><?php echo wp_strip_all_tags( get_the_content() ); ?></p>
            <?php else : ?>
                <p class="cities-subtitle"><?php _e('Book airport transfers in London, Manchester, Istanbul and Antalya. Fixed rates, professional drivers, real-time flight tracking — available 24/7.', 'airlinel-theme'); ?></p>
            <?php endif; ?>
            <?php endif; ?>

            <div class="cities-stats">
                <div class="cities-stat">
                    <span class="cities-stat-num"><?php echo $city_count; ?>+</span>
                    <span class="cities-stat-label"><?php _e('Cities', 'airlinel-theme'); ?></span>
                </div>
                <div class="cities-stat">
                    <span class="cities-stat-num">24/7</span>
                    <span class="cities-stat-label"><?php _e('Available', 'airlinel-theme'); ?></span>
                </div>
                <div class="cities-stat">
                    <span class="cities-stat-num accent">5★</span>
                    <span class="cities-stat-label"><?php _e('Rated', 'airlinel-theme'); ?></span>
                </div>
            </div>
        </div>

        <!-- RIGHT: photo card -->
        <div class="cities-hero-photo">
            <img src="https://airlinel.com/wp-content/uploads/2026/03/london-airport-transfer-1024x572.webp"
                 alt="Premium airport transfer service"
                 fetchpriority="high">
            <div class="cities-hero-photo-badge">
                <div class="cities-hero-photo-badge-icon">
                    <i class="fa-solid fa-car-side"></i>
                </div>
                <div class="cities-hero-photo-badge-text">
                    <strong>Mercedes-Benz Fleet</strong>
                    <span>Meet &amp; Greet · Flight Tracking · Fixed Rates</span>
                </div>
            </div>
        </div>

    </div>
    <div class="cities-divider"></div>
</section>


<!-- CITIES GRID -->
<section class="cities-section">
    <div class="cities-grid-wrap">

        <?php if ( ! empty( $cities ) ) : ?>

        <div class="cities-grid">
            <?php foreach ( $cities as $index => $category ) :
                $thumb   = airlinel_cat_thumb( $category, $index );
                $link    = get_category_link( $category->term_id );
                $name    = esc_html( $category->name );
                $desc    = $category->description;
                $cnt     = (int) $category->count;
                $is_feat = ( $index === 0 );
                $class   = 'city-card' . ( $is_feat ? ' featured' : '' );
            ?>
            <a href="<?php echo esc_url( $link ); ?>" class="<?php echo $class; ?>">
                <div class="city-card-bg" style="background-image:url('<?php echo $thumb; ?>')"></div>
                <div class="city-card-overlay"></div>

                <?php if ( $cnt > 0 ) : ?>
                <div class="city-card-badge">
                    <i class="fa-solid fa-book-open"></i>
                    <?php echo $cnt; ?> Guide<?php echo $cnt !== 1 ? 's' : ''; ?>
                </div>
                <?php endif; ?>

                <div class="city-card-content">
                    <h2 class="city-card-title"><?php echo $name; ?></h2>
                    <span class="city-card-link">
                        <?php echo $name; ?> <?php _e('Airport Transfers', 'airlinel-theme'); ?>
                        <i class="fa-solid fa-arrow-right" style="font-size:10px;"></i>
                    </span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <?php else : ?>
        <div style="text-align:center;padding:80px 0;">
            <p style="color:#9ca3af;">No cities found. Add blog categories via WordPress Admin → Posts → Categories.</p>
        </div>
        <?php endif; ?>

    </div>
</section>


<!-- TRUST STRIP -->
<section class="cities-trust">
    <div class="cities-trust-inner">
        <?php
        $trust = array(
            array( 'fa-clock',         __( 'Always On Time', 'airlinel-theme' ),  __( 'Real-time flight tracking & guaranteed punctuality.', 'airlinel-theme' ) ),
            array( 'fa-shield-halved', __( 'Fully Licensed', 'airlinel-theme' ),   __( 'TfL licensed. All drivers professionally vetted & insured.', 'airlinel-theme' ) ),
            array( 'fa-star',          __( '5-Star Rated', 'airlinel-theme' ),    __( 'Thousands of verified reviews from worldwide travellers.', 'airlinel-theme' ) ),
            array( 'fa-headset',       __( '24/7 Support', 'airlinel-theme' ),    __( 'Around the clock support in English and Turkish.', 'airlinel-theme' ) ),
        );
        foreach ( $trust as $t ) : ?>
        <div class="trust-item">
            <div class="trust-icon"><i class="fa-solid <?php echo $t[0]; ?>"></i></div>
            <div>
                <p class="trust-title"><?php echo $t[1]; ?></p>
                <p class="trust-body"><?php echo $t[2]; ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>


<!-- CTA -->
<section class="cities-cta">
    <h2><?php _e('Ready to Book Your Transfer?', 'airlinel-theme'); ?></h2>
    <p><?php _e('Select your city above or book directly — any destination, any time.', 'airlinel-theme'); ?></p>
    <a href="<?php echo home_url( '/book-your-ride' ); ?>" class="cities-cta-btn">
        <?php _e('Book Now', 'airlinel-theme'); ?> <i class="fa-solid fa-arrow-right"></i>
    </a>
</section>

<?php get_footer(); ?>
