=== Service Feature CTA ===
Contributors: chatgpt
Tags: services, cta, landing page, marketing, shortcode
Requires at least: 5.8
Tested up to: 6.6
Stable tag: 1.0.0
License: GPLv2 or later

Launch polished feature highlight sections with a single shortcode. Comes with animated feature cards, hero copy, and a gradient CTA button.

== Description ==
- Supports stacked or two-column layouts
- Accepts custom features, icons, and CTA copy
- Intersection Observer animation with staggered timing

== Shortcode ==
```
[service_feature_cta title="Why brands hire us" subtitle="Fractional growth team embedded with your marketing org." eyebrow="Growth services" button_text="Schedule intro" button_url="https://example.com/contact" layout="two-column" background="dark" features="Launch playbooks|Validated experiments shipped in 14 days|<svg viewBox='0 0 24 24'><path d='M5 12l4 4L19 7' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/></svg>;Intelligence dashboards|Eliminate reporting busywork with Looker + HubSpot.;Retention audits|From onboarding through expansion, plug the leaks."]
```

**Attributes**
- `title`, `subtitle`, `eyebrow`: Headline copy
- `button_text`, `button_url`: CTA button label and destination
- `layout`: `two-column` (default) or `stacked`
- `background`: `light` (default) or `dark`
- `features`: Semicolon-separated features. Each feature accepts `Title|Description|Icon`. Icons can be SVG markup or an image URL.

== Installation ==
1. Upload the plugin to `/wp-content/plugins/` and activate it.
2. Use the shortcode inside posts, pages, patterns, or templates.
3. Override default content via shortcode attributes for each placement.

== Changelog ==
= 1.0.0 =
* Initial release
