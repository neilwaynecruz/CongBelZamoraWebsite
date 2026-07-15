<?php

namespace App\Enums;

enum ResponsibleOfficeType: string
{
    case DistrictOffice = 'district_office';
    case CityGovernment = 'city_government';
    case NationalAgency = 'national_agency';
    case JointInitiative = 'joint_initiative';

    public function label(): string
    {
        return match ($this) {
            self::DistrictOffice => 'District Office',
            self::CityGovernment => 'City Government',
            self::NationalAgency => 'National Agency',
            self::JointInitiative => 'Joint Initiative',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type): array => [$type->value => $type->label()])
            ->all();
    }
}
