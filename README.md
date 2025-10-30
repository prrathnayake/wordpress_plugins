# WordPress Plugin Collection

A curated set of modern WordPress plugins focused on marketing and conversion-ready components. Every plugin ships with a shortcode so you can embed the widget in posts, pages, or template parts without additional builders.

## Plugins & Shortcodes

| Plugin | Description | Primary Shortcode |
| --- | --- | --- |
| Customer Images & Video Slider | Fetches customer gallery assets from ACF fields and renders a responsive slider + lightbox. | `[customer_images size="large" columns="3" title="Happy Customers" product_id="123"]` |
| Google Reviews Marquee | Syncs Google Places reviews nightly and displays them in an auto-scrolling marquee. | `[google_reviews_marquee title="What Our Customers Say" limit="24" min_rating="4.8" autoplay="true" speed="normal"]` |
| Team Member Profiles | Responsive team roster cards with bios and social links plus subtle animation. | `[team_member_profiles columns="3" theme="light" members="Jane Doe|CEO|https://example.com/jane.jpg|Lead visionary.|LinkedIn~https://linkedin.com/in/janedoe"]` |
| Service Feature CTA | Hero section for services including animated feature list and CTA button. | `[service_feature_cta title="Why brands hire us" subtitle="Fractional growth team" button_text="Schedule intro" button_url="https://example.com/contact" layout="two-column" background="dark" features="Launch playbooks|Validated experiments shipped in 14 days.;Retention audits|Plug the leaks."]` |
| Event Countdown Timer | Gradient countdown hero for launches, webinars, or product drops. | `[event_countdown title="Launch Summit" date="2024-11-05T16:00:00-05:00" timezone="America/New_York" button_text="Claim your ticket" button_url="https://example.com/rsvp" background="gradient"]` |
| Pricing Table Pro | Conversion-ready pricing columns with highlight badges and feature lists. | `[pricing_table columns="3" highlight="2" currency="$" billing_period="/mo" plans="Starter|29|For new teams.|Up to 3 seats,Email support;Growth|79|Automation and insights.|Unlimited seats,Priority support|Most popular|Start trial|https://example.com/growth|14-day trial;Enterprise|149|Dedicated success.|Custom contracts,SAML SSO|Best value|Talk to sales|https://example.com/enterprise"]` |

## Usage

1. Copy the desired plugin folder into your WordPress installation's `wp-content/plugins/` directory.
2. Activate the plugin from **Plugins → Installed Plugins**.
3. Paste the shortcode into any block that supports shortcodes (Group block, Paragraph, Site Editor, etc.).
4. Adjust shortcode attributes to match your brand voice, links, and imagery.

Each plugin directory contains a `readme.txt` with deeper configuration notes, attribute breakdowns, and usage tips.

## Development Notes

- CSS and JavaScript assets are enqueued only when their shortcode runs to keep payloads lean.
- Animations rely on the Intersection Observer API for performant reveal effects.
- Default content is provided for quick previews and can be overridden entirely via shortcode attributes.

Pull requests and suggestions are welcome!
