<?php

/**
 * Created by PhpStorm.
 * User: max
 * Date: 12/09/16
 * Time: 15:18
 */

namespace Mindy\Orm\Flysystem;

use Mimibox\Mimibox;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Plugin\AbstractPlugin;

class UrlPlugin extends AbstractPlugin
{
    /**
     * @var string
     */
    protected $baseUrl = '';

    /**
     * CloudPlugin constructor.
     * @param $baseUrl
     */
    public function __construct($baseUrl = '/media/')
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /**
     * Get the method name.
     *
     * @return string
     */
    public function getMethod()
    {
        return 'url';
    }

    /**
     * @param null $path
     * @param array $params
     * @return string
     */
    public function handle($path = null, array $params = [])
    {
        $adapter = $this->filesystem->getAdapter();

        if ($adapter instanceof AwsS3Adapter) {
            return sprintf('https://s3.amazonaws.com/%s/%s', $adapter->getBucket(), $path);
        } else if ($adapter instanceof Local) {
            return sprintf('%s/%s', $this->baseUrl, ltrim($path, '/'));
        } else if ($adapter instanceof Mimibox) {
            return $adapter->url($path, $params);
        } else {
            return '?';
        }
    }
}