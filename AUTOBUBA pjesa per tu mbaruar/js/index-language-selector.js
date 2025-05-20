document.addEventListener('DOMContentLoaded', function() {
    const translations = {
        'en': {
            // Header
            "Cars": "Cars",
            "Blog": "Blog",
            "Car Details": "Car Details",
            "About": "About",
            "Work": "Work",
            "Cart": "Cart",
            "Report": "Report",
            "Log In": "Log In",
            
            // Hero Section
            "IT ALL STARTS WITH A DREAM": "IT ALL STARTS WITH A DREAM",
            "All of those cars were once just a dream in somebody's head. - Peter Gabriel": "All of those cars were once just a dream in somebody's head. - Peter Gabriel",
            "Test Drive": "Test Drive",
            "Learn More": "Learn More",
            "Car Rental": "Car Rental",
            "Buy Car": "Buy Car",
            "Rent Your Dream Car": "Rent Your Dream Car",
            "Select Year": "Select Year",
            "Select Brand": "Select Brand",
            "Select Model": "Select Model",
            "Select Mileage": "Select Mileage",
            "Price Range:": "Price Range:",
            "Search": "Search",
            "Buy Your Dream Car": "Buy Your Dream Car",
            "Search Results": "Search Results",
            "No results found. Try different search criteria.": "No results found. Try different search criteria.",
            "Close": "Close",
            
            // Services Section
            "Our Services": "Our Services",
            "What We Offer": "What We Offer",
            "Well-maintained, clean, and sanitized cars, excellent customer service and fast & hassle-free rental process": "Well-maintained, clean, and sanitized cars, excellent customer service and fast & hassle-free rental process",
            "Renting Cars": "Renting Cars",
            "Need a reliable ride? We offer a seamless car rental experience with a wide range of vehicles to fit your needs.": "Need a reliable ride? We offer a seamless car rental experience with a wide range of vehicles to fit your needs.",
            "Buying Cars": "Buying Cars",
            "Looking for your next car? We offer a hassle-free buying experience with a selection of high-quality vehicles.": "Looking for your next car? We offer a hassle-free buying experience with a selection of high-quality vehicles.",
            "Quality Assurance": "Quality Assurance",
            "Well-maintained, clean, and reliable vehicles, ensuring a safe and comfortable drive every time.": "Well-maintained, clean, and reliable vehicles, ensuring a safe and comfortable drive every time.",
            
            // Feature Section
            "Our Feature": "Our Feature",
            "We Are a Trusted Name In Auto": "We Are a Trusted Name In Auto",
            "As a trusted name in the automotive industry, we don't just meet expectations—we aim to exceed them. Our commitment to innovation, professionalism, and building lasting relationships has solidified our place as a leader in the field.": "As a trusted name in the automotive industry, we don't just meet expectations—we aim to exceed them. Our commitment to innovation, professionalism, and building lasting relationships has solidified our place as a leader in the field.",
            "Our Reviews": "Our Reviews",
            "Engine": "Engine",
            "Turbo": "Turbo",
            "Cooling": "Cooling",
            "Suspension": "Suspension",
            "Electrical": "Electrical",
            "Brakes": "Brakes",
            
            // Car Listing Section
            "Our Car": "Our Car",
            "Best Vehicle Offers": "Best Vehicle Offers",
            "Most Researched": "Most Researched",
            "Latest on sale": "Latest on sale",
            "Discount Vehicles": "Discount Vehicles",
            "For Rent": "For Rent",
            "For Sale": "For Sale",
            "View More": "View More",
            "mi": "mi",
            "hp": "hp",
            "Auto": "Auto",
            "/mo": "/mo",
            
            // Choose Us Section
            "Why People Choose Us": "Why People Choose Us",
            "People choose us because we deliver unmatched quality, personalized service, and reliability that you can always count on.": "People choose us because we deliver unmatched quality, personalized service, and reliability that you can always count on.",
            "Unmatched quality and attention to detail.": "Unmatched quality and attention to detail.",
            "Exceptional customer service tailored to your needs.": "Exceptional customer service tailored to your needs.",
            "Affordable solutions without compromising reliability.": "Affordable solutions without compromising reliability.",
            "A proven track record of trust and excellence.": "A proven track record of trust and excellence.",
            "About Us": "About Us",
            
            // Footer
            "Navigation": "Navigation",
            "Contact Us": "Contact Us",
            "Learn": "Learn",
            "About Us": "About Us",
            "Why Us?": "Why Us?",
            "Who are we?": "Who are we?",
            "Hatchback": "Hatchback",
            "Sedan": "Sedan",
            "SUV": "SUV",
            "© Copyright 2025 AUTOBUBA. All Rights Reserved.": "© Copyright 2025 AUTOBUBA. All Rights Reserved.",
            "Facebook": "Facebook",
            "Twitter": "Twitter",
            "Instagram": "Instagram",
            
            // Phone Modal
            "Contact Information": "Contact Information",
            "Please call us at:": "Please call us at:",
            "Copy": "Copy",
            "Copied to clipboard!": "Copied to clipboard!",
            
            // Shopping Cart
            "Your cart is empty": "Your cart is empty",
            "Add some cars to get started!": "Add some cars to get started!",
            
            // Loading Screen
            "km/h": "km/h",
            "Home - AUTOBUBA": "Home - AUTOBUBA"
        },
        'sq': {
            // Header
            "Cars": "Makinat",
            "Blog": "Blog",
            "Car Details": "Detajet e Makinës",
            "About": "Rreth Nesh",
            "Work": "Puna",
            "Cart": "Shporta",
            "Report": "Raporto",
            "Log In": "Hyr",
            
            // Hero Section
            "IT ALL STARTS WITH A DREAM": "E GJITHA FILLON ME NJË ËNDËRR",
            "All of those cars were once just a dream in somebody's head. - Peter Gabriel": "Të gjitha ato makina ishin dikur thjesht një ëndërr në kokën e dikujt. - Peter Gabriel",
            "Test Drive": "Test Drive",
            "Learn More": "Mësoni Më Shumë",
            "Car Rental": "Qiraja e Makinave",
            "Buy Car": "Blej Makinë",
            "Rent Your Dream Car": "Merr Me Qira Makinën Tuaj të Ëndrrave",
            "Select Year": "Zgjidhni Vitin",
            "Select Brand": "Zgjidhni Markën",
            "Select Model": "Zgjidhni Modelin",
            "Select Mileage": "Zgjidhni Kilometrazhin",
            "Price Range:": "Çmimet :",
            "Search": "Kërko",
            "Buy Your Dream Car": "Blej Makinën Tuaj të Ëndrrave",
            "Search Results": "Rezultatet e Kërkimit",
            "No results found. Try different search criteria.": "Nuk u gjetën rezultate. Provoni kritere të ndryshme kërkimi.",
            "Close": "Mbyll",
            
            // Services Section
            "Our Services": "Shërbimet Tona",
            "What We Offer": "Çfarë Ofrojmë",
            "Well-maintained, clean, and sanitized cars, excellent customer service and fast & hassle-free rental process": "Makina të mirëmbajtura, të pastra dhe të dezinfektuara, shërbim të shkëlqyer të klientit dhe proces të shpejtë dhe të lehtë të qirasë",
            "Renting Cars": "Qira e Makinave",
            "Need a reliable ride? We offer a seamless car rental experience with a wide range of vehicles to fit your needs.": "Keni nevojë për një udhëtim të besueshëm? Ne ofrojmë një përvojë të qetë të qirasë së makinave me një gamë të gjerë automjetesh që përshtaten me nevojat tuaja.",
            "Buying Cars": "Blerja e Makinave",
            "Looking for your next car? We offer a hassle-free buying experience with a selection of high-quality vehicles.": "Po kërkoni makinën tuaj të radhës? Ne ofrojmë një përvojë blerjeje të lehtë me një përzgjedhje automjetesh me cilësi të lartë.",
            "Quality Assurance": "Siguria e Cilësisë",
            "Well-maintained, clean, and reliable vehicles, ensuring a safe and comfortable drive every time.": "Automjete të mirëmbajtura, të pastra dhe të besueshme, duke siguruar një vozitje të sigurt dhe të rehatshme çdo herë.",
            
            // Feature Section
            "Our Feature": "Karakteristika Jonë",
            "We Are a Trusted Name In Auto": "Ne Jemi Një Emër i Besuar Në Automjete",
            "As a trusted name in the automotive industry, we don't just meet expectations—we aim to exceed them. Our commitment to innovation, professionalism, and building lasting relationships has solidified our place as a leader in the field.": "Si një emër i besuar në industrinë automobilistike, ne jo vetëm që plotësojmë pritshmëritë - synojmë t'i tejkalojmë ato. Angazhimi ynë për inovacion, profesionalizëm dhe ndërtimin e marrëdhënieve të qëndrueshme ka forcuar vendin tonë si lider në këtë fushë.",
            "Our Reviews": "Vlerësimet Tona",
            "Engine": "Motor",
            "Turbo": "Turbo",
            "Cooling": "Ftohje",
            "Suspension": "Peisje",
            "Electrical": "Elektrike",
            "Brakes": "Frena",
            
            // Car Listing Section
            "Our Car": "Makinat Tona",
            "Best Vehicle Offers": "Ofertat Më të Mira të Automjeteve",
            "Most Researched": "Më të Kërkuarat",
            "Latest on sale": "Më të Fundit në shitje",
            "Discount Vehicles": "Automjete me Zbritje",
            "For Rent": "Për Qira",
            "For Sale": "Për Shitje",
            "View More": "Shiko Më Shumë",
            "mi": "km",
            "hp": "hp",
            "Auto": "Automatik",
            "/mo": "/muaj",
            
            // Choose Us Section
            "Why People Choose Us": "Pse Njerëzit Na Zgjedhin Ne",
            "People choose us because we deliver unmatched quality, personalized service, and reliability that you can always count on.": "Njerëzit na zgjedhin sepse ne ofrojmë cilësi të paarsyeshme, shërbim të personalizuar dhe besueshmëri që gjithmonë mund të mbështeteni.",
            "Unmatched quality and attention to detail.": "Cilësi e paarsyeshme dhe vëmendje ndaj detajeve.",
            "Exceptional customer service tailored to your needs.": "Shërbim të jashtëzakonshëm të klientit të përshtatur sipas nevojave tuaja.",
            "Affordable solutions without compromising reliability.": "Zgjidhje të përballueshme pa kompromis për besueshmërinë.",
            "A proven track record of trust and excellence.": "Një rekord i provuar i besimit dhe i ekselencës.",
            "About Us": "Rreth Nesh",
            
            // Footer
            "Navigation": "Navigimi",
            "Contact Us": "Na Kontaktoni",
            "Learn": "Mësoni",
            "About Us": "Rreth Nesh",
            "Why Us?": "Pse Ne?",
            "Who are we?": "Kush jemi ne?",
            "Hatchback": "Hatchback",
            "Sedan": "Sedan",
            "SUV": "SUV",
            "© Copyright 2025 AUTOBUBA. All Rights Reserved.": "© E drejta e autorit 2025 AUTOBUBA. Të gjitha të drejtat e rezervuara.",
            "Facebook": "Facebook",
            "Twitter": "Twitter",
            "Instagram": "Instagram",
            
            // Phone Modal
            "Contact Information": "Informacioni i Kontaktit",
            "Please call us at:": "Ju lutemi na telefononi në:",
            "Copy": "Kopjo",
            "Copied to clipboard!": "U kopjua në clipboard!",
            
            // Shopping Cart
            "Your cart is empty": "Shporta juaj është bosh",
            "Add some cars to get started!": "Shtoni disa makina për të filluar!",
            
            // Loading Screen
            "km/h": "km/h",
            "Home - AUTOBUBA": "Kryefaqja - AUTOBUBA"
        }
    };

    $(document).ready(function() {
    // Initialize owl carousel
    $(".car__item__pic__slider").owlCarousel({
        loop: true,
        margin: 0,
        items: 1,
        dots: true,
        nav: true,
        navText: ['<i class="fa fa-angle-left"></i>', '<i class="fa fa-angle-right"></i>'],
        smartSpeed: 1200,
        autoHeight: false,
        autoplay: false
    });
    
    // Set initial language
    let currentLanguage = localStorage.getItem('language') || 'en';
    applyLanguage(currentLanguage);
    updateLanguageSelector(currentLanguage);
    
    // Language selector functionality
    $('.language-option').click(function(e) {
        e.preventDefault();
        const lang = $(this).data('lang');
        currentLanguage = lang;
        localStorage.setItem('language', lang);
        applyLanguage(lang);
        updateLanguageSelector(lang);
    });
    
    // Apply language translations
    function applyLanguage(lang) {
        $('[data-translate]').each(function() {
            const key = $(this).data('translate');
            if (translations[lang] && translations[lang][key]) {
                $(this).text(translations[lang][key]);
            }
        });
        
        $('[data-translate-placeholder]').each(function() {
            const key = $(this).data('translate-placeholder');
            if (translations[lang] && translations[lang][key]) {
                $(this).attr('placeholder', translations[lang][key]);
            }
        });
    }
    
    // Update language selector display and flag
    function updateLanguageSelector(lang) {
        // Update active state
        $('.language-option').removeClass('active');
        $(`.language-option[data-lang="${lang}"]`).addClass('active');
        
        // Update flag icon in dropdown button
        const flagClass = lang === 'sq' ? 'flag-icon-al' : 'flag-icon-gb';
        $('#languageDropdown').html(`<span class="flag-icon ${flagClass}"></span>`);
    }
});
    
    // Special handling for the loading text animation
    function handleLoadingText() {
        const loadingScreen = document.getElementById('loading-screen');
        if (loadingScreen && window.getComputedStyle(loadingScreen).display !== 'none') {
            const loadingText = document.querySelector('.loading-text');
            if (loadingText) {
                // Clear existing content
                loadingText.innerHTML = '';
                
                // Create new content based on language
                const text = currentLang === 'en' ? 'LOADING...' : 'DUKE NGARKUAR...';
                
                // Add each letter as a span for animation
                for (let i = 0; i < text.length; i++) {
                    const span = document.createElement('span');
                    span.textContent = text[i];
                    loadingText.appendChild(span);
                }
            }
        }
    }
});