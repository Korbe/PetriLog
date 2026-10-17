<?php

namespace App\Support;

/**
 * Builds the per-request SEO data that app.blade.php renders into <head>.
 *
 * Pass it to the root view with Inertia's withViewData(['seo' => ...]) — it must
 * never be stored in the session, otherwise it leaks onto the next page.
 */
class Seo
{
    public const SITE_NAME = 'PetriLog';

    public const DEFAULT_DESCRIPTION = 'Das digitale Fangbuch für Angler in Österreich: Länge, Gewicht, Köder, Tiefe, '
        . 'Wassertemperatur und GPS-Spot zu jedem Fang festhalten – mit bis zu 3 Fotos.';

    /**
     * @param string $title       Page title without the site name (it is appended like on the client).
     * @param string $description 140–160 characters work best.
     * @param array  $options     robots, image, imageAlt, type, canonical, jsonLd (list of schema.org blocks), breadcrumb
     */
    public static function page(string $title, string $description, array $options = []): array
    {
        $fullTitle = $title === '' ? self::SITE_NAME : "{$title} - " . self::SITE_NAME;
        $canonical = $options['canonical'] ?? url()->current();
        $image = $options['image'] ?? asset('images/og/petrilog-og.jpg');
        $imageAlt = $options['imageAlt'] ?? 'PetriLog – das digitale Fangbuch für Angler in Österreich';

        $jsonLd = $options['jsonLd'] ?? [];
        if (!empty($options['breadcrumb'])) {
            $jsonLd[] = self::breadcrumb($title, $canonical);
        }

        return [
            'title' => $fullTitle,
            'description' => $description,
            'robots' => $options['robots'] ?? 'index, follow, max-image-preview:large',
            'canonical' => $canonical,
            'og' => [
                'type' => $options['type'] ?? 'website',
                'site_name' => self::SITE_NAME,
                'locale' => 'de_AT',
                'title' => $fullTitle,
                'description' => $description,
                'url' => $canonical,
                'image' => $image,
                'image:alt' => $imageAlt,
            ],
            'twitter' => [
                'card' => 'summary_large_image',
                'title' => $fullTitle,
                'description' => $description,
                'image' => $image,
                'image:alt' => $imageAlt,
            ],
            'jsonLd' => $jsonLd,
        ];
    }

    /**
     * Fallback for pages without explicit SEO data (app, auth). Those are not meant for search engines.
     */
    public static function defaults(): array
    {
        return self::page('', self::DEFAULT_DESCRIPTION, ['robots' => 'noindex, follow']);
    }

    /**
     * Site-wide structured data, rendered on every page.
     */
    public static function siteJsonLd(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Organization',
                    '@id' => url('/') . '/#organization',
                    'name' => self::SITE_NAME,
                    'url' => url('/'),
                    'logo' => asset('images/icons/logo-512.png'),
                    'email' => 'info@petrilog.com',
                ],
                [
                    '@type' => 'WebSite',
                    '@id' => url('/') . '/#website',
                    'name' => self::SITE_NAME,
                    'url' => url('/'),
                    'inLanguage' => 'de-AT',
                    'publisher' => ['@id' => url('/') . '/#organization'],
                ],
            ],
        ];
    }

    public static function webApplication(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebApplication',
            'name' => self::SITE_NAME,
            'url' => url('/'),
            'description' => self::DEFAULT_DESCRIPTION,
            'applicationCategory' => 'SportsApplication',
            'operatingSystem' => 'Web, Android, iOS, Windows, macOS',
            'inLanguage' => 'de-AT',
            'offers' => [
                [
                    '@type' => 'Offer',
                    'name' => 'Kostenlos (bis zu 5 Fänge)',
                    'price' => '0',
                    'priceCurrency' => 'EUR',
                ],
                [
                    '@type' => 'Offer',
                    'name' => 'PetriLog Plus (Jahresabo)',
                    'price' => '49.99',
                    'priceCurrency' => 'EUR',
                ],
            ],
            'publisher' => ['@id' => url('/') . '/#organization'],
        ];
    }

    /**
     * @param array $faqs [['question' => ..., 'answer' => ...], ...]
     */
    public static function faqPage(array $faqs): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn(array $faq) => [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['answer']],
            ], $faqs),
        ];
    }

    private static function breadcrumb(string $title, string $url): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Startseite', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => $title, 'item' => $url],
            ],
        ];
    }
}
