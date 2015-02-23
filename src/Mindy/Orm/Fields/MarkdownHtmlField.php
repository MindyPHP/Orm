<?php

namespace Mindy\Orm\Fields;

use cebe\markdown\GithubMarkdown;

/**
 * Class MarkdownHtmlField
 * @package Mindy\Orm
 */
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
