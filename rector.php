<?php

declare(strict_types=1);

use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {

    // get services
    $services = $containerConfigurator->services();

    // register single rule
    $services->set(ClosureToArrowFunctionRector::class);
};