<?php

use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class StateTestParser extends ParsedownExtended
{
    public int $documentCount = 0;

    protected function beginDocument(): void
    {
        ++$this->documentCount;
        parent::beginDocument();
    }
}

class StateTest extends TestCase
{
    public function testFullDocumentParsingUsesExplicitLifecycleEntryPoint(): void
    {
        $parser = new StateTestParser();

        $parser->line('Inline only');
        $this->assertSame(0, $parser->documentCount);

        $parser->body('# First document');
        $this->assertSame(1, $parser->documentCount);

        $parser->text('# Second document');
        $this->assertSame(2, $parser->documentCount);
    }
}
