// Mirrors Product::SCENT_FAMILIES / Product::MOOD_TAGS in app/Models/Product.php.
// Keep in sync — these are the only values the backend will accept.

export interface ScentOption {
    value: string;
    label: string;
}

export const SCENT_FAMILIES: ScentOption[] = [
    { value: 'floral', label: 'Floral' },
    { value: 'woody', label: 'Woody' },
    { value: 'citrus_fresh', label: 'Citrus & Fresh' },
    { value: 'spicy_warm', label: 'Spicy & Warm' },
    { value: 'gourmand_sweet', label: 'Gourmand & Sweet' },
    { value: 'herbal_green', label: 'Herbal & Green' },
];

export const MOOD_TAGS: ScentOption[] = [
    { value: 'relaxing', label: 'Relaxing' },
    { value: 'energising', label: 'Energising' },
    { value: 'cosy', label: 'Cosy' },
    { value: 'romantic', label: 'Romantic' },
    { value: 'fresh_clean', label: 'Fresh & Clean' },
    { value: 'focus_clarity', label: 'Focus & Clarity' },
];

export const INTENSITY_LABELS: Record<number, string> = {
    1: 'Very subtle',
    2: 'Subtle',
    3: 'Moderate',
    4: 'Strong',
    5: 'Very strong',
};

export function scentFamilyLabel(value: string): string {
    return SCENT_FAMILIES.find(f => f.value === value)?.label ?? value;
}

export function moodTagLabel(value: string): string {
    return MOOD_TAGS.find(m => m.value === value)?.label ?? value;
}
