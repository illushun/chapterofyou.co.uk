// Mirrors Product::SCENT_FAMILIES / Product::MOOD_TAGS / Product::ROOMS in app/Models/Product.php.
// Keep in sync - these are the only values the backend will accept.

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

export const ROOMS: ScentOption[] = [
    { value: 'bedroom', label: 'Bedroom' },
    { value: 'living_room', label: 'Living Room' },
    { value: 'bathroom', label: 'Bathroom' },
    { value: 'kitchen', label: 'Kitchen' },
    { value: 'office', label: 'Office' },
    { value: 'hallway_entryway', label: 'Hallway & Entryway' },
];

export function scentFamilyLabel(value: string): string {
    return SCENT_FAMILIES.find((f) => f.value === value)?.label ?? value;
}

export function moodTagLabel(value: string): string {
    return MOOD_TAGS.find((m) => m.value === value)?.label ?? value;
}

export function roomLabel(value: string): string {
    return ROOMS.find((r) => r.value === value)?.label ?? value;
}
