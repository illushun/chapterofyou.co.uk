# Chapter of You storefront design system

## Source of truth

The current production versions of these pages define the storefront design direction:

- Homepage: `resources/js/pages/home/LandingPage.vue`
- Basket: `resources/js/pages/cart/View.vue`
- Products: `resources/js/pages/product/View.vue`
- Checkout: `resources/js/pages/checkout/View.vue`

New customer-facing pages should feel native beside these pages. When written guidance conflicts with their established patterns, inspect these implementations and follow the most relevant existing pattern.

The experimental editorial homepage prompt and the reverted 9 September redesigns are not part of the active design system.

## Shared foundation

Use `resources/css/storefront-design-system.css` and its `coy-*` components and tokens. Do not create a separate token set inside an individual page.

### Colour

- Blush: `#EBC7CF`
- Rose: `#D9A6B3`
- Champagne: `#E8D5C4`
- Rose gold: `#B8897C`
- Cream: `#FAF6F2`
- Taupe: `#665A57`
- Heading ink: `#342A28`
- Primary accent: `#794750`
- Primary accent hover: `#60363E`

Prefer semantic variables such as `--coy-color-page`, `--coy-color-surface`, `--coy-color-text`, `--coy-color-heading`, `--coy-color-accent` and `--coy-color-border` over hardcoded values.

### Typography

- Display and headings: Newsreader
- Body and interface text: Source Sans 3
- Use the responsive `--coy-text-*` scale.
- Keep body text readable and avoid undersized labels.
- Use sentence case for headings, labels and buttons unless an existing component establishes another pattern.

### Layout and spacing

- Use `.coy-container` for standard content width.
- Use `.coy-section` or `--coy-section-space` for major sections.
- Use the `--coy-space-*` scale for gaps, padding and margins.
- Keep navigation and page headers compact enough that products or the main task remain prominent.
- Preserve clear alignment across headings, content, forms and actions.

### Components

- Reuse `.coy-heading`, `.coy-eyebrow`, `.coy-card`, `.coy-button`, `.coy-field` and `.coy-page-header` where appropriate.
- Primary buttons use the deep rose accent and light text.
- Secondary actions use a light surface with a fine border.
- Cards use warm surfaces, restrained shadows and the established rounded corners.
- Focus states must remain clearly visible.

## Experience principles

- Prefer familiar ecommerce behaviour over experimental interaction.
- Make the page's main purpose and next action immediately clear.
- Put products, prices, basket contents or checkout controls before supporting content.
- Keep copy concise, specific and useful.
- Use the Scent Finder as optional help rather than a required step.
- Do not add decorative sections merely to fill the page.
- Avoid generic trust-icon rows, false urgency, countdowns and unnecessary promotional banners.
- Never preselect or automatically add refills and other extras.
- Use real product and business data rather than invented claims.

## Page patterns

### Product discovery

Follow the current products page for category navigation, product cards, filters, spacing and responsive behaviour. Product names, images and prices should be easy to scan. Keep product links crawlable.

### Basket

Follow the current basket for line-item presentation, quantities, totals, empty states and checkout progression. Keep pricing transparent and make the checkout action dominant.

### Checkout

Follow the current checkout for its compact branded header, form hierarchy, order summary, validation and responsive layout. Reduce distractions and preserve entered information when errors occur.

### Content and landing pages

Follow the current homepage for brand expression, promotional hierarchy, section rhythm, imagery and calls to action. Adapt its language to the task instead of copying its complete section sequence.

### Product image galleries

- Show one main image at a time.
- Place thumbnail previews beneath the main image.
- Support previous and next controls, keyboard navigation and mobile swiping.
- Allow mouse-wheel image changes only while the pointer is over the gallery.
- Open a larger viewer when the main image is selected.
- Support a second click to zoom towards the cursor.
- Do not stack every product image vertically.

## SEO and accessibility

- Use one descriptive `h1` per page and maintain a logical heading hierarchy.
- Use semantic landmarks, descriptive links and useful image alt text.
- Preserve canonical URLs, metadata and relevant structured data.
- Keep important product and category links crawlable.
- Meet WCAG AA contrast and provide visible keyboard focus.
- Use properly labelled controls and communicate validation errors accessibly.
- Support mobile, tablet and desktop layouts without horizontal scrolling.
- Respect `prefers-reduced-motion`.

## Repository rules

- Keep code comments to one physical line.
- Remove comments that merely repeat the code.
- Do not use em dash characters or encoded equivalents in code, documentation or customer-facing copy.
- Format, lint, type-check and test relevant changes.
- Visually review customer-facing changes at desktop and mobile sizes.
