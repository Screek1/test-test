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
use function Clue\StreamFilter\remove;

class GenerateImageSitemapCommand extends Command
{
    protected static $defaultName = 'app:generate-image-sitemap';
    protected static $defaultDescription = 'Generate image sitemap xml';

    const UPLOAD_SITEMAP_PATH = '/viksemenovSitemap/sitemap/image/';
    const FILE_USED_STATES = './var/usedStates.log';
    const FILE_FILENAMES = './var/filenames.log';


    private ListingRepository $listingRepository;
    private RouterInterface $router;
    private LoggerInterface $logger;
    private AwsService $awsService;
    private Filesystem $filesystem;
    private SitemapService $sitemapService;

    private string $appUrl;
    private $stateOrProvinces;

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
        $cloudDestination = 'sitemap-image/';

        if ($this->filesystem->exists(self::FILE_USED_STATES)) {
            $stateOrProvinces = json_decode(file_get_contents(self::FILE_USED_STATES));
            if (count($stateOrProvinces)) {
                $stateOrProvince = $stateOrProvinces[0];
            } else {
                $filenames = json_decode(file_get_contents(self::FILE_FILENAMES));
                $this->sitemapService->generateIndexImageSitemapFile($sitemapPathForUpload, $filenames, 'image_sitemap.xml');

                $this->awsService->delete($cloudDestination);
                $this->awsService->upload($sitemapPathForUpload, $cloudDestination);
                $this->filesystem->remove(self::FILE_USED_STATES);
                $this->filesystem->remove(self::FILE_FILENAMES);
                $this->logger->alert('File uploaded and removed');
                return 1;
            }
        } else {
            $data = [];
            $stateOrProvinces = $this->listingRepository->getStateOrProvinces();
            foreach ($stateOrProvinces as $stateOrProvince) {
                $data[] = $stateOrProvince[1];
            }
            file_put_contents(self::FILE_USED_STATES, json_encode($data));
            $this->logger->alert('File generated');
            return 1;
        }

        $filenames = [];

        try {
            if (!$this->appUrl) {
                $this->logger->error('Need to fill APP_URL in .env file');
                return Command::FAILURE;
            }
            $context = $this->router->getContext();
            $context->setBaseUrl($this->appUrl);

            $filenames = $this->sitemapService->generateListingImageUrls($sitemapPathForUpload, $stateOrProvince, $filenames);

            if ($this->filesystem->exists(self::FILE_FILENAMES)) {
                $existFilenames = json_decode(file_get_contents(self::FILE_FILENAMES));
                $filenames = array_merge($existFilenames, $filenames);
            }
            file_put_contents(self::FILE_FILENAMES, json_encode($filenames));
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
        } finally {
            $stateOrProvinces = json_decode(file_get_contents(self::FILE_USED_STATES));
            array_shift($stateOrProvinces);
            file_put_contents(self::FILE_USED_STATES, json_encode($stateOrProvinces));
            $this->filesystem->remove($sitemapPathForUpload);
            $this->logger->alert('Command generate-image-sitemap completed');
        }

        return Command::SUCCESS;
    }
}
