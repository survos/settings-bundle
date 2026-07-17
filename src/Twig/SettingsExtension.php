<?php

declare(strict_types=1);

namespace Survos\SettingsBundle\Twig;

use Survos\SettingsBundle\Service\SettingsManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class SettingsExtension extends AbstractExtension
{
    public function __construct(private readonly SettingsManagerInterface $settings) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('settings_value', $this->settings->get(...)),
            new TwigFunction('settings_all', $this->settings->all(...)),
        ];
    }
}
