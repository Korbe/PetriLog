<?php

namespace App\Support;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerAction;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Bereinigt Rich-Text (Quill/VEditor) bevor er gespeichert und später per v-html ausgegeben wird.
 *
 * Erlaubt ist nur, was die VEditor-Toolbar erzeugt: Absätze, Zeilenumbrüche,
 * fett/kursiv/unterstrichen/durchgestrichen, Listen (Quill 2 nutzt <ol><li data-list="...">)
 * und Links mit http/https/mailto.
 */
class RichText
{
    private static ?HtmlSanitizer $sanitizer = null;

    public static function sanitize(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        $clean = trim(self::sanitizer()->sanitize($html));

        return $clean === '' ? null : $clean;
    }

    private static function sanitizer(): HtmlSanitizer
    {
        return self::$sanitizer ??= new HtmlSanitizer(
            (new HtmlSanitizerConfig())
                // Unbekannte Tags entfernen, Textinhalt aber behalten (z.B. eingefügtes <div>/<span>)
                ->defaultAction(HtmlSanitizerAction::Block)
                ->dropElement('script')
                ->dropElement('style')
                ->dropElement('noscript')
                ->dropElement('template')
                ->dropElement('iframe')
                ->dropElement('object')
                ->dropElement('embed')
                ->dropElement('svg')
                ->dropElement('math')
                ->dropElement('textarea')
                ->dropElement('select')
                ->dropElement('title')
                ->allowElement('p')
                ->allowElement('br')
                ->allowElement('strong')
                ->allowElement('em')
                ->allowElement('u')
                ->allowElement('s')
                ->allowElement('ol')
                ->allowElement('ul')
                ->allowElement('li', ['data-list'])
                ->allowElement('a', ['href'])
                ->allowLinkSchemes(['http', 'https', 'mailto'])
                ->allowRelativeLinks(false)
                // Quill setzt diese Attribute selbst, wir erzwingen sie für jeden Link
                ->forceAttribute('a', 'target', '_blank')
                ->forceAttribute('a', 'rel', 'noopener noreferrer')
                // text-Spalte (65.535 Bytes); Standard von Symfony wäre 20.000 Zeichen
                ->withMaxInputLength(65535)
        );
    }
}
