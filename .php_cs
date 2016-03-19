<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->exclude('./vendor')
    ->in('./src')
;

return Symfony\CS\Config\Config::create()
    ->level(Symfony\CS\FixerInterface::PSR2_LEVEL)
    ->fixers(array('strict_param', 'short_array_syntax'))
    ->finder($finder)
;