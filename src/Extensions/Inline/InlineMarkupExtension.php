<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Inline;

trait InlineMarkupExtension
{
    /**
     * Processes inline HTML markup.
     *
     * Parses inline HTML if raw HTML is allowed in the configuration.
     *
     * @since 0.1.0
     *
     * @param array $Excerpt The portion of text being parsed
     * @return mixed|null The parsed HTML markup or null if not allowed
     */
    protected function inlineMarkup($Excerpt)
    {
        $config = $this->config();

        if ($config->get('allow_raw_html')) {
            return parent::inlineMarkup($Excerpt);
        }

        return null;
    }
}
