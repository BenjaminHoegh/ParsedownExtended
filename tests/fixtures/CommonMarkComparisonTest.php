<?php

use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

/**
 * Compare ParsedownExtended vs ParsedownExtra against the CommonMark spec
 *
 * ParsedownExtended is configured in a ParsedownExtra-compatible profile so
 * intentional extension features do not hide unintended parser drift. Run with:
 *
 *   vendor/bin/phpunit tests/fixtures/CommonMarkComparisonTest.php --verbose
 *
 * Or for a specific comparison output:
 *
 *   vendor/bin/phpunit tests/fixtures/CommonMarkComparisonTest.php --testdox
 *
 * This is an optional test and is excluded from the default suite because it
 * downloads the upstream CommonMark specification.
 */
/** @group commonmark */
class CommonMarkComparisonTest extends TestCase
{
    const SPEC_URL = 'https://raw.githubusercontent.com/jgm/CommonMark/master/spec.txt';

    protected $parsedownExtended;
    protected $parsedownExtra;
    protected $differences = [];

    protected function setUp() : void
    {
        $this->parsedownExtended = new ParsedownExtended();
        $this->parsedownExtended->setUrlsLinked(false);
        $this->configureParsedownExtendedCompatibilityMode($this->parsedownExtended);

        $this->parsedownExtra = new \ParsedownExtra();
        $this->parsedownExtra->setUrlsLinked(false);

        $this->differences = [];
    }

    /**
     * Compare both parsers on all CommonMark examples
     *
     * @dataProvider data
     * @param $id
     * @param $section
     * @param $markdown
     * @param $expectedHtml
     */
    public function testCompareParsedownExtendedVsParsedownExtra($id, $section, $markdown, $expectedHtml)
    {
        $extendedHtml = $this->parsedownExtended->text($markdown);
        $extraHtml = $this->parsedownExtra->text($markdown);

        // Only fail if ParsedownExtended and ParsedownExtra produce different results
        $this->assertEquals(
            $extraHtml,
            $extendedHtml,
            "Example #{$id} in section '{$section}' produces different output between ParsedownExtended and ParsedownExtra"
        );
    }

    private function configureParsedownExtendedCompatibilityMode(ParsedownExtended $parsedownExtended): void
    {
        $parsedownExtended->config()
            ->set('headings.auto_anchors', false)
            ->set('toc', false)
            ->set('links.external_links.nofollow', false)
            ->set('links.external_links.noopener', false)
            ->set('links.external_links.noreferrer', false)
            ->set('links.external_links.open_in_new_window', false)
            ->set('emojis', false)
            ->set('typographer', false)
            ->set('smartypants', false)
            ->set('emphasis.mark', false)
            ->set('emphasis.insertions', false)
            ->set('emphasis.keystrokes', false)
            ->set('emphasis.superscript', false)
            ->set('emphasis.subscript', false)
            ->set('math', false)
            ->set('alerts', false);
    }

    /**
     * Teardown: output comparison summary
     */
    public static function tearDownAfterClass(): void
    {
        // Summary is printed via test output
    }

    /**
     * @return array
     */
    public function data()
    {
        $spec = file_get_contents(self::SPEC_URL);
        if ($spec === false) {
            $this->fail('Unable to load CommonMark spec from ' . self::SPEC_URL);
        }

        $spec = str_replace("\r\n", "\n", $spec);
        $spec = strstr($spec, '<!-- END TESTS -->', true);

        $matches = array();
        preg_match_all('/^`{32} example\n((?s).*?)\n\.\n(?:|((?s).*?)\n)`{32}$|^#{1,6} *(.*?)$/m', $spec, $matches, PREG_SET_ORDER);

        $data = array();
        $currentId = 0;
        $currentSection = '';
        foreach ($matches as $match) {
            if (isset($match[3])) {
                $currentSection = $match[3];
            } else {
                $currentId++;
                $markdown = str_replace('→', "\t", $match[1]);
                $expectedHtml = isset($match[2]) ? str_replace('→', "\t", $match[2]) : '';

                $data[$currentId] = array(
                    'id' => $currentId,
                    'section' => $currentSection,
                    'markdown' => $markdown,
                    'expectedHtml' => $expectedHtml
                );
            }
        }

        return $data;
    }
}
