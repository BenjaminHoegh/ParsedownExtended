<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions;

use BenjaminHoegh\ParsedownExtended\Extensions\Inline\EmojiExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Inline\EmphasisExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Inline\EscapeSequenceExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Inline\InlineCodeExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Inline\InlineImageExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Inline\InlineMarkupExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Inline\InlineMathExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Inline\InsertionsExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Inline\KeystrokesExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Inline\LinkExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Inline\MarkingExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Inline\SmartypantsExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Inline\StrikethroughExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Inline\SubscriptExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Inline\SuperscriptExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Inline\TypographerExtension;

trait InlineExtensions
{
    use InlineCodeExtension;
    use InlineImageExtension;
    use InlineMarkupExtension;
    use StrikethroughExtension;
    use EmphasisExtension;
    use EscapeSequenceExtension;
    use MarkingExtension;
    use InsertionsExtension;
    use KeystrokesExtension;
    use SuperscriptExtension;
    use SubscriptExtension;
    use InlineMathExtension;
    use EmojiExtension;
    use SmartypantsExtension;
    use TypographerExtension;
    use LinkExtension;
}
