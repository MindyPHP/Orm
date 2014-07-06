<?php
/**
 *
 *
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 18/04/14.04.2014 19:29
 */

namespace Mindy\Orm\Traits;

use Imagine\Image\Box;
use Imagine\Image\ImagineInterface;
use Imagine\Image\ManipulatorInterface;
use Imagine\Image\Point;
use Mindy\Exception\Exception;


trait ImageProcess
{
    /**
     * GD2 driver definition for Imagine implementation using the GD library.
     */
    public static $DRIVER_GD2 = 'gd2';
    /**
     * imagick driver definition.
     */
    public static $DRIVER_IMAGICK = 'imagick';
    /**
     * gmagick driver definition.
     */
    public static $DRIVER_GMAGICK = 'gmagick';

    /**
     * @var ImagineInterface instance.
     */
    private static $_imagine;

    /**
     * Returns the `Imagine` object that supports various image manipulations.
     * @return ImagineInterface the `Imagine` object
     */
    public static function getImagine()
    {
        if (self::$_imagine === null) {
            self::$_imagine = static::createImagine();
        }

        return self::$_imagine;
    }

    /**
     * Creates an `Imagine` object based on the specified [[driver]].
     * @return ImagineInterface the new `Imagine` object
     * @throws Exception if [[driver]] is unknown or the system doesn't support any [[driver]].
     */
    protected static function createImagine()
    {
        $drivers = [self::$DRIVER_GMAGICK, self::$DRIVER_IMAGICK, self::$DRIVER_GD2];
        foreach ((array)$drivers as $driver) {
            switch ($driver) {
                case self::$DRIVER_GMAGICK:
                    if (class_exists('Gmagick', false)) {
                        return new \Imagine\Gmagick\Imagine();
                    }
                    break;
                case self::$DRIVER_IMAGICK:
                    if (class_exists('Imagick', false)) {
                        return new \Imagine\Imagick\Imagine();
                    }
                    break;
                case self::$DRIVER_GD2:
                    if (function_exists('gd_info')) {
                        return new \Imagine\Gd\Imagine();
                    }
                    break;
                default:
                    throw new Exception("Unknown driver: $driver");
            }
        }
        throw new Exception("Your system does not support any of these drivers: " . implode(',', $drivers));
    }

    public function resize($img, $width, $height, $method)
    {
        $box = new Box($width, $height);

        $imgBox = $img->getSize();

        if (($imgBox->getWidth() <= $box->getWidth() && $imgBox->getHeight() <= $box->getHeight()) || (!$box->getWidth() && !$box->getHeight())) {
            return $img->copy();
        }

        $thumb = null;
        if ($method == 'resize') {
            $thumb = $img->thumbnail($box, ManipulatorInterface::THUMBNAIL_INSET);
        } elseif ($method == 'adaptiveResize') {
            $thumb = $img->thumbnail($box, ManipulatorInterface::THUMBNAIL_OUTBOUND);
        } elseif ($method == 'adaptiveResizeFromTop') {
            $fromWidth = $imgBox->getWidth();
            $fromHeight = $imgBox->getHeight();

            $toWidth = $box->getWidth();
            $toHeight = $box->getHeight();

            $fromPercent = $fromWidth / $fromHeight;
            $toPercent = $toWidth / $toHeight;

            if ($toPercent >= $fromPercent) {
                $resizeWidth = $toWidth;
                $resizeHeight = round($toWidth / $fromWidth * $fromHeight);
                $thumb = $img
                    ->resize(new Box($resizeWidth, $resizeHeight))
                    ->crop(
                        new Point(0, 0),
                        new Box($toWidth, $toHeight)
                    );
            } else {
                $thumb = $img->thumbnail($box, ManipulatorInterface::THUMBNAIL_OUTBOUND);
            }
        }

        return $thumb;
    }

    public function applyWatermark($source, $options)
    {
        if ($options && is_array($options) && isset($options['file']) && isset($options['position'])) {
            $watermark = $this->getImagine()->open($options['file']);
            $position = $options['position'];

            $x = 0;
            $y = 0;

            $wSize = $watermark->getSize();
            $sSize = $source->getSize();

            $wWidth = $wSize->getWidth();
            $wHeight = $wSize->getHeight();

            $sWidth = $sSize->getWidth();
            $sHeight = $sSize->getHeight();


            if (is_array($position)) {
                list($x, $y) = $position;
            } else {
                switch ($position) {
                    case 'top':
                        $x = $sWidth / 2 - $wWidth / 2;
                        $y = 0;
                        break;
                    case 'bottom':
                        $x = $sWidth / 2 - $wWidth / 2;
                        $y = $sHeight - $wHeight;
                        break;
                    case 'center':
                        $x = $sWidth / 2 - $wWidth / 2;
                        $y = $sHeight / 2 - $wHeight / 2;
                        break;
                    case 'left':
                        $x = 0;
                        $y = $sHeight / 2 - $wHeight / 2;
                        break;
                    case 'right':
                        $x = $sWidth - $wWidth;
                        $y = $sHeight / 2 - $wHeight / 2;
                        break;
                    case 'top-left':
                        $x = 0;
                        $y = 0;
                        break;
                    case 'top-right':
                        $x = $sWidth - $wWidth;
                        $y = 0;
                        break;
                    case 'bottom-left':
                        $x = 0;
                        $y = $sHeight - $wHeight;
                        break;
                    case 'bottom-right':
                        $x = $sWidth - $wWidth;
                        $y = $sHeight - $wHeight;
                        break;
                }
                if ($x < 0) {
                    $x = 0;
                }
                if ($y < 0) {
                    $y = 0;
                }
            }
            if (($x + $wWidth <= $sWidth) || ($y + $wHeight <= $sHeight))
                return $source->paste($watermark, new Point($x, $y));
        }
        return $source;
    }
}
