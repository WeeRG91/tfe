<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <script>
            (function () {
                const context = {{ Illuminate\Support\Js::from($themeContext ?? []) }};
                const initialTheme = context['initialTheme'];

                if (!initialTheme) {
                    return;
                }

                let theme = initialTheme;

                if (context['selection'] === 'system') {
                    const prefersDark = window.matchMedia(
                        '(prefers-color-scheme: dark)',
                    ).matches;

                    const systemLightTheme = context['systemLightTheme'];
                    const systemDarkTheme = context['systemDarkTheme'];

                    theme = prefersDark
                        ? systemDarkTheme ?? systemLightTheme
                        : systemLightTheme;
                }

                if (!theme) {
                    return;
                }

                const root = document.documentElement;

                root.dataset.theme = theme.key;
                root.dataset.themeMode = theme.mode;

                root.classList.toggle('dark', theme.mode === 'dark');
                root.style.colorScheme = theme.mode;
                root.style.setProperty('--radius', theme.radius);

                Object.entries(theme.colors ?? {}).forEach(
                    ([colorKey, value]) => {
                        const cssVariable = colorKey
                            .replace(/([a-z0-9])([A-Z])/g, '$1-$2')
                            .replace(/([a-zA-Z])(\d+)/g, '$1-$2')
                            .toLowerCase();

                        root.style.setProperty(`--${cssVariable}`, value);
                    },
                );
            })();
        </script>

        <style>
            html {
                background-color: var(--background, oklch(1 0 0));
                color: var(--foreground, oklch(0.145 0 0));
            }
        </style>

        <!--suppress HtmlUnknownAttribute -->
        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <link
            rel="icon"
            type="image/png"
            sizes="32x32"
            href="/images/branding/kin-dee-favicon-32-thai-v2.png"
        >
        <link
            rel="apple-touch-icon"
            sizes="180x180"
            href="/images/branding/kin-dee-apple-touch-icon-thai-v2.png"
        >

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased max-w-screen overflow-x-hidden">
        @inertia
    </body>
</html>
