<?php get_header(); ?>

<main class="pt-32 pb-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if ( have_posts() ) : ?>
            <header class="mb-12">
                <h1 class="text-4xl font-bold font-[var(--font-family-heading)] text-[var(--dark-text-color)]">
                    <?php 
                    if ( is_home() && ! is_front_page() ) :
                        single_post_title();
                    else :
                        _e( 'Blog', 'airlinel-theme' );
                    endif;
                    ?>
                </h1>
            </header>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php while ( have_posts() ) : the_post(); ?>
                    <article class="bg-white rounded-2xl overflow-hidden border border-gray-100 shadow-sm hover:shadow-md transition">
                        <div class="aspect-video">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <?php the_post_thumbnail( 'medium_large', array( 'class' => 'w-full h-full object-cover' ) ); ?>
                            <?php else : ?>
                                <img src="<?php echo get_template_directory_uri(); ?>/images/theme-image-014.png" alt="Placeholder image for London airport transfer" title="London airport transfer placeholder" class="w-full h-full object-cover">
                            <?php endif; ?>
                        </div>
                        <div class="p-6">
                            <h2 class="text-xl font-bold mb-3 hover:text-[var(--primary-color)] transition">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>
                            <div class="text-sm text-gray-500 mb-4 line-clamp-3">
                                <?php the_excerpt(); ?>
                            </div>
                            <a href="<?php the_permalink(); ?>" class="text-[var(--primary-color)] font-bold text-sm"><?php _e('Read More', 'airlinel-theme'); ?> &rarr;</a>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>

            <div class="mt-12">
                <?php the_posts_pagination( array(
                    'prev_text' => '<i class="fa-solid fa-arrow-left" style="font-size:11px;margin-right:6px;"></i> Previous',
                    'next_text' => 'Next <i class="fa-solid fa-arrow-right" style="font-size:11px;margin-left:6px;"></i>',
                    'mid_size'  => 2,
                ) ); ?>
            </div>

        <?php else : ?>
            <p><?php _e( 'No posts found.', 'airlinel-theme' ); ?></p>
        <?php endif; ?>
    </div>
</main>

<?php get_footer(); ?>