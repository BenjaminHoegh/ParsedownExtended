<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Registry;

use BenjaminHoegh\ParsedownExtended\Extensions\Block\AbbreviationExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Block\AlertExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Block\BlockCodeExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Block\BlockMarkupExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Block\BlockMathExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Block\CommentExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Block\DefinitionListExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Block\DiagramExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Block\FootnoteExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Block\HeadingAnchorExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Block\HeadingExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Block\ListExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Block\QuoteExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Block\ReferenceExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Block\RuleExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Block\TableExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Block\TableSpanExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Block\TaskListExtension;

trait BlockExtensions
{
    use FootnoteExtension;
    use DefinitionListExtension;
    use BlockCodeExtension;
    use CommentExtension;
    use ListExtension;
    use QuoteExtension;
    use RuleExtension;
    use BlockMarkupExtension;
    use ReferenceExtension;
    use TableExtension;
    use AbbreviationExtension;
    use AlertExtension;
    use BlockMathExtension;
    use DiagramExtension;
    use TaskListExtension;
    use TableSpanExtension;
    use HeadingExtension;
    use HeadingAnchorExtension;
}
