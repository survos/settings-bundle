<?php

declare(strict_types=1);

namespace Survos\SettingsBundle\Twig\Components;

use Survos\SettingsBundle\Form\SettingsFormType;
use Survos\SettingsBundle\Service\SettingsManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('SettingsForm', template: '@SurvosSettings/components/SettingsForm.html.twig')]
final class SettingsForm
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public ?string $scope = null;

    /** @var list<string> */
    #[LiveProp(writable: true)]
    public array $settings = [];

    /** @var array<string, mixed> */
    #[LiveProp(writable: true)]
    public array $initialValues = [];

    /** @param array<string, array{default?: mixed}> $definitions */
    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly SettingsManagerInterface $settingsManager,
        private readonly array $definitions = [],
    ) {}

    #[LiveAction]
    public function save(): void
    {
        $this->submitForm();
        $data = $this->getForm()->getData();

        foreach ($data as $name => $value) {
            $this->settingsManager->set((string) $name, $value, $this->scope, flush: false);
        }
        $this->settingsManager->flush();
        $this->initialValues = $data;
    }

    protected function instantiateForm(): FormInterface
    {
        $names = $this->settings ?: array_keys($this->definitions);
        $data = [];
        foreach ($names as $name) {
            $data[$name] = $this->initialValues[$name]
                ?? $this->settingsManager->get($name, $this->definitions[$name]['default'] ?? null, $this->scope);
        }

        return $this->formFactory->create(SettingsFormType::class, $data, [
            'settings' => $names,
        ]);
    }
}
