<?php

$config = new PhpCsFixer\Config();

return $config
    ->setRules([
        '@PHP74Migration' => true,
        '@PSR12' => true,
    ])
    ->setFinder(PhpCsFixer\Finder::create()
        ->in(__DIR__)
        ->exclude('vendor')
    )
;
