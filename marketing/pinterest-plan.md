# Pinterest — push plan

Pinterest is a much better fit for this brand than most social platforms:
it's a visual *search engine* (not a feed you compete for attention in),
it has no "self-promo" culture to tiptoe around like Reddit, pins have a
long shelf life (a good pin can drive traffic for years, not days), and
home fragrance / gifting / cosy-home content performs consistently well
on it. It's slower to start than Reddit but compounds — worth running
alongside the Reddit push rather than instead of it.

## Setup (once)

1. Convert to a **Pinterest Business account** (free) — unlocks analytics
   and the ability to claim the website.
2. **Claim chapterofyou.co.uk** in Pinterest settings — this attributes
   pins made *by other people* of your product photos back to your
   account too, and unlocks richer analytics.
3. Add the Pinterest save button / verify the site's Open Graph tags are
   solid — they already are (`useSeoHead.ts` sets `og:image`,
   `og:title`, `og:description` per product/page), so pins saved directly
   from the site should pull good title/image/description automatically.

## Board structure

Keep boards specific — Pinterest's search rewards focused boards over one
big "Chapter of You" catch-all. Suggested boards, each seeded with your
own pins first then filled out with a handful of *relevant* repins from
others (search results should never be 100% your own products; it looks
more like a real interest board):

- **Reed Diffuser Scents** — one pin per scent, product photography
- **Cosy Home Ideas** — lifestyle/room shots, using the existing
  `room_tags` taxonomy (bedroom, living room, bathroom, kitchen, office,
  hallway/entryway) as a natural content split
- **Self-Care Rituals** — ties directly to the founder's therapist
  background; less product-forward, more "how to build a wind-down
  routine" — good place to repurpose journal posts
- **Gift Ideas (UK)** — seasonal: Mother's Day, Christmas, birthdays,
  "new home" gifts — this board should get the most active seasonal
  attention since gift search volume on Pinterest spikes hard 4–6 weeks
  before each occasion
- **Behind the Handmade Process** — bottling, labelling, oil blending —
  process content builds trust and differentiates from mass-market diffuser
  brands on Pinterest

## Pin content types (mix these, don't only post product photos)

1. **Product Pins** — clean shot of a single diffuser, vertical (2:3
   ratio, e.g. 1000×1500px), linking to that product's page.
2. **Idea/lifestyle Pins** — diffuser styled in a real room setting
   (matches the "Cosy Home Ideas" board). These outperform plain product
   shots on Pinterest by a wide margin.
3. **Text-overlay Pins** — a photo with a short overlaid headline, e.g.
   "5 signs you need a scent switch-up this season" — these drive the
   *best* click-through because they promise an answer, and can link to a
   journal post rather than a product page.
4. **Blog repins** — every existing journal post is free Pinterest
   content already written — make one pin per post linking back to it.
   This is the single fastest thing to do this week since no new content
   creation is needed, just pin design.

## SEO — pin titles & descriptions

Pinterest search works like a search engine, so titles/descriptions
should read naturally but include the terms people actually search,
reusing the scent/mood language already built into the product data
(`scent_families`, `mood_tags` on `Product`):

| Product example | Pin title | Pin description |
|---|---|---|
| Citrus Sunrise | Citrus Reed Diffuser for an Energising Morning Routine | A bright citrus reed diffuser blend — grapefruit, lemon & mandarin. Handmade in the UK, designed to lift the room without overpowering it. Perfect for kitchens & hallways. |
| Serenity Bloom | Calming Floral Reed Diffuser for the Bedroom | A soft, relaxing floral diffuser blend for bedrooms and quiet spaces. Handmade by a qualified aromatherapist — no factory, just care in every bottle. |
| Cloud Comfort | Cosy Reed Diffuser Scent for Living Rooms | A warm, cosy diffuser blend for evenings in. Handmade reed diffuser, refillable, UK-made. |

General pattern: **[scent family] + reed diffuser + [mood/room] +
outcome**, not just the product name — nobody searches "Citrus Sunrise,"
but plenty of people search "citrus diffuser for kitchen" or "calming
scent for bedroom."

## Cadence

Pinterest rewards *consistency* over volume — 3–5 pins/week beats one big
batch upload once a month, since it signals an active account to the
algorithm.

- **Week 1**: set up boards, pin every existing product (one pin each) +
  every existing journal post (one pin each) — this alone could be
  15–25 pins from content that already exists.
- **Ongoing**: 3–5 new pins/week, mixing product/lifestyle/text-overlay,
  weighted toward whichever board is seasonally relevant (e.g. Gift Ideas
  in the run-up to Christmas/Mother's Day).

## Later / optional technical follow-up

Pinterest supports a **product catalog feed** (similar in spirit to the
Google Shopping feed already built in
`app/Http/Controllers/GoogleShoppingFeedController.php`) that turns
regular pins into shoppable Product Pins with live price/availability. Not
needed to start — worth doing once there's an established pin presence,
and could likely reuse a lot of the existing feed logic. Flagging it here
so it's not forgotten, not something to build yet.
