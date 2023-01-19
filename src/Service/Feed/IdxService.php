<?php


namespace App\Service\Feed;


use App\Entity\Listing;
use App\Exception\ListingNotFoundException;
use App\Service\CurlPhotoDownloadService;
use Carbon\Carbon;
use PHRETS\Configuration;
use PHRETS\Session;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class IdxService
{
    private LoggerInterface $logger;
    private ?Session $rets;
    private CurlPhotoDownloadService $curlPhotoDownloadService;

    private string $idxLoginUrl;
    private string $idxUsername;
    private string $idxPassword;
    private string $idxUserAgent;
    private string $idxUserAgentPassword;
    private string $idxRetsVersion;
    private ?string $classId = null;

    public function __construct(
        LoggerInterface          $logger,
        CurlPhotoDownloadService $curlPhotoDownloadService,
        string                   $idxLoginUrl,
        string                   $idxUsername,
        string                   $idxPassword,
        string                   $idxUserAgent,
        string                   $idxUserAgentPassword,
        string                   $idxRetsVersion
    )
    {
        $this->logger = $logger;
        $this->curlPhotoDownloadService = $curlPhotoDownloadService;
        $this->rets = null;
        $this->idxLoginUrl = $idxLoginUrl;
        $this->idxUsername = $idxUsername;
        $this->idxPassword = $idxPassword;
        $this->idxUserAgent = $idxUserAgent;
        $this->idxUserAgentPassword = $idxUserAgentPassword;
        $this->idxRetsVersion = $idxRetsVersion;
    }

    public function connect()
    {
        if (!$this->rets) {
            $config = new Configuration;
            $config->setLoginUrl($this->idxLoginUrl)
                ->setUsername($this->idxUsername)
                ->setPassword($this->idxPassword)
                ->setUserAgent($this->idxUserAgent)
                ->setUserAgentPassword($this->idxUserAgentPassword)
                ->setHttpAuthenticationMethod(Configuration::AUTH_BASIC)
                ->setRetsVersion($this->idxRetsVersion);

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

    public function searchUpdatedListings(\DateTimeInterface $date, $classId, $offset = null, $limit = 100)
    {
        $this->connect();
        $results = $this->rets->Search(
            'Property',
            $classId,
            "L_UpdateDate=" . $date->format('Y-m-d\TH:i:s') . "+",
            ['Format' => 'COMPACT-DECODED', 'Limit' => $limit, 'Offset' => $offset]
        );
        $totalRecordsCount = $results->getTotalResultsCount();
        $nextRecordOffset = $offset + $results->getReturnedResultsCount();
        $moreAvailable = $results->isMaxRowsReached();

        return new SearchResult($moreAvailable, $results->toArray(), $nextRecordOffset, $totalRecordsCount);
    }

    public function searchActiveListings($classId, $offset = null, $limit = 100)
    {
        $this->connect();
        $results = $this->rets->Search(
            'Property',
            $classId,
            "*",
            ['Format' => 'COMPACT-DECODED', 'Limit' => $limit, 'Offset' => $offset]
        );
        $totalRecordsCount = $results->getTotalResultsCount();
        $nextRecordOffset = $offset + $results->getReturnedResultsCount();
        $moreAvailable = $results->isMaxRowsReached();

        return new SearchResult($moreAvailable, $results->toArray(), $nextRecordOffset, $totalRecordsCount);
    }

    public function getMasterList($classId): array
    {
        $this->connect();
        $this->classId = $classId;
        $results = $this->rets->Search('Property', $classId, '(L_Status=|1_0,1_1)', ['Limit' => null, 'Select' => 'L_ListingID,L_UpdateDate']);
        $this->logger->log(LogLevel::INFO, 'listing-master-total-count :: ' . $results->count());

        return array_map(array($this, 'toMasterListItem'), $results->toArray());
    }

    public function getListingById($listingId): array
    {
        $this->connect();
        $result = $this->rets->Search('Property', 'Property', 'ID=' . $listingId);

        return $result->toArray();
    }

    public function toMasterListItem(array $listItem): MasterListItem
    {
        return new MasterListItem(
            $listItem['L_ListingID'],
            \DateTime::createFromFormat('Y-m-d\TH:i:s', Carbon::parse($listItem['L_UpdateDate'])->format('Y-m-d\TH:i:s')),
            $this->classId
        );
    }

    public function getListingByFeedListingId(Listing $listing): ?array
    {
        $this->connect();
        $result = $this->rets->Search('Property', $listing->getClassID(), 'L_ListingID=' . $listing->getFeedListingID(), ['Limit' => null]);

        if (!$result->first()) {
            throw new ListingNotFoundException("Listing record not found! listingFeedId: {$listing->getFeedListingID()}");
        }

        $data = $result->first()->toArray();

        unset($data['AnalyticsClick'], $data['AnalyticsView']);

        return $data;
    }

    public function fetchListingPhotosFromFeed(Listing $listing, string $destination): array
    {
        $this->connect();
        $results = $this->rets->GetObject('Property', 'Photo', $listing->getFeedListingID(), '*', 1);
        $photoUrls = $this->getPhotoUrls($results);
        $listingFullAddress = str_replace(' ', '-', preg_replace('/[^a-z\d]/ui', '-', $listing->getFullAddress()));
        $baseFilename = $listing->getMlsNum() . '-' . $listingFullAddress;
        $photoNamesArray = $this->curlPhotoDownloadService->photoDownload($photoUrls, $destination, $baseFilename);

        return $photoNamesArray;
    }

    private function getPhotoUrls($results)
    {
        $photoUrls = [];
        foreach ($results as $result) {
            $photoUrls[] = substr($result->getLocation(), 2);
        }
        return $photoUrls;
    }
}