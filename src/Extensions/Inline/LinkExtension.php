<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Inline;

trait LinkExtension
{
    /** @var array|null $internalHostsSet Cached set of internal hosts for link processing */
    private ?array $internalHostsSet = null;

    /** @var string $internalHostsCacheKey Hash key for the current cached internal host set */
    private string $internalHostsCacheKey = '';

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
        $config = $this->config();

        if (!$config->get('links') || !$config->get('links.email_links')) {
            return null;
        }

        $Excerpt = parent::inlineEmailTag($Excerpt);

        if (isset($Excerpt['element']['attributes']['href'])) {
            $Excerpt['element']['attributes']['target'] = '_blank';
        }

        return $Excerpt;
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
        $config = $this->config();

        // Fast fail for missing config or href
        if (!$config->get('links') || !$Excerpt || empty($Excerpt['element']['attributes']['href'])) {
            return null;
        }

        $href = $Excerpt['element']['attributes']['href'];

        // Only process external links if enabled
        if ($this->isExternalLink($href)) {
            if (!$config->get('links.external_links')) {
                return null;
            }

            // Only build rel if needed
            $rel = [];

            if ($config->get('links.external_links.nofollow')) {
                $rel[] = 'nofollow';
            }
            if ($config->get('links.external_links.noopener')) {
                $rel[] = 'noopener';
            }
            if ($config->get('links.external_links.noreferrer')) {
                $rel[] = 'noreferrer';
            }

            if ($config->get('links.external_links.open_in_new_window')) {
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

        // Normalize current host
        $currentHost = $this->normalizeHost($_SERVER['HTTP_HOST'] ?? '');
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

    /**
     * Builds and caches the internal host lookup set.
     *
     * @return array<string, bool>
     */
    private function getInternalHostsSet(): array
    {
        $internalHosts = $this->config()->get('links.external_links.internal_hosts');
        $cacheKey = json_encode($internalHosts);
        if (!is_string($cacheKey)) {
            $cacheKey = md5(print_r($internalHosts, true));
        }

        if ($this->internalHostsSet !== null && $this->internalHostsCacheKey === $cacheKey) {
            return $this->internalHostsSet;
        }

        $hostSet = [];
        foreach ($internalHosts as $host) {
            $normalizedHost = $this->normalizeHost((string) $host);
            if ($normalizedHost !== '') {
                $hostSet[$normalizedHost] = true;
            }
        }

        $this->internalHostsSet = $hostSet;
        $this->internalHostsCacheKey = $cacheKey;

        return $this->internalHostsSet;
    }
}
