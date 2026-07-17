# survos/settings-bundle

Tenant-scoped settings storage for Symfony 8.1+ applications.

This bundle is intentionally small: it stores named JSON values with an optional opaque `scope` string. Apps decide what scopes mean: tenant, site, user, locale group, or global. Typical values include a site logo, tagline, displayed locales, contact links, and feature toggles.

## Install

```bash
composer require survos/settings-bundle
bin/console doctrine:schema:update --dump-sql
bin/console make:migration
bin/console doctrine:migrations:migrate
```

The bundle uses `survos/kit-bundle`, so its Doctrine entity mapping is prepended automatically.

## Configuration

```yaml
# config/packages/survos_settings.yaml
survos_settings:
    # `global` stores global settings with an explicit scope. Use 'global' if your app prefers an explicit row key.
    global_scope: global
    fallback_to_global: true
    settings:
        logo:
            type: Symfony\Component\Form\Extension\Core\Type\UrlType
            options: { label: Logo URL }
            default: /default-logo.svg
        tagline:
            options: { label: Tagline }
            default: Welcome
        displayed_locales:
            type: Symfony\Component\Form\Extension\Core\Type\ChoiceType
            options:
                label: Displayed locales
                multiple: true
                choices: { English: en, Spanish: es, French: fr }
            default: [en]
```

## Usage

```php
use Survos\SettingsBundle\Service\SettingsManagerInterface;

final readonly class BrandingController
{
    public function __construct(private SettingsManagerInterface $settings) {}

    public function updateTenantBranding(): void
    {
        $this->settings->set('logo', '/uploads/tenant/acme.svg', scope: 'tenant:acme');
        $this->settings->set('tagline', 'Archives for everyone', scope: 'tenant:acme');
        $this->settings->set('displayed_locales', ['en', 'es'], scope: 'tenant:acme');
    }
}
```

Reads use the current scope from `ScopeResolverInterface` when the caller does not pass one. If the scoped row does not exist, reads fall back to the global scope when `fallback_to_global` is enabled.

```php
$logo = $settings->get('logo', '/default-logo.svg', scope: 'tenant:acme');
$locales = $settings->get('displayed_locales', ['en'], scope: 'tenant:acme');
```

In Twig:

```twig
<img src="{{ settings_value('logo', '/default-logo.svg') }}" alt="">
<p>{{ settings_value('tagline', 'Welcome') }}</p>
```

Render the editor as a UX LiveComponent:

```twig
<twig:SettingsForm scope="tenant:acme" :settings="['logo', 'tagline', 'displayed_locales']" />
```

When `scope` is omitted, the component uses the app's `ScopeResolverInterface` just like normal reads and writes.

## Tenant scope resolver

Provide an app service that implements `ScopeResolverInterface` when settings should follow the current request tenant automatically:

```php
use Survos\SettingsBundle\Service\ScopeResolverInterface;

final readonly class TenantScopeResolver implements ScopeResolverInterface
{
    public function __construct(private TenantContext $tenantContext) {}

    public function currentScope(): ?string
    {
        return $this->tenantContext->tenant()?->code
            ? 'tenant:'.$this->tenantContext->tenant()->code
            : null;
    }
}
```

Then alias it in the app:

```yaml
services:
    Survos\SettingsBundle\Service\ScopeResolverInterface: '@App\Tenant\TenantScopeResolver'
```

## Relationship to older bundles

This bundle borrows the simple database-centric setting row from `dmishh/settings-bundle` and the typed, modern Symfony style from `jbtronics/settings-bundle`, but keeps scope as a first-class storage key for tenant-aware applications.
