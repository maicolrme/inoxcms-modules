<?php

namespace Inox\Seo\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $content = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $content .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        $content .= '</urlset>';

        return response($content, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    public function robots(): Response
    {
        $content = "User-agent: *\nAllow: /\nSitemap: " . url('/sitemap.xml');

        return response($content, 200, [
            'Content-Type' => 'text/plain',
        ]);
    }
}
