<?php

namespace Mindy\Orm\Traits;

/**
 * Class UniqueUrl
 * @package Mindy\Orm
 */
trait UniqueUrl
{
    public function uniqueUrl($url, $count = 0, $pk = null)
    {
        /* @var $model \Mindy\Orm\Model */
        $model = $this->getModel();
        $newUrl = $url;
        if ($count) {
            $newUrl .= '-' . $count;
        }

        $qs = $model::objects()->filter([$this->getName() => $newUrl]);
        if ($pk) {
            $qs = $qs->exclude(['pk' => $pk]);
        }
        if ($qs->count() > 0) {
            $count++;
            return $this->uniqueUrl($url, $count, $pk);
        }
        return $newUrl;
    }

} 