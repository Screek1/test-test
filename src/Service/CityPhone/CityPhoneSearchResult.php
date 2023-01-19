<?php
declare(strict_types=1);

namespace App\Service\CityPhone;

class CityPhoneSearchResult
{
    public int $total;
    public array $results;
    public int $currentPage;
    public int $pagesCount;

    public function __construct(int $total, array $results, int $currentPage, int $pagesCount)
    {
        $this->total = $total;
        $this->results = $results;
        $this->currentPage = $currentPage;
        $this->pagesCount = $pagesCount;
    }
}