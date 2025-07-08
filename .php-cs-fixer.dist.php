
<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$config = new Config();

return $config
    ->setRules([
        '@PHP74Migration' => true,
        '@PSR12' => true,
    ])
    ->setFinder(
        Finder::create()
            ->in(__DIR__)
            ->exclude('vendor')
    )
;
