<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Observers\Webshop\WebshopScraperObserver;
use Spatie\Crawler\Crawler;
use Exception;
use GuzzleHttp\Exception\RequestException;

class WebshopScraperController extends Controller
{
    public function scrape(Request $request)
    {
        $url = $request->input('url');

        try {
            $observer = new WebshopScraperObserver();

            Crawler::create()
                ->setCrawlObserver($observer)
                ->setMaximumDepth(0)
                ->setTotalCrawlLimit(1)
                ->startCrawling($url);

            $scrapedData = $observer->getScrapedData();

            if (empty($scrapedData)) {
                return response()->json(['error' => 'No data could be scraped from the provided URL.'], 404);
            }

            return response()->json($scrapedData);

        } catch (RequestException $e) {
            return response()->json(['error' => 'Failed to scrape the site. The site may not allow scraping.'], 403);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
