<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 24/07/16
 * Time: 07:34.
 */

namespace Mindy\Orm\Tests\Fields;

use Mindy\Orm\Tests\Models\Member;
use Mindy\Orm\Tests\Models\MemberProfile;
use Mindy\Orm\Tests\OrmDatabaseTestCase;

abstract class OneToOneFieldTest extends OrmDatabaseTestCase
{
    protected function getModels()
    {
        return [new Member(), new MemberProfile()];
    }

    public function testOneToOnePrimaryWithNull()
    {
        $profile = new MemberProfile();
        $profile->user_id = 1;

        $this->assertFalse($profile->isValid());
        $this->assertEquals(['user_id' => ['The primary model not found']], $profile->getErrors());

        // Invalid model, but i can it save

        $this->assertTrue($profile->save());
        $this->assertNull($profile->user);
    }

    public function testOneToOnePrimaryExists()
    {
        $profile = new MemberProfile();
        $profile->user_id = 1;

        $this->assertFalse($profile->isValid());
        $this->assertEquals(['user_id' => ['The primary model not found']], $profile->getErrors());

        // Invalid model, but i can it save

        $this->assertTrue($profile->save());
        $this->assertNull($profile->user);

        $member = new Member();
        $this->assertTrue($member->isValid());
        $this->assertTrue($member->save());

        $this->assertInstanceOf(Member::class, $profile->user);
        $this->assertInstanceOf(MemberProfile::class, $member->profile);

        $this->assertEquals(1, $profile->user->pk);
        $this->assertEquals(1, $member->profile->pk);
    }
}
