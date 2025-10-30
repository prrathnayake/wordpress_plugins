=== Team Member Profiles ===
Contributors: chatgpt
Tags: team, profiles, cards, shortcode, responsive, design
Requires at least: 5.8
Tested up to: 6.6
Stable tag: 1.0.0
License: GPLv2 or later

Polished, responsive team member cards with social links and subtle animations. Drop the `[team_member_profiles]` shortcode anywhere to introduce your leadership team in seconds.

== Description ==
- Fully responsive grid that adapts from 1-4 columns
- Optional dark theme and smooth Intersection Observer reveal
- Customizable roster via shortcode attributes with graceful defaults

== Shortcode ==
```
[team_member_profiles columns="3" theme="light" members="Jordan Blake|Founder & CEO|https://example.com/jordan.jpg|Vision-led leader focused on customer experience.|LinkedIn~https://linkedin.com/company/example;Priya Desai|Head of Product|https://example.com/priya.jpg|Owns the product roadmap with human-centred design.|Dribbble~https://dribbble.com/priya" ]
```

**Attributes**
- `columns` (1-4): Number of cards per row
- `theme` (`light` or `dark`)
- `layout`: `grid` (default) reserved for future variants
- `members`: Semicolon-separated list of members. Each member accepts `Name|Role|Photo URL|Bio|SocialLabel~URL~target`

== Installation ==
1. Upload the plugin to `/wp-content/plugins/` and activate it.
2. Add the shortcode to any page, post, or block pattern.
3. Optionally override member data with the `members` attribute.

== Changelog ==
= 1.0.0 =
* Initial release
