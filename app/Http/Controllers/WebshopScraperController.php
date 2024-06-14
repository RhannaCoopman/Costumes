<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Observers\Webshop\WebshopScraperObserver;
use Spatie\Crawler\Crawler;

class WebshopScraperController extends Controller
{
    public function scrape(Request $request)
    {
        $url = $request->input('url');

        $observer = new WebshopScraperObserver();

        Crawler::create()
            ->setCrawlObserver($observer)
            ->setMaximumDepth(0)
            ->setTotalCrawlLimit(1)
            ->startCrawling($url);

        $scrapedData = $observer->getScrapedData();

        return response()->json($scrapedData);
    }
}
