<?php

/*
 * This file is part of Mindy Framework.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Orm\Fields;

use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\BlobType;
use Doctrine\DBAL\Types\BooleanType;
use Doctrine\DBAL\Types\DateType;
use Doctrine\DBAL\Types\DecimalType;
use Doctrine\DBAL\Types\FloatType;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\TimeType;
use Mindy\Orm\Fields\AutoField;
use Mindy\Orm\Fields\BigIntField;
use Mindy\Orm\Fields\BlobField;
use Mindy\Orm\Fields\BooleanField;
use Mindy\Orm\Fields\CharField;
use Mindy\Orm\Fields\DateField;
use Mindy\Orm\Fields\DateTimeField;
use Mindy\Orm\Fields\DecimalField;
use Mindy\Orm\Fields\FloatField;
use Mindy\Orm\Fields\IntField;
use Mindy\Orm\Fields\TimeField;
use Mindy\Orm\Fields\TimestampField;
use Mindy\QueryBuilder\Expression;
use PHPUnit\Framework\TestCase;

class FieldTest extends TestCase
{
    public function testBlobField()
    {
        $field = new BlobField();
        $this->assertInstanceOf(BlobType::class, $field->getSqlType());
    }

    public function testAutoField()
    {
        $field = new AutoField();
        $options = $field->getSqlOptions();
        $this->assertArrayHasKey('notnull', $options);
        $this->assertArrayHasKey('autoincrement', $options);

        $this->assertInstanceOf(BigIntField::class, $field);

        $platform = new PostgreSqlPlatform();
        $this->assertInstanceOf(Expression::class, $field->convertToDatabaseValue(null, $platform));
    }

    public function testTimestampField()
    {
        $field = new TimestampField();
        $field->setValue(time());
        $this->assertTrue($field->isValid());

        $field->setValue(null);
        $this->assertFalse($field->isValid());
    }

    public function testTimeField()
    {
        $field = new TimeField();
        $this->assertInstanceOf(TimeType::class, $field->getSqlType());

        $this->assertTrue($field->isRequired());

        $field = new TimeField(['autoNow' => true]);
        $this->assertFalse($field->isRequired());

        $field = new TimeField(['autoNowAdd' => true]);
        $this->assertFalse($field->isRequired());

        $field = new TimeField();
        $field->setValue('foo');
        $this->assertFalse($field->isValid());

        $field->setValue(new \DateTime());
        $this->assertTrue($field->isValid());

        $field->setValue(time());
        $this->assertFalse($field->isValid());

        $field->setValue('2016-10-10');
        $this->assertFalse($field->isValid());

        $field->setValue('2016-10-10 10:10:02');
        $this->assertFalse($field->isValid());

        $field->setValue('10:10:02');
        $this->assertTrue($field->isValid());
    }

    public function testIntField()
    {
        $field = new IntField([
            'primary' => true,
        ]);
        $this->assertArrayHasKey('autoincrement', $field->getSqlOptions());

        $field = new IntField([
            'unsigned' => true,
        ]);
        $this->assertArrayHasKey('unsigned', $field->getSqlOptions());

        $platform = new SqlitePlatform();
        $this->assertNull($field->convertToPHPValue(null, $platform));
        $this->assertSame(1, $field->convertToPHPValue('1', $platform));
    }

    public function testFloatField()
    {
        $field = new FloatField();
        $this->assertInstanceOf(FloatType::class, $field->getSqlType());

        $field->setValue(1);
        $this->assertSame(1.0, $field->getValue());
    }

    public function testDateField()
    {
        $field = new DateField();
        $this->assertInstanceOf(DateType::class, $field->getSqlType());

        $this->assertTrue($field->isRequired());

        $field = new DateField(['autoNow' => true]);
        $this->assertFalse($field->isRequired());

        $field = new DateField(['autoNowAdd' => true]);
        $this->assertFalse($field->isRequired());

        $field = new DateField();
        $field->setValue('foo');
        $this->assertFalse($field->isValid());

        $field->setValue(new \DateTime());
        $this->assertTrue($field->isValid());

        $field->setValue(time());
        $this->assertFalse($field->isValid());

        $field->setValue('2016-10-10');
        $this->assertTrue($field->isValid());

        $field->setValue('2016-10-10 10:10:02');
        $this->assertFalse($field->isValid());
    }

    public function testDecimalField()
    {
        $field = new DecimalField();
        $this->assertInstanceOf(DecimalType::class, $field->getSqlType());

        $field->setValue(10);
        $this->assertSame(10.00, $field->getValue());

        $field->setValue(null);
        $this->assertSame(null, $field->getValue());

        $options = $field->getSqlOptions();
        $this->assertArrayHasKey('precision', $options);
        $this->assertArrayHasKey('scale', $options);
    }

    public function testDateTimeField()
    {
        $field = new DateTimeField();
        $this->assertTrue($field->isRequired());

        $field = new DateTimeField(['autoNow' => true]);
        $this->assertFalse($field->isRequired());

        $field = new DateTimeField(['autoNowAdd' => true]);
        $this->assertFalse($field->isRequired());

        $field = new DateTimeField();
        $field->setValue('foo');
        $this->assertFalse($field->isValid());

        $field->setValue(new \DateTime());
        $this->assertTrue($field->isValid());

        $field->setValue(time());
        $this->assertFalse($field->isValid());

        $field->setValue('2016-10-10');
        $this->assertFalse($field->isValid());

        $field->setValue('2016-10-10 10:10:10');
        $this->assertTrue($field->isValid());
    }

    public function testCharField()
    {
        $field = new CharField([
            'null' => true,
            'length' => 155,
        ]);
        $this->assertInstanceOf(StringType::class, $field->getSqlType());
        $field->setName('test');
        $this->assertSame('test', $field->getName());
        $this->assertInstanceOf(Column::class, $field->getColumn());
    }

    public function testBooleanField()
    {
        $field = new BooleanField(['default' => false]);
        $this->assertInstanceOf(BooleanType::class, $field->getSqlType());

        $options = $field->getSqlOptions();
        $this->assertArrayHasKey('default', $options);

        $this->assertFalse($field->getValue());

        $field->setValue(1);
        $this->assertTrue($field->getValue());
        $field->setValue(true);
        $this->assertTrue($field->getValue());

        $field->setValue(0);
        $this->assertFalse($field->getValue());
        $field->setValue(false);
        $this->assertFalse($field->getValue());

        $field = new BooleanField();
        $this->assertFalse($field->default);

        $field = new BooleanField(['default' => true]);
        $this->assertTrue($field->getValue());
        $field->setValue(new \stdClass());
        $this->assertTrue($field->getValue());
        $this->assertTrue($field->isValid());
    }
}
