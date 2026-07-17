<?php

declare(strict_types=1);

namespace Survos\SettingsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SettingsFormType extends AbstractType
{
    /** @param array<string, array{type?: class-string, options?: array<string, mixed>, default?: mixed}> $definitions */
    public function __construct(private readonly array $definitions = []) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $names = $options['settings'];
        if ($names === []) {
            $names = array_keys($this->definitions);
        }

        foreach ($names as $name) {
            $definition = $this->definitions[$name] ?? [];
            $type = $definition['type'] ?? TextType::class;
            $fieldOptions = $definition['options'] ?? [];
            $fieldOptions['required'] ??= false;
            $fieldOptions['label'] ??= ucfirst(strtr((string) $name, ['_' => ' ', '-' => ' ']));

            $builder->add((string) $name, $type, $fieldOptions);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'settings' => [],
            'csrf_protection' => true,
        ]);
        $resolver->setAllowedTypes('settings', ['array']);
    }
}
