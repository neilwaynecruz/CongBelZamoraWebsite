<?php

namespace App\Enums;

enum SourceType: string
{
    case PrimaryOfficial = 'primary_official';
    case ReliableSecondary = 'reliable_secondary';
    case DiscoveryOnly = 'discovery_only';
    case InternalApproval = 'internal_approval';

    public function label(): string
    {
        return match ($this) {
            self::PrimaryOfficial => 'Primary official source',
            self::ReliableSecondary => 'Reliable secondary source',
            self::DiscoveryOnly => 'Discovery-only source',
            self::InternalApproval => 'Internal signed approval',
        };
    }
}
