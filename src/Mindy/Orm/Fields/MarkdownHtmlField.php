<?php
/**
 *
 *
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 02/05/14.05.2014 17:16
 */

namespace Mindy\Orm\Fields;

use cebe\markdown\GithubMarkdown;

class MarkdownHtmlField extends TextField
{
    public function getDbPrepValue()
    {
        $parser = new GithubMarkdown();
        $parser->enableNewlines = true;
        $parser->html5 = true;
        return (string)$parser->parse($this->value);
    }
}
