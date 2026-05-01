<!DOCTYPE html>
<html class="h-full" <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link
      rel="preconnect"
      href="https://fonts.gstatic.com"
      crossorigin="anonymous"
    />
    <link
      id="google-fonts-link"
      href="https://fonts.googleapis.com/css2?family=Barlow:wght@300;400;500;600;700&family=Noto+Sans:wght@300;400;500;600;700&display=swap"
      rel="stylesheet"
    />
    <script
      src="https://kit.fontawesome.com/8e98006f77.js"
      crossorigin="anonymous"
      defer
    ></script>

    <style>
      :root { 
        --accent-color: #DD808E; 
        --accent2-color: #D56A76; 
        --accent3-color: #E28692; 
        --accent4-color: #EF9DA5; 
        --primary-color: #CC4452; 
        --dark-text-color: #232323; 
        --gray-text-color: #A0A0A0; 
        --button-padding-x: 16px; 
        --button-padding-y: 12px; 
        --font-family-body: 'Noto Sans', sans-serif; 
        --light-text-color: #F9F9F9; 
        --dark-border-color: #4A4A4A; 
        --light-border-color: #DFDFDF; 
        --font-family-heading: 'Barlow', sans-serif; 
        --button-rounded-radius: 24px; 
        --dark-background-color: #343434; 
        --light-background-color: #FEEFEF; 
        --medium-background-color: #FAC9C9; 
        --primary-button-text-color: #FFFFFF; 
        --secondary-button-bg-color: #464646; 
        --secondary-button-text-color: #FFFFFF; 
        --primary-button-hover-bg-color: #D55660; 
        --primary-button-hover-text-color: #FFFFFF; 
        --secondary-button-hover-bg-color: #575757; 
        --secondary-button-hover-text-color: #FFFFFF; 
      }

      .hovered-element {
        outline: #3871E0 dashed 2px;
      }

      .clicked-element {
        outline: #3871E0 solid 2px;
      }

      .clicked-code-section {
        border: #3B82F6 solid 2px;
      }

      #language-dropdown .language-option {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        cursor: pointer;
        transition: background-color 0.2s ease;
        color: var(--dark-text-color);
        font-size: 14px;
        border-bottom: 1px solid #f0f0f0;
      }

      #language-dropdown .language-option:last-child {
        border-bottom: none;
      }

      #language-dropdown .language-option:hover {
        background-color: #f9f9f9;
      }

      #language-dropdown .language-option.active {
        background-color: #fff0f1;
        color: var(--primary-color);
        font-weight: 500;
      }

      #language-dropdown .language-option span:first-child {
        font-size: 20px;
        flex-shrink: 0;
      }

      #header-currency-selector {
        font-family: var(--font-family-body);
      }
    </style>
    
    <!-- Preconnect for critical third parties (max 4) -->
    <link rel="preconnect" href="https://js.stripe.com">
    <link rel="preconnect" href="https://m.stripe.network">
    <?php wp_head(); ?>

