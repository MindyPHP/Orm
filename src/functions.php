<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 04/10/16
 * Time: 20:04
 */

namespace Mindy;

/**
 * @param bool $throw
 * @return App
 */
function app($throw = true)
{
    return App::getInstance($throw);
}

/**
 * @deprecated
 * @param $id
 * @param array $parameters
 * @param null $domain
 * @param null $locale
 * @return mixed
 */
function trans($id, array $parameters = array(), $domain = null, $locale = null)
{
    return app()->getContainer()->get('translator')->trans($id, $parameters, $domain, $locale);
}

/**
 * @deprecated
 * @param $id
 * @param $number
 * @param array $parameters
 * @param null $domain
 * @param null $locale
 * @return mixed
 */
function transChoice($id, $number, array $parameters = array(), $domain = null, $locale = null)
{
    return app()->getContainer()->get('translator')->trans($id, $number, $parameters, $domain, $locale);
}