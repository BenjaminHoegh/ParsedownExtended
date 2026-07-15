<?php

declare(strict_types=1);

$root = dirname(__DIR__);

/**
 * @param list<string> $sources
 */
function mergeExtensionTraits(string $target, string $trait, array $sources): void
{
    global $root;

    $bodies = [];
    foreach ($sources as $source) {
        $code = file_get_contents($root . '/' . $source);
        if (!is_string($code)) {
            throw new RuntimeException("Unable to read {$source}");
        }

        $traitPosition = strpos($code, 'trait ');
        $bodyStart = $traitPosition === false ? false : strpos($code, '{', $traitPosition);
        $bodyEnd = strrpos($code, "\n}");
        if ($bodyStart === false || $bodyEnd === false || $bodyEnd <= $bodyStart) {
            throw new RuntimeException("Unable to find trait body in {$source}");
        }

        $bodies[] = trim(substr($code, $bodyStart + 1, $bodyEnd - $bodyStart - 1), "\r\n");
    }

    $output = "<?php\n\ndeclare(strict_types=1);\n\n";
    $output .= "namespace BenjaminHoegh\\ParsedownExtended\\Extensions;\n\n";
    $output .= "trait {$trait}\n{\n";
    $output .= implode("\n\n", $bodies);
    $output .= "\n}\n";

    file_put_contents($root . '/' . $target, $output);
}

mergeExtensionTraits(
    'src/Extensions/BlockExtensions.php',
    'BlockExtensions',
    [
        'src/Extensions/Block/ReferenceExtension.php',
        'src/Extensions/Block/AbbreviationExtension.php',
        'src/Extensions/Block/AlertExtension.php',
        'src/Extensions/Block/BlockMathExtension.php',
        'src/Extensions/Block/DiagramExtension.php',
        'src/Extensions/Block/TaskListExtension.php',
        'src/Extensions/Block/TableSpanExtension.php',
        'src/Extensions/Block/HeadingExtension.php',
        'src/Extensions/Block/HeadingAnchorExtension.php',
    ]
);

mergeExtensionTraits(
    'src/Extensions/InlineExtensions.php',
    'InlineExtensions',
    [
        'src/Extensions/Inline/EmphasisExtension.php',
        'src/Extensions/Inline/EscapeSequenceExtension.php',
        'src/Extensions/Inline/FootnoteMarkerExtension.php',
        'src/Extensions/Inline/MarkingExtension.php',
        'src/Extensions/Inline/InsertionsExtension.php',
        'src/Extensions/Inline/KeystrokesExtension.php',
        'src/Extensions/Inline/SuperscriptExtension.php',
        'src/Extensions/Inline/SubscriptExtension.php',
        'src/Extensions/Inline/InlineMathExtension.php',
        'src/Extensions/Inline/EmojiExtension.php',
        'src/Extensions/Inline/SmartypantsExtension.php',
        'src/Extensions/Inline/TypographerExtension.php',
        'src/Extensions/Inline/LinkExtension.php',
    ]
);
