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
            self::EducationalAssistance => 'Questions about Tulong Dunong, student orientation, or education-program routing.',
            self::MedicalSocialServices => 'General routing questions for medical, welfare, burial, or city/national-agency services.',
            self::LegislativeFeedback => 'Comments or questions about bills, policy work, committee concerns, or public consultations.',
            self::GeneralInquiry => 'Basic District Office questions that do not fit the other categories.',
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
