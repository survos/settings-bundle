<?php

declare(strict_types=1);

namespace Survos\SettingsBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Survos\SettingsBundle\Entity\Setting;

/** @extends ServiceEntityRepository<Setting> */
class SettingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Setting::class);
    }

    public function findOneForScope(?string $scope, string $name): ?Setting
    {
        return $this->findOneBy(['scope' => $scope, 'name' => $name]);
    }

    /** @return array<string, mixed> */
    public function valuesForScope(?string $scope): array
    {
        $values = [];
        foreach ($this->findBy(['scope' => $scope], ['name' => 'ASC']) as $setting) {
            $values[$setting->name] = $setting->value;
        }

        return $values;
    }
}
