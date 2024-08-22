<?php

namespace App\Observers\Webshop;

use Exception;
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
            // Meta tags to look for
            $metaTags = [
                'name' => [
                    'meta[name="product"]',
                    'meta[property="og:title"]',
                    'meta[name="twitter:title"]'
                ],
                'description' => [
                    'meta[name="description"]',
                    'meta[property="og:description"]',
                    'meta[name="twitter:description"]'
                ],
                'brand' => [
                    'meta[name="brand"]',
                    'meta[property="product:brand"]',
                    'meta[itemprop="brand"]',
                    'meta[name="og:brand"]'
                ],
                'article_number' => [
                    'meta[name="sku"]',
                    'meta[property="product:sku"]'
                ],
                'price' => [
                    'meta[name="price"]',
                    'meta[property="product:price:amount"]',
                    'meta[property="og:price:amount"]',
                    'meta[itemprop="price"]',
                    'meta[property="product:price"]'
                ],
                'image' => [
                    'meta[property="og:image"]',
                    'meta[name="twitter:image"]'
                ]
            ];

            // Function to extract content from meta tags
            $extractMetaContent = function($metaTags, $crawler) {
                foreach ($metaTags as $metaTag) {
                    if ($crawler->filter($metaTag)->count() > 0) {
                        return $crawler->filter($metaTag)->attr('content');
                    }
                }
                return null;
            };

            // Extract data
            $productName = $extractMetaContent($metaTags['name'], $crawler);
            $productDescription = $extractMetaContent($metaTags['description'], $crawler);
            $productBrand = $extractMetaContent($metaTags['brand'], $crawler);
            $articleNumber = $extractMetaContent($metaTags['article_number'], $crawler);
            $productPrice = $extractMetaContent($metaTags['price'], $crawler);
            $productImage = $extractMetaContent($metaTags['image'], $crawler);

            if (!$productName && !$productDescription && !$productBrand && !$articleNumber && !$productPrice && !$productImage) {
                throw new Exception('No product data found.');
            }

            $storeName = $this->extractStoreName($url);

            // Store product data
            $this->scrapedData = [
                'name' => $productName,
                'description' => $productDescription,
                'brand' => $productBrand,
                'article_number' => $articleNumber,
                'price' => $productPrice,
                'image' => $productImage,
                'store' => $storeName,
            ];

            // Log product data
            Log::info('Product Data', ['data' => $this->scrapedData]);

        } catch (\Exception $e) {
            Log::error('Error extracting product data: ' . $e->getMessage());
        }
    }


    /**
     * Extracts the store name from the given URL heuristically.
     *
     * @param UriInterface $url
     * @return string
     */
    private function extractStoreName(UriInterface $url): string
    {
        $host = $url->getHost();

        // Remove 'www.' if present
        $host = preg_replace('/^www\./', '', $host);

        // Extract the main part of the domain name (e.g., 'zalando', 'c-and-a', 'jules')
        $parts = explode('.', $host);
        $domain = $parts[0];

        // Convert domain part to a more readable store name
        $storeName = $this->formatStoreName($domain);

        return $storeName;
    }

    /**
     * Formats the domain part to a more readable store name.
     *
     * @param string $domain
     * @return string
     */
    private function formatStoreName(string $domain): string
    {
        // Replace hyphens with spaces
        $domain = str_replace('-', ' ', $domain);

        // Capitalize each word
        $storeName = ucwords($domain);

        return $storeName;
    }

    public function crawlFailed(UriInterface $url, RequestException $requestException, ?UriInterface $foundOnUrl = null, ?string $linkText = null): void
    {
        Log::error("Failed: {$url}", ['error' => $requestException->getMessage()]);
    }

    public function finishedCrawling(): void
    {
        Log::info('Finished crawling');
    }

    public function getScrapedData(): array
    {
        return $this->scrapedData;
    }
}
