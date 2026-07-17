<?php

declare(strict_types=1);

namespace Survos\SettingsBundle\Service;

interface SettingsManagerInterface
{
    public function get(string $name, mixed $default = null, ?string $scope = null): mixed;

    public function set(string $name, mixed $value, ?string $scope = null, bool $flush = true): void;

    /** @return array<string, mixed> */
    public function all(?string $scope = null, bool $includeGlobalFallback = true): array;

    public function delete(string $name, ?string $scope = null, bool $flush = true): void;

    public function flush(): void;
}
