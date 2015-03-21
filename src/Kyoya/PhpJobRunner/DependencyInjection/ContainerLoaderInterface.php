<?php

namespace Kyoya\PhpJobRunner\DependencyInjection;

interface ContainerLoaderInterface
{
    public function loadContainer($cacheFile);
}
