<?php

/**
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 06/02/15 18:56
 */

namespace Tests\Cases\Orm\Pgsql;

use Mindy\Orm\Tests\HasManyFieldTest;
use Modules\Tests\Models\Color;
use Modules\Tests\Models\Cup;
use Modules\Tests\Models\Design;

class PgsqlHasManyFieldTest extends HasManyFieldTest
{
    public $driver = 'pgsql';

    public function testMultiple()
    {
        $cup = new Cup();
        $cup->name = 'Amazing cup';
        $cup->save();

        $design = new Design();
        $design->name = 'Dragon';
        $design->cup = $cup;
        $design->save();

        $color = new Color();
        $color->name = 'red';
        $color->cup = $cup;
        $color->save();

        $sql = Cup::objects()->filter(['designs__name' => 'Dragon', 'colors__name' => 'red'])->allSql();
        $this->assertEquals('SELECT * FROM (SELECT DISTINCT ON ("cup_1"."id") "cup_1"."id",  "cup_1".* FROM "cup" "cup_1" LEFT OUTER JOIN "design" "design_2" ON "cup_1"."id" = "design_2"."cup_id" LEFT OUTER JOIN "color" "color_3" ON "cup_1"."id" = "color_3"."cup_id" WHERE ("design_2"."name"=\'Dragon\') AND ("color_3"."name"=\'red\') GROUP BY "cup_1"."id", "color_3"."cup_id") "_tmp"', $sql);
    }
}
