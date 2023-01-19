<?php

namespace App\Command;

use App\Repository\ListingRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

const pIdIdxFile = './var/idxListingWatchPid';


class WatchIdxListingsChangesCommand extends Command
{
    use MicroKernelTrait;

    protected static $defaultName = 'app:watch-idx-listings-changes';
    protected static string $defaultDescription = 'Watches for any ddf listings that needs processing and executes the processing';

    private Filesystem $filesystem;
    private ListingRepository $listingRepository;
    private LoggerInterface $logger;

    const maxPerBatch = 500;

    public function __construct(Filesystem $filesystem, ListingRepository $listingRepository, LoggerInterface $logger)
    {
        parent::__construct();

        $this->filesystem = $filesystem;
        $this->listingRepository = $listingRepository;
        $this->logger = $logger;
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
        if ($this->filesystem->exists(pIdIdxFile)) {
            $this->logger->info('Watch is running');

            return Command::SUCCESS;
        }

        try {
            $this->filesystem->touch(pIdIdxFile);
            register_shutdown_function(array($this, 'watchShutdown'));
            $count = 0;
            while ($this->listingRepository->getListingCountForProcessing() > 0 && $count < self::maxPerBatch) {

                $result = shell_exec('./bin/console app:processing-single-idx-listing 1 -vv');
                sleep(8);

                if ($result) {
                    $this->logger->alert($result);
                    $count++;
                } else {
                    $this->logger->info('false');
                }
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());

            return Command::FAILURE;
        } finally {
            $this->filesystem->remove(pIdIdxFile);
        }

    }

    function watchShutdown()
    {
        $this->filesystem->remove(pIdIdxFile);
    }
}
