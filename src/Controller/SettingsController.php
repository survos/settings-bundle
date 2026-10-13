<?php

declare(strict_types=1);

namespace Survos\SettingsBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Twig\Environment;

/**
 * Generic "my settings" page for whatever the host app configured under
 * survos_settings.settings — renders <twig:SettingsForm /> with no explicit
 * `settings` prop, so it defaults to every configured definition (see
 * SettingsForm::instantiateForm()). Scope resolves via whatever
 * ScopeResolverInterface the host app registered (e.g. a per-user resolver),
 * same as any other settings-bundle read/write.
 *
 * Auto-registered by kit-bundle's src/Controller/ convention (AbstractSurvosBundle),
 * same as auth-bundle's ProfileController — no explicit DI wiring needed here.
 * SettingsMenuSubscriber links to this page from the user menu, but only when
 * there's at least one setting configured (see SurvosSettingsBundle::loadExtension).
 */
final class SettingsController
{
    public function __construct(private readonly Environment $twig)
    {
    }

    #[Route('/settings', name: 'survos_settings_index', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function __invoke(): Response
    {
        return new Response($this->twig->render('@SurvosSettings/settings.html.twig'));
    }
}
