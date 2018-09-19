<?php
/**
 * Created by PhpStorm.
 * User: Andreas
 * Date: 30/03/17
 * Time: 16:10
 */

namespace Schoenef\GraphHopperConnectorBundle\DependencyInjection;


use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 * this is testing the configuration in the following manner:
 * html2pdf:
 *   provider: defualt pdfrocket
 *   timeout: default 20
 *   apikey: required
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface {
    const KEY_TIMEOUT = 'timeout';
    const KEY_API_HOST = 'host';
    const KEY_LANG = 'lang';

    const API_HOST_DEFAULT = 'https://photon.komoot.de/api/';

    const CONFIG_NAMESPACE = 'graph_hopper_connector';

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        // the root must be the name of the bundle: http://stackoverflow.com/a/35505189/2776727
        $rootNode = $treeBuilder->root(self::CONFIG_NAMESPACE);

        $rootNode
            ->children()
                ->enumNode(self::KEY_LANG)->values(['en', 'de', 'fr', 'es', 'ru'])->defaultValue('en')->end()
                ->scalarNode(self::KEY_API_HOST)->defaultValue(self::API_HOST_DEFAULT)->end()
                ->integerNode(self::KEY_TIMEOUT)->defaultValue(20)->end()
            ->end();
        return $treeBuilder;
    }
}