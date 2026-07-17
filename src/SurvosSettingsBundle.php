<?php

declare(strict_types=1);

namespace Survos\SettingsBundle;

use Survos\Kit\AbstractSurvosBundle;
use Survos\Kit\SurvosKitBundle;
use Survos\Kit\Traits\HasDoctrineEntities;
use Survos\SettingsBundle\Form\SettingsFormType;
use Survos\SettingsBundle\Repository\SettingRepository;
use Survos\SettingsBundle\Service\NullScopeResolver;
use Survos\SettingsBundle\Service\ScopeResolverInterface;
use Survos\SettingsBundle\Service\SettingsManager;
use Survos\SettingsBundle\Service\SettingsManagerInterface;
use Survos\SettingsBundle\Twig\SettingsExtension;
use Survos\SettingsBundle\Twig\Components\SettingsForm;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Kernel\RequiredBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

#[RequiredBundle(SurvosKitBundle::class)]
// Symfony\Component\HttpKernel\Bundle\Bundle <-- Flex auto-registration marker (see Survos\Kit\AbstractSurvosBundle)
final class SurvosSettingsBundle extends AbstractSurvosBundle
{
    use HasDoctrineEntities;

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->scalarNode('global_scope')
                    ->info('Scope key used for global fallback settings. Defaults to "global" so the (scope, name) uniqueness constraint works consistently across databases.')
                    ->defaultValue('global')
                ->end()
                ->booleanNode('fallback_to_global')
                    ->info('When true, scoped reads fall back to the global scope before returning the caller default.')
                    ->defaultTrue()
                ->end()
                ->arrayNode('settings')
                    ->info('Optional settings exposed by the UX form component.')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('type')->defaultValue('Symfony\\Component\\Form\\Extension\\Core\\Type\\TextType')->end()
                            ->variableNode('options')->defaultValue([])->end()
                            ->variableNode('default')->defaultNull()->end()
                        ->end()
                    ->end()
                    ->defaultValue([])
                ->end()
            ->end();
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        parent::loadExtension($config, $container, $builder);

        $services = $container->services()
            ->defaults()
                ->autowire()
                ->autoconfigure();

        $services->set(SettingRepository::class)
            ->tag('doctrine.repository_service');

        $services->set(NullScopeResolver::class);
        $services->alias(ScopeResolverInterface::class, NullScopeResolver::class);

        $services->set(SettingsFormType::class)
            ->arg('$definitions', $config['settings']);

        $services->set(SettingsManager::class)
            ->arg('$globalScope', $config['global_scope'])
            ->arg('$fallbackToGlobal', $config['fallback_to_global']);
        $services->alias(SettingsManagerInterface::class, SettingsManager::class);

        $services->set(SettingsExtension::class)
            ->tag('twig.extension');
        $services->set(SettingsForm::class)
            ->arg('$definitions', $config['settings']);
    }
}
