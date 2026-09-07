# UX review

Reviewed 7 September 2026.

Implementation status: the findings below were addressed in the working tree after this review. The screenshots remain the before-state evidence used to guide the changes.

The site has a cohesive visual identity, appealing product photography and a personal tone that suits a small handmade business. The biggest opportunities are purchase recovery, clearer delivery expectations and helping mobile visitors understand and compare products sooner. A full visual redesign is not necessary to address these problems.

## Scope and evidence

Live browser inspection covered the homepage at 1440 × 1000 and 390 × 844, plus the collection, Citrus Sunrise product, gift vouchers, first Scent Finder step, delivery, contact and empty basket at 390 × 844. Screenshots are saved alongside this report. The product page was also captured after declining analytics cookies in a temporary browser profile.

Checkout, account-related behaviour and review image behaviour were assessed from the local source. These findings are not live payment reproductions, and the deployed version may differ. No orders, payments, messages or account registrations were submitted. This is an expert inspection, not participant research, a complete accessibility audit or a performance benchmark. Authenticated journeys, populated basket behaviour, quiz results and payment completion still need hands-on testing in staging.

## Priority findings

### 1. Recoverable checkout errors disable further payment attempts

**Priority: High. Evidence: source-confirmed.**

The checkout sets `paymentError` when required details are missing or Stripe returns an error. The payment button is disabled whenever that error exists. The only reset is inside the same payment handler, so correcting an input cannot make the button usable again. A customer can get stuck after an ordinary validation error or declined payment.

Source: [checkout handler](../../resources/js/pages/checkout/View.vue), `handleCardPayment` around line 267 and payment button around line 821.

**Change:** Separate recoverable validation errors from payment readiness. Allow retry after correction, preserve entered details and put relevant errors beside the fields. Move focus to the first invalid field.

**Check:** In Stripe test mode, submit incomplete details, correct them and retry successfully without refreshing. Repeat with a declined card and then a successful test card. Verify duplicate clicks cannot create duplicate orders.

### 2. Delivery promises conflict and leave the arrival date unclear

**Priority: High. Evidence: live copy and source.**

The live product page says dispatch normally takes 3 to 5 working days. The delivery page says processing normally takes 2 to 3 working days. Its delivery section gives neither a normal transit range nor standard postage prices. Customers buying gifts cannot confidently judge timing or total cost.

The basket source displays “At checkout” for paid delivery. Free delivery copy says “over £50”, while the calculation includes exactly £50. Physical gift vouchers add £2.99 even when the free delivery threshold is reached, which needs an explicit exception to the general promise.

Evidence: [delivery capture](delivery-mobile.png), [product source](../../resources/js/pages/product/Show.vue), [delivery source](../../resources/js/pages/Delivery.vue), [basket source](../../resources/js/pages/cart/View.vue), [shipping calculation](../../app/Services/Checkout/CheckoutTotals.php).

**Change:** Agree one dispatch policy, distinguish dispatch from transit time, and show a realistic delivery range and postage cost before checkout. Use consistent threshold wording and disclose physical voucher postage at selection.

**Check:** Compare product, basket, checkout and delivery copy. Verify totals below, at and above £50, including physical vouchers and mixed baskets.

### 3. The collection sort control is blank on first load

**Priority: Medium. Evidence: live visual and source.**

The mobile collection screenshot shows an empty sort dropdown. The default and reset value is `mpn,asc`, but none of the four displayed options has that value. Customers cannot tell the current ordering, and the control looks broken.

Evidence: [collection screenshot](products-mobile.png), [collection source](../../resources/js/pages/product/View.vue), initial sort around line 75 and options around line 430.

**Change:** Use a supported default or add an honestly labelled default ordering option. Keep the selected label visible on mobile.

**Check:** First visit, filter reset, pagination and browser Back all show the correct selected sort option.

### 4. Mobile product information arrives late

**Priority: Medium. Evidence: live visual.**

On Citrus Sunrise at 390 × 844, the main image and two rows of thumbnails occupy most of the first screen. The full product title appears near the bottom, with details such as 100ml size and 8 to 12 week lifespan further down. The sticky purchase bar usefully provides a name, price and Add to Cart button after the cookie notice is dismissed, but customers still need to scroll to understand what they are buying.

Evidence: [product with cookie notice dismissed](product-mobile-clear.png).

**Change:** Place the title and a short scent summary above or beside the main image on mobile. Keep thumbnails to a horizontal row and surface size, expected lifespan and dispatch timing close to the price. Preserve the sticky purchase bar.

**Check:** At 390px and 320px widths, visitors can quickly identify the product, price and essential specifications without navigating a tall thumbnail grid. Verify refill selection and quantity still work.

### 5. First-visit overlays compete with the main actions

**Priority: Medium. Evidence: live visual.**

The mobile cookie notice occupies roughly the bottom 185px of an 844px viewport. On the homepage it overlaps part of the main shopping button; on the collection it obscures product information; on the quiz it covers lower choices. The homepage also spends substantial space on the seasonal banner and brand title before showing the products or explicitly describing them as reed diffusers.

Evidence: [mobile homepage](home-mobile.png), [collection](products-mobile.png), [quiz](scent-mobile.png), [desktop homepage](home-desktop.png).

