=== Case Study Carousel ===
Contributors: chatgpt
Tags: case study, slider, marketing, carousel
Tested up to: 6.6
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Showcase your strongest customer wins in a polished carousel that matches the shared marketing UI language used across this plugin suite.

== Description ==

Use the `[case_study_carousel]` shortcode to highlight impact metrics, imagery, and deep links to full case studies. Cards inherit the shared gradients, typography, and reveal animations so the component blends with the rest of the collection.

== Shortcode Attributes ==

* `title` – Section headline displayed above the carousel.
* `subtitle` – Supporting copy under the title.
* `cases` – Semicolon-delimited list of case studies using the pattern `Name|Metric|Summary|ImageURL|LinkURL`.
* `autoplay` – Whether the carousel should auto-play (`true`/`false`).
* `speed` – Transition speed in milliseconds (defaults to 6000).
* `interval` – Delay between auto-play transitions (0 runs continuously).

== Example ==

`[case_study_carousel title="Growth stories" subtitle="Results from the last quarter" cases="Acme Corp|+140% pipeline|Scaled outbound and lifecycle journeys.|https://example.com/acme.jpg|https://example.com/case/acme;Vertex Labs|3.8x demo conversions|Personalised intent playbooks.|https://example.com/vertex.jpg|https://example.com/case/vertex"]`

== Installation ==

1. Upload the `case-study-carousel` folder to `/wp-content/plugins/`.
2. Activate the plugin through the **Plugins** menu.
3. Drop the `[case_study_carousel]` shortcode into any block that accepts shortcodes.

== Changelog ==

= 1.0.0 =
* Initial release.
