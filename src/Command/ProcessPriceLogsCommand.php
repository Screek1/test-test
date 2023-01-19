<?php

namespace App\Command;

use App\Repository\ListingRepository;
use App\Repository\PriceLogRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class ProcessPriceLogsCommand extends Command
{
    use MicroKernelTrait;

    protected static $defaultName = 'app:process-price-logs';
    protected static $defaultDescription = 'Processes price log calculation for today';

    private Filesystem $filesystem;
    private ListingRepository $listingRepository;
    private LoggerInterface $logger;
    private PriceLogRepository $priceLogRepository;

    const batch = 100;
    const pIdFile = './var/processPriceLogsPid';

    const maxIterations = 50;

    public function __construct(Filesystem $filesystem, ListingRepository $listingRepository, LoggerInterface $logger, PriceLogRepository $priceLogRepository)
    {
        parent::__construct();

        $this->filesystem = $filesystem;
        $this->listingRepository = $listingRepository;
        $this->logger = $logger;
        $this->priceLogRepository = $priceLogRepository;
    }

    protected function configure()
    {
        $this
            ->setDescription(self::$defaultDescription)
//            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
//            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
//        if ($this->filesystem->exists(self::pIdFile)) {
//            $this->logger->info('Processing is running');
//
//            return Command::SUCCESS;
//        }

        $this->priceLogRepository->createForAllApplicable();

        $this->logger->info("Done!");

        return Command::SUCCESS;

//        try {
//            $this->filesystem->touch(self::pIdFile);
//            register_shutdown_function(array($this, 'shutdown'));
//
//            $batch = self::batch;
//
//            $count = 0;
//            while (count(
//                    $this->listingRepository->getListingWithMissingPriceLog($batch)
//                ) > 0 && $count < self::maxIterations) {
//                $result = shell_exec("./bin/console app:create-price-log-batch $batch -vv");
//                $this->logger->info($result);
//                $count++;
//            }
//
//            return Command::SUCCESS;
//        } catch (\Exception $e) {
//            $this->logger->error($e->getMessage());
//
//            return Command::FAILURE;
//        } finally {
//            $this->filesystem->remove(self::pIdFile);
//        }
    }

    function shutdown()
    {
        $this->filesystem->remove(self::pIdFile);
    }
}
