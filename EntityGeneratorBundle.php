<?php
declare(strict_types=1);

namespace K3ssen\EntityGeneratorBundle;

use K3ssen\EntityGeneratorBundle\DependencyInjection\Compiler\EntityGeneratorCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EntityGeneratorBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new EntityGeneratorCompilerPass());
    }
}
