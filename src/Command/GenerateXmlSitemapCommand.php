<?php

namespace App\Command;

use App\Repository\ListingRepository;
use App\Service\AwsService;
use App\Service\SitemapService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\RouterInterface;

class GenerateXmlSitemapCommand extends Command
{
    protected static $defaultName = 'app:generate-xml-sitemap';
    protected static $defaultDescription = 'Generate xml sitemap';

    const UPLOAD_SITEMAP_PATH = '/viksemenovSitemap/sitemap/';

    private ListingRepository $listingRepository;
    private RouterInterface $router;
    private LoggerInterface $logger;
    private Filesystem $filesystem;
    private SitemapService $sitemapService;
    private AwsService $awsService;

    private $appUrl;
    private $stateOrProvinces;
    private $cities;


    public function __construct(
        ListingRepository $listingRepository,
        RouterInterface $router,
        LoggerInterface $logger,
        Filesystem $filesystem,
        AwsService $awsService,
        SitemapService $sitemapService,
        string $appUrl
    )
    {
        parent::__construct();
        $this->appUrl = $appUrl;
        $this->listingRepository = $listingRepository;
        $this->router = $router;
        $this->logger = $logger;
        $this->filesystem = $filesystem;
        $this->awsService = $awsService;
        $this->sitemapService = $sitemapService;
    }

    protected function configure()
    {
        $this->setDescription(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('memory_limit','2048M');
        $sitemapPathForUpload = sys_get_temp_dir() . self::UPLOAD_SITEMAP_PATH;
        if (!is_dir($sitemapPathForUpload)) $this->filesystem->mkdir($sitemapPathForUpload);
        $cloudDestination = 'sitemap/';
        $urls = [];
        $filenames = [];

        try {

            if (!$this->appUrl) {
                $this->logger->error('Need to fill APP_URL in .env file');
                return Command::FAILURE;
            }
            $context = $this->router->getContext();
            $context->setBaseUrl($this->appUrl);

            $this->stateOrProvinces = $this->listingRepository->getStateOrProvinces();
            $this->cities = $this->listingRepository->getCities();

            $urls = $this->sitemapService->generateRoutesWithoutParameters($urls);
            $urls = $this->sitemapService->generateListingsMapUrls($urls, $this->cities);

            $filenames = $this->sitemapService->generateMainSitemapFile($urls, $sitemapPathForUpload, $filenames);
            $filenames = $this->sitemapService->generateListingUrls($sitemapPathForUpload, $this->stateOrProvinces, $filenames);
            $this->sitemapService->generateIndexSitemapFile($sitemapPathForUpload, $filenames, 'sitemap.xml');

            $this->awsService->delete($cloudDestination);
            $this->awsService->upload($sitemapPathForUpload, $cloudDestination);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
        } finally {
            $this->filesystem->remove($sitemapPathForUpload);
            $this->logger->alert('Command generate-xml-sitemap completed');
        }

        return Command::SUCCESS;
    }
}
