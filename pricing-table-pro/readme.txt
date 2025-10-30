=== Pricing Table Pro ===
Contributors: chatgpt
Tags: pricing table, plans, conversion, shortcode
Requires at least: 5.8
Tested up to: 6.6
Stable tag: 1.0.0
License: GPLv2 or later

Conversion-ready pricing tables with highlight badges, feature checklists, and animated reveal.

== Description ==
- 1-4 responsive columns with Intersection Observer reveal
- Highlight the hero plan and add contextual footnotes
- Customize plans inline using shortcode attributes

== Shortcode ==
```
[pricing_table columns="3" highlight="2" currency="$" billing_period="/mo" cta_text="Choose plan" plans="Starter|29|For new product teams.|Up to 3 seats,Email support,Analytics dashboard||Start free|https://example.com/start;Growth|79|Automation and deeper insights.|Unlimited seats,Priority support,Advanced reporting|Most popular|Start trial|https://example.com/growth|14-day free trial;Enterprise|149|Dedicated success + SSO.|Custom contracts,SAML SSO,Quarterly strategy session|Best value|Talk to sales|https://example.com/enterprise|Custom onboarding included"]
```

**Attributes**
- `columns`: 1-4 columns
- `highlight`: 1-indexed plan to emphasize (default 2)
- `currency`, `billing_period`: Displayed next to prices
- `cta_text`: Fallback button label when not provided per plan
- `plans`: Semicolon-separated plans. Each plan accepts `Name|Price|Tagline|Feature1,Feature2|Badge|Button label|Button URL|Footnote`.

== Installation ==
1. Upload the plugin to `/wp-content/plugins/` and activate it.
2. Place the shortcode on any landing page, post, or template.
3. Override attributes to align with your offer.

== Changelog ==
= 1.0.0 =
* Initial release
