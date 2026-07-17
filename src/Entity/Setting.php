<?php

declare(strict_types=1);

namespace Survos\SettingsBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Survos\SettingsBundle\Repository\SettingRepository;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: SettingRepository::class)]
#[ORM\Table(name: 'survos_setting')]
#[ORM\UniqueConstraint(name: 'uniq_survos_setting_scope_name', fields: ['scope', 'name'])]
#[ORM\Index(fields: ['scope'], name: 'idx_survos_setting_scope')]
final class Setting
{
    #[ORM\Id]
    #[ORM\Column(length: 26)]
    public private(set) string $id;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    public private(set) \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    public private(set) \DateTimeImmutable $updatedAt;

    public function __construct(
        #[ORM\Column(length: 128)]
        public private(set) string $scope,

        #[ORM\Column(length: 128)]
        public private(set) string $name,

        #[ORM\Column(type: Types::JSON)]
        public private(set) mixed $value = null,
    ) {
        $this->id = (string) new Ulid();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
    }

    public function update(mixed $value): void
    {
        $this->value = $value;
        $this->updatedAt = new \DateTimeImmutable();
    }
}
