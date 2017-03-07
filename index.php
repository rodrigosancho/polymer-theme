<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <title><?php wp_title(''); ?></title>
    <meta name="description" content="<?php bloginfo('description'); ?>">
    <meta name="viewport" content="width=device-width, minimum-scale=1, initial-scale=1, user-scalable=yes">

    <!--    <link rel="canonical" href="">-->

    <!-- Google Tag Manager -->
    <script>(function (w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start': new Date().getTime(), event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src =
                'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-TJSXJ3C');</script>
    <!-- End Google Tag Manager -->

    <link href="<?php echo get_template_directory_uri(); ?>/img/icons/favicon.ico" rel="shortcut icon">
    <link href="<?php echo get_template_directory_uri(); ?>/img/icons/touch.png" rel="apple-touch-icon-precomposed">


    <link rel="icon" href="<?php echo get_template_directory_uri(); ?>/images/favicon.ico">

    <!-- See https://goo.gl/OOhYW5 -->
    <link rel="manifest" href="<?php echo get_template_directory_uri(); ?>/manifest.json">

    <!-- See https://goo.gl/qRE0vM -->
    <meta name="theme-color" content="#3f51b5">

    <!-- Add to homescreen for Chrome on Android. Fallback for manifest.json -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="application-name" content="<?php echo get_option('blogname'); ?>">

    <!-- Add to homescreen for Safari on iOS -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="<?php echo get_option('blogname'); ?>">

    <!-- Homescreen icons -->
    <link rel="apple-touch-icon" href="<?php echo get_template_directory_uri(); ?>/images/manifest/icon-48x48.png">
    <link rel="apple-touch-icon" sizes="72x72"
          href="<?php echo get_template_directory_uri(); ?>/images/manifest/icon-72x72.png">
    <link rel="apple-touch-icon" sizes="96x96"
          href="<?php echo get_template_directory_uri(); ?>/images/manifest/icon-96x96.png">
    <link rel="apple-touch-icon" sizes="144x144"
          href="<?php echo get_template_directory_uri(); ?>/images/manifest/icon-144x144.png">
    <link rel="apple-touch-icon" sizes="192x192"
          href="<?php echo get_template_directory_uri(); ?>/images/manifest/icon-192x192.png">

    <!-- Tile icon for Windows 8 (144x144 + tile color) -->
    <meta name="msapplication-TileImage"
          content="<?php echo get_template_directory_uri(); ?>/images/manifest/icon-144x144.png">
    <meta name="msapplication-TileColor" content="#3f51b5">
    <meta name="msapplication-tap-highlight" content="no">

    <script>
        // Setup Polymer options
        window.Polymer = {
            dom: 'shadow',
            lazyRegister: true
        };

        // Load webcomponentsjs polyfill if browser does not support native Web Components
        (function () {
            'use strict';

            var onload = function () {
                // For native Imports, manually fire WebComponentsReady so user code
                // can use the same code path for native and polyfill'd imports.
                if (!window.HTMLImports) {
                    document.dispatchEvent(
                        new CustomEvent('WebComponentsReady', {bubbles: true})
                    );
                }
            };

            var webComponentsSupported = (
                'registerElement' in document
                && 'import' in document.createElement('link')
                && 'content' in document.createElement('template')
            );

            if (!webComponentsSupported) {
                var script = document.createElement('script');
                script.async = true;
                script.src = '<?php echo get_template_directory_uri(); ?>/bower_components/webcomponentsjs/webcomponents-lite.min.js';
                script.onload = onload;
                document.head.appendChild(script);
            } else {
                onload();
            }
        })();

        // Load pre-caching Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('<?php echo get_template_directory_uri(); ?>/service-worker.js');
            });
        }
    </script>

    <link rel="import" href="<?php echo get_template_directory_uri(); ?>/src/core/polymer-theme-shell.html">
    <link rel="import" href="<?php echo get_template_directory_uri(); ?>/bower_components/global-variable/global-variable.html">

    <style>
        body {
            margin: 0;
            font-family: 'Roboto', 'Noto', sans-serif;
            line-height: 1.5;
            min-height: 100vh;
            background-color: #eeeeee;
        }
    </style>


</head>
<body <?php body_class(); ?>>

<!-- Google Tag Manager (noscript) -->
<noscript>
    <iframe src="https://www.googletagmanager.com/ns.html?id=GTM-TJSXJ3C"
            height="0" width="0" style="display:none;visibility:hidden"></iframe>
</noscript>
<!-- End Google Tag Manager (noscript) -->

<!-- App Status variables -->
<global-variable key="siteUrl" value="<?php echo get_option('siteurl'); ?>"></global-variable>
<global-variable key="siteDescription"
                        value="<?php echo get_option('blogdescription'); ?>"></global-variable>
<global-variable key="siteTitle" value="<?php echo get_option('blogname'); ?>"></global-variable>
<global-variable key="frontPage" value="<?php echo get_front_page(); ?>"></global-variable>
<global-variable key="postPerPage" value="<?php echo get_option('posts_per_page'); ?>"></global-variable>

<!-- App -->
<polymer-theme-shell unresolved></polymer-theme-shell>

<script>
    (function () {
        var shell = document.querySelector('polymer-theme-shell');
        shell.menus = <?php echo get_menus(); ?>;
    })()
</script>

</body>
</html>
