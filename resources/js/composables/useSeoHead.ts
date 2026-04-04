/**
 * useSeoHead — Chapter of You
 * ─────────────────────────────────────────────────────────────────────────
 * Returns computed SEO values (title, description, canonical, OG tags)
 * for use inside each page's <Head> block.
 *
 * Usage:
 *
 *   import { useSeoHead } from '@/composables/useSeoHead';
 *
 *   const seo = useSeoHead({
 *     title:       'Lavender Reed Diffuser',
 *     description: 'A calming blend of lavender and eucalyptus...',
 *     canonical:   '/product/lavender-reed-diffuser',   // path OR full URL
 *     ogImage:     product.images[0]?.image,
 *     ogType:      'product',
 *   });
 *
 * Then in your template:
 *
 *   <Head>
 *     <title>{{ seo.title }}</title>
 *     <meta name="description"        :content="seo.description" />
 *     <link rel="canonical"           :href="seo.canonical" />
 *     <meta property="og:title"       :content="seo.title" />
 *     <meta property="og:description" :content="seo.description" />
 *     <meta property="og:url"         :content="seo.canonical" />
 *     <meta property="og:image"       :content="seo.ogImage" />
 *     <meta property="og:type"        :content="seo.ogType" />
 *     <meta name="twitter:title"      :content="seo.title" />
 *     <meta name="twitter:description":content="seo.description" />
 *     <meta name="twitter:image"      :content="seo.ogImage" />
 *     <meta name="robots"             :content="seo.robots" />
 *   </Head>
 *
 * All options are optional — defaults are applied for anything not supplied.
 * ─────────────────────────────────────────────────────────────────────────
 */

const SITE_NAME = 'Chapter of You';
const BASE_URL = 'https://www.chapterofyou.co.uk';
const DEFAULT_DESC = 'Discover luxurious hand-crafted reed diffusers made with care. Chapter of You — your chapter, your self-care.';
const DEFAULT_IMG = `${BASE_URL}/storage/images/large_image.png`;

export interface SeoOptions {
    /** Page-level title. Will be output as "{title} | Chapter of You". */
    title?: string;
    /** Meta description. Keep under 160 characters. */
    description?: string;
    /**
     * Canonical URL for this page.
     * Pass a path ('/product/lavender') or a full URL.
     * Defaults to window.location.href if not supplied.
     */
    canonical?: string;
    /** Absolute URL of the Open Graph / Twitter share image. */
    ogImage?: string;
    /**
     * Open Graph type.
     * Use 'product' on product detail pages, 'website' everywhere else.
     */
    ogType?: 'website' | 'product' | 'article';
    /**
     * Set true on pages that should not be indexed
     * (e.g. order confirmation, account pages, admin).
     */
    noIndex?: boolean;
}

export function useSeoHead(options: SeoOptions = {}) {
    // ── Title ──────────────────────────────────────────────────────────────
    // Homepage: just "Chapter of You"
    // All other pages: "Page Name | Chapter of You"
    const title = options.title
        ? `${options.title} | ${SITE_NAME}`
        : SITE_NAME;

    // ── Description ────────────────────────────────────────────────────────
    // Truncate to 160 chars to avoid Google truncating in search results.
    const rawDesc = options.description ?? DEFAULT_DESC;
    const description = rawDesc.length > 160
        ? rawDesc.slice(0, 157) + '…'
        : rawDesc;

    // ── Canonical ──────────────────────────────────────────────────────────
    // If caller passes a relative path, prepend the base URL.
    // If caller passes nothing, we fall back to BASE_URL (the composable
    // runs server-side during SSR too, so window may be undefined — safe).
    let canonical = options.canonical ?? '';
    if (canonical && !canonical.startsWith('http')) {
        // Ensure leading slash
        canonical = BASE_URL + (canonical.startsWith('/') ? '' : '/') + canonical;
    }
    if (!canonical) {
        // Best effort: use current URL in browser, BASE_URL during SSR
        canonical = typeof window !== 'undefined'
            ? window.location.origin + window.location.pathname
            : BASE_URL;
    }

    // ── OG / Twitter ───────────────────────────────────────────────────────
    const ogImage = options.ogImage
        // If the image URL is relative (starts with /storage/...) prepend base
        ? (options.ogImage.startsWith('http') ? options.ogImage : BASE_URL + options.ogImage)
        : DEFAULT_IMG;

    const ogType = options.ogType ?? 'website';

    // ── Robots ─────────────────────────────────────────────────────────────
    const robots = options.noIndex ? 'noindex, nofollow' : 'index, follow';

    return {
        title,
        description,
        canonical,
        ogImage,
        ogType,
        robots,
        siteName: SITE_NAME,
    };
}
