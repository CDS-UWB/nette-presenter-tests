<?php

$finder = PhpCsFixer\Finder::create()
    ->ignoreUnreadableDirs()
    ->in(__DIR__)
    ->exclude('vendor')
    ->exclude('tests/temp')
;

return (new PhpCsFixer\Config())
    ->setRules(Cds\PhpCodeStyle\rules())
    ->setFinder($finder)
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
;
