<?php

declare(strict_types=1);

namespace Cowegis\Bundle\ContaoGeocodeWidget\DependencyInjection;

use Override;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

final class CowegisContaoGeocodeWidgetExtension extends Extension
{
    /** {@inheritDoc} */
    #[Override]
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('services.xml');

        $config = $this->processConfiguration(new Configuration(), $configs);
        $container->setParameter('cowegis_contao_geocode_widget.url_template', $config['url_template'] ?? null);
    }
}
