<?php
/**
 * Plugin Name: Customer Images & Video Slider (ACF)
 * Description: Shortcode [customer_images size="large" columns="3" title="Happy Customers" product_id=""] shows a 3-column image slider + 1 video column using ACF fields. Hero title/text/button/bg are pulled dynamically from ACF if present, with fallbacks.
 * Version: 1.7.0
 * Author: BuiltByPasan + ChatGPT
 * License: GPLv2 or later
 * Text Domain: customer-images-slider
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Customer_Images_Video_Slider {
    private static $instance = null;

    public static function init() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_shortcode( 'customer_images', array( $this, 'shortcode' ) );
    }

    private function enqueue_assets() {
        // Swiper via jsDelivr (no jQuery)
        wp_enqueue_style( 'swiper', 'https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css', array(), '10.3.1' );
        wp_enqueue_script( 'swiper', 'https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js', array(), '10.3.1', true );

        wp_enqueue_style( 'customer-images-slider', plugins_url( 'assets/css/customer-images-slider.css', __FILE__ ), array('swiper'), '1.7.0' );
        wp_enqueue_script( 'customer-images-slider', plugins_url( 'assets/js/customer-images-slider.js', __FILE__ ), array('swiper'), '1.7.0', true );
    }

    public function shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'size'       => 'large',
            'columns'    => '3',
            'title'      => 'Happy Customers',
            'product_id' => '',
        ), $atts, 'customer_images' );

        $columns = max( 1, intval( $atts['columns'] ) );

        // Resolve product ID
        $product_id = 0;
        if ( ! empty( $atts['product_id'] ) ) {
            $product_id = intval( $atts['product_id'] );
        } elseif ( function_exists('is_product') && is_product() ) {
            global $product, $post;
            if ( $product && method_exists( $product, 'get_id' ) ) {
                $product_id = $product->get_id();
            } elseif ( $post ) {
                $product_id = $post->ID;
            }
        } else {
            global $post;
            if ( $post ) { $product_id = $post->ID; }
        }

        $has_acf = function_exists( 'get_field' );

        // Fields from ACF group_68c3b71cc5578
        $images = $has_acf ? get_field( 'customer_images', $product_id ) : array(); // Gallery
        $video  = $has_acf ? get_field( 'product_video', $product_id )   : '';      // oEmbed

        // Hero dynamic (prefer ACF optional fields, then fall back)
        $hero_title = $has_acf ? ( get_field( 'hero_title', $product_id ) ?: '' ) : '';
        if ( ! $hero_title ) { $hero_title = get_the_title( $product_id ); }

        $hero_text = $has_acf ? ( get_field( 'hero_text', $product_id ) ?: '' ) : '';
        if ( ! $hero_text ) {
            $post_obj = get_post( $product_id );
            if ( $post_obj ) {
                $hero_text = has_excerpt( $product_id ) ? get_the_excerpt( $product_id ) : wp_trim_words( wp_strip_all_tags( $post_obj->post_content ), 24, '…' );
            }
        }

        $hero_button_text = $has_acf ? ( get_field( 'hero_button_text', $product_id ) ?: '' ) : '';
        if ( ! $hero_button_text ) { $hero_button_text = __( 'See more', 'customer-images-slider' ); }

        $hero_button_url = $has_acf ? ( get_field( 'hero_button_url', $product_id ) ?: '' ) : '';
        if ( ! $hero_button_url ) { $hero_button_url = get_permalink( $product_id ); }

        // Hero background image/url or product featured
        $hero_bg_url = '';
        if ( $has_acf ) {
            $bg = get_field( 'hero_bg', $product_id );
            if ( is_array( $bg ) && isset( $bg['ID'] ) ) {
                $img = wp_get_attachment_image_src( $bg['ID'], 'full' );
                if ( $img ) { $hero_bg_url = $img[0]; }
            } elseif ( is_numeric( $bg ) ) {
                $img = wp_get_attachment_image_src( intval( $bg ), 'full' );
                if ( $img ) { $hero_bg_url = $img[0]; }
            } elseif ( is_string( $bg ) ) {
                $hero_bg_url = esc_url_raw( $bg );
            }
        }
        if ( ! $hero_bg_url && has_post_thumbnail( $product_id ) ) {
            $img = wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), 'full' );
            if ( $img ) { $hero_bg_url = $img[0]; }
        }

        // If nothing at all, bail
        if ( empty( $images ) && empty( $video ) ) {
            return '';
        }

        // Enqueue assets
        $this->enqueue_assets();

        $title = sanitize_text_field( $atts['title'] );
        $size  = sanitize_text_field( $atts['size'] );

        ob_start();
        ?>
        <section class="cis-wrap mkt-ui" data-columns="<?php echo esc_attr( $columns ); ?>">
            <?php if ( ! empty( $title ) ) : ?>
                <h3 class="cis-title"><?php echo esc_html( $title ); ?></h3>
            <?php endif; ?>

            <div class="cis-hero cis-hero-has-video" <?php if($hero_bg_url){ echo 'style="--cis-hero-bg:url('.esc_url($hero_bg_url).')">'; } else { echo '>'; } ?>
                <div class="cis-hero-left">
                    <div class="cis-hero-card" role="region" aria-label="<?php echo esc_attr( $hero_title ); ?>">
                        <?php if ( $hero_title ) : ?>
                            <h4 class="cis-hero-heading"><?php echo esc_html( $hero_title ); ?></h4>
                        <?php endif; ?>
                        <?php if ( $hero_text ) : ?>
                            <div class="cis-hero-text"><?php echo wp_kses_post( $hero_text ); ?></div>
                        <?php endif; ?>
                        <a class="cis-hero-btn" href="<?php echo esc_url( $hero_button_url ); ?>">
                            <?php echo esc_html( $hero_button_text ); ?>
                        </a>
                    </div>
                </div>

                <div class="cis-hero-right">
                    <div class="cis-layout cis-layout-3-1">
                        <?php if ( ! empty( $images ) ) : ?>
                        <div class="cis-col cis-col-images">
                            <div class="cis-block">
                                <div class="swiper cis-swiper cis-swiper-images"
                                     data-type="images"
                                     data-columns="3"
                                     data-count="<?php echo esc_attr( is_array($images) ? count($images) : 0 ); ?>"
                                     data-space-between="16"
                                     data-breakpoints='{"1024":{"slidesPerView":3},"768":{"slidesPerView":2},"0":{"slidesPerView":1}}'>
                                    <div class="swiper-wrapper">
                                        <?php foreach ( $images as $img ) : 
                                            $img_id = is_array( $img ) && isset( $img['ID'] ) ? intval( $img['ID'] ) : ( is_numeric( $img ) ? intval( $img ) : 0 );
                                            if ( ! $img_id ) { continue; }
                                            $full = wp_get_attachment_image_src( $img_id, 'full' );
                                            ?>
                                            <div class="swiper-slide">
                                                <figure class="cis-figure">
                                                    <?php echo wp_get_attachment_image( $img_id, $size, false, array(
                                                        'class'    => 'cis-img',
                                                        'loading'  => 'lazy',
                                                        'decoding' => 'async',
                                                        'alt'      => get_post_meta( $img_id, '_wp_attachment_image_alt', true ),
                                                        'data-full'=> $full ? $full[0] : '',
                                                    ) ); ?>
                                                </figure>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <button class="cis-nav cis-nav-prev" aria-label="<?php esc_attr_e('Previous images','customer-images-slider'); ?>"></button>
                                    <button class="cis-nav cis-nav-next" aria-label="<?php esc_attr_e('Next images','customer-images-slider'); ?>"></button>
                                    <div class="cis-pagination" aria-hidden="true"></div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ( ! empty( $video ) ) : ?>
                        <div class="cis-col cis-col-video">
                            <div class="cis-block cis-video-block">
                                <div class="swiper cis-swiper cis-swiper-video" data-type="video" data-columns="1" data-space-between="16">
                                    <div class="swiper-wrapper">
                                        <div class="swiper-slide">
                                            <div class="cis-video-embed">
                                                <?php echo wp_kses_post( $video ); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <button class="cis-nav cis-nav-prev" aria-label="<?php esc_attr_e('Previous video','customer-images-slider'); ?>"></button>
                                    <button class="cis-nav cis-nav-next" aria-label="<?php esc_attr_e('Next video','customer-images-slider'); ?>"></button>
                                    <div class="cis-pagination" aria-hidden="true"></div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }
}

Customer_Images_Video_Slider::init();
