<?php

namespace BenjaminHoegh\ParsedownExtended\Configurables;

use Erusev\Parsedown\MutableConfigurable;

final class HeadingBook implements MutableConfigurable
{
    /** @var array<int,array{slug:string,text:string,level:int}> */
    private $headings;

    /**
     * @param array<int,array{slug:string,text:string,level:int}> $headings
     */
    public function __construct(array $headings = [])
    {
        $this->headings = $headings;
    }

    /** @return self */
    public static function initial()
    {
        return new self();
    }

    public function mutatingAdd(string $slug, string $text, int $level): void
    {
        $this->headings[] = [
            'slug' => $slug,
            'text' => $text,
            'level' => $level,
        ];
    }

    /**
     * @return array<int,array{slug:string,text:string,level:int}>
     */
    public function all(): array
    {
        return $this->headings;
    }

    /** @return self */
    public function isolatedCopy(): self
    {
        return new self($this->headings);
    }
}