**Change:** Reduce the mobile notice height while keeping both consent choices clear and equally accessible. Tighten the seasonal banner and hero spacing, and put “Handmade reed diffusers” or similarly concrete product wording in the hero. Ensure fixed purchase controls and the notice do not overlap.

**Check:** Test a fresh visit and both consent choices on narrow screens, including keyboard navigation and enlarged text. Shopping actions remain reachable and consent behaviour is preserved.

### 6. Product cards make scent comparison harder than necessary

**Priority: Medium. Evidence: live collection inspection.**

Cards primarily provide photography, names and prices. Names such as Serenity Bloom and Cloud Comfort do not tell a new customer enough to choose between fragrances. Visitors must repeatedly open product pages or find the quiz.

**Change:** Add a short, accurate scent descriptor to each card, such as the principal notes or fragrance family. Clearly identify refills and full diffusers, and offer the Scent Finder near collection browsing controls. With only 17 products, useful descriptions matter more than adding more complex navigation.

**Check:** A visitor can distinguish scent style and product type from the collection alone. Verify descriptions remain legible in the mobile two-column layout.

### 7. Gift voucher server validation can fail without visible field errors

**Priority: High. Evidence: source-confirmed.**

The voucher form sends fields such as `sender_email` and `recipient_email`. Returned server validation errors are stored using those names, while the template looks for `senderEmail` and `recipientEmail`. Errors for these fields therefore do not appear in their intended locations. A rejected request can leave the buyer unsure what to correct.

Source: [gift voucher form](../../resources/js/pages/gift-voucher/Purchase.vue), request/error handling around lines 75 to 96 and email errors around lines 305 and 395.

**Change:** Use consistent field names or explicitly map server errors. Add an accessible error summary and focus the first invalid field.

**Check:** In staging, trigger server validation errors for each required field and confirm that the message appears, the entered values remain and a corrected submission succeeds.

### 8. E-voucher checkout repeats information and uses physical-order fields

**Priority: Medium. Evidence: live voucher form and checkout source.**

The voucher form already asks for the sender's name and email. The checkout form starts those fields empty and does not receive the sender fields in its voucher prop. It also requires an address for an email-only purchase. This adds effort and leaves the role of that address unclear. The email voucher form also displays “Messages are handwritten for a personal touch”, which needs to be restricted to the appropriate delivery type.

Source: [voucher form](../../resources/js/pages/gift-voucher/Purchase.vue), [checkout](../../resources/js/pages/checkout/View.vue), [request requirements](../../app/Http/Requests/CheckoutRequest.php).

**Change:** Carry sender details into checkout. For digital-only orders, request only necessary billing information and label it explicitly. Show delivery-specific message and postage copy.

**Check:** Complete digital-only, physical-only and mixed orders in staging. Sender, recipient and billing details stay distinct and reach the correct destinations.

### 9. “Save my details” promises behaviour that is not wired up

**Priority: Medium. Evidence: source-confirmed.**

Signed-in checkout offers “Save my details for faster checkout next time”. The `saveInfo` value is present in the frontend, but is absent from validated checkout fields and has no persistence handling in the inspected checkout path. The control creates an expectation that the current implementation does not fulfil.

Source: [checkout checkbox](../../resources/js/pages/checkout/View.vue), around line 724, and [checkout request](../../app/Http/Requests/CheckoutRequest.php).

**Change:** Implement deliberate address saving with clear semantics or remove the checkbox until it works.

**Check:** With a staging account, save new details and verify they are available on the next visit. Leaving the option unchecked must not silently change saved details.

### 10. Image viewing has context and keyboard problems

**Priority: Medium. Evidence: source-confirmed; review-photo case not reproduced live.**

Clicking a customer review photo opens the shared viewer with `product.images`, rather than that review's photos. The viewer supports Escape and arrow keys, but has no explicit focus transfer, containment or restoration. Keyboard users can remain focused behind a dialog that declares itself modal.

Source: [review photo click and viewer props](../../resources/js/pages/product/Show.vue), around lines 1056 and 1132; [image viewer](../../resources/js/components/ui/coy/ModalImageViewer.vue).

**Change:** Pass the clicked image collection and index to the viewer. Use consistent modal focus behaviour and make review photo triggers keyboard-operable buttons.

**Check:** Open both gallery and review images by keyboard. The correct image appears, focus stays inside while open and returns to the trigger after closing.

## What to preserve

- Cohesive colours, typography, photography and personal brand voice.
- Guest checkout and the mobile sticky purchase action.
- Product usage guidance, lifespan details and FAQs.
- The Scent Finder's visible progress and clear selection limits.
- The empty basket's direct route back to shopping.
- Contact details and a stated response time of 1 to 2 working days.
- No horizontal page overflow was detected in the sampled mobile viewport captures.

## Suggested implementation sequence

1. Fix checkout recovery and voucher error visibility, with meaningful regression tests.
2. Align delivery promises, costs and gift voucher expectations.
3. Correct collection sorting and simplify the mobile product and first-visit layouts.
4. Add scent comparison details, complete saved-address behaviour and improve image viewer accessibility.

Keep each change small and independently reviewable. Before release, run existing backend and frontend checks, then exercise a staging guest purchase, a signed-in purchase, a payment failure followed by retry, vouchers, discounts and narrow-screen keyboard navigation. This review changed documentation and screenshots only; it does not establish that purchasing works end to end.
