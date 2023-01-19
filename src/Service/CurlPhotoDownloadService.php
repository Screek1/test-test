<?php
/**
 * Created by TutMee Co.
 * User: Domenik88(kataevevgenii@gmail.com)
 * Date: 28.09.2020
 *
 * @package viksemenov20
 */

namespace App\Service;


use App\Service\Listing\ListingImageResizeService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Imagine\Imagick\Imagine;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

class CurlPhotoDownloadService
{
    private Filesystem $filesystem;
    private LoggerInterface $logger;
    private ListingImageResizeService $imageResizeService;

    public function __construct(
        Filesystem $filesystem,
        LoggerInterface $logger,
        ListingImageResizeService $imageResizeService
    ) {
        $this->filesystem = $filesystem;
        $this->logger = $logger;
        $this->imageResizeService = $imageResizeService;
    }

    /**
     * @param array $photoUrls
     * @param string $destination
     * @param string $baseFileName
     * @return array
     * @throws \Exception
     * @throws GuzzleException
     */
    public function photoDownload(array $photoUrls, string $destination, string $baseFileName): array
    {
        $photosCounter = 1;
        $photoNamesArray = [];
        $client = new Client();
        $this->logger->info('Temp image folder');
        foreach ($photoUrls as $photoUrl) {
            if ($photoUrl) {
                try {
                    $fullFileName = $baseFileName . '-' . $photosCounter . '.jpg';
                    $fullFilePath = $destination . $fullFileName;

                    $client->request('GET', $photoUrl, ['sink' => $fullFilePath]);
                    if (file_exists($fullFilePath)) {
                        $imagine = new Imagine();
                        $size = $imagine->open($fullFilePath)->getSize();
                        if ($size->getWidth() > 1200 || $size->getHeight() > 1200) {
                            $this->imageResizeService->resizeImage($fullFilePath);
                        }
                        unset($imagine);

                        $this->logger->info('Downloaded image :: ' . $fullFileName);
                        $photoNamesArray[$photosCounter] = $fullFileName;
                    } else {
                        $this->logger->warning('failed-to-download-image :: ' . $photoUrl);
                    }
                } catch (\Exception $e) {
                    $this->logger->error($e->getMessage(), $e->getTrace());
                    $this->logger->error('failed-to-download-image :: ' . $photoUrl);
                } finally {
                    $photosCounter++;
                }
            }
        }

        return $photoNamesArray;
    }

}