<?php

return Symfony\CS\Config\Config::create()
    // use SYMFONY_LEVEL:
    ->level(Symfony\CS\FixerInterface::SYMFONY_LEVEL)
    // and extra fixers:
    ->fixers(array(
        'short_array_syntax',
    ))
    ->finder(
        Symfony\CS\Finder\DefaultFinder::create()
            ->in('src/')
            ->in('app/')
            ->exclude('cache')
            ->exclude('logs')
            ->exclude('Resources')
    )
;