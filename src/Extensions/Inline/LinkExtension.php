<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Inline;

trait LinkExtension
{
    /**
     * Processes inline links.
     *
     * Extends link processing to handle custom link behaviors.
     *
     * @since 0.1.0
     *
     * @param array $Excerpt The portion of text being parsed
     * @return array|null The processed link element or null if not processed
     */
    protected function inlineLink($Excerpt)
    {
        if (strpos($Excerpt['text'], ']') === false || !$this->configEnabled('links')) {
            return null;
        }

        return $this->processLinkElement(parent::inlineLink($Excerpt));
    }

    /**
     * Processes inline URLs.
     *
     * Extends the URL processing to include additional custom behavior, such as modifying the parsed URL element.
     *
     * @since 0.1.0
     *
     * @param array $Excerpt The portion of text being parsed
     * @return array|null The processed URL element or null if not processed
     */
    protected function inlineUrl($Excerpt)
    {
        if (!isset($Excerpt['text'][2]) || $Excerpt['text'][2] !== '/' || !$this->configEnabled('links')) {
            return null;
        }

        return $this->processLinkElement(parent::inlineUrl($Excerpt));
    }

    /**
     * Processes inline URL tags.
     *
     * Handles parsing of inline URL tags, adding any custom behavior if needed.
     *
     * @since 0.1.0
     *
     * @param array $Excerpt The portion of text being parsed
     * @return array|null The processed URL tag or null if not processed
     */
    protected function inlineUrlTag($Excerpt)
    {
        if (strpos($Excerpt['text'], '>') === false || !$this->configEnabled('links')) {
            return null;
        }

        return $this->processLinkElement(parent::inlineUrlTag($Excerpt));
    }

    /**
     * Processes inline email tags.
     *
     * Handles email links if the feature is enabled in the configuration.
     *
     * @since 0.1.0
     *
     * @param array $Excerpt The portion of text being parsed
     * @return mixed|null The parsed email tag or null if links are disabled
     */
    protected function inlineEmailTag($Excerpt)
    {
        if (
            strpos($Excerpt['text'], '>') === false ||
            !$this->configEnabled('links') ||
            !$this->configEnabled('links.email_links')
        ) {
            return null;
        }

        return parent::inlineEmailTag($Excerpt);
    }

    /**
     * Processes link elements to add behavior control attributes.
     *
     * Extends parsed Markdown link elements to include attributes such as `nofollow`, `noopener`, and `noreferrer`
     * based on the configuration settings, particularly for external links. This helps control search engine indexing,
     * external page behavior, and referrer privacy.
     *
     * @since 1.3.0
     *
     * @param array $Excerpt The portion of text representing the link element.
     * @return array|null Modified link element with added attributes or null if the link is disallowed.
     */
    protected function processLinkElement($Excerpt)
    {
        // Fast fail for missing config or href
        if (!$this->configEnabled('links') || !$Excerpt || empty($Excerpt['element']['attributes']['href'])) {
            return null;
        }

        $href = $Excerpt['element']['attributes']['href'];

        // Only process external links if enabled
        if ($this->isExternalLink($href)) {
            if (!$this->configEnabled('links.external_links')) {
                return null;
            }

            // Only build rel if needed
            $rel = [];

            if ($this->configEnabled('links.external_links.nofollow')) {
                $rel[] = 'nofollow';
            }
            if ($this->configEnabled('links.external_links.noopener')) {
                $rel[] = 'noopener';
            }
            if ($this->configEnabled('links.external_links.noreferrer')) {
                $rel[] = 'noreferrer';
            }

            if ($this->configEnabled('links.external_links.open_in_new_window')) {
                $Excerpt['element']['attributes']['target'] = '_blank';
            }

            if ($rel) {
                $existing = $Excerpt['element']['attributes']['rel'] ?? '';
                $tokens = preg_split('/\s+/', trim($existing));
                if (!is_array($tokens)) {
                    $tokens = [];
                }

                $tokens = array_filter($tokens, 'strlen');
                $tokens = array_unique(array_merge($tokens, $rel));
                $Excerpt['element']['attributes']['rel'] = implode(' ', $tokens);
            }
        }

        return $Excerpt;
    }

    /**
     * Determines if a given link is an external link.
     *
     * Checks if the link is either protocol-relative (starts with `//`) or absolute (`http://` or `https://`)
     * and if the host differs from the current server's host. It also checks against a list of internal hosts to identify external links.
     *
     * @since 1.3.0
     *
     * @param string $href The URL to check.
     * @return bool Returns true if the link is external, false otherwise.
     */
    private function isExternalLink(string $href): bool
    {
        // Early return for relative URLs (not starting with http(s):// or //)
        $protocolRelative = strncmp($href, '//', 2);
        if (
            $protocolRelative !== 0 &&
            stripos($href, 'http://') !== 0 &&
            stripos($href, 'https://') !== 0
        ) {
            return false;
        }

        // Normalize protocol-relative URLs for parse_url
        $url = ($protocolRelative === 0) ? 'http:' . $href : $href;
        $host = parse_url($url, PHP_URL_HOST);
        if (!$host) {
            return false;
        }

        $host = $this->normalizeHost((string) $host);

        // Normalize configured current host, falling back to the legacy server value.
        $currentHost = $this->getCurrentHost();
        if ($host === $currentHost) {
            return false;
        }

        $internalHostsSet = $this->getInternalHostsSet();

        return !isset($internalHostsSet[$host]);
    }

    /**
     * Normalizes host names for case-insensitive comparisons.
     *
     * @param string $host Raw host.
     * @return string Normalized host.
     */
    private function normalizeHost(string $host): string
    {
        $parsedHost = parse_url('http://' . ltrim($host, '/'), PHP_URL_HOST);
        if (is_string($parsedHost) && $parsedHost !== '') {
            $host = $parsedHost;
        }

        $host = strtolower($host);
        if (strpos($host, 'www.') === 0) {
            return substr($host, 4);
        }

        return $host;
    }

    private function getCurrentHost(): string
    {
        $configuredHost = $this->configValue('links.current_host');
        if (is_string($configuredHost) && $configuredHost !== '') {
            return $this->normalizeHost($configuredHost);
        }

        return $this->normalizeHost($_SERVER['HTTP_HOST'] ?? '');
    }

    /**
     * Builds and caches the internal host lookup set.
     *
     * @return array<string, bool>
     */
    private function getInternalHostsSet(): array
    {
        $cacheKey = 'links.internal_hosts';
        if ($this->hasRuntimeCacheValue($cacheKey)) {
            $internalHosts = $this->runtimeCacheValue($cacheKey);

            return is_array($internalHosts) ? $internalHosts : [];
        }

        $internalHosts = $this->configValue('links.external_links.internal_hosts');
        $hostSet = [];
        foreach ($internalHosts as $host) {
            $normalizedHost = $this->normalizeHost((string) $host);
            if ($normalizedHost !== '') {
                $hostSet[$normalizedHost] = true;
            }
        }

        $this->storeRuntimeCacheValue($cacheKey, $hostSet);

        return $hostSet;
    }
}
