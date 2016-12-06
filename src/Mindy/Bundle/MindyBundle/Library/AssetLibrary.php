<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 20/10/2016
 * Time: 20:35
 */

namespace Mindy\Bundle\MindyBundle\Library;

use Symfony\Component\Asset\Packages;
use Mindy\Template\Library;

class AssetLibrary extends Library
{
    /**
     * @var Packages
     */
    private $packages;

    /**
     * AssetLibrary constructor.
     * @param Packages $packages
     */
    public function __construct(Packages $packages)
    {
        $this->packages = $packages;
    }

    /**
     * Returns the public url/path of an asset.
     *
     * If the package used to generate the path is an instance of
     * UrlPackage, you will always get a URL and not a path.
     *
     * @param string $path        A public path
     * @param string $packageName The name of the asset package to use
     *
     * @return string The public path of the asset
     */
    public function getUrl($path, $packageName = null)
    {
        return $this->packages->getUrl($path, $packageName);
    }

    /**
     * Returns the version of an asset.
     *
     * @param string $path        A public path
     * @param string $packageName The name of the asset package to use
     *
     * @return string The asset version
     */
    public function getVersion($path, $packageName = null)
    {
        return $this->packages->getVersion($path, $packageName);
    }

    /**
     * @return array
     */
    public function getHelpers()
    {
        return [
            'asset' => function ($path, $packageName = null) {
                return $this->getUrl($path, $packageName);
            },
            'asset_version' => function ($path, $packageName = null) {
                return $this->getVersion($path, $packageName);
            }
        ];
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return [];
    }
}