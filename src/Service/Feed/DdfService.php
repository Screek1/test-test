<?php
/**
 * Created by TutMee Co.
 * User: Domenik88(kataevevgenii@gmail.com)
 * Date: 16.09.2020
 *
 * @package viksemenov20
 */

namespace App\Service\Feed;

use App\Entity\Listing;
use App\Exception\ListingNotFoundException;
use App\Service\CurlPhotoDownloadService;
use PHRETS\Configuration;
use PHRETS\Exceptions\CapabilityUnavailable;
use PHRETS\Parsers\XML;
use PHRETS\Session;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class DdfService
{
    private LoggerInterface $logger;
    private ?Session $rets;
    private CurlPhotoDownloadService $curlPhotoDownloadService;

    private string $ddfLoginURL;
    private string $ddfUsername;
    private string $ddfPassword;
    private string $ddfRetsVersion;

    public function __construct(
        LoggerInterface $logger,
        CurlPhotoDownloadService $curlPhotoDownloadService,
        string $ddfLoginURL,
        string $ddfUsername,
        string $ddfPassword,
        string $ddfRetsVersion
    ){
        $this->logger = $logger;
        $this->curlPhotoDownloadService = $curlPhotoDownloadService;
        $this->rets = null;
        $this->ddfLoginURL = $ddfLoginURL;
        $this->ddfUsername = $ddfUsername;
        $this->ddfPassword = $ddfPassword;
        $this->ddfRetsVersion = $ddfRetsVersion;
    }

    public function connect()
    {
        if (!$this->rets) {
            $config = new Configuration;
            $config->setLoginUrl($this->ddfLoginURL)
                ->setUsername($this->ddfUsername)
                ->setPassword($this->ddfPassword)
                ->setRetsVersion($this->ddfRetsVersion);

            $this->rets = new Session($config);
            $this->rets->Login();
        }
    }

    public function __destruct()
    {
        $this->logger->info('Disconnecting from RETS!');
        if ($this->rets) {
            $this->rets->Disconnect();
            $this->rets = null;
        }
    }

    public function searchUpdatedListings(\DateTimeInterface $date, $offset = null, $limit = 100)
    {
        $this->connect();
        $results = $this->rets->Search(
            'Property',
            'Property',
            'LastUpdated='.$date->format('Y-m-d\TH:i:s\Z'),
            ['Format' => 'COMPACT-DECODED', 'Limit' => $limit, 'Offset' => $offset]
        );

        $totalRecordsCount = $results->getTotalResultsCount();
        $nextRecordOffset = $offset + $results->getReturnedResultsCount();
        $moreAvailable = $results->isMaxRowsReached();

        return new SearchResult($moreAvailable, $results->toArray(), $nextRecordOffset, $totalRecordsCount);
    }

    public function getMasterList(): array
    {
        $this->connect();
        $results = $this->rets->Search('Property', 'Property', 'ID=*', ['Limit' => null]);
        $this->logger->log(LogLevel::INFO, 'listing-master-total-count :: '.$results->count());

        return array_map(array($this, 'toMasterListItem'), $results->toArray());
    }

    public function getListingById($listingId): array
    {
        $this->connect();
        $result = $this->rets->Search('Property', 'Property', 'ID='.$listingId);

        return $result->toArray();
    }

    public function toMasterListItem(array $listItem): MasterListItem
    {
        return new MasterListItem(
            $listItem['ListingKey'],
            \DateTime::createFromFormat('d/m/Y H:i:s A', $listItem['ModificationTimestamp'])
        );
    }

    /**
     * @param Listing $listing
     * @param string $destination
     * @return array
     * @throws CapabilityUnavailable
     * @throws \Exception
     */
    public function fetchListingPhotosFromFeed(Listing $listing, string $destination): array
    {
        $this->connect();
        $results = $this->rets->getObject('Property', 'LargePhoto', $listing->getFeedListingID());

        $result = $results[0]->getContent();
        $res = new XML();
        $tmp = $res->parse($result);
        $photoUrls = array_map([$this, 'extractImageUrl'], (array)$tmp->DATA);
        $listingFullAddress = str_replace(' ', '-', preg_replace('/[^a-z\d]/ui', '-', $listing->getFullAddress()));
        $baseFilename = $listing->getMlsNum().'-'.$listingFullAddress;
        $photoNamesArray = $this->curlPhotoDownloadService->photoDownload($photoUrls, $destination, $baseFilename);

        return $photoNamesArray;
    }

    public function extractImageUrl(string $imgDataString): ?string
    {
        return explode("\t", $imgDataString)[3];
    }

    /**
     * @param $feedListingId
     * @return array|null
     * @throws ListingNotFoundException
     * @throws CapabilityUnavailable
     */
    public function getListingByFeedListingId($feedListingId): ?array
    {
        $this->connect();
        $result = $this->rets->Search('Property', 'Property', 'ID='.$feedListingId, ['Limit' => null]);

        if (!$result->first()) {
            throw new ListingNotFoundException("Listing record not found! listingFeedId: {$feedListingId}");
        }

        $data = $result->first()->toArray();

        unset($data['AnalyticsClick'], $data['AnalyticsView']);

        return $data;
    }

}