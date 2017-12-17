<?php

/*
 * This file is part of Mindy Framework.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm\Event;

use Mindy\Orm\ModelInterface;
use Symfony\Component\EventDispatcher\Event;

class SaveEvent extends Event
{
    const BEFORE_SAVE_EVENT = 'mindy.orm.before_save';
    const AFTER_SAVE_EVENT = 'mindy.orm.after_save';
    const BEFORE_UPDATE_EVENT = 'mindy.orm.before_update';
    const AFTER_UPDATE_EVENT = 'mindy.orm.after_update';

    /**
     * @var ModelInterface
     */
    protected $model;

    /**
     * @var bool
     */
    protected $isNew;

    /**
     * GenericOrmEvent constructor.
     * @param ModelInterface $model
     */
    public function __construct(ModelInterface $model, bool $isNew)
    {
        $this->model = $model;
        $this->isNew = $isNew;
    }

    /**
     * @return bool
     */
    public function getIsNew(): bool
    {
        return $this->isNew;
    }

    /**
     * @return ModelInterface
     */
    public function getModel(): ModelInterface
    {
        return $this->model;
    }
}
