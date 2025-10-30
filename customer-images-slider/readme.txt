=== Customer Images & Video Slider (ACF) ===
Contributors: builtbypasan, chatgpt
Tags: acf, woocommerce, gallery, video, slider, swiper, hero, lightbox
Requires at least: 5.8
Tested up to: 6.6
Stable tag: 1.8.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Shortcode [customer_images size="large" columns="3" title="Happy Customers" product_id="" autoplay="true" delay="2500" speed="600"] renders a Customer Gallery Slide-inspired carousel that mirrors the global gallery styling. Images sit inside centered frames with hover and lightbox states, and the optional video column inherits the same dark shell treatment. All hero content is fetched from product ACF with clean fallbacks. Click any image/video to open fullscreen.

== How to Use ==
1. Install and activate the plugin, then assign the "Customer Images & Video" ACF field group (below) to the WooCommerce products you want to feature.
2. Upload customer gallery images, optionally add a product video, and fill in any hero fields while editing the product.
3. Insert the `[customer_images]` shortcode into a post, page, or template part. Adjust attributes such as `size`, `columns`, `title`, `autoplay`, and timing (see example above) to fit your layout.

== ACF Fields ==
Group: group_68c3b71cc5578
- customer_images (Gallery)
- product_video (oEmbed)
Optional hero:
- hero_title (Text), hero_text (Text/WYSIWYG), hero_button_text (Text), hero_button_url (URL), hero_bg (Image/URL)

== Features ==
- Customer Gallery Slide visuals — dark shell, centered frames, blur accents, and hover overlays
- 3-up image slider (static grid if <= columns) plus an optional video spotlight column
- Autoplay timing controls via `autoplay`, `delay`, and `speed` shortcode attributes
- Fullscreen **lightbox** on click for images and video
- Accessible buttons & lazy images
