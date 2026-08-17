<?php

namespace App\Support;

class Feature
{
    const BRANCH_MODULE      = 'branch_module';
    const INVENTORY_MODULE   = 'inventory_module';
    const CARD_MODULE        = 'card_module';
    const CERTIFICATE_MODULE = 'certificate_module';

    /**
     * Sob feature-er list, label soho.
     * Notun module add korte hole shudhu ekhane ekta entry barate hobe.
     */
    public static function all(): array
    {
        return [
            self::BRANCH_MODULE      => 'Branch Management',
            self::INVENTORY_MODULE   => 'Inventory Management',
            self::CARD_MODULE        => 'Card Management',
            self::CERTIFICATE_MODULE => 'Certificate Management',
        ];
    }
}