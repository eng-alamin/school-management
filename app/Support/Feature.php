<?php

namespace App\Support;

class Feature
{
    const BRANCH_MODULE                 = 'branch_module';
    const INVENTORY_MANAGEMENT_MODULE   = 'inventory_management_module';
    const CARD_MANAGEMENT_MODULE        = 'card_management_module';
    const CERTIFICATE_MANAGEMENT_MODULE = 'certificate_management_module';

    /**
     * Sob feature-er list, label soho.
     * Notun module add korte hole shudhu ekhane ekta entry barate hobe.
     */
    public static function all(): array
    {
        return [
            self::BRANCH_MODULE                 => 'Branch Management',
            self::INVENTORY_MANAGEMENT_MODULE   => 'Inventory Management',
            self::CARD_MANAGEMENT_MODULE        => 'Card Management',
            self::CERTIFICATE_MANAGEMENT_MODULE => 'Certificate Management',
        ];
    }
}