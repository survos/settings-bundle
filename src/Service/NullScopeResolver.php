<?php

declare(strict_types=1);

namespace Survos\SettingsBundle\Service;

final class NullScopeResolver implements ScopeResolverInterface
{
    public function currentScope(): ?string
    {
        return null;
    }
}
