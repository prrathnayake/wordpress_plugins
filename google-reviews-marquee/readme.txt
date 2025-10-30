=== Google Reviews Marquee ===
Contributors: builtbypasan, chatgpt
Tags: google reviews, places api, slider, marquee, testimonials, cron, cache
Requires at least: 5.8
Tested up to: 6.6
Stable tag: 1.0.0
License: GPLv2 or later

Fetches Google reviews via the Places API, caches the highest-rated ones nightly, and displays them in a professional horizontal auto-sliding marquee.

== How to Use ==
1. Go to **Settings → Google Reviews**, paste your Google Places API Key and Place ID, and configure rating thresholds. Click **Sync Now** to pull the latest reviews.
2. Embed the marquee by adding the shortcode anywhere shortcodes are supported (widgets, Elementor, block editor, etc.):
   `[google_reviews_marquee title="What Our Customers Say" limit="24" min_rating="4.8" autoplay="true" speed="normal"]`
3. Optionally override shortcode attributes to change the review limit, autoplay behavior, or scroll speed for each placement.

Note: Google Places Reviews API often lacks per-review images. The plugin uses reviewer avatars and optional place photos to enrich visuals.
