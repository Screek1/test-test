<?php
/**
 * Created by TutMee Co.
 * User: Domenik88(kataevevgenii@gmail.com)
 * Date: 22.09.2020
 *
 * @package viksemenov20
 */

namespace App\Service;


use App\Service\Provider\AwsProvider;
use Aws\S3\Transfer;
use Psr\Log\LoggerInterface;

class AwsService
{
    private AwsProvider $awsProvider;
    private LoggerInterface $logger;

    private string $ESBLApiEndpoint;
    private string $bucket;

    public function __construct(
        AwsProvider     $awsProvider,
        string          $ESBLApiEndpoint,
        string          $bucket,
        LoggerInterface $logger
    )
    {
        $this->awsProvider = $awsProvider;
        $this->bucket = $bucket;
        $this->ESBLApiEndpoint = $ESBLApiEndpoint;
        $this->logger = $logger;
    }

    public function upload(string $source, string $destination)
    {
        try {
            // Where the files will be transferred to
            $dest = $this->ESBLApiEndpoint . $destination;
            $uploader = new Transfer($this->awsProvider->getClient(), $source, $dest, [
                'before' => function (\Aws\Command $command) {
                    // Commands can vary for multipart uploads, so check which command
                    // is being processed
                    if (in_array($command->getName(), ['PutObject', 'CreateMultipartUpload'])) {
                        // Set custom cache-control metadata
                        $command['CacheControl'] = 'max-age=3600';
                        // Apply a canned ACL
                        $command['ACL'] = 'public-read';
                    }
                },
            ]);
            $uploader->transfer();
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
        unset($uploader);
    }

    public function delete(string $prefix)
    {
        $list = $this->awsProvider->getClient()->listObjects([
            'Bucket' => $this->bucket,
            'Prefix' => $prefix,
        ]);

        if (isset($list['Contents']) && count($list['Contents'])) {
            foreach ($list['Contents'] as $key) {
                $this->awsProvider->getClient()->deleteObjects([
                    'Bucket' => $this->bucket,
                    'Delete' => [
                        'Objects' => [
                            [
                                'Key' => $key['Key'],
                            ],
                        ],
                    ],
                ]);
            }
        }
    }

}