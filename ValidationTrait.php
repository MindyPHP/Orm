<?php

/*
 * (c) Studio107 <mail@studio107.ru> http://studio107.ru
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * Author: Maxim Falaleev <max@studio107.ru>
 */

namespace Mindy\Orm;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;

/**
 * Class ValidationTrait.
 */
trait ValidationTrait
{
    /**
     * @var ConstraintViolationListInterface
     */
    protected $errors = [];

    /**
     * @return array
     */
    protected function getValidationConstraints()
    {
        return [];
    }

    /**
     * @return \Symfony\Component\Validator\Validator\ValidatorInterface
     */
    protected function getValidator()
    {
        return Validation::createValidatorBuilder()->getValidator();
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        $errors = $this->getValidator()->validate($this->getValue(), $this->getValidationConstraints());
        $this->setErrors($errors);

        return count($errors) === 0;
    }

    /**
     * @param ConstraintViolationListInterface $errors
     *
     * @return $this
     */
    protected function setErrors(ConstraintViolationListInterface $errors)
    {
        $this->errors = $errors;

        return $this;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        $errors = [];
        foreach ($this->errors as $key => $error) {
            $errors[] = $error->getMessage();
        }

        return $errors;
    }
}
