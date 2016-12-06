<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 09/10/2016
 * Time: 22:59
 */

namespace Mindy\Bundle\MindyBundle\Model;

use Mindy\Orm\Fields\CharField;
use Mindy\Orm\Fields\DateTimeField;
use Mindy\Orm\Fields\EmailField;
use Mindy\Orm\Fields\PasswordField;
use Mindy\Orm\Model;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class User
 * @property int $id
 * @property string $email
 * @property string $password
 * @property string $salt
 * @property \DateTime $created_at
  */
class User extends Model implements UserInterface
{
    public static function getFields()
    {
        return array_merge(parent::getFields(), [
            'email' => [
                'class' => EmailField::class,
                'unique' => true,
            ],
            'password' => [
                'class' => PasswordField::class,
            ],
            'salt' => [
                'class' => CharField::class,
                'editable' => false,
                'null' => true,
            ],
            'created_at' => [
                'class' => DateTimeField::class,
                'autoNowAdd' => true,
                'editable' => false,
            ],
        ]);
    }

    public function __toString()
    {
        return (string)$this->email;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        $this->password = null;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string The password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return Role[]|string[] The user roles
     */
    public function getRoles()
    {
        return ['ROLE_ADMIN'];
    }
}