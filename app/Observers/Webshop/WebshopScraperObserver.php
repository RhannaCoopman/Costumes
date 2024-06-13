<?php

namespace App\Observers\Webshop;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Spatie\Crawler\CrawlObservers\CrawlObserver;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class WebshopScraperObserver extends CrawlObserver
{
    protected $scrapedData = [];

    public function willCrawl(UriInterface $url, ?string $linkText): void
    {
        Log::info('willCrawl', ['url' => $url]);
    }

    public function crawled(
        UriInterface $url,
        ResponseInterface $response,
        ?UriInterface $foundOnUrl = null,
        ?string $linkText = null
    ): void {
        Log::info("Crawled: {$url}");

        $crawler = new \Symfony\Component\DomCrawler\Crawler((string) $response->getBody());

        try {
            // Product name
            $productName = $crawler->filter('h1')->text() ?? null;

            // $productImage = null;
            // $imageNode = $crawler->filter('img')->first();
            // info('image node');
            // info(json_encode($imageNode, JSON_PRETTY_PRINT));
            // if ($imageNode->count()) {
            //     $productImage = $imageNode->attr('src');
            // }

            // Store product data
            $this->scrapedData = [
                'name' => $productName,
                // 'image' => $productImage,
            ];

            // Log product data
            Log::info('Product Data', ['data' => $this->scrapedData]);

        } catch (\Exception $e) {
            Log::error('Error extracting product data: ' . $e->getMessage());
        }
    }

    public function crawlFailed(
        UriInterface $url,
        RequestException $requestException,
        ?UriInterface $foundOnUrl = null,
        ?string $linkText = null
    ): void {
        Log::error("Failed: {$url}", ['error' => $requestException->getMessage()]);
    }

    public function finishedCrawling(): void
    {
        Log::info("Finished crawling");
    }

    public function getScrapedData(): array
    {
        return $this->scrapedData;
    }
}
