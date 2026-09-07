# Chapter of You storefront design system

The storefront foundation lives in `resources/css/storefront-design-system.css`. Brand-wide visual decisions should be made there. Admin styles remain separate.

## Using the system

Wrap each public page in `coy-storefront`. This supplies the storefront typeface, text colour, line height, background, focus treatment, reduced-motion behaviour, and high-contrast support.

Use semantic tokens in component styles instead of copying palette values:

- `--coy-color-page` for the page canvas
- `--coy-color-surface` and `--coy-color-surface-soft` for raised or alternate surfaces
- `--coy-color-text` for body copy
- `--coy-color-heading` for headings and strong text
- `--coy-color-accent` for primary actions and important highlights
- `--coy-color-border` for standard borders
- `--coy-font-display` and `--coy-font-body` for typography
- `--coy-section-space` and `--coy-gutter` for responsive page spacing
- `--coy-container-*` for content widths
- `--coy-radius-*`, `--coy-shadow-*`, and `--coy-duration-*` for consistent components

The raw palette tokens describe the brand colours. Components should normally use semantic tokens so a future palette change does not require component edits.

## Reusable classes

- `coy-container` centres content with a responsive gutter
- `coy-section` applies standard responsive vertical spacing
- `coy-heading`, `coy-heading--hero`, and `coy-heading--section` provide the display type hierarchy
- `coy-eyebrow` provides an accessible short label style
- `coy-prose` provides a readable line length and line height
- `coy-card` provides the standard surface, border, radius, and shadow
- `coy-button coy-button--primary` provides the main action
- `coy-button coy-button--secondary` provides the secondary action
- `coy-field` provides a shared form control foundation

## Page-specific CSS

Page styles should control composition, such as grids, hero arrangements, image ratios, and unique artwork. They should consume the shared tokens for brand styling. A page should introduce a local custom property only when the value has no useful meaning elsewhere.

## Accessibility baseline

Source Sans 3 is the functional and reading typeface. Newsreader is reserved for headings and short editorial accents. Body content starts at 16px and uses a relaxed line height. Interactive controls have a minimum height of 44px and a visible keyboard focus state.

Motion is reduced automatically when the visitor requests reduced motion. Reusable controls also preserve their borders in forced-colour modes.
