<?php

declare(strict_types=1);

namespace Survos\SettingsBundle\Menu;

use Survos\MenuBundle\Attribute\AsMenuListener;
use Survos\MenuBundle\Event\MenuEvent;
use Survos\MenuBundle\MenuSlot;
use Survos\TablerBundle\Menu\MenuBuilderTrait;
use Survos\TablerBundle\Service\IconService;
use Survos\TablerBundle\Service\RouteAliasService;
use Symfony\Component\Routing\RouterInterface;

/**
 * Adds a "Settings" link to the user-dropdown (AUTH) menu — self-registered only
 * when the host app has configured at least one setting (see
 * SurvosSettingsBundle::loadExtension). An app requiring this bundle with an empty
 * `settings:` config gets neither this subscriber nor a working link; add a setting
 * and it appears automatically, no per-app menu code needed.
 *
 * SurvosTabler is an OPTIONAL dependency of this bundle — same reasoning as
 * survos/auth-bundle's AuthMenuSubscriber, which this mirrors.
 */
final class SettingsMenuSubscriber
{
    use MenuBuilderTrait;

    public function __construct(
        protected readonly ?RouterInterface $router = null,
        protected readonly ?RouteAliasService $routeAliasService = null,
        protected readonly ?IconService $iconService = null,
    ) {
    }

    #[AsMenuListener(MenuSlot::AUTH)]
    public function onAuthMenu(MenuEvent $event): void
    {
        $this->add($event->getMenu(), 'survos_settings_index', label: 'Settings', icon: 'tabler:settings');
    }
}
