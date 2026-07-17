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
}

final class FixedScopeResolver implements ScopeResolverInterface
{
    public function __construct(private readonly ?string $scope) {}

    public function currentScope(): ?string
    {
        return $this->scope;
    }
}

/** @internal test double */
final class InMemorySettingRepository extends SettingRepository
{
    /** @param list<Setting> $settings */
    public function __construct(private array $settings) {}

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
