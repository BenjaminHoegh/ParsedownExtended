<?php

$config = new PhpCsFixer\Config();

return $config
    ->setRules([
        '@PHP80Migration' => true,
        '@PSR12' => true,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
        ->in(__DIR__)
        ->exclude('vendor')
    )
;
