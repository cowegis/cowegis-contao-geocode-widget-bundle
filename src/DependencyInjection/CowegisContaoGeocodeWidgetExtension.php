<?php

declare(strict_types=1);

namespace Cowegis\Bundle\ContaoGeocodeWidget\DependencyInjection;

use Override;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

final class CowegisContaoGeocodeWidgetExtension extends Extension
{
    /** {@inheritDoc} */
    #[Override]
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $container->setParameter('cowegis_contao_geocode_widget.url_template', $config['url_template'] ?? null);
    }
}
