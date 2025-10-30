=== Success Metrics Panels ===
Contributors: chatgpt
Tags: metrics, kpi, dashboard, marketing
Tested up to: 6.6
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Highlight impact metrics with animated KPI cards that reuse the shared marketing design tokens shipped in this plugin pack.

== Description ==

Drop the `[success_metrics]` shortcode onto a page to render three or more progress-driven KPI panels. Each card animates in on scroll, uses the unified color system, and features a progress indicator to reinforce growth.

== Shortcode Attributes ==

* `heading` – Main headline for the section.
* `subtitle` – Supporting description.
* `metrics` – Semicolon separated list of metrics in the format `Label|Value|Trend|Description|ProgressPercent`.

== Example ==

`[success_metrics heading="Momentum" subtitle="Real-time scorecard" metrics="Activation rate|74%|+12 pts vs last quarter|Guided onboarding flows unlocked a new baseline.|74;Expansion ARR|$428K|+38% QoQ|Strategic success reviews drove new cross-sell.|86;Support CSAT|4.9/5|Top 5% in SaaS|Triage bots and async help center cut wait times.|92"]`

== Installation ==

1. Upload the `success-metrics-panels` folder to `/wp-content/plugins/`.
2. Activate from the **Plugins** screen.
3. Place the `[success_metrics]` shortcode anywhere shortcodes are supported.

== Changelog ==

= 1.0.0 =
* Initial release.
