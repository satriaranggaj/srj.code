<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

/**
 * Serves robots.txt.
 *
 * The sitemap URL is generated from APP_URL rather than from the incoming Host
 * header, so a staging deployment can never advertise one canonical domain, and a
 * spoofed Host header cannot inject an arbitrary sitemap URL. This removes the
 * manual "remember to update robots.txt when the domain changes" step.
 */
class RobotsController extends Controller
{
    /**
     * Paths that must never be indexed.
     *
     * @var array<int, string>
     */
    private const DISALLOWED = [
        '/dashboard',
        '/profile',
        '/project',
        '/skill',
        '/certificate',
        '/messages',
        '/login',
        '/register',
        '/forgot-password',
        '/reset-password',
        '/verify-email',
        '/confirm-password',
        '/email',
    ];

    public function __invoke(): Response
    {
        $lines = [
            '# SRJ Portfolio',
            '',
            'User-agent: *',
            'Allow: /',
            '',
            '# Authenticated areas carry no public value and must never be indexed.',
        ];

        foreach (self::DISALLOWED as $path) {
            $lines[] = 'Disallow: '.$path;
        }

        $lines[] = '';
        $lines[] = 'Sitemap: '.rtrim(config('app.url'), '/').'/sitemap.xml';

        return response(implode(PHP_EOL, $lines).PHP_EOL, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
        ]);
    }
}
