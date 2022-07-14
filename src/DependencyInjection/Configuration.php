<?php

declare(strict_types=1);

namespace Cowegis\Bundle\ContaoGeocodeWidget\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('cowegis_contao_geocode_widget');

        $treeBuilder
            ->getRootNode()
            ->children()
                ->scalarNode('url_template')
                    ->info('Map url template. If empty https://{s}.tile.osm.org/{z}/{x}/{y}.png will be used.')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
