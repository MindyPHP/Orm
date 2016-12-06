<?php

namespace Mindy\Component\Template;

use Countable;
use Mindy\Component\Template\Helper\RangeIterator;
use Traversable;

/**
 * Class Helper
 * @package Mindy\Component\Template
 */
class Helper
{
    static $encoding = 'UTF-8';

    public static function method_exists($obj = null, $method)
    {
        if ($obj === null) {
            return false;
        }

        return method_exists($obj, $method);
    }

    public static function implode($obj = null, $glue)
    {
        if ($obj === null) {
            return [];
        }

        return implode($glue, $obj);
    }

    public static function explode($obj = null, $delimiter)
    {
        if ($obj === null) {
            return [];
        }

        return explode($delimiter, $obj);
    }

    public static function abs($obj = null)
    {
        return abs(intval($obj));
    }

    public static function slice($obj = null, $start, $length)
    {
        if (is_array($obj)) {
            return array_slice($obj, $start, $length);
        } elseif (is_string($obj)) {
            return mb_substr($obj, $start, $length, self::$encoding);
        }
        return null;
    }

    public static function startswith($obj = null, $needle)
    {
        return mb_strpos((string) $obj, $needle, 0, self::$encoding) === 0;
    }

    public static function contains($obj = null, $needle)
    {
        return mb_strpos((string) $obj, $needle, 0, self::$encoding) !== false;
    }

    public static function bytes($obj = null, $decimals = 1, $dec = '.', $sep = ',')
    {
        $obj = max(0, intval($obj));
        $places = strlen($obj);
        if ($places <= 9 && $places >= 7) {
            $obj = number_format($obj / 1048576, $decimals, $dec, $sep);
            return "$obj MB";
        } elseif ($places >= 10) {
            $obj = number_format($obj / 1073741824, $decimals, $dec, $sep);
            return "$obj GB";
        } else {
            $obj = number_format($obj / 1024, $decimals, $dec, $sep);
            return "$obj KB";
        }
    }

    public static function capitalize($obj)
    {
        $str = (string) $obj;
        return mb_strtoupper(mb_substr($str, 0, 1, self::$encoding), self::$encoding) . mb_strtolower(mb_substr($str, 1, mb_strlen($str), self::$encoding), self::$encoding);
    }

    public static function cycle($obj = null)
    {
        $obj = ($obj instanceof Traversable) ? iterator_to_array($obj) : (array) $obj;
        return new Helper\Cycler((array) $obj);
    }

    public static function time($obj = null)
    {
        return time();
    }

    public static function date($obj = null, $format = 'Y-m-d H:m:s')
    {
        if (!is_numeric($obj) && is_string($obj)) {
            $obj = strtotime($obj);
        }
        return date($format, $obj ? $obj : time());
    }

    public static function strtotime($obj = null)
    {
        return (string) $obj;
    }

    public static function dump($obj = null)
    {
        echo '<pre>';
        print_r($obj);
        echo '</pre>';
    }

    public static function e($obj = null, $force = false)
    {
        return self::escape($obj, $force);
    }

    public static function escape($obj = null, $force = false)
    {
        return htmlspecialchars((string) $obj, ENT_QUOTES, self::$encoding, $force);
    }

    public static function first($obj = null, $default = null)
    {
        if (is_string($obj)) {
            return strlen($obj) ? substr($obj, 0, 1) : $default;
        }
        $obj = $obj instanceof Traversable ? iterator_to_array($obj) : (array) $obj;
        $keys = array_keys($obj);
        if (count($keys)) {
            return $obj[$keys[0]];
        }
        return $default;
    }

    public static function format($obj, $args)
    {
        return call_user_func_array('sprintf', func_get_args());
    }

    public static function is_divisible_by($obj = null, $number = null)
    {
        if (!isset($number)) {
            return false;
        }
        if (!is_numeric($obj) || !is_numeric($number)) {
            return false;
        }
        if ($number == 0) {
            return false;
        }
        return fmod($obj, $number) == 0;
    }

    public static function is_empty($obj = null)
    {
        if (is_null($obj)) {
            return true;
        } elseif (is_array($obj)) {
            return empty($obj);
        } elseif (is_string($obj)) {
            return strlen($obj) == 0;
        } elseif ($obj instanceof Countable) {
            return count($obj) ? false : true;
        } elseif ($obj instanceof Traversable) {
            return iterator_count($obj);
        } else {
            return false;
        }
    }

    public static function is_even($obj = null)
    {
        if (is_scalar($obj) || is_null($obj)) {
            $obj = is_numeric($obj) ? intval($obj) : strlen($obj);
        } elseif (is_array($obj)) {
            $obj = count($obj);
        } elseif ($obj instanceof Traversable) {
            $obj = iterator_count($obj);
        } else {
            return false;
        }
        return abs($obj % 2) == 0;
    }

