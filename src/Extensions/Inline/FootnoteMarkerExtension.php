<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Inline;

trait FootnoteMarkerExtension
{
    /**
     * Parses a footnote reference using document-local numbering.
     *
     * ParsedownExtra keeps its counter private and does not reset it between
     * text() calls, so the small handler is reproduced here with local state.
     */
    protected function inlineFootnoteMarker($Excerpt)
    {
        if (!$this->configEnabled('footnotes')) {
            return null;
        }

        if (!preg_match('/^\[\^(.+?)\]/', $Excerpt['text'], $matches)) {
            return null;
        }

        $name = $matches[1];
        if (!isset($this->DefinitionData['Footnote'][$name])) {
            return null;
        }

        ++$this->DefinitionData['Footnote'][$name]['count'];

        if (!isset($this->DefinitionData['Footnote'][$name]['number'])) {
            $this->DefinitionData['Footnote'][$name]['number'] = ++$this->footnoteCount;
        }

        return [
            'extent' => strlen($matches[0]),
            'element' => [
                'name' => 'sup',
                'attributes' => [
                    'id' => 'fnref' . $this->DefinitionData['Footnote'][$name]['count'] . ':' . $name,
                ],
                'element' => [
                    'name' => 'a',
                    'attributes' => [
                        'href' => '#fn:' . $name,
                        'class' => 'footnote-ref',
                    ],
                    'text' => $this->DefinitionData['Footnote'][$name]['number'],
                ],
            ],
        ];
    }
}
