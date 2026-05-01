/**
 * Airlinel Header Language & Currency Selector
 * NEW SYSTEM: Language = Domain redirect, Currency = Session
 */

(function() {
    'use strict';

    const HeaderSelectors = {
        // Supported languages with flags
        languages: {
            'en_US': { name: 'English', flag: '🇬🇧', code: 'EN' },
            'tr_TR': { name: 'Türkçe', flag: '🇹🇷', code: 'TR' },
            'de_DE': { name: 'Deutsch', flag: '🇩🇪', code: 'DE' },
            'es_ES': { name: 'Español', flag: '🇪🇸', code: 'ES' },
            'fr_FR': { name: 'Français', flag: '🇫🇷', code: 'FR' },
            'it_IT': { name: 'Italiano', flag: '🇮🇹', code: 'IT' },
            'ru_RU': { name: 'Русский', flag: '🇷🇺', code: 'RU' },
            'ar': { name: 'العربية', flag: '🌍', code: 'AR' },
            'da_DK': { name: 'Dansk', flag: '🇩🇰', code: 'DA' },
            'nl_NL': { name: 'Nederlands', flag: '🇳🇱', code: 'NL' },
            'sv_SE': { name: 'Svenska', flag: '🇸🇪', code: 'SV' },
            'zh_CN': { name: '中文', flag: '🇨🇳', code: 'ZH' },
            'ja': { name: '日本語', flag: '🇯🇵', code: 'JA' }
        },

        /**
         * Initialize language selector - NEW: Domain lookup version
         */
        initLanguageSelector: function() {
            const btn = document.getElementById('language-selector-btn');
            const dropdown = document.getElementById('language-dropdown');

            if (!btn || !dropdown) {
                console.warn('Language selector elements not found');
                return;
            }

            console.log('HeaderSelectors: Initializing language selector');

            // Populate dropdown
            this.populateLanguageDropdown();

            // Toggle dropdown on button click
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdown.classList.toggle('hidden');
                console.log('Language button clicked');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!btn.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });

            // Handle language option clicks - NEW: AJAX domain lookup
            dropdown.addEventListener('click', (e) => {
                const option = e.target.closest('[data-language]');
                if (option) {
                    const locale = option.dataset.language;
                    console.log('Language option clicked:', locale);
                    this.switchLanguage(locale);
                    dropdown.classList.add('hidden');
                }
            });
        },

        /**
         * Initialize currency selector - NEW: Session-based
         */
        initCurrencySelector: function() {
            const selector = document.getElementById('header-currency-selector');

            if (!selector) {
                console.warn('Currency selector element not found');
                return;
            }

            console.log('HeaderSelectors: Initializing currency selector');
            console.log('Current session currency:', window.airinelCurrency);

            // Set value from session
            selector.value = window.airinelCurrency || 'GBP';

            // Handle currency change - NEW: Add query string
            selector.addEventListener('change', (e) => {
                const currency = e.target.value;
                console.log('Currency changed to:', currency);
                this.switchCurrency(currency);
            });
        },

        /**
         * Populate language dropdown with all languages
         */
        populateLanguageDropdown: function() {
            const dropdown = document.getElementById('language-dropdown');
            if (!dropdown) {
                console.warn('Language dropdown not found');
                return;
            }

            dropdown.innerHTML = '';
            console.log('Populating language dropdown');

            Object.entries(this.languages).forEach(([locale, langData]) => {
                const option = document.createElement('div');
                option.className = 'language-option';
                option.dataset.language = locale;
                option.innerHTML = `
                    <span class="text-lg">${langData.flag}</span>
                    <span>${langData.name}</span>
                `;

                dropdown.appendChild(option);
            });

            console.log('Language dropdown populated');
        },

        /**
         * Switch language - NEW: Domain lookup via AJAX
         * Looks up domain for language, then redirects
         */
        switchLanguage: function(locale) {
            if (!this.languages[locale]) {
                console.error('Unsupported language:', locale);
                return;
            }

            console.log('Switching language to:', locale);

            // AJAX request to get domain for this language
            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'airlinel_get_language_domain',
                    language_code: locale,
                    nonce: window.airinelNonce
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Domain lookup response:', data);

                if (data.success && data.data.domain_url) {
                    const domain = data.data.domain_url;
                    const redirectUrl = 'https://' + domain + '/';
                    console.log('Redirecting to:', redirectUrl);
                    window.location.href = redirectUrl;
                } else {
                    console.warn('No domain found for language', locale);
                }
            })
            .catch(error => {
                console.error('Language domain lookup error:', error);
            });
        },

        /**
         * Switch currency - NEW: Query string + session
         */
        switchCurrency: function(currency) {
            const validCurrencies = ['GBP', 'EUR', 'TRY', 'USD'];

            if (!validCurrencies.includes(currency)) {
                console.error('Unsupported currency:', currency);
                return;
            }

            console.log('Switching currency to:', currency);

            // Add currency to URL as query string
            const url = new URL(window.location);
            url.searchParams.set('currency', currency);

            console.log('Redirecting with currency param:', url.toString());
            window.location.href = url.toString();
        },

        /**
         * Initialize all selectors
         */
        init: function() {
            console.log('HeaderSelectors.init() called');

            // Wait for DOM to be ready
            if (document.readyState === 'loading') {
                console.log('DOM still loading, waiting for DOMContentLoaded');
                document.addEventListener('DOMContentLoaded', () => {
                    console.log('DOMContentLoaded triggered');
                    this.initLanguageSelector();
                    this.initCurrencySelector();
                });
            } else {
                console.log('DOM already loaded');
                this.initLanguageSelector();
                this.initCurrencySelector();
            }
        }
    };

    // Initialize on load
    console.log('header-selectors.js script loaded');
    HeaderSelectors.init();

    // Expose to global scope
    window.airinelHeaderSelectors = HeaderSelectors;
    console.log('HeaderSelectors exposed to window.airinelHeaderSelectors');
})();