    public static function is_odd($obj = null)
    {
        if (is_scalar($obj) || is_null($obj)) {
            $obj = is_numeric($obj) ? intval($obj) : strlen($obj);
        } elseif (is_array($obj)) {
            $obj = count($obj);
        } elseif ($obj instanceof Traversable) {
            $obj = iterator_count($obj);
        } else {
            return false;
        }
        return abs($obj % 2) == 1;
    }

    public static function join($obj = null, $glue = '')
    {
        return join($glue, ($obj instanceof Traversable) ? iterator_to_array($obj) : (array) $obj);
    }

    public static function json_encode($obj = null)
    {
        return json_encode($obj, JSON_UNESCAPED_UNICODE);
    }

    public static function keys($obj = null)
    {
        if (is_array($obj)) {
            return array_keys($obj);
        } elseif ($obj instanceof Traversable) {
            return array_keys(iterator_to_array($obj));
        }
        return null;
    }

    public static function last($obj = null, $default = null)
    {
        if (is_string($obj)) {
            return strlen($obj) ? substr($obj, -1) : $default;
        }
        $obj = ($obj instanceof Traversable) ? iterator_to_array($obj) : (array) $obj;
        $keys = array_keys($obj);
        if ($len = count($keys)) {
            return $obj[$keys[$len - 1]];
        }
        return $default;
    }

    public static function length($obj = null)
    {
        if (is_string($obj)) {
            return mb_strlen((string) $obj, self::$encoding);
        } elseif (is_array($obj) || ($obj instanceof Countable)) {
            return count($obj);
        } elseif ($obj instanceof Traversable) {
            return iterator_count($obj);
        } else {
            return null;
        }
    }

    public static function is_array($obj = null)
    {
        return is_array($obj);
    }

    public static function lower($obj = null)
    {
        return mb_strtolower((string) $obj, self::$encoding);
    }

    public static function nl2br($obj = null, $is_xhtml = false)
    {
        return nl2br((string) $obj, $is_xhtml);
    }

    public static function range($lower = null, $upper = null, $step = 1)
    {
        return new RangeIterator(intval($lower), intval($upper), intval($step));
    }

    public static function repeat($obj, $times = 2)
    {
        return str_repeat((string) $obj, $times);
    }

    public static function replace($obj = null, $search = '', $replace = '', $regex = false)
    {
        if ($regex) {
            return preg_replace($search, $replace, (string) $obj);
        } else {
            return str_replace($search, $replace, (string) $obj);
        }
    }

    public static function strip_tags($obj = null, $allowableTags = '')
    {
        return strip_tags((string) $obj, $allowableTags);
    }

    public static function title($obj = null)
    {
        return ucwords((string) $obj);
    }

    public static function trim($obj = null, $charlist = " \t\n\r\0\x0B")
    {
        return trim((string) $obj, $charlist);
    }

    public static function striptags($obj = null, $allowable_tags = null)
    {
        return strip_tags((string) $obj, $allowable_tags);
    }

    public static function truncate($obj = null, $length = 255, $preserve_words = false, $hellip = '&hellip;')
    {
        $obj = (string) $obj;
        $len = mb_strlen($obj, self::$encoding);

        if ($length >= $len) {
            return $obj;
        }

        $truncated = $preserve_words ? preg_replace('/\s+?(\S+)?$/', '', mb_substr($obj, 0, $length + 1, self::$encoding)) : mb_substr($obj, 0, $length, self::$encoding);
        return $truncated . $hellip;
    }

    public static function unescape($obj = null)
    {
        return htmlspecialchars_decode((string) $obj, ENT_QUOTES);
    }

    public static function chunk($obj = null, $by)
    {
        return $obj ? array_chunk($obj, $by) : null;
    }

    public static function upper($obj = null)
    {
        return mb_strtoupper((string) $obj, self::$encoding);
    }

    public static function url_encode($obj = null)
    {
        return urlencode((string) $obj);
    }

    public static function word_wrap($obj = null, $width = 75, $break = "\n", $cut = false)
    {
        return wordwrap((string) $obj, $width, $break, $cut);
    }

    public static function round($obj = null, $precision = 0, $type = 'common')
    {
        switch ($type) {
            case 'ceil':
                return ceil($obj);
                break;
            case 'floor':
                return floor($obj);
                break;
        }
        return round($obj, $precision);
    }

    public static function toint($obj = null)
    {
        return (int) $obj;
    }

    public static function has_key($obj = null, $key)
    {
        return array_key_exists($key, (array) $obj);
    }

    public static function call($obj = null, $method, array $args = [])
    {
        return call_user_func_array([$obj, $method], $args);
    }

    public static function merge($src = null, $dst = null)
    {
        if (!$src) {
            $src = [];
        }

        if (!$dst) {
            $dst = [];
        }

        return array_merge($src, $dst);
    }

    public static function strict_type($obj = null)
    {
        if (is_numeric($obj) && mb_strlen($obj, self::$encoding)) {
            return (bool) $obj;
        } else if (is_numeric($obj)) {
            return (int) $obj;
        } else if (is_string($obj) && in_array((string) $obj, ['true', 'false'])) {
            return (bool) $obj;
        } else {
            return (string) $obj;
        }
    }
}
