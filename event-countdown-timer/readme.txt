=== Event Countdown Timer ===
Contributors: chatgpt
Tags: countdown, launch, event, webinar, shortcode
Requires at least: 5.8
Tested up to: 6.6
Stable tag: 1.0.0
License: GPLv2 or later

Premium countdown hero for launches, conferences, or webinars. Responsive layout, animated metrics, and polished CTA ready to drop into any landing page via shortcode.

== Description ==
- Accepts ISO8601 date strings (e.g. `2024-10-15T14:00:00-04:00`)
- Gradient or neutral background skins
- Accessible live region keeps screen readers informed

== Shortcode ==
```
[event_countdown title="Launch Summit" date="2024-11-05T16:00:00-05:00" timezone="America/New_York" description="Three hours of product deep dives and founder firesides." button_text="Claim your ticket" button_url="https://example.com/rsvp" background="gradient" badge="Live virtual event"]
```

**Attributes**
- `title`, `description`, `badge`: Copy for the hero
- `date`: ISO8601 datetime string (with timezone offset)
- `timezone`: Label displayed beneath the timer
- `button_text`, `button_url`: CTA button
- `background`: `gradient` (default) or `light`

== Installation ==
1. Upload the plugin to `/wp-content/plugins/` and activate it.
2. Add the shortcode to any page, post, or widget area.
3. Provide a future `date` value to start the countdown.

== Changelog ==
= 1.0.0 =
* Initial release
