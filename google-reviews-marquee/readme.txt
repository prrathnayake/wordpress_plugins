=== Google Reviews Marquee ===
Contributors: builtbypasan, chatgpt
Tags: google reviews, places api, slider, marquee, testimonials, cron, cache
Requires at least: 5.8
Tested up to: 6.6
Stable tag: 1.0.0
License: GPLv2 or later

Fetches Google reviews via the Places API, caches the highest-rated ones nightly, and displays them in a professional horizontal auto-sliding marquee.

Usage:
1) Settings → Google Reviews: set API Key + Place ID, thresholds. Click Sync Now.
2) Add shortcode anywhere (Elementor Shortcode widget OK):
   [google_reviews_marquee title="What Our Customers Say" limit="24" min_rating="4.8" autoplay="true" speed="normal"]

Note: Google Places Reviews API often lacks per-review images. The plugin uses reviewer avatars and optional place photos to enrich visuals.
