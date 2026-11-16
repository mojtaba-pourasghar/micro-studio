<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/** Allow-list HTML sanitizer for post bodies written by an admin or the agent. */
class Html
{
    /** tag => allowed attributes */
    protected const ALLOWED = [
        'p' => ['class'], 'br' => [], 'h2' => ['id'], 'h3' => ['id'], 'h4' => [], 'strong' => [], 'b' => [], 'em' => [], 'i' => [],
        'u' => [], 's' => [], 'a' => ['href', 'title', 'target', 'rel'], 'ul' => ['class'], 'ol' => [], 'li' => [],
        'blockquote' => ['class'], 'figure' => ['class'], 'figcaption' => [], 'img' => ['src', 'alt', 'width', 'height', 'loading'],
        'code' => [], 'pre' => [], 'hr' => [], 'table' => [], 'thead' => [], 'tbody' => [], 'tr' => [], 'th' => [], 'td' => [],
        'span' => ['class'], 'div' => ['class'],
    ];

    public static function sanitize(string $html): string
    {
        if (trim($html) === '') {
            return '';
        }
        $doc = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8"><div id="__root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        $root = $doc->getElementById('__root');
        if (! $root) {
            return e(strip_tags($html));
        }
        static::clean($root);
        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }

        return trim($out);
    }

    protected static function clean(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMElement) {
                $tag = strtolower($child->tagName);
                if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'svg', 'math'], true)) {
                    $node->removeChild($child);

                    continue;
                }
                if (! array_key_exists($tag, self::ALLOWED)) {
                    static::clean($child);
                    while ($child->firstChild) {
                        $node->insertBefore($child->firstChild, $child);
                    }
                    $node->removeChild($child);

                    continue;
                }
                foreach (iterator_to_array($child->attributes) as $attr) {
                    $name = strtolower($attr->name);
                    $ok = in_array($name, self::ALLOWED[$tag], true);
                    if ($ok && in_array($name, ['href', 'src'], true)) {
                        $ok = (bool) preg_match('~^(https?://|/|#|mailto:|tel:|uploads/|assets/)~i', trim($attr->value));
                    }
                    if (! $ok) {
                        $child->removeAttribute($attr->name);
                    }
                }
                if ($tag === 'a' && $child->getAttribute('target') === '_blank') {
                    $child->setAttribute('rel', 'noopener');
                }
                static::clean($child);
            }
        }
    }
}
