<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm\Utils;

use ArrayAccess;
use Countable;

/**
 * Interface CollectionInterface
 */
interface CollectionInterface extends ArrayAccess, Countable
{
    /**
     * @return array
     */
    public function keys(): array;

    /**
     * @void
     */
    public function clear();

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function get(string $key);

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * @param string $key
     * @param $value
     */
    public function set(string $key, $value);

    /**
     * @return array
     */
    public function all(): array;

    /**
     * @param array|CollectionInterface $collection
     *
     * @return array
     */
    public function diff($collection): array;
}
