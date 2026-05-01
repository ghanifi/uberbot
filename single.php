<?php get_header(); ?>

<main class="bg-white min-h-screen pt-24 pb-12">
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
    <?php
    $article_schema = array(
        '@context'         => 'https://schema.org',
        '@type'            => 'Article',
        'headline'         => get_the_title(),
        'datePublished'    => get_the_date( 'c' ),
        'dateModified'     => get_the_modified_date( 'c' ),
        'author'           => array( '@type' => 'Person', 'name' => get_the_author() ),
        'publisher'        => array(
            '@type' => 'Organization',
            'name'  => 'Airlinel Airport Transfers',
            'logo'  => array( '@type' => 'ImageObject', 'url' => 'https://airlinel.com/wp-content/uploads/2025/10/logo-scaled.webp' ),
        ),
        'description'      => strip_tags( get_the_excerpt() ),
        'mainEntityOfPage' => array( '@type' => 'WebPage', '@id' => get_permalink() ),
    );
    if ( has_post_thumbnail() ) {
        $article_schema['image'] = get_the_post_thumbnail_url( null, 'full' );
    }
    ?>
    <script type="application/ld+json"><?php echo wp_json_encode( $article_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?></script>
        <article class="max-w-4xl mx-auto px-4">
            <header class="text-center mb-12">
                <div class="mb-4">
                    <?php the_category(' '); ?>
                </div>
                <h1 class="text-4xl md:text-5xl font-black text-gray-900 mb-6 leading-tight">
                    <?php the_title(); ?>
                </h1>
                <div class="flex items-center justify-center gap-4 text-gray-500 text-sm uppercase tracking-widest">
                    <span><?php echo get_the_date(); ?></span>
                    <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
                    <span><?php _e('By', 'airlinel-theme'); ?> <?php the_author(); ?></span>
                </div>
            </header>

            <?php if (has_post_thumbnail()) : ?>
                <div class="mb-12 rounded-[2rem] overflow-hidden shadow-2xl">
                    <?php the_post_thumbnail('full', ['class' => 'w-full h-auto object-cover']); ?>
                </div>
            <?php endif; ?>

            <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed mb-20">
                <?php the_content(); ?>
            </div>

            <hr class="border-gray-100 mb-20">
        </article>
    <?php endwhile; endif; ?>

    <section class="bg-gray-50 py-20 border-t border-gray-100">
        <div class="container mx-auto px-4">
            <h3 class="text-2xl font-bold text-gray-900 mb-10 text-center uppercase tracking-tighter"><?php _e('Continue Reading', 'airlinel-theme'); ?></h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php
                // Mevcut yazıyı hariç tutarak son 3 yazıyı getir
                $related_query = new WP_Query([
                    'post_type' => 'post',
                    'posts_per_page' => 3,
                    'post__not_in' => [get_the_ID()],
                    'orderby' => 'date',
                    'order' => 'DESC'
                ]);

                if ($related_query->have_posts()) : 
                    while ($related_query->have_posts()) : $related_query->the_post(); ?>
                        
                        <a href="<?php the_permalink(); ?>" class="group bg-white rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-500 border border-gray-100">
                            <div class="relative h-48 overflow-hidden">
                                <?php if (has_post_thumbnail()) : ?>
                                    <?php the_post_thumbnail('medium_large', ['class' => 'w-full h-full object-cover group-hover:scale-110 transition-transform duration-700']); ?>
                                <?php endif; ?>
                            </div>
                            <div class="p-6">
                                <span class="text-[10px] font-bold text-[var(--primary-color)] uppercase tracking-widest mb-2 block">
                                    <?php echo get_the_date(); ?>
                                </span>
                                <h4 class="text-lg font-bold text-gray-900 group-hover:text-[var(--primary-color)] transition-colors line-clamp-2">
                                    <?php the_title(); ?>
                                </h4>
                            </div>
                        </a>

                    <?php endwhile;
                    wp_reset_postdata();
                endif; ?>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>