<?php

require_once __DIR__ . '/TestParsedownExtended.php';

use PHPUnit\Framework\TestCase;

/**
 * Optional: excluded from the default suite because it downloads the upstream
 * CommonMark specification. Run this file directly when needed.
 *
 * @group commonmark
 */
class CommonMarkTestStrict extends TestCase
{
    public const SPEC_URL = 'https://raw.githubusercontent.com/jgm/CommonMark/master/spec.txt';

    protected $parsedown;

    protected function setUp(): void
    {
        $this->parsedown = new TestParsedownExtended();
        $this->parsedown->setUrlsLinked(false);
    }

    /**
     * @dataProvider data
     * @param $id
     * @param $section
     * @param $markdown
     * @param $expectedHtml
     */
    public function testExample($id, $section, $markdown, $expectedHtml)
    {
        $actualHtml = $this->parsedown->text($markdown);
        $this->assertEquals($expectedHtml, $actualHtml);
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

        $matches = [];
        preg_match_all('/^`{32} example\n((?s).*?)\n\.\n(?:|((?s).*?)\n)`{32}$|^#{1,6} *(.*?)$/m', $spec, $matches, PREG_SET_ORDER);

        $data = [];
        $currentId = 0;
        $currentSection = '';
        foreach ($matches as $match) {
            if (isset($match[3])) {
                $currentSection = $match[3];
            } else {
                $currentId++;
                $markdown = str_replace('→', "\t", $match[1]);
                $expectedHtml = isset($match[2]) ? str_replace('→', "\t", $match[2]) : '';

                $data[$currentId] = [
                    'id' => $currentId,
                    'section' => $currentSection,
                    'markdown' => $markdown,
                    'expectedHtml' => $expectedHtml,
                ];
            }
        }

        return $data;
    }
}
