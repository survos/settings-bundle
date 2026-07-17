<?php

declare(strict_types=1);

namespace Survos\SettingsBundle\Service;

interface ScopeResolverInterface
{
    /** Return the current tenant/user/site scope, or null for global context. */
    public function currentScope(): ?string;
}
