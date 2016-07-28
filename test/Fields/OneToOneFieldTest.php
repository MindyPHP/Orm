<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 24/07/16
 * Time: 07:34
 */

namespace Mindy\Orm\Tests\Fields;

use Exception;
use Mindy\Orm\Fields\OneToOneField;
use Mindy\Orm\Model;
use Mindy\Orm\Tests\OrmDatabaseTestCase;

class Member extends Model
{
    public static function getFields()
    {
        return [
            'profile' => [
                'class' => OneToOneField::class,
                'modelClass' => MemberProfile::class,
                'reversed' => true,
                'to' => 'user_id'
            ],
        ];
    }
}

class MemberProfile extends Model
{
    public static function getFields()
    {
        return [
            'user' => [
                'class' => OneToOneField::class,
                'modelClass' => Member::class,
                'primary' => true,
                'to' => 'id'
            ]
        ];
    }
}

abstract class OneToOneFieldTest extends OrmDatabaseTestCase
{
    protected function getModels()
    {
        return [new Member, new MemberProfile];
    }

    public function testOneToOneKey()
    {
        $member = new Member();
        $this->assertTrue($member->hasField('id'));
        $this->assertTrue($member->hasField('profile'));
        $this->assertTrue($member->hasAttribute('profile_id'));
        $this->assertEquals('id', $member->primaryKeyName());
        $this->assertTrue($member->hasField('pk'));
        $this->assertTrue($member->save());
        $this->assertEquals(1, $member->pk);

        $profile = new MemberProfile();
        $profile->user = $member;
        $this->assertTrue($profile->hasField('user'));
        $this->assertTrue($profile->hasAttribute('user_id'));
        $this->assertEquals(1, $profile->getAttribute('user_id'));
        $this->assertTrue($profile->save());
        $this->assertEquals(1, $profile->getAttribute('user_id'));
        $this->assertEquals(1, $profile->user_id);
        $this->assertEquals(1, $profile->pk);
        $this->assertEquals(1, $member->pk);
        $this->assertEquals(1, MemberProfile::objects()->filter(['user_id' => $member->id])->count());
        $this->assertEquals(1, $member->profile->pk);
        $profile->delete();
        $this->assertNull($member->profile);
    }

    public function testOneToOne()
    {
        $member = new Member();
        $member->save();

        $profile = new MemberProfile();
        $profile->user = $member;
        $this->assertTrue($profile->isValid());
        $profile->save();

        $profile2 = new MemberProfile();
        $profile2->user = $member;
        $this->assertFalse($profile2->isValid());
        $profile2->save();

        $this->assertEquals(2, MemberProfile::objects()->count());
        $this->assertEquals(1, Member::objects()->count());
    }

    public function testOneToOneReverseException()
    {
        $this->setExpectedException(Exception::class);
        $member = new Member();
        $member->save();

        $member2 = new Member();
        $member2->save();

        $profile = new MemberProfile();
        $profile->user = $member;
        $profile->save();

        $profile2 = new MemberProfile();
        $profile2->user = $member2;
        $profile2->save();

        $member->profile = $profile2;
        $member->save();
    }

    public function testOneToOneKeyInt()
    {
        $member = new Member();
        $member->save();

        $profile = new MemberProfile();
        $profile->user_id = 1;
        $profile->save();

        $this->assertEquals(1, MemberProfile::objects()->filter(['user' => $member->id])->count());
        $this->assertEquals(1, $member->profile->pk);
        $profile->delete();
        $this->assertNull($member->profile);
    }
}