<?php

namespace BenjaminHoegh\ParsedownExtended\Configurables;

use Erusev\Parsedown\MutableConfigurable;

final class AlertsConfig implements MutableConfigurable
{
    /** @var string[] */
    private $types;

    /** @var string */
    private $class;

    /**
     * @param string[] $types
     * @param string $class
     */
    public function __construct(array $types = ['note', 'tip', 'important', 'warning', 'caution'], string $class = 'markdown-alert')
    {
        $this->types = $types;
        $this->class = $class;
    }

    /** @return self */
    public static function initial()
    {
        return new self();
    }

    /**
     * @return string[]
     */
    public function types(): array
    {
        return $this->types;
    }

    public function class(): string
    {
        return $this->class;
    }

    /** @return self */
    public function withTypes(array $types): self
    {
        $new = clone $this;
        $new->types = $types;
        return $new;
    }

    /** @return self */
    public function withClass(string $class): self
    {
        $new = clone $this;
        $new->class = $class;
        return $new;
    }

    /** @return self */
    public function isolatedCopy(): self
    {
        return new self($this->types, $this->class);
    }
}
