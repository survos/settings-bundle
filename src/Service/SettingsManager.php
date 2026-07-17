<?php

declare(strict_types=1);

namespace Survos\SettingsBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Survos\SettingsBundle\Entity\Setting;
use Survos\SettingsBundle\Repository\SettingRepository;

final readonly class SettingsManager implements SettingsManagerInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private SettingRepository $settings,
        private ScopeResolverInterface $scopeResolver,
        private string $globalScope = 'global',
        private bool $fallbackToGlobal = true,
    ) {}

    public function get(string $name, mixed $default = null, ?string $scope = null): mixed
    {
        $scope = $this->normalizeScope($scope);

        if (null !== $setting = $this->settings->findOneForScope($scope, $name)) {
            return $setting->value;
        }

        if ($this->fallbackToGlobal && $scope !== $this->globalScope) {
            if (null !== $setting = $this->settings->findOneForScope($this->globalScope, $name)) {
                return $setting->value;
            }
        }

        return $default;
    }

    public function set(string $name, mixed $value, ?string $scope = null, bool $flush = true): void
    {
        $scope = $this->normalizeScope($scope);
        $setting = $this->settings->findOneForScope($scope, $name);

        if (null === $setting) {
            $setting = new Setting($scope, $name, $value);
            $this->em->persist($setting);
        } else {
            $setting->update($value);
        }

        if ($flush) {
            $this->em->flush();
        }
    }

    public function all(?string $scope = null, bool $includeGlobalFallback = true): array
    {
        $scope = $this->normalizeScope($scope);
        $values = $includeGlobalFallback && $scope !== $this->globalScope
            ? $this->settings->valuesForScope($this->globalScope)
            : [];

        return array_replace($values, $this->settings->valuesForScope($scope));
    }

    public function delete(string $name, ?string $scope = null, bool $flush = true): void
    {
        $scope = $this->normalizeScope($scope);
        $setting = $this->settings->findOneForScope($scope, $name);
        if (null === $setting) {
            return;
        }

        $this->em->remove($setting);
        if ($flush) {
            $this->em->flush();
        }
    }

    public function flush(): void
    {
        $this->em->flush();
    }

    private function normalizeScope(?string $scope): string
    {
        return $scope ?? $this->scopeResolver->currentScope() ?? $this->globalScope;
    }
}
