<?php
/**
 * Category Archive Template
 * Used for /cities/antalya/, /cities/london/ etc.
 */
get_header();

$category    = get_queried_object();
$cat_name    = $category ? esc_html( $category->name )        : 'City Guides';
$cat_desc    = $category ? $category->description              : '';
$cat_count   = $category ? (int) $category->count              : 0;
$thumb_id    = $category ? get_term_meta( $category->term_id, 'category_thumbnail_id', true ) : '';
$hero_url    = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'full' ) : '';
if ( ! $hero_url ) {
    $hero_url = get_template_directory_uri() . '/images/theme-image-009.webp';
}
?>

<style>
/* ── Category Archive — scoped styles ── */
.cat-hero {
    position: relative;
    padding-top: 80px; /* nav height */
    min-height: 420px;
    display: flex;
    align-items: flex-end;
    overflow: hidden;
    background: #1a1a1a;
}
.cat-hero-bg {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
}
.cat-hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top,
        rgba(0,0,0,0.88) 0%,
        rgba(0,0,0,0.50) 45%,
        rgba(0,0,0,0.18) 100%);
}
.cat-hero-inner {
    position: relative;
    z-index: 10;
    width: 100%;
    max-width: 1280px;
    margin: 0 auto;
    padding: 60px 32px 56px;
}
.cat-breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 20px;
    font-size: 13px;
    color: rgba(255,255,255,0.55);
}
.cat-breadcrumb a {
    color: rgba(255,255,255,0.55);
    text-decoration: none;
    transition: color 0.2s;
}
.cat-breadcrumb a:hover { color: var(--primary-color); }
.cat-breadcrumb .sep { color: rgba(255,255,255,0.3); }
.cat-breadcrumb .current { color: rgba(255,255,255,0.85); }
.cat-hero-tag {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 14px;
    background: var(--primary-color);
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    border-radius: 999px;
    margin-bottom: 16px;
}
.cat-hero-h1 {
    font-family: var(--font-family-heading);
    font-size: clamp(2.4rem, 5vw, 4rem);
    font-weight: 700;
    color: #fff;
    margin: 0 0 16px;
    line-height: 1.1;
    text-shadow: 0 2px 12px rgba(0,0,0,0.4);
}
.cat-hero-desc {
    color: rgba(255,255,255,0.75);
    font-size: 1rem;
    line-height: 1.65;
    max-width: 620px;
    margin: 0 0 28px;
}
.cat-hero-meta {
    display: flex;
    align-items: center;
    gap: 24px;
    flex-wrap: wrap;
}
.cat-hero-meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
    color: rgba(255,255,255,0.65);
    font-size: 13px;
}
.cat-hero-meta-item i { color: var(--primary-color); }

/* Main content area */
.cat-main {
    background: #f9fafb;
    padding: 64px 0 80px;
}
.cat-main-inner {
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 32px;
}
.cat-section-label {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 36px;
}
.cat-section-label span.line {
    display: block;
    width: 32px;
    height: 2px;
    background: var(--primary-color);
    border-radius: 2px;
}
.cat-section-label span.text {
    font-family: var(--font-family-heading);
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: var(--primary-color);
}

/* Post grid */
.cat-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
    margin-bottom: 56px;
}
@media (max-width: 960px) { .cat-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 580px) { .cat-grid { grid-template-columns: 1fr; } }

.cat-post-card {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    border: 1px solid #e5e7eb;
    box-shadow: 0 1px 4px rgba(0,0,0,0.06);
    transition: box-shadow 0.3s ease, transform 0.3s ease;
    display: flex;
    flex-direction: column;
    text-decoration: none;
    color: inherit;
}
.cat-post-card:hover {
    box-shadow: 0 8px 28px rgba(0,0,0,0.12);
    transform: translateY(-3px);
}
.cat-post-thumb {
    width: 100%;
    aspect-ratio: 16/9;
    overflow: hidden;
    background: #e5e7eb;
    flex-shrink: 0;
}
.cat-post-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.5s ease;
}
.cat-post-card:hover .cat-post-thumb img { transform: scale(1.05); }
.cat-post-body {
    padding: 20px 22px 22px;
    display: flex;
    flex-direction: column;
    flex: 1;
}
.cat-post-date {
    font-size: 11px;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-bottom: 8px;
}
.cat-post-title {
    font-family: var(--font-family-heading);
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--dark-text-color);
    line-height: 1.35;
    margin: 0 0 10px;
    transition: color 0.2s;
}
.cat-post-card:hover .cat-post-title { color: var(--primary-color); }
.cat-post-excerpt {
    font-size: 0.84rem;
    color: #6b7280;
    line-height: 1.6;
    margin: 0 0 16px;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    flex: 1;
}
.cat-post-readmore {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--primary-color);
    margin-top: auto;
}
.cat-post-readmore i { font-size: 10px; transition: transform 0.2s; }
.cat-post-card:hover .cat-post-readmore i { transform: translateX(3px); }

/* ── Pagination ── */
.cat-pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    flex-wrap: wrap;
}
.cat-pagination a,
.cat-pagination span {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    height: 40px;
    padding: 0 10px;
    border-radius: 10px;
    font-size: 0.88rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
    border: 1px solid #e5e7eb;
    background: #fff;
    color: var(--dark-text-color);
}
.cat-pagination a:hover {
    background: var(--light-background-color);
    border-color: var(--primary-color);
    color: var(--primary-color);
}
.cat-pagination span.current {
    background: var(--primary-color);
    border-color: var(--primary-color);
    color: #fff;
}
.cat-pagination span.dots {
    border: none;
    background: transparent;
    color: #9ca3af;
}
.cat-pagination .prev-next {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 0 16px;
    background: var(--dark-background-color);
    border-color: var(--dark-background-color);
    color: #fff;
    border-radius: 999px;
}
.cat-pagination .prev-next:hover {
    background: var(--primary-color);
    border-color: var(--primary-color);
    color: #fff;
}

