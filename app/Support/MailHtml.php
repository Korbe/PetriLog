<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Support\HtmlString;

/**
 * Macht Quill-HTML (Newsletter, Ticket-Texte) mailtauglich:
 * Mail-Clients ignorieren <style>-Blöcke oft, daher Inline-Styles,
 * und Quill 2 schreibt Aufzählungen als <ol><li data-list="bullet">.
 */
class MailHtml
{
    public const STYLES = [
        'p' => 'margin:0 0 16px; font-size:16px; line-height:1.6; color:#0f1d22;',
        'a' => 'color:#0c6fc0; text-decoration:underline;',
        'ul' => 'margin:0 0 16px; padding-left:22px; color:#0f1d22;',
        'ol' => 'margin:0 0 16px; padding-left:22px; color:#0f1d22;',
        'li' => 'margin:0 0 6px; font-size:16px; line-height:1.6;',
        'h1' => 'margin:0 0 12px; font-size:22px; line-height:1.25; color:#0f1d22;',
        'h2' => 'margin:0 0 12px; font-size:19px; line-height:1.3; color:#0f1d22;',
        'h3' => 'margin:0 0 10px; font-size:17px; line-height:1.3; color:#0f1d22;',
    ];

    public static function format(?string $html): HtmlString
    {
        if ($html === null || trim($html) === '') {
            return new HtmlString('');
        }

        $doc = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8"><div id="mail-root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $xpath = new DOMXPath($doc);

        // Quill-Hilfselemente entfernen
        foreach (iterator_to_array($xpath->query('//span[contains(@class, "ql-ui")]')) as $node) {
            $node->parentNode->removeChild($node);
        }

        // <ol> nur mit Bullet-Einträgen → <ul>
        foreach (iterator_to_array($xpath->query('//ol')) as $ol) {
            $items = $xpath->query('./li', $ol);
            $bullets = $xpath->query('./li[@data-list="bullet"]', $ol);

            if ($items->length > 0 && $items->length === $bullets->length) {
                self::rename($ol, 'ul');
            }
        }

        foreach (self::STYLES as $tag => $style) {
            foreach ($doc->getElementsByTagName($tag) as $el) {
                $el->setAttribute('style', $style);
            }
        }

        $root = $xpath->query('//div[@id="mail-root"]')->item(0);
        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }

        return new HtmlString($out);
    }

    private static function rename(DOMElement $el, string $tag): void
    {
        $new = $el->ownerDocument->createElement($tag);

        foreach (iterator_to_array($el->attributes) as $attr) {
            $new->setAttribute($attr->name, $attr->value);
        }
        while ($el->firstChild) {
            $new->appendChild($el->firstChild);
        }

        $el->parentNode->replaceChild($new, $el);
    }
}
