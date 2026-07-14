<?php
require_once __DIR__ . '/TestParsedownExtended.php';

use PHPUnit\Framework\TestCase;

class ParsedownExtendedTest extends TestCase
{
    final function __construct($name = null, array $data = array(), $dataName = '')
    {
        $this->dirs = $this->initDirs();
        $this->Parsedown = $this->initParsedown();

        parent::__construct($name, $data, $dataName);
    }

    private $dirs;
    protected $Parsedown;

    /**
     * @return array
     */
    protected function initDirs()
    {
        $dirs []= dirname(__FILE__).'/data/';

        return $dirs;
    }

    /**
     * @return Parsedown
     */
    protected function initParsedown()
    {
        $Parsedown = new TestParsedownExtended();

        return $Parsedown;
    }

    /**
     * @dataProvider data
     * @param $test
     * @param $dir
     */
    function test_($test, $dir)
    {
        // Use a fresh instance so parser state (anchors, footnotes, TOC entries,
        // and references) cannot leak between fixture files.
        $this->Parsedown = $this->initParsedown();

        $configFile = $dir . $test . '.json';
        if (file_exists($configFile))
        {
            $config = json_decode(file_get_contents($configFile), true, 512, JSON_THROW_ON_ERROR);
            $this->Parsedown->config()->set($config);
        }

        $markdown = file_get_contents($dir . $test . '.md');

        $expectedMarkup = file_get_contents($dir . $test . '.html');

        $expectedMarkup = str_replace("\r\n", "\n", $expectedMarkup);
        $expectedMarkup = str_replace("\r", "\n", $expectedMarkup);
        $expectedMarkup = rtrim($expectedMarkup, "\n");

        $this->Parsedown->setSafeMode(substr($test, 0, 3) === 'xss');
        $this->Parsedown->setStrictMode(substr($test, 0, 6) === 'strict');

        $actualMarkup = $this->Parsedown->text($markdown);

        $this->assertEquals($expectedMarkup, $actualMarkup, "Failed for fixture: {$dir}{$test}.md");
    }

    function data()
    {
        $data = array();

        foreach ($this->dirs as $dir)
        {
            $Folder = new DirectoryIterator($dir);

            foreach ($Folder as $File)
            {
                /** @var $File DirectoryIterator */

                if ( ! $File->isFile())
                {
                    continue;
                }

                $filename = $File->getFilename();

                $extension = pathinfo($filename, PATHINFO_EXTENSION);

                if ($extension !== 'md')
                {
                    continue;
                }

                $basename = $File->getBasename('.md');

                if (file_exists($dir . $basename . '.html'))
                {
                    $data[$basename] = array($basename, $dir);
                }
            }
        }

        return $data;
    }
}
