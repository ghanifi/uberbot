<?php /* Template Name: Search Only Page */ get_header(); ?>

<main class="min-h-screen relative flex items-center justify-center py-20 overflow-hidden">
    <div class="absolute inset-0 bg-[#0a0a0a] z-0">
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-[var(--primary-color)] opacity-[0.05] blur-[120px]"></div>
        <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-[var(--primary-color)] opacity-[0.05] blur-[120px]"></div>
    </div>

    <div class="container mx-auto px-4 relative z-10">
        <div class="max-w-3xl mx-auto">
            
            <div class="text-center mb-10">
                <span class="inline-block px-4 py-1.5 mb-4 text-xs font-bold tracking-[0.2em] text-[var(--primary-color)] uppercase bg-[var(--primary-color)]/10 rounded-full">
                    <?php _e('Premium Transfer Service', 'airlinel-theme'); ?>
                </span>
                <h1 class="font-[var(--font-family-heading)] text-4xl md:text-6xl font-black text-white mb-6 tracking-tight">
                    <?php _e('Where to', 'airlinel-theme'); ?> <span class="text-[var(--primary-color)]"><?php _e('Next?', 'airlinel-theme'); ?></span>
                </h1>
                <p class="text-gray-400 text-lg max-w-xl mx-auto">
                    <?php _e('Experience luxury and punctuality. Book your private chauffeur in just a few clicks.', 'airlinel-theme'); ?>
                </p>
            </div>

            <div class="bg-white/[0.03] backdrop-blur-3xl rounded-[2.5rem] p-8 md:p-12 border border-white/10 shadow-2xl">
                <form class="space-y-8">
                    
                    <div class="grid grid-cols-1 gap-6">
                        <div class="relative group">
                            <label class="absolute -top-3 left-6 px-2 bg-[#121212] text-[var(--primary-color)] text-[10px] font-bold uppercase tracking-widest z-10"><?php _e('Pickup Location', 'airlinel-theme'); ?></label>
                            <div class="relative">
                                <i class="fa-solid fa-location-dot absolute left-5 top-1/2 -translate-y-1/2 text-gray-500 group-focus-within:text-[var(--primary-color)] transition-colors"></i>
                                <input type="text" id="pickup-location" placeholder="<?php esc_attr_e('Airport, hotel, or address', 'airlinel-theme'); ?>"
                                    class="w-full pl-12 pr-6 py-5 bg-white/5 border border-white/10 rounded-2xl text-white placeholder:text-gray-600 outline-none focus:border-[var(--primary-color)] focus:ring-4 focus:ring-[var(--primary-color)]/10 transition-all">
                            </div>
                        </div>

                        <div class="relative group">
                            <label class="absolute -top-3 left-6 px-2 bg-[#121212] text-gray-400 text-[10px] font-bold uppercase tracking-widest z-10"><?php _e('Destination', 'airlinel-theme'); ?></label>
                            <div class="relative">
                                <i class="fa-solid fa-route absolute left-5 top-1/2 -translate-y-1/2 text-gray-500 group-focus-within:text-[var(--primary-color)] transition-colors"></i>
                                <input type="text" id="dropoff-location" placeholder="<?php esc_attr_e('Where are you going?', 'airlinel-theme'); ?>"
                                    class="w-full pl-12 pr-6 py-5 bg-white/5 border border-white/10 rounded-2xl text-white placeholder:text-gray-600 outline-none focus:border-[var(--primary-color)] focus:ring-4 focus:ring-[var(--primary-color)]/10 transition-all">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="relative group">
                            <label class="block text-[10px] font-bold text-gray-400 mb-2 uppercase tracking-widest ml-2"><?php _e('Pickup Date', 'airlinel-theme'); ?></label>
                            <input type="date" id="pickup_date" name="pickup_date" required
                                class="w-full px-6 py-4 bg-white/5 border border-white/10 rounded-2xl text-white outline-none focus:border-[var(--primary-color)] transition-all">
                        </div>
                        <div class="relative group">
                            <label class="block text-[10px] font-bold text-gray-400 mb-2 uppercase tracking-widest ml-2"><?php _e('Pickup Time', 'airlinel-theme'); ?></label>
                            <input type="time" id="pickup_time" name="pickup_time" required
                                class="w-full px-6 py-4 bg-white/5 border border-white/10 rounded-2xl text-white outline-none focus:border-[var(--primary-color)] transition-all">
                        </div>
                    </div>

                    <div id="trip-summary" class="hidden border-y border-white/5 py-6 my-6">
                        <div class="flex justify-around items-center">
                            <div class="text-center">
                                <span class="block text-[10px] font-bold text-gray-500 uppercase mb-1"><?php _e('Total Distance', 'airlinel-theme'); ?></span>
                                <div class="flex items-baseline justify-center gap-1">
                                    <span id="display-distance" class="text-3xl font-black text-white">0</span>
                                    <span class="text-sm font-bold text-[var(--primary-color)]">KM</span>
                                </div>
                            </div>
                            <div class="h-8 w-px bg-white/10"></div>
                            <div class="text-center">
                                <span class="block text-[10px] font-bold text-gray-500 uppercase mb-1"><?php _e('Est. Duration', 'airlinel-theme'); ?></span>
                                <div class="flex items-baseline justify-center">
                                    <span id="display-duration" class="text-3xl font-black text-white">0</span>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" id="distance-value">
                        <input type="hidden" id="duration-value">
                    </div>

                    <button id="search-button" class="group relative w-full bg-[var(--primary-color)] text-white font-black py-6 rounded-2xl overflow-hidden transition-all hover:scale-[1.02] active:scale-[0.98] shadow-[0_20px_40px_rgba(204,68,82,0.3)]">
                        <div class="relative z-10 flex items-center justify-center gap-3 tracking-widest uppercase text-sm">
                            <span><?php _e('Check Availability & Price', 'airlinel-theme'); ?></span>
                            <i class="fa-solid fa-arrow-right group-hover:translate-x-2 transition-transform"></i>
                        </div>
                        <div class="absolute inset-0 bg-black/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    </button>

                </form>
            </div>

            <div class="mt-12 flex flex-wrap justify-center items-center gap-8 opacity-40 grayscale hover:opacity-100 transition-all duration-500">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-shield-halved text-2xl text-white"></i>
                    <span class="text-xs text-white font-bold uppercase tracking-tighter"><?php _e('Secure Payments', 'airlinel-theme'); ?></span>
                </div>
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-clock text-2xl text-white"></i>
                    <span class="text-xs text-white font-bold uppercase tracking-tighter"><?php _e('24/7 Support', 'airlinel-theme'); ?></span>
                </div>
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-star text-2xl text-white"></i>
                    <span class="text-xs text-white font-bold uppercase tracking-tighter"><?php _e('Fixed Rates', 'airlinel-theme'); ?></span>
                </div>
            </div>

        </div>
    </div>
</main>

<style>
    /* Date ve Time inputlarının takvim ikonlarını beyaza çevirir */
    input[type="date"]::-webkit-calendar-picker-indicator,
    input[type="time"]::-webkit-calendar-picker-indicator {
        filter: invert(1);
        cursor: pointer;
    }
    
    /* Sayfa geçiş animasyonu */
    .booking-flow {
        animation: fadeIn 0.8s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<?php get_footer(); ?>