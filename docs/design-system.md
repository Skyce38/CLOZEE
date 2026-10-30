# CLOZEE visual system

Generated visual concept: `docs/design/concept.png`. The concept was selected as the implementation reference during this build, without a separate user review gate. It is a design mockup, never test evidence.

- White page, ink #212b26, forest #244b36, pale sage #eef2ed, lime #dbe8ac. No gradients or image tint overlays.
- Editorial Georgia serif for CLOZEE and the catalog title; local system sans-serif for controls and Thai text. No external font/network dependency.
- 1420px maximum content, 56px desktop gutters, 24px grid gaps. 80px header. Two-column editorial intro and four-column portrait product catalog. Two-column catalog on phones; stacked forms/detail/checkout.
- Native text and controls. Search, condition, category, size and pagination submit real GET queries. Cards are unboxed product listings with portrait 4:5 photography, condition tag, title, shop, price and sizes.
- Supporting views reuse green primary buttons, white inputs, fine borders, generous form spacing, clear focus states, labelled fields and visible error summaries. Seller data uses tables with horizontal scroll on small screens.
- Intentionally required additions to concept: cart, registration, protected seller pages, order statuses, real form errors, keyboard accessibility, pagination and Thai functional copy. Decorative footer links from the mockup are omitted unless an actual destination exists.
- Hero copy: “Good clothes.” / “Closer to you.” and “New & preloved finds around BUU and Bangsaen.” Header: CLOZEE, Explore, My orders, Sell with us, Log in. Logged-in navigation adds cart and logout as functional states.
- Clothing images are original generated fictional product visuals. All sample products are marked demo in setup/report; no customer uploads or real inventory claims.

Image generation used the built-in imagegen tool. Hero prompt: ivory linen shirt and vintage denim on pale sage tabletop, overhead soft natural light, no UI or logos. Four catalog prompts: linen shirt, folded denim, cotton Bangsaen tee, olive cardigan, each full garment centered on pale warm gray, original editorial product photography, no UI/watermark. Saved web assets: `public/assets/images/hero.jpg`, `linen.jpg`, `denim.jpg`, `tee.jpg`, `knit.jpg`.
