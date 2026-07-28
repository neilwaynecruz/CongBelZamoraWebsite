<?php

namespace App\Enums;

enum InquiryCategory: string
{
    case EducationalAssistance = 'educational_assistance';
    case MedicalSocialServices = 'medical_social_services';
    case LegislativeFeedback = 'legislative_feedback';
    case GeneralInquiry = 'general_inquiry';

    public function label(): string
    {
        return match ($this) {
            self::EducationalAssistance => 'Educational assistance',
            self::MedicalSocialServices => 'Medical or social-service routing',
            self::LegislativeFeedback => 'Legislative feedback',
            self::GeneralInquiry => 'General inquiry',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::EducationalAssistance => 'For questions about Tulong Dunong, published student-assistance requirements, schedules, and official application-channel guidance.',
            self::MedicalSocialServices => 'For privacy-safe routing questions involving medical, welfare, burial, or other city/barangay/national-agency services.',
            self::LegislativeFeedback => 'For comments or questions about bills, policy work, committee concerns, public consultations, or legislative source records.',
            self::GeneralInquiry => 'For basic District Office questions that do not fit the other service categories.',
        };
    }

    /**
     * @return array<int, array{value: string, label: string, description: string}>
     */
    public static function publicOptions(): array
    {
        return collect(self::cases())
            ->map(fn (self $category): array => [
                'value' => $category->value,
                'label' => $category->label(),
                'description' => $category->description(),
            ])
            ->all();
    }
}
