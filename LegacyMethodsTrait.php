<?php

/*
 * (c) Studio107 <mail@studio107.ru> http://studio107.ru
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * Author: Maxim Falaleev <max@studio107.ru>
 */

namespace Mindy\Orm;

use Mindy\Application\App;

trait LegacyMethodsTrait
{
    /**
     * @return string
     */
    public function getVerboseName()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 3.0 and will be removed in 4.0.', E_USER_DEPRECATED);

        return $this->getShortName();
    }

    /**
     * @param $route
     * @param array $data
     *
     * @return string
     */
    public function reverse($route, array $data = [])
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 3.0 and will be removed in 4.0.', E_USER_DEPRECATED);

        return App::getInstance()->getComponent('router')->trans($route, $data);
    }

    /**
     * @param $id
     * @param array $parameters
     * @param null  $domain
     * @param null  $locale
     *
     * @return string
     */
    public static function t($id, array $parameters = [], $domain = null, $locale = null)
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 3.0 and will be removed in 4.0.', E_USER_DEPRECATED);

        return App::getInstance()->getComponent('translator')->trans($id, $parameters, $domain, $locale);
    }
}
