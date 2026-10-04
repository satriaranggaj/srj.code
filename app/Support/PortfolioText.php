<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Small, dependency-free string helpers shared by the Portfolio V2 models,
 * form requests and migrations.
 */
final class PortfolioText
{
    /**
     * Acronyms that must not be title-cased when derived from a file name.
     *
     * @var array<int, string>
     */
    private const ACRONYMS = [
        'php' => 'PHP',
        'css' => 'CSS',
        'html' => 'HTML',
        'js' => 'JS',
        'sql' => 'SQL',
        'api' => 'API',
        'ui' => 'UI',
        'ux' => 'UX',
        'ai' => 'AI',
        'aws' => 'AWS',
        'ci' => 'CI',
        'cd' => 'CD',
    ];

    /**
     * Derive a URL-safe slug from arbitrary text.
     *
     * camelCase and PascalCase boundaries are treated as word breaks, so
     * "BarcodeIdentify" becomes "barcode-identify" rather than "barcodeidentify".
     */
    public static function slugify(?string $value): string
    {
        $text = (string) $value;

        $slug = Str::slug(self::splitCamelCase($text));

        if ($slug === '') {
            $slug = Str::slug((string) preg_replace('/\.[^.]+$/', '', $text));
        }

        return mb_substr($slug, 0, 180);
    }

    /**
     * Insert a separator at lower-to-upper transitions and at the last capital of a
     * run followed by lowercase letters, e.g. "HTTPServer" -> "HTTP Server".
     */
    private static function splitCamelCase(string $value): string
    {
        $value = preg_replace('/([a-z0-9])([A-Z])/', '$1 $2', $value) ?? $value;

        return preg_replace('/([A-Z]+)([A-Z][a-z])/', '$1 $2', $value) ?? $value;
    }

    /**
     * Derive a human-readable technology name from an uploaded file name.
     *
     * Used only to give pre-existing `skills` rows a display name. Nothing is invented:
     * if the file name carries no information, null is returned and the UI falls back
     * to a generic badge.
     */
    public static function nameFromFilename(?string $path): ?string
    {
        $basename = pathinfo(basename((string) $path), PATHINFO_FILENAME);

        if ($basename === '') {
            return null;
        }

        $words = preg_split('/[\s\-_.]+/', $basename, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($words === []) {
            return null;
        }

        $name = implode(' ', $words);

        return self::ACRONYMS[strtolower($name)] ?? Str::title($name);
    }
}
