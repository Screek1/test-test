<?php
/**
 * Created by TutMee Co.
 * User: Domenik88(kataevevgenii@gmail.com)
 * Date: 23.09.2020
 *
 * @package viksemenov20
 */

namespace App\Service\Provider;


use Aws\Credentials\Credentials;
use Aws\S3\S3Client;

class AwsProvider
{
    protected Credentials $credentials;
    protected S3Client $s3Client;

    protected string $key;
    protected string $secret;
    protected string $region;
    protected string $endpoint;
    protected string $bucket;

    public function __construct(
        string $key,
        string $secret,
        string $region,
        string $endpoint,
        string $bucket
    )
    {
        $this->key = $key;
        $this->secret = $secret;
        $this->region = $region;
        $this->endpoint = $endpoint;
        $this->bucket = $bucket;
    }

    private function connect()
    {
        $this->credentials = new Credentials($this->key, $this->secret);

        $options = [
            'version' => 'latest',
            'region' => $this->region,
            'endpoint' => $this->endpoint,
            'credentials' => $this->credentials,
        ];
        $this->s3Client = new S3Client($options);
    }

    public function getClient(): S3Client
    {
        if (!isset($this->s3Client)) {
            $this->connect();
        }
        return $this->s3Client;
    }

    public function getBucket()
    {
        return $this->bucket;
    }

    public function getKeyName($destination = null)
    {
        if (!is_null($destination)) {
            return $destination;
        }
        return '';
    }

}