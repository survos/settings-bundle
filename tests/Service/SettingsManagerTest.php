<?php

declare(strict_types=1);

namespace Survos\SettingsBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Survos\SettingsBundle\Entity\Setting;
use Survos\SettingsBundle\Repository\SettingRepository;
use Survos\SettingsBundle\Service\ScopeResolverInterface;
use Survos\SettingsBundle\Service\SettingsManager;

final class SettingsManagerTest extends TestCase
{
    public function testScopedValueFallsBackToGlobal(): void
    {
        $repo = new InMemorySettingRepository([
            new Setting('global', 'tagline', 'Global tagline'),
            new Setting('tenant:rhs', 'logo', '/rhs.svg'),
        ]);

        $manager = new SettingsManager(
            $this->createStub(EntityManagerInterface::class),
            $repo,
            new FixedScopeResolver('tenant:rhs'),
        );

        self::assertSame('Global tagline', $manager->get('tagline'));
        self::assertSame('/rhs.svg', $manager->get('logo'));
        self::assertSame('fallback', $manager->get('missing', 'fallback'));
        self::assertSame(['tagline' => 'Global tagline', 'logo' => '/rhs.svg'], $manager->all());
    }

    public function testDeclaredDefaultBeatsTheCallersDefault(): void
    {
        $manager = new SettingsManager(
            $this->createStub(EntityManagerInterface::class),
            new InMemorySettingRepository([new Setting('global', 'stored', 'yes')]),
            new FixedScopeResolver(null),
            definitions: [
                'show_github' => ['default' => false],
                'no_default' => ['default' => null],
                'stored' => ['default' => 'declared'],
            ],
        );

        // a bundle that asks get('show_github', true) gets the app's `default: false`
        self::assertFalse($manager->get('show_github', true));
        // a declared null means "no default": the caller's default still applies
        self::assertSame('caller', $manager->get('no_default', 'caller'));
        // an undeclared setting behaves exactly as before
        self::assertSame('caller', $manager->get('undeclared', 'caller'));
        // a stored value always wins over any default
        self::assertSame('yes', $manager->get('stored', 'caller'));
    }
}

final class FixedScopeResolver implements ScopeResolverInterface
{
    public function __construct(private readonly ?string $scope)
    {
    }

    public function currentScope(): ?string
    {
        return $this->scope;
    }
}

/** @internal test double */
final class InMemorySettingRepository extends SettingRepository
{
    /** @param list<Setting> $settings */
    public function __construct(private array $settings)
    {
    }

    public function findOneForScope(?string $scope, string $name): ?Setting
    {
        foreach ($this->settings as $setting) {
            if ($setting->scope === $scope && $setting->name === $name) {
                return $setting;
            }
        }

        return null;
    }

    public function valuesForScope(?string $scope): array
    {
        $values = [];
        foreach ($this->settings as $setting) {
            if ($setting->scope === $scope) {
                $values[$setting->name] = $setting->value;
            }
        }

        return $values;
    }
}
