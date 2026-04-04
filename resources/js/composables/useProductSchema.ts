/**
 * useProductSchema — Chapter of You
 * ─────────────────────────────────────────────────────────────────────────
 * Builds a Schema.org Product JSON-LD object for a given product.
 *
 * This enables Google rich results showing:
 *   - Star rating and review count under the search listing
 *   - Price and currency
 *   - Availability (in stock / out of stock)
 *   - Product image in image search
 *
 * Usage:
 *
 *   import { useProductSchema } from '@/composables/useProductSchema';
 *   import ProductSchema from '@/components/ProductSchema.vue';
 *
 *   const schema = useProductSchema({
 *       product,           // the full product object from Inertia props
 *       slug,              // SEO slug, e.g. 'lavender-reed-diffuser'
 *   });
 *
 *   // In template:
 *   <ProductSchema :schema="schema" />
 *
 * ─────────────────────────────────────────────────────────────────────────
 */

const BASE_URL = 'https://www.chapterofyou.co.uk';
const BRAND_NAME = 'Chapter of You';
const CURRENCY = 'GBP';

// ── Types matching what ProductController::show() passes down ─────────────
export interface SchemaImage { image: string; }
export interface SchemaReview {
    id: number;
    rating: number;
    message: string;
    created_at: string;
    user: { name: string };
    // admin_reply not needed for schema
}
export interface SchemaProduct {
    id: number;
    name: string;
    mpn: string;
    description: string;
    cost: number;
    stock_qty: number;
    average_rating: number;
    approved_reviews_count: number;
    images: SchemaImage[];
    reviews: SchemaReview[];
    seo?: { meta_title?: string; meta_description?: string; slug?: string; };
}

export interface ProductSchemaOptions {
    product: SchemaProduct;
    /** The SEO slug — used to build the canonical product URL */
    slug?: string;
}

export function useProductSchema({ product, slug }: ProductSchemaOptions): object {
    const productUrl = `${BASE_URL}/product/${slug || product.seo?.slug || product.id}`;

    // ── Images ────────────────────────────────────────────────────────────
    // Google recommends at least one image. Include all enabled product images.
    const images = product.images
        .map(img => img.image.startsWith('http') ? img.image : BASE_URL + img.image)
        .filter(Boolean)
        .slice(0, 5); // Google uses up to 5

    // ── Availability ──────────────────────────────────────────────────────
    const availability = product.stock_qty > 0
        ? 'https://schema.org/InStock'
        : 'https://schema.org/OutOfStock';

    // ── Offers ───────────────────────────────────────────────────────────
    // Price shown includes VAT (Google expects the price customers pay).
    const priceWithVat = +(product.cost * 1.20).toFixed(2);

    const offer = {
        '@type': 'Offer',
        'url': productUrl,
        'priceCurrency': CURRENCY,
        'price': priceWithVat,
        'priceValidUntil': new Date(Date.now() + 365 * 24 * 60 * 60 * 1000)
            .toISOString().split('T')[0], // 1 year from today
        'availability': availability,
        'itemCondition': 'https://schema.org/NewCondition',
        'seller': {
            '@type': 'Organization',
            'name': BRAND_NAME,
            'url': BASE_URL,
        },
    };

    // ── Aggregate rating ─────────────────────────────────────────────────
    // Only include if we have approved reviews — Google ignores empty ratings
    // and may penalise fake ones.
    const aggregateRating = product.approved_reviews_count > 0
        ? {
            '@type': 'AggregateRating',
            'ratingValue': product.average_rating.toFixed(1),
            'reviewCount': product.approved_reviews_count,
            'bestRating': '5',
            'worstRating': '1',
        }
        : undefined;

    // ── Individual reviews ────────────────────────────────────────────────
    // Include up to 5 approved reviews. Google can surface these in rich
    // results but requires they be genuine — we only pass approved ones
    // (the controller already filters by status = 'approved').
    const reviews = product.reviews.slice(0, 5).map(review => ({
        '@type': 'Review',
        'reviewRating': {
            '@type': 'Rating',
            'ratingValue': String(review.rating),
            'bestRating': '5',
            'worstRating': '1',
        },
        'author': {
            '@type': 'Person',
            'name': review.user.name,
        },
        'reviewBody': review.message,
        'datePublished': review.created_at.split('T')[0],
    }));

    // ── Description ───────────────────────────────────────────────────────
    // Strip HTML tags from the description for the schema plain-text field.
    const plainDescription = (product.seo?.meta_description || product.description)
        .replace(/<[^>]*>/g, '')
        .trim()
        .slice(0, 500);

    // ── Build final schema object ─────────────────────────────────────────
    const schema: Record<string, any> = {
        '@context': 'https://schema.org',
        '@type': 'Product',
        'name': product.name,
        'description': plainDescription,
        'sku': product.mpn,
        'mpn': product.mpn,
        'brand': {
            '@type': 'Brand',
            'name': BRAND_NAME,
        },
        'url': productUrl,
        'offers': offer,
    };

    // Add images if available
    if (images.length > 0) {
        schema['image'] = images.length === 1 ? images[0] : images;
    }

    // Add aggregate rating if available
    if (aggregateRating) {
        schema['aggregateRating'] = aggregateRating;
    }

    // Add individual reviews if available
    if (reviews.length > 0) {
        schema['review'] = reviews;
    }

    return schema;
}


// ══════════════════════════════════════════════════════════════════════════
// ORGANISATION SCHEMA
// Use this on the homepage and any page that benefits from site-wide context.
// ══════════════════════════════════════════════════════════════════════════
export function useOrganizationSchema(): object {
    return {
        '@context': 'https://schema.org',
        '@type': 'Organization',
        'name': BRAND_NAME,
        'url': BASE_URL,
        'logo': {
            '@type': 'ImageObject',
            'url': `${BASE_URL}/storage/images/large_image.png`,
        },
        'sameAs': [
            // Add social profile URLs here when available, e.g.:
            // 'https://www.instagram.com/chapterofyou',
        ],
        'contactPoint': {
            '@type': 'ContactPoint',
            'contactType': 'customer service',
            'email': 'contact@chapterofyou.co.uk',
            'availableLanguage': 'English',
        },
    };
}


// ══════════════════════════════════════════════════════════════════════════
// WEBSITE SCHEMA
// Enables Google Sitelinks Searchbox in search results.
// ══════════════════════════════════════════════════════════════════════════
export function useWebsiteSchema(): object {
    return {
        '@context': 'https://schema.org',
        '@type': 'WebSite',
        'name': BRAND_NAME,
        'url': BASE_URL,
        'potentialAction': {
            '@type': 'SearchAction',
            'target': {
                '@type': 'EntryPoint',
                'urlTemplate': `${BASE_URL}/products?search={search_term_string}`,
            },
            'query-input': 'required name=search_term_string',
        },
    };
}


// ══════════════════════════════════════════════════════════════════════════
// BREADCRUMB SCHEMA
// Use this on product pages alongside the visual breadcrumb.
// Helps Google understand site structure and can show breadcrumbs
// in search results instead of the raw URL.
// ══════════════════════════════════════════════════════════════════════════
export interface BreadcrumbItem {
    name: string;
    url: string;
}

export function useBreadcrumbSchema(items: BreadcrumbItem[]): object {
    return {
        '@context': 'https://schema.org',
        '@type': 'BreadcrumbList',
        'itemListElement': items.map((item, index) => ({
            '@type': 'ListItem',
            'position': index + 1,
            'name': item.name,
            'item': item.url.startsWith('http') ? item.url : BASE_URL + item.url,
        })),
    };
}