</head>
<body <?php body_class('h-full bg-white'); ?>>
    <div class="frame-root">
      <div class="frame-content">
        <div class="[font-family:var(--font-family-body)]">
          <header id="global-header" class="code-section fixed top-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-md border-b border-[var(--light-border-color)]">
            <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
              <div class="flex items-center justify-between h-20">
                <div class="flex-shrink-0">
                  <a href="<?php echo home_url(); ?>" class="block">
                    <img src="<?php echo get_template_directory_uri(); ?>/images/logo.webp" alt="<?php bloginfo('name'); ?> logo – London airport transfer service" title="<?php bloginfo('name'); ?> airport transfer logo" class="w-[170px] h-auto py-1" width="170" height="256" data-logo="">
                  </a>
                </div>

                <div class="hidden lg:flex items-center gap-8">
                  <?php
                  wp_nav_menu(array(
                      'theme_location' => 'primary',
                      'container' => false,
                      'menu_class' => 'flex items-center space-x-8 list-none desktop-menu',
                      'fallback_cb' => 'wp_page_menu',
                      'depth' => 1,
                      'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                  ));
                  ?>

                  <!-- Language Selector -->
                  <div class="relative group">
                    <button id="language-selector-btn" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-100 transition text-sm font-medium text-[var(--dark-text-color)]">
                      <span id="current-language-flag" class="text-lg">🇬🇧</span>
                      <span id="current-language-code" class="text-xs font-semibold">EN</span>
                      <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                      </svg>
                    </button>

                    <!-- Language Dropdown -->
                    <div id="language-dropdown" class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-[var(--light-border-color)] hidden group-hover:block z-40 max-h-96 overflow-y-auto">
                      <!-- Options populated by JavaScript -->
                    </div>
                  </div>

                  <!-- Currency Selector -->
                  <select id="header-currency-selector" class="bg-white border border-[var(--light-border-color)] rounded-lg px-3 py-2 text-sm font-medium text-[var(--dark-text-color)] focus:border-[var(--primary-color)] outline-none transition-all cursor-pointer hover:border-[var(--primary-color)]">
                    <option value="GBP">£ GBP</option>
                    <option value="EUR">€ EUR</option>
                    <option value="TRY">₺ TRY</option>
                    <option value="USD">$ USD</option>
                  </select>

                  <!-- Book Now Button -->
                  <a href="<?php echo home_url('/book-your-ride'); ?>" class="inline-flex items-center px-6 py-3 rounded-full bg-[var(--primary-color)] text-[var(--primary-button-text-color)] font-medium text-sm hover:bg-[var(--primary-button-hover-bg-color)] transition-all duration-300 hover:shadow-lg hover:shadow-[var(--primary-color)]/30 whitespace-nowrap">
                    <?php _e('Book Now', 'airlinel-theme'); ?>
                  </a>
                </div>

                <button data-landingsite-mobile-menu-toggle="" aria-label="Open navigation menu" class="lg:hidden p-2 rounded-lg text-[var(--dark-text-color)] hover:bg-gray-100 transition-colors">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                  </svg>
                </button>
              </div>

              <div data-landingsite-mobile-menu="" class="hidden lg:hidden pb-4 pt-2 bg-white/95 backdrop-blur-md border-t border-[var(--light-border-color)]">
                <div>
                  <?php
                  wp_nav_menu(array(
                      'theme_location' => 'primary',
                      'container' => false,
                      'menu_class' => 'flex flex-col space-y-3 list-none mobile-menu',
                      'fallback_cb' => 'wp_page_menu',
                      'depth' => 1,
                      'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                  ));
                  ?>
                </div>
                <!-- Mobile: Currency selector + Book Now -->
                <div class="mt-3 flex items-center gap-3">
                  <select id="header-currency-selector-mobile" class="flex-1 bg-white border border-[var(--light-border-color)] rounded-lg px-3 py-3 text-sm font-medium text-[var(--dark-text-color)] focus:border-[var(--primary-color)] outline-none transition-all cursor-pointer">
                    <option value="GBP">£ GBP</option>
                    <option value="EUR">€ EUR</option>
                    <option value="TRY">₺ TRY</option>
                    <option value="USD">$ USD</option>
                  </select>
                  <a href="<?php echo home_url('/book-your-ride'); ?>" class="flex-1 inline-flex items-center justify-center px-6 py-3 rounded-full bg-[var(--primary-color)] text-[var(--primary-button-text-color)] font-medium text-base hover:bg-[var(--primary-button-hover-bg-color)] transition-colors">
                      <?php _e('Book Now', 'airlinel-theme'); ?>
                  </a>
                </div>
                <script>
                // Sync mobile currency selector with header selector value + handle change
                document.addEventListener('DOMContentLoaded', function() {
                    var mobileSel = document.getElementById('header-currency-selector-mobile');
                    var desktopSel = document.getElementById('header-currency-selector');
                    if (mobileSel && desktopSel) {
                        mobileSel.value = desktopSel.value;
                    }
                    if (mobileSel && window.airinelHeaderSelectors) {
                        mobileSel.addEventListener('change', function() {
                            window.airinelHeaderSelectors.switchCurrency(this.value);
                        });
                    } else if (mobileSel) {
                        mobileSel.addEventListener('change', function() {
                            var url = new URL(window.location);
                            url.searchParams.set('currency', this.value);
                            window.location.href = url.toString();
                        });
                    }
                });
                </script>
              </div>
            </nav>
          </header>