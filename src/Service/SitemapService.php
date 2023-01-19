<?php


namespace App\Service;


use App\Repository\ListingRepository;
use App\Service\Listing\ListingMediaService;
use App\Service\Listing\ListingService;
use App\Service\Search\SearchService;
use Carbon\Carbon;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class SitemapService
{
    const LINKS_LIMIT = 40000;

    private RouterInterface $router;
    private Environment $twig;
    private Filesystem $filesystem;
    private ListingRepository $listingRepository;
    private ListingMediaService $listingMediaService;
    private SearchService $searchService;


    public function __construct(
        RouterInterface     $router,
        Environment         $twig,
        Filesystem          $filesystem,
        ListingRepository   $listingRepository,
        ListingMediaService $listingMediaService,
        SearchService       $searchService
    )
    {
        $this->router = $router;
        $this->twig = $twig;
        $this->filesystem = $filesystem;
        $this->listingRepository = $listingRepository;
        $this->listingMediaService = $listingMediaService;
        $this->searchService = $searchService;
    }

    public function generateListingsMapUrls($urls, $cities)
    {
        $lastmod = Carbon::now()->toW3cString();
        foreach ($cities as $city) {
            $stateOrProvinceCode = ListingService::getProvinceCode($city['stateOrProvince']);
            if (strpos($city['city'], $stateOrProvinceCode) !== false) {
                $city['city'] = substr($city['city'], 0, strpos($city['city'], ", "));
            }
            if (strpos($city['city'], strtolower($stateOrProvinceCode)) !== false) {
                $city['city'] = substr(strtolower($city['city']), 0, strpos(strtolower($city['city']), ", "));
            }
            if (strpos($city['city'], ucfirst(strtolower($stateOrProvinceCode))) !== false) {
                $city['city'] = substr(ucfirst(strtolower($city['city'])), 0, strpos(ucfirst(strtolower($city['city'])), ", "));
            }
            $url = $this->router->generate('listings_map', ['location' => $city['city'] . ',' . $stateOrProvinceCode]);
            $urls[] = ['url' => $url, 'changefreq' => 'weekly', 'priority' => 0.9, 'lastmod' => $lastmod];
        }
        return $urls;
    }

    public function generateRoutesWithoutParameters($urls)
    {
        $routes = ['assessment', 'price-your-home', 'contact_us', 'home', 'app_login', 'how-it-works', 'selling', 'buying', 'sitemap'];
        $lastmod = Carbon::now()->toW3cString();

        foreach ($routes as $key) {
            $urls[] = ['url' => $this->router->generate($key), 'changefreq' => 'weekly', 'priority' => 0.9, 'lastmod' => $lastmod];
        }
        return $urls;
    }

    public function generateIndexSitemapFile($sitemapPathForUpload, $filenames, $sitemapFilename)
    {
        $fileUrls = [];
        foreach ($filenames as $filename) {
            $fileUrls[] = ['url' => $this->router->generate('index_sitemap', ['filename' => $filename])];
        }
        $fileUrls[] = ['url' => $this->router->generate('image_sitemap', ['filename' => 'sitemap'])];
        $xmlContent = $this->twig->render('sitemap/index-sitemap.xml.twig', ['fileUrls' => $fileUrls, 'lastmod' => Carbon::now()->toW3cString()]);
        $path = $sitemapPathForUpload . $sitemapFilename;
        $this->filesystem->dumpFile($path, $xmlContent);
    }

    public function generateIndexImageSitemapFile($sitemapPathForUpload, $filenames, $sitemapFilename)
    {
        $fileUrls = [];
        foreach ($filenames as $filename) {
            $fileUrls[] = ['url' => $this->router->generate('image_sitemap', ['filename' => $filename])];
        }
        $xmlContent = $this->twig->render('sitemap/index-sitemap.xml.twig', ['fileUrls' => $fileUrls, 'lastmod' => Carbon::now()->toW3cString()]);
        $path = $sitemapPathForUpload . $sitemapFilename;
        $this->filesystem->dumpFile($path, $xmlContent);
    }

    public function generateListingUrls($sitemapPathForUpload, $stateOrProvinces, $filenames)
    {
        $lastmod = Carbon::now()->toW3cString();
        foreach ($stateOrProvinces as $stateOrProvince) {
            $linksCounter = 0;
            $fileCounter = 0;
            $urls = [];
            $listingUrls = $this->searchService->listingForSitemapByStatueOrProvince($stateOrProvince[1]);
            foreach ($listingUrls as $url) {
                $urls[] = ['url' => $url, 'changefreq' => 'hourly', 'priority' => 0.9, 'lastmod' => $lastmod];
                $linksCounter++;
                if ($linksCounter > self::LINKS_LIMIT) {
                    $filename = $this->generateFilename($stateOrProvince[1], $fileCounter);
                    $filenames[] = $filename;
                    $this->createXMLFile($urls, $sitemapPathForUpload, $filename);
                    $fileCounter++;
                    unset($urls);
                    $urls = [];
                    $linksCounter = 0;
                }
            }

            $filename = $this->generateFilename($stateOrProvince[1], $fileCounter);
            $this->createXMLFile($urls, $sitemapPathForUpload, $filename);
            $filenames[] = $filename;
        }
        return $filenames;
    }

    public function generateMainSitemapFile($urls, $sitemapPathForUpload, $filenames)
    {
        $xmlContent = $this->twig->render('sitemap/sitemap.xml.twig', [
            'urls' => $urls
        ]);
        $filename = 'main-sitemap';
        $path = $sitemapPathForUpload . $filename . '.xml';
        $filenames[] = $filename;
        $this->filesystem->dumpFile($path, $xmlContent);
        return $filenames;
    }

    public function generateListingImageUrls($sitemapPathForUpload, $stateOrProvince, $filenames)
    {
        $linksCounter = 0;
        $fileCounter = 0;
        $urls = [];
        $lastmod = Carbon::now()->toW3cString();
        $listings = $this->searchService->listingForSitemapByStatueOrProvinceWithImages($stateOrProvince);
        foreach ($listings as $listing) {
            if (count($listing['images']) > 0) {
                $urls[] = [
                    'url' => $listing['url'],
                    'images' => $listing['images'],
                    'lastmod' => $lastmod
                ];
                $linksCounter += count($listing['images']);
            }
            if ($linksCounter > self::LINKS_LIMIT) {
                $filename = $this->generateFilename($stateOrProvince, $fileCounter);
                $filenames[] = $filename;
                $this->createImageXMLFile($urls, $sitemapPathForUpload, $filename);
                $fileCounter++;
                unset($urls);
                $urls = [];
                $linksCounter = 0;
            }
        }
        $filename = $this->generateFilename($stateOrProvince, $fileCounter);
        $filenames[] = $filename;
        $this->createImageXMLFile($urls, $sitemapPathForUpload, $filename);
        unset($listings);
        unset($listing);
        unset($urls);
        return $filenames;
    }

    public function createImageXMLFile($urls, $sitemapPathForUpload, $filename)
    {
        $xmlContent = $this->twig->render('sitemap/image-sitemap.xml.twig', ['urls' => $urls]);
        $path = $sitemapPathForUpload . 'image_' . $filename . '.xml';
        $fileSystem = new Filesystem();
        $fileSystem->dumpFile($path, $xmlContent);
    }

    public function createXMLFile($urls, $sitemapPathForUpload, $filename)
    {
        $xmlContent = $this->twig->render('sitemap/sitemap.xml.twig', ['urls' => $urls]);
        $path = $sitemapPathForUpload . $filename . '.xml';
        $fileSystem = new Filesystem();
        $fileSystem->dumpFile($path, $xmlContent);
    }

    private function generateFilename($stateOrProvince, $fileCounter)
    {
        $filename = preg_replace('![\s]+!', "-", mb_strtolower($stateOrProvince));
        if ($fileCounter > 0) $filename = $fileCounter . '_' . $filename;
        return $filename;
    }

}