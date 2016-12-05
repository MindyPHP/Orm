<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 05/12/2016
 * Time: 21:39
 */

namespace Mindy\Component\Pagination\Handler;

/**
 * Interface PaginationHandlerInterface
 * @package Mindy\Component\Pagination\Handler
 */
interface PaginationHandlerInterface
{
    /**
     * @param $key
     * @param $defaultPageSize
     * @return int
     */
    public function getPageSize($key, $defaultPageSize);

    /**
     * @param $key
     * @param int $defaultPage
     * @return int
     */
    public function getPage($key, $defaultPage = 1);

    /**
     * @param $key
     * @param $value
     * @return string
     */
    public function getUrlForQueryParam($key, $value);

    /**
     * @param callable $callback
     */
    public function setIncorrectPageCallback(callable $callback);

    /**
     * Throw exception or redirect user to correct page
     * Example for redirect:
     * function ($handler) {
     *      header("Location: " . $handler->getUrl(1));
     *      exit();
     * }
     *
     * or throw not found exception:
     * function ($handler) {
     *      throw new NotFoundHttpException();
     * }
     */
    public function wrongPageCallback();
}