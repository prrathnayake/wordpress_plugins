<?php
/*
Plugin Name: MFCC Custom Widgets
Description: Provides a suite of custom shortcodes to display dynamic content on the Mentone Furniture Clearance Centre website.  The shortcodes render product categories, featured products, latest offers, testimonials, FAQs, newsletter form, contact information, opening hours, social icons and hero banners in a unified professional style.  Data is pulled dynamically from WordPress and WooCommerce wherever possible.
Version: 1.0.0
Author: OpenAI Assistant
License: GPL2
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Main plugin class.
 */
class MFCC_Custom_Widgets {
    /**
     * Initialize hooks.
     */
    public static function init() {
        add_action( 'init', array( __CLASS__, 'register_shortcodes' ) );
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
    }

    /**
     * Enqueue plugin styles.
     */
    public static function enqueue_assets() {
        wp_enqueue_style( 'mfcc-custom-widgets', plugin_dir_url( __FILE__ ) . 'assets/css/mfcc-style.css', array(), '1.0.0' );
        // Enqueue slider assets for image slider functionality.
        wp_enqueue_style( 'mfcc-slider', plugin_dir_url( __FILE__ ) . 'assets/css/mfcc-slider.css', array(), '1.0.0' );
        wp_enqueue_script( 'mfcc-slider', plugin_dir_url( __FILE__ ) . 'assets/js/mfcc-slider.js', array( 'jquery' ), '1.0.0', true );
    }

    /**
     * Register all shortcodes.
     */
    public static function register_shortcodes() {
        add_shortcode( 'mfcc_categories', array( __CLASS__, 'render_categories' ) );
        add_shortcode( 'mfcc_featured_products', array( __CLASS__, 'render_featured_products' ) );
        add_shortcode( 'mfcc_latest_offers', array( __CLASS__, 'render_latest_offers' ) );
        add_shortcode( 'mfcc_testimonials', array( __CLASS__, 'render_testimonials' ) );
        add_shortcode( 'mfcc_faq', array( __CLASS__, 'render_faq' ) );
        add_shortcode( 'mfcc_newsletter_form', array( __CLASS__, 'render_newsletter_form' ) );
        add_shortcode( 'mfcc_contact_info', array( __CLASS__, 'render_contact_info' ) );
        add_shortcode( 'mfcc_open_hours', array( __CLASS__, 'render_open_hours' ) );
        add_shortcode( 'mfcc_social_icons', array( __CLASS__, 'render_social_icons' ) );
        add_shortcode( 'mfcc_hero', array( __CLASS__, 'render_hero' ) );
        add_shortcode( 'mfcc_image_slider', array( __CLASS__, 'render_image_slider' ) );
    }

    /**
     * Render product categories in a grid.
     *
     * @param array $atts Shortcode attributes.
     * @return string
     */
    public static function render_categories( $atts ) {
        $atts = shortcode_atts( array(
            'number'  => 5,
            'columns' => 5,
            'orderby' => 'menu_order',
            'order'   => 'ASC',
        ), $atts, 'mfcc_categories' );

        if ( ! class_exists( 'WooCommerce' ) ) {
            return '';
        }
        // Fetch product categories.
        $terms = get_terms( array(
            'taxonomy'   => 'product_cat',
            'number'     => intval( $atts['number'] ),
            'orderby'    => sanitize_key( $atts['orderby'] ),
            'order'      => sanitize_key( $atts['order'] ),
            'hide_empty' => false,
        ) );

        if ( is_wp_error( $terms ) || empty( $terms ) ) {
            return '';
        }

        $output  = '<div class="mfcc-categories-grid columns-' . esc_attr( $atts['columns'] ) . '">';
        foreach ( $terms as $term ) {
            $thumbnail_id = get_term_meta( $term->term_id, 'thumbnail_id', true );
            $image_url    = $thumbnail_id ? wp_get_attachment_image_url( $thumbnail_id, 'medium' ) : wc_placeholder_img_src();
            $term_link    = get_term_link( $term );
            $output      .= '<div class="mfcc-category-item mfcc-animate">';
            $output      .= '<a href="' . esc_url( $term_link ) . '">';
            $output      .= '<div class="mfcc-category-image"><img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $term->name ) . '"></div>';
            $output      .= '<h3 class="mfcc-category-title">' . esc_html( $term->name ) . '</h3>';
            $output      .= '</a>';
            $output      .= '</div>';
        }
        $output .= '</div>';
        return $output;
    }