/* Empty state */
.cat-empty {
    text-align: center;
    padding: 80px 0;
    color: #9ca3af;
}
.cat-empty i { font-size: 3rem; display: block; margin-bottom: 16px; }
</style>

<!-- ══ HERO ══ -->
<section class="cat-hero">
    <div class="cat-hero-bg" style="background-image:url('<?php echo esc_url( $hero_url ); ?>')"></div>
    <div class="cat-hero-overlay"></div>

    <div class="cat-hero-inner">

        <!-- Breadcrumb -->
        <nav class="cat-breadcrumb" aria-label="Breadcrumb">
            <a href="<?php echo home_url(); ?>"><?php _e('Home', 'airlinel-theme'); ?></a>
            <span class="sep">/</span>
            <a href="<?php echo home_url( '/cities/' ); ?>"><?php _e('Cities', 'airlinel-theme'); ?></a>
            <span class="sep">/</span>
            <span class="current"><?php echo $cat_name; ?></span>
        </nav>

        <div class="cat-hero-tag">
            <i class="fa-solid fa-plane-arrival"></i>
            Airport Transfer &amp; Chauffeur
        </div>

        <h1 class="cat-hero-h1"><?php echo $cat_name; ?> Transfer Services</h1>

        <?php if ( ! empty( $cat_desc ) ) : ?>
        <p class="cat-hero-desc"><?php echo esc_html( wp_trim_words( $cat_desc, 35, '…' ) ); ?></p>
        <?php endif; ?>

        <div class="cat-hero-meta">
            <div class="cat-hero-meta-item">
                <i class="fa-solid fa-book-open"></i>
                <span><?php echo $cat_count; ?> Guide<?php echo $cat_count !== 1 ? 's' : ''; ?></span>
            </div>
            <div class="cat-hero-meta-item">
                <i class="fa-solid fa-clock"></i>
                <span><?php _e('24/7 Service Available', 'airlinel-theme'); ?></span>
            </div>
            <div class="cat-hero-meta-item">
                <i class="fa-solid fa-star"></i>
                <span><?php _e('5-Star Rated', 'airlinel-theme'); ?></span>
            </div>
            <a href="<?php echo home_url( '/book-your-ride' ); ?>"
               style="display:inline-flex;align-items:center;gap:8px;padding:10px 22px;background:var(--primary-color);color:#fff;font-size:0.85rem;font-weight:700;border-radius:999px;text-decoration:none;transition:background 0.2s;"
               onmouseover="this.style.background='var(--primary-button-hover-bg-color)'"
               onmouseout="this.style.background='var(--primary-color)'">
                <?php _e('Book a Transfer', 'airlinel-theme'); ?> <i class="fa-solid fa-arrow-right" style="font-size:11px;"></i>
            </a>
        </div>

    </div>
</section>


<!-- ══ POSTS ══ -->
<main class="cat-main">
    <div class="cat-main-inner">

        <div class="cat-section-label">
            <span class="line"></span>
            <span class="text"><?php echo $cat_name; ?> Travel Guides</span>
        </div>

        <?php if ( have_posts() ) : ?>

        <div class="cat-grid">
            <?php while ( have_posts() ) : the_post(); ?>
            <a href="<?php the_permalink(); ?>" class="cat-post-card">

                <div class="cat-post-thumb">
                    <?php if ( has_post_thumbnail() ) : ?>
                        <?php the_post_thumbnail( 'medium_large' ); ?>
                    <?php else : ?>
                        <img src="<?php echo get_template_directory_uri(); ?>/images/theme-image-009.webp"
                             alt="<?php the_title_attribute(); ?>"
                             loading="lazy">
                    <?php endif; ?>
                </div>

                <div class="cat-post-body">
                    <p class="cat-post-date">
                        <i class="fa-regular fa-calendar" style="margin-right:4px;"></i>
                        <?php echo get_the_date( 'j M Y' ); ?>
                    </p>
                    <h2 class="cat-post-title"><?php the_title(); ?></h2>
                    <p class="cat-post-excerpt"><?php echo wp_trim_words( get_the_excerpt(), 22, '…' ); ?></p>
                    <span class="cat-post-readmore">
                        <?php _e('Read Guide', 'airlinel-theme'); ?> <i class="fa-solid fa-arrow-right"></i>
                    </span>
                </div>

            </a>
            <?php endwhile; ?>
        </div>

        <!-- Pagination -->
        <?php
        $pagination = paginate_links( array(
            'type'      => 'array',
            'prev_text' => '<i class="fa-solid fa-arrow-left" style="font-size:11px;"></i> Previous',
            'next_text' => 'Next <i class="fa-solid fa-arrow-right" style="font-size:11px;"></i>',
        ) );

        if ( $pagination ) : ?>
        <nav class="cat-pagination" aria-label="Posts navigation">
            <?php foreach ( $pagination as $page ) :
                // Add class for prev/next arrows
                if ( strpos( $page, 'prev' ) !== false || strpos( $page, 'next' ) !== false ) {
                    $page = str_replace( 'page-numbers', 'page-numbers prev-next', $page );
                }
                echo $page;
            endforeach; ?>
        </nav>
        <?php endif; ?>

        <?php else : ?>
        <div class="cat-empty">
            <i class="fa-solid fa-file-circle-xmark"></i>
            <p>No guides published for <?php echo $cat_name; ?> yet.</p>
        </div>
        <?php endif; ?>

    </div>
</main>

<?php get_footer(); ?>
