<?php

declare(strict_types=1);

/*
 * This file is part of Mindy Framework.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm;

use Doctrine\Common\Collections\ArrayCollection;

class AttributeCollection
{
    /**
     * @var array
     */
    protected $attributes = [];
    /**
     * @var array
     */
    protected $oldAttributes = [];

    public function __construct(array $attributes = [])
    {
        $this->attributes = new ArrayCollection($attributes);
        $this->oldAttributes = new ArrayCollection();
    }
}
