<?php get_header(); ?>

<?php while ( have_posts() ) : the_post(); ?>
    <section class="relative pt-40 pb-20 bg-gradient-to-br from-[var(--dark-background-color)] to-black overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-[var(--primary-color)] to-transparent animate-gradient"></div>
        </div>
        
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <nav class="flex justify-center mb-6 text-sm font-medium text-gray-400 uppercase tracking-widest">
                <a href="<?php echo home_url(); ?>" class="hover:text-[var(--primary-color)] transition"><?php _e('Home', 'airlinel-theme'); ?></a>
                <span class="mx-3">/</span>
                <span class="text-white"><?php the_title(); ?></span>
            </nav>
            <h1 class="font-[var(--font-family-heading)] text-4xl md:text-6xl font-bold text-white mb-4">
                <?php the_title(); ?>
            </h1>
            <?php if (has_excerpt()) : ?>
                <p class="text-xl text-gray-400 max-w-2xl mx-auto leading-relaxed">
                    <?php echo get_the_excerpt(); ?>
                </p>
            <?php endif; ?>
        </div>
    </section>

    <section class="py-20 bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="prose prose-lg max-w-none 
                        prose-headings:font-[var(--font-family-heading)] 
                        prose-headings:text-[var(--dark-text-color)] 
                        prose-p:text-gray-600 prose-p:leading-relaxed
                        prose-a:text-[var(--primary-color)] prose-a:no-underline hover:prose-a:underline
                        prose-img:rounded-3xl prose-img:shadow-lg">
                
                <?php the_content(); ?>

            </div>
        </div>
    </section>

    <section class="py-20 bg-gray-50 border-t border-gray-100">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold font-[var(--font-family-heading)] mb-8"><?php _e('Need a Custom Solution?', 'airlinel-theme'); ?></h2>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="<?php echo home_url('/book-your-ride'); ?>" class="px-8 py-4 bg-[var(--primary-color)] text-white rounded-full font-bold hover:bg-[var(--primary-button-hover-bg-color)] transition shadow-lg">
                    <?php _e('Book Now', 'airlinel-theme'); ?>
                </a>
                <a href="<?php echo home_url('/contact'); ?>" class="px-8 py-4 bg-white border-2 border-gray-200 text-[var(--dark-text-color)] rounded-full font-bold hover:border-[var(--primary-color)] transition">
                    <?php _e('Contact Us', 'airlinel-theme'); ?>
                </a>
            </div>
        </div>
    </section>
<?php endwhile; ?>

<?php get_footer(); ?>