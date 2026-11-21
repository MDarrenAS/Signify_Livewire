<?php

namespace App\Support;

use Aws\ResultInterface;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use GuzzleHttp\Promise\PromiseInterface;

class SupabaseS3Client extends S3Client
{
    /**
     * @param  array<string, mixed>  $args
     */
    public function __construct(array $args)
    {
        $args['service'] = 's3';
        $args['exception_class'] = S3Exception::class;

        parent::__construct($args);
    }

    /**
     * Supabase Storage rejects S3 ACL parameters; bucket privacy is configured separately.
     *
     * @param  array<string, mixed>  $options
     */
    public function upload($bucket, $key, $body, $acl = 'private', array $options = []): ResultInterface
    {
        return parent::upload($bucket, $key, $body, null, $options);
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function uploadAsync($bucket, $key, $body, $acl = 'private', array $options = []): PromiseInterface
    {
        return parent::uploadAsync($bucket, $key, $body, null, $options);
    }
}
