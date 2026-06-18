<?php

use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class ExtensionRegistryTestParser extends ParsedownExtended
{
    protected function inlineSpoiler(array $Excerpt): ?array
    {
        if (strpos($Excerpt['text'], '||') !== 0) {
            return null;
        }

        if (!preg_match('/^\|\|(.+?)\|\|/s', $Excerpt['text'], $matches)) {
            return null;
        }

        return [
            'extent' => strlen($matches[0]),
            'element' => [
                'name' => 'span',
                'attributes' => [
                    'class' => 'spoiler',
                ],
                'text' => $matches[1],
            ],
        ];
    }

    protected function blockAside(array $Line, ?array $Block = null): ?array
    {
        if ($Line['text'] !== '@aside') {
            return null;
        }

        return [
            'element' => [
                'name' => 'aside',
                'text' => 'custom',
            ],
        ];
    }
}

class ExtensionRegistryTest extends TestCase
{
    public function testDefinitionClassesLiveInDefinitionNamespace(): void
    {
        $blockDefinition = \BenjaminHoegh\ParsedownExtended\Extensions\Definition\BlockExtensionDefinition::core('Code');
        $inlineDefinition = \BenjaminHoegh\ParsedownExtended\Extensions\Definition\InlineExtensionDefinition::core('Code');

        $this->assertInstanceOf(
            \BenjaminHoegh\ParsedownExtended\Extensions\Definition\BlockExtensionDefinition::class,
            $blockDefinition
        );
        $this->assertInstanceOf(
            \BenjaminHoegh\ParsedownExtended\Extensions\Definition\InlineExtensionDefinition::class,
            $inlineDefinition
        );
        $this->assertContainsOnlyInstancesOf(
            \BenjaminHoegh\ParsedownExtended\Extensions\Definition\InlineExtensionDefinition::class,
            \BenjaminHoegh\ParsedownExtended\Extensions\Definition\ExtensionDefinitions::coreInline()
        );
    }

    public function testRegistryTraitsLiveInRegistryNamespace(): void
    {
        $this->assertTrue(trait_exists(\BenjaminHoegh\ParsedownExtended\Extensions\Registry\BlockExtensions::class));
        $this->assertTrue(trait_exists(\BenjaminHoegh\ParsedownExtended\Extensions\Registry\InlineExtensions::class));
        $this->assertTrue(trait_exists(\BenjaminHoegh\ParsedownExtended\Extensions\Registry\ExtensionRegistration::class));
        $this->assertTrue(trait_exists(\BenjaminHoegh\ParsedownExtended\Extensions\Registry\ExtensionRegistrar::class));
    }

    public function testRuntimeInlineExtensionRegistrationHonorsConfigMetadata(): void
    {
        $parsedownExtended = new ExtensionRegistryTestParser();
        $parsedownExtended->registerInlineExtension('|', 'Spoiler', ['emojis']);

        $this->assertSame(
            '<p>Use <span class="spoiler">secret</span></p>',
            $parsedownExtended->text('Use ||secret||')
        );

        $parsedownExtended->config()->set('emojis', false);

        $this->assertSame(
            '<p>Use ||secret||</p>',
            $parsedownExtended->text('Use ||secret||')
        );
    }

    public function testRuntimeBlockExtensionRegistrationHonorsConfigMetadata(): void
    {
        $parsedownExtended = new ExtensionRegistryTestParser();
        $parsedownExtended->registerBlockExtension('@', 'Aside', ['emojis']);

        $this->assertSame('<aside>custom</aside>', $parsedownExtended->text('@aside'));

        $parsedownExtended->config()->set('emojis', false);

        $this->assertSame('<p>@aside</p>', $parsedownExtended->text('@aside'));
    }

    public function testExtensionMarkersMustBeSingleCharacters(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Extension markers must be single-character strings.');

        $parsedownExtended = new ExtensionRegistryTestParser();
        $parsedownExtended->registerInlineExtension('||', 'Spoiler');
    }

    public function testExtensionHandlerMustExist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing extension handler: inlineMissing');

        $parsedownExtended = new ExtensionRegistryTestParser();
        $parsedownExtended->registerInlineExtension('|', 'Missing');
    }
}
