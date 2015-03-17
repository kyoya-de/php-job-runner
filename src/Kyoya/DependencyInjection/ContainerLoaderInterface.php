<?php
/**
 * Created by PhpStorm.
 * User: stefan
 * Date: 17.03.15
 * Time: 19:20
 */

namespace Kyoya\DependencyInjection;

interface ContainerLoaderInterface
{
    public function loadContainer($cacheFile);
}
