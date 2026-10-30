# Visual and responsive QA

Reference: `design/concept.png` (1536×1024 generated design concept selected for this implementation). Actual browser screenshots: `evidence/catalog-desktop.png`, `catalog-mobile.png`, product/checkout/order/chat and seller screenshots. Mockup is not test evidence.

Verification used Playwright with installed Chrome because the Browser skill/plugin was absent; desktop viewport 1536×1024, authenticated screens 1440×1000, mobile 390×844. Waited for image decoding and network completion. The latest browser and concept images were visually compared using the image viewer.

| Comparison point | Reference and actual result |
| --- | --- |
| Palette | White background, dark ink, forest green buttons, pale sage and restrained lime condition tags; no added gradients or hero color wash |
| Typography | Large two-line editorial serif headline and CLOZEE wordmark, plain readable sans-serif controls; Thai functional copy uses local fonts |
| Header | Brand left, Explore / My orders / Sell with us, login right. Logged-in state adds cart/messages/logout as required operations |
| Hero | Left typography, right linen/denim image; original standalone generated asset with the same material/palette, no text baked into the web UI |
| Filters | Search, All/Preloved/New radios, category and size; actual GET-backed controls and an explicit submit button |
| Catalog | Four unboxed product columns with photos, condition/name/shop/price/sizes; image aspect and gutters adjusted after browser comparison |
| Mobile | Header wraps, hero stacks, filters remain labelled, two-column product grid; no horizontal page overflow at 390px |
| Transaction screens | Same colors/spacing/input/button system applied to real forms, tables and confirmations; table overflow is contained in table-wrap rather than the page |

Allowed copy/functional deviations: explicit Thai search button, THB formatted as ฿ with two decimals, Thai field labels/errors, cart/chat/logout for authenticated state, pagination, accessible labels and no dead Help/Terms/Privacy footer links. Standalone garment photography matches the reference direction but is not a pixel copy of its individual products. No separate user concept approval was requested because continuous implementation was authorized. Functional screens beyond the catalog follow the documented design tokens and requirements.

The result was verified against the selected visual direction and core UI controls. This is not a claim of cross-device hardware testing or pixel identity to AI-generated text. Local workflows and asset/page/console health are covered in browser-results.json. Angsila was subsequently inspected through CUA and independently captured from a fresh anonymous Chrome context at 1536×1024 and 390×844; images loaded and no horizontal page overflow occurred. See evidence/angsila-visual-results.json and the Angsila desktop/mobile screenshots. The live catalogue currently has one explicitly marked fictional QA product, while the local demo has four clothing examples.
