<?php


namespace App\Service\User;


class AdminUsersResult
{
    public int $listingCount;
    public array $results;
    public int $currentPage;
    public int $pagesCount;

    public function __construct(int $listingCount, array $results, int $currentPage, int $pagesCount)
    {
        $this->listingCount = $listingCount;
        $this->results = $results;
        $this->currentPage = $currentPage;
        $this->pagesCount = $pagesCount;

    }
}