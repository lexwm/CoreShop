<?php

namespace CoreShop\Component\Index\Extension;

use CoreShop\Component\Index\Model\IndexColumnInterface;
use CoreShop\Component\Index\Model\IndexInterface;

class StringCaseSensitiveTypeConfigExtension implements IndexColumnTypeConfigExtension
{
    public function getColumnConfig(IndexColumnInterface $column): array
    {
        return match ($column->getColumnType()) {
            IndexColumnInterface::FIELD_TYPE_STRING_CASE_SENSITIVE => ['ColumnDefinition' => 'varchar (255) COLLATE `utf8mb4_bin`'],
            default => [],
        };
    }

    public function supports(IndexInterface $index): bool
    {
        return $index->getWorker() === 'mysql';
    }
}