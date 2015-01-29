<?php
/**
 *
 *
 * All rights reserved.
 *
 * @author Okulov Anton
 * @email qantus@mail.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 27/01/15 16:59
 */

namespace Mindy\Orm\Traits;


trait UniqueUrl {
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