    /**
     * Render featured products grid.
     *
     * @param array $atts Shortcode attributes.
     * @return string
     */
    public static function render_featured_products( $atts ) {
        $atts = shortcode_atts( array(
            'limit'   => 4,
            'columns' => 4,
        ), $atts, 'mfcc_featured_products' );

        if ( ! class_exists( 'WooCommerce' ) ) {
            return '';
        }
        // Query featured products.
        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => intval( $atts['limit'] ),
            'tax_query'      => array(
                array(
                    'taxonomy' => 'product_visibility',
                    'field'    => 'name',
                    'terms'    => 'featured',
                    'operator' => 'IN',
                ),
            ),
        );
        $products = new WP_Query( $args );
        if ( ! $products->have_posts() ) {
            return '';
        }
        $output  = '<div class="mfcc-products-grid columns-' . esc_attr( $atts['columns'] ) . '">';
        while ( $products->have_posts() ) {
            $products->the_post();
            $product = wc_get_product( get_the_ID() );
            if ( ! $product ) {
                continue;
            }
            $output .= '<div class="mfcc-product-item mfcc-animate">';
            $output .= '<a href="' . get_permalink() . '" class="mfcc-product-image">' . woocommerce_get_product_thumbnail( 'medium' ) . '</a>';
            $output .= '<h4 class="mfcc-product-title"><a href="' . get_permalink() . '">' . get_the_title() . '</a></h4>';
            $output .= '<div class="mfcc-product-price">' . $product->get_price_html() . '</div>';
            ob_start();
            woocommerce_template_single_add_to_cart();
            $output .= '<div class="mfcc-add-to-cart">' . ob_get_clean() . '</div>';
            $output .= '</div>';
        }
        wp_reset_postdata();
        $output .= '</div>';
        return $output;
    }

    /**
     * Render latest offers (on-sale products).
     *
     * @param array $atts Shortcode attributes.
     * @return string
     */
    public static function render_latest_offers( $atts ) {
        $atts = shortcode_atts( array(
            'limit'   => 4,
            'columns' => 4,
        ), $atts, 'mfcc_latest_offers' );
        if ( ! class_exists( 'WooCommerce' ) ) {
            return '';
        }
        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => intval( $atts['limit'] ),
            'meta_query'     => array(
                array(
                    'key'     => '_sale_price',
                    'value'   => 0,
                    'compare' => '>',
                    'type'    => 'NUMERIC',
                ),
            ),
        );
        $products = new WP_Query( $args );
        if ( ! $products->have_posts() ) {
            return '';
        }
        $output  = '<div class="mfcc-products-grid columns-' . esc_attr( $atts['columns'] ) . '">';
        while ( $products->have_posts() ) {
            $products->the_post();
            $product = wc_get_product( get_the_ID() );
            if ( ! $product ) {
                continue;
            }
            $output .= '<div class="mfcc-product-item mfcc-animate">';
            $output .= '<a href="' . get_permalink() . '" class="mfcc-product-image">' . woocommerce_get_product_thumbnail( 'medium' ) . '</a>';
            $output .= '<h4 class="mfcc-product-title"><a href="' . get_permalink() . '">' . get_the_title() . '</a></h4>';
            // Show sale badge.
            if ( $product->is_on_sale() ) {
                $sale_percentage = round( ( ( $product->get_regular_price() - $product->get_sale_price() ) / $product->get_regular_price() ) * 100 );
                $output .= '<span class="mfcc-sale-badge">-' . esc_html( $sale_percentage ) . '%</span>';
            }
            $output .= '<div class="mfcc-product-price">' . $product->get_price_html() . '</div>';
            ob_start();
            woocommerce_template_single_add_to_cart();
            $output .= '<div class="mfcc-add-to-cart">' . ob_get_clean() . '</div>';
            $output .= '</div>';
        }
        wp_reset_postdata();
        $output .= '</div>';
        return $output;
    }

    /**
     * Render testimonials carousel.
     *
     * Assumes a custom post type "testimonial" with meta fields: _testimonial_position (author title) and optional featured image.
     *
     * @param array $atts Shortcode attributes.
     * @return string
     */
    public static function render_testimonials( $atts ) {
        $atts = shortcode_atts( array(
            'limit' => 5,
        ), $atts, 'mfcc_testimonials' );
        $args = array(
            'post_type'      => 'testimonial',
            'posts_per_page' => intval( $atts['limit'] ),
            'post_status'    => 'publish',
        );
        $testimonials = new WP_Query( $args );
        if ( ! $testimonials->have_posts() ) {
            return '';
        }
        $output  = '<div class="mfcc-testimonials">';
        while ( $testimonials->have_posts() ) {
            $testimonials->the_post();
            $author_title = get_post_meta( get_the_ID(), '_testimonial_position', true );
            $output      .= '<div class="mfcc-testimonial-item">';
            if ( has_post_thumbnail() ) {
                $output .= '<div class="mfcc-testimonial-photo">' . get_the_post_thumbnail( get_the_ID(), 'thumbnail' ) . '</div>';
            }
            $output .= '<div class="mfcc-testimonial-content">';
            $output .= '<p class="mfcc-testimonial-text">' . wp_kses_post( get_the_content() ) . '</p>';
            $output .= '<h5 class="mfcc-testimonial-author">' . esc_html( get_the_title() );
            if ( $author_title ) {
                $output .= ' <span class="mfcc-testimonial-position">– ' . esc_html( $author_title ) . '</span>';
            }
            $output .= '</h5>';
            $output .= '</div>';
            $output .= '</div>';
        }
        wp_reset_postdata();
        $output .= '</div>';
        return $output;
    }

    /**
     * Render FAQs in an accordion.
     *
     * Assumes that FAQs are stored as posts in a post type "faq" or posts with category "faq".
     *
     * @param array $atts Shortcode attributes.
     * @return string
     */
    public static function render_faq( $atts ) {
        $atts = shortcode_atts( array(
            'limit'     => 6,
            'post_type' => 'faq',
        ), $atts, 'mfcc_faq' );
        $args = array(
            'post_type'      => $atts['post_type'],
            'posts_per_page' => intval( $atts['limit'] ),
            'post_status'    => 'publish',
        );
        $faqs = new WP_Query( $args );
        if ( ! $faqs->have_posts() ) {
            return '';
        }
        $output = '<div class="mfcc-faq">';
        while ( $faqs->have_posts() ) {
            $faqs->the_post();
            $output .= '<div class="mfcc-faq-item">';
            $output .= '<button class="mfcc-faq-question" aria-expanded="false">' . esc_html( get_the_title() ) . '</button>';
            $output .= '<div class="mfcc-faq-answer">' . apply_filters( 'the_content', get_the_content() ) . '</div>';
            $output .= '</div>';
        }
        wp_reset_postdata();
        $output .= '</div>';
        // Include simple script for toggling accordions.
        $output .= '<script type="text/javascript">document.addEventListener("DOMContentLoaded",function(){const questions=document.querySelectorAll(".mfcc-faq-question");questions.forEach(btn=>{btn.addEventListener("click",()=>{const expanded=btn.getAttribute("aria-expanded")==="true";btn.setAttribute("aria-expanded",!expanded);const answer=btn.nextElementSibling;answer.style.display=!expanded?"block":"none";});});});</script>';
        return $output;
    }

    /**
     * Render newsletter subscription form.
     *
     * This shortcode acts as a wrapper for Mailchimp for WordPress or other newsletter plugins.  It simply calls another shortcode provided via attribute.
     *
     * @param array $atts Shortcode attributes.
     * @return string
     */
    public static function render_newsletter_form( $atts ) {
        $atts = shortcode_atts( array(
            'form_shortcode' => '',
        ), $atts, 'mfcc_newsletter_form' );
        if ( ! $atts['form_shortcode'] ) {
            return '';
        }
        $output  = '<div class="mfcc-newsletter-form">';
        $output .= do_shortcode( $atts['form_shortcode'] );
        $output .= '</div>';
        return $output;
    }

    /**
     * Render contact information.
     *
     * Pulls dynamic data from options, with sensible defaults.
     *
     * @param array $atts Shortcode attributes.
     * @return string
     */
    public static function render_contact_info( $atts ) {
        $defaults = array(
            'phone'   => get_option( 'mfcc_phone', '03 8522 2115' ),
            'mobile'  => get_option( 'mfcc_mobile', '0421 768 052' ),
            'email'   => get_option( 'mfcc_email', 'hello@mentonefurnitureclearance.com.au' ),
            'address' => get_option( 'mfcc_address', '65A Grange Road, Cheltenham, Victoria 3192' ),
        );
        $atts = shortcode_atts( $defaults, $atts, 'mfcc_contact_info' );
        $output  = '<div class="mfcc-contact-info">';
        $output .= '<ul>';
        if ( $atts['phone'] ) {
            $output .= '<li><strong>Land Line:</strong> <a href="tel:' . esc_attr( preg_replace( '/\s+/', '', $atts['phone'] ) ) . '">' . esc_html( $atts['phone'] ) . '</a></li>';
        }
        if ( $atts['mobile'] ) {
            $output .= '<li><strong>Mobile:</strong> <a href="tel:' . esc_attr( preg_replace( '/\s+/', '', $atts['mobile'] ) ) . '">' . esc_html( $atts['mobile'] ) . '</a></li>';
        }
        if ( $atts['email'] ) {
            $output .= '<li><strong>Email:</strong> <a href="mailto:' . esc_attr( $atts['email'] ) . '">' . esc_html( $atts['email'] ) . '</a></li>';
        }
        if ( $atts['address'] ) {
            $output .= '<li><strong>Address:</strong> ' . esc_html( $atts['address'] ) . '</li>';
        }
        $output .= '</ul>';
        $output .= '</div>';
        return $output;
    }

    /**
     * Render opening hours.
     *
     * Accepts days/hours via shortcode attributes or falls back to stored options.
     *
     * @param array $atts Shortcode attributes.
     * @return string
     */
    public static function render_open_hours( $atts ) {
        // Default hours; can be overridden via attributes or options.
        $defaults = array(
            'monday'    => get_option( 'mfcc_hours_monday', '10:00 AM – 5:00 PM' ),
            'tuesday'   => get_option( 'mfcc_hours_tuesday', 'Closed' ),
            'wednesday' => get_option( 'mfcc_hours_wednesday', '10:00 AM – 5:00 PM' ),
            'thursday'  => get_option( 'mfcc_hours_thursday', '10:00 AM – 5:00 PM' ),
            'friday'    => get_option( 'mfcc_hours_friday', '10:00 AM – 5:00 PM' ),
            'saturday'  => get_option( 'mfcc_hours_saturday', '10:00 AM – 5:00 PM' ),
            'sunday'    => get_option( 'mfcc_hours_sunday', '11:00 AM – 5:00 PM' ),
        );
        $atts = shortcode_atts( $defaults, $atts, 'mfcc_open_hours' );
        $days = array(
            'Monday'    => $atts['monday'],
            'Tuesday'   => $atts['tuesday'],
            'Wednesday' => $atts['wednesday'],
            'Thursday'  => $atts['thursday'],
            'Friday'    => $atts['friday'],
            'Saturday'  => $atts['saturday'],
            'Sunday'    => $atts['sunday'],
        );
        $output  = '<div class="mfcc-open-hours">';
        $output .= '<ul>';
        foreach ( $days as $day => $hours ) {
            $output .= '<li><strong>' . esc_html( $day ) . ':</strong> ' . esc_html( $hours ) . '</li>';
        }
        $output .= '</ul>';
        $output .= '</div>';
        return $output;
    }

    /**
     * Render social icons.
     *
     * Accept URLs via attributes or options. Icons are generated using Font Awesome classes.  You can enqueue Font Awesome in your theme.
     *
     * @param array $atts Shortcode attributes.
     * @return string
     */
    public static function render_social_icons( $atts ) {
        $defaults = array(
            'facebook'  => get_option( 'mfcc_facebook', 'https://facebook.com/' ),
            'instagram' => get_option( 'mfcc_instagram', 'https://instagram.com/' ),
            'linkedin'  => get_option( 'mfcc_linkedin', '' ),
        );
        $atts = shortcode_atts( $defaults, $atts, 'mfcc_social_icons' );
        $icons = array(
            'facebook'  => 'fab fa-facebook-f',
            'instagram' => 'fab fa-instagram',
            'linkedin'  => 'fab fa-linkedin-in',
        );
        $output  = '<div class="mfcc-social-icons">';
        foreach ( $icons as $key => $class ) {
            if ( ! empty( $atts[ $key ] ) ) {
                $output .= '<a href="' . esc_url( $atts[ $key ] ) . '" class="mfcc-social-link ' . esc_attr( $key ) . '" target="_blank" rel="noopener"><i class="' . esc_attr( $class ) . '"></i></a>';
            }
        }
        $output .= '</div>';
        return $output;
    }

    /**
     * Render hero section.
     *
     * @param array $atts Shortcode attributes.
     * @return string
     */
    public static function render_hero( $atts ) {
        $atts = shortcode_atts( array(
            'title'       => get_option( 'mfcc_hero_title', 'Transform your Home into a Haven' ),
            'subtitle'    => get_option( 'mfcc_hero_subtitle', 'Discover our elegant and functional furniture collections designed to bring style and comfort to every part of your home.' ),
            'button_text' => get_option( 'mfcc_hero_button_text', 'Shop More' ),
            'button_url'  => get_option( 'mfcc_hero_button_url', '/shop' ),
            'image'       => get_option( 'mfcc_hero_image', '' ),
        ), $atts, 'mfcc_hero' );
        $output  = '<div class="mfcc-hero">';
        if ( $atts['image'] ) {
            $output .= '<div class="mfcc-hero-image" style="background-image:url(' . esc_url( $atts['image'] ) . ');"></div>';
        }
        $output .= '<div class="mfcc-hero-content">';
        $output .= '<h2 class="mfcc-hero-title">' . esc_html( $atts['title'] ) . '</h2>';
        $output .= '<p class="mfcc-hero-subtitle">' . esc_html( $atts['subtitle'] ) . '</p>';
        if ( $atts['button_text'] && $atts['button_url'] ) {
            $output .= '<a href="' . esc_url( $atts['button_url'] ) . '" class="mfcc-hero-button">' . esc_html( $atts['button_text'] ) . '</a>';
        }
        $output .= '</div>';
        $output .= '</div>';
        return $output;
    }

    /**
     * Render an image slider with optional vertical or horizontal orientation.
     *
     * Accepts a comma‑separated list of attachment IDs in the 'ids' attribute.  Orientation can be 'horizontal' or 'vertical'.
     * Example usage: [mfcc_image_slider ids="1,2,3" orientation="vertical"]
     *
     * @param array $atts Shortcode attributes.
     * @return string
     */
    public static function render_image_slider( $atts ) {
        $atts = shortcode_atts( array(
            'ids'        => '',
            'orientation' => 'horizontal',
            'autoplay'    => 'true',
            'interval'    => 5000,
        ), $atts, 'mfcc_image_slider' );
        if ( empty( $atts['ids'] ) ) {
            return '';
        }
        $ids        = array_filter( array_map( 'intval', explode( ',', $atts['ids'] ) ) );
        $orientation = ( $atts['orientation'] === 'vertical' ) ? 'vertical' : 'horizontal';
        $autoplay    = ( $atts['autoplay'] === 'true' ) ? 'true' : 'false';
        $interval    = intval( $atts['interval'] );
        $slider_id   = 'mfcc-slider-' . wp_unique_id();
        $output  = '<div id="' . esc_attr( $slider_id ) . '" class="mfcc-image-slider orientation-' . esc_attr( $orientation ) . '" data-autoplay="' . esc_attr( $autoplay ) . '" data-interval="' . esc_attr( $interval ) . '">';
        $output .= '<div class="mfcc-slider-inner">';
        foreach ( $ids as $attachment_id ) {
            $src = wp_get_attachment_image_url( $attachment_id, 'large' );
            if ( ! $src ) {
                continue;
            }
            $output .= '<div class="mfcc-slide"><img src="' . esc_url( $src ) . '" alt=""></div>';
        }
        $output .= '</div>';
        $output .= '<button class="mfcc-slide-prev" aria-label="Previous"></button>';
        $output .= '<button class="mfcc-slide-next" aria-label="Next"></button>';
        $output .= '</div>';
        return $output;
    }
}

// Initialise the plugin.
MFCC_Custom_Widgets::init();