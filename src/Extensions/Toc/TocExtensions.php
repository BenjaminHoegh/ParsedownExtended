<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Toc;

trait TocExtensions
{
    // Depends on heading parsing to collect heading text, levels, and generated anchors.
    use TableOfContentsExtension;
    use TransliterationExtension;
}
