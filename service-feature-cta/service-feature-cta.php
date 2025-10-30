<?php
/**
 * Plugin Name:       Service Feature CTA
 * Description:       Elegant feature highlights with a built-in call-to-action button powered by a shortcode.
 * Version:           1.0.0
 * Author:            ChatGPT
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 5.8
 * Tested up to:      6.6
 * Text Domain:       service-feature-cta
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SFC_Service_Feature_CTA {
    const VERSION = '1.0.0';

    public function __construct() {
        add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
        add_shortcode( 'service_feature_cta', [ $this, 'render_shortcode' ] );
    }

    public function register_assets() {
        $handle = 'sfc-feature-cta';
        wp_register_style(
            $handle,
            plugins_url( 'assets/css/service-feature-cta.css', __FILE__ ),
            [],
            self::VERSION
        );
        wp_register_script(
            $handle,
            plugins_url( 'assets/js/service-feature-cta.js', __FILE__ ),
            [],
            self::VERSION,
            true
        );
    }

    public function render_shortcode( $atts ) {
        $atts = shortcode_atts(
            [
                'title'        => __( 'What we deliver', 'service-feature-cta' ),
                'subtitle'     => __( 'Strategic services designed to compound results.', 'service-feature-cta' ),
                'button_text'  => __( 'Book a strategy call', 'service-feature-cta' ),
                'button_url'   => '#',
                'features'     => '',
                'layout'       => 'two-column',
                'background'   => 'light',
                'eyebrow'      => __( 'Services', 'service-feature-cta' ),
            ],
            $atts,
            'service_feature_cta'
        );

        $features = $this->parse_features( $atts['features'] );
        if ( empty( $features ) ) {
            $features = $this->get_default_features();
        }

        wp_enqueue_style( 'sfc-feature-cta' );
        wp_enqueue_script( 'sfc-feature-cta' );

        $wrapper_classes = [
            'sfc-feature-section',
            'sfc-feature-section--' . sanitize_html_class( $atts['layout'] ),
            'sfc-feature-section--' . sanitize_html_class( $atts['background'] ),
        ];

        ob_start();
        ?>
        <section class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?> mkt-ui">
            <div class="sfc-feature-shell" data-animate="fade">
                <header class="sfc-feature-header">
                    <?php if ( ! empty( $atts['eyebrow'] ) ) : ?>
                        <p class="sfc-feature-eyebrow"><?php echo esc_html( $atts['eyebrow'] ); ?></p>
                    <?php endif; ?>
                    <h2 class="sfc-feature-title"><?php echo esc_html( $atts['title'] ); ?></h2>
                    <p class="sfc-feature-subtitle"><?php echo esc_html( $atts['subtitle'] ); ?></p>
                    <?php if ( ! empty( $atts['button_text'] ) && ! empty( $atts['button_url'] ) ) : ?>
                        <a class="sfc-feature-button" href="<?php echo esc_url( $atts['button_url'] ); ?>">
                            <span><?php echo esc_html( $atts['button_text'] ); ?></span>
                            <svg class="sfc-feature-button__icon" viewBox="0 0 20 20" aria-hidden="true" focusable="false"><path d="M5 10h8.5M11 6.5 15.5 10 11 13.5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </a>
                    <?php endif; ?>
                </header>
                <ul class="sfc-feature-list">
                    <?php foreach ( $features as $feature ) : ?>
                        <li class="sfc-feature-item">
                            <div class="sfc-feature-icon" aria-hidden="true">
                                <?php echo wp_kses_post( $feature['icon'] ); ?>
                            </div>
                            <div class="sfc-feature-copy">
                                <h3><?php echo esc_html( $feature['title'] ); ?></h3>
                                <p><?php echo esc_html( $feature['description'] ); ?></p>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }

    private function parse_features( $features_string ) {
        if ( empty( $features_string ) ) {
            return [];
        }

        $features = [];
        $raw_features = array_filter( array_map( 'trim', explode( ';', $features_string ) ) );

        foreach ( $raw_features as $raw_feature ) {
            $parts = array_map( 'trim', explode( '|', $raw_feature ) );
            if ( empty( $parts[0] ) ) {
                continue;
            }

            $features[] = [
                'title'       => $parts[0],
                'description' => $parts[1] ?? '',
                'icon'        => ! empty( $parts[2] ) ? $this->sanitize_icon( $parts[2] ) : $this->get_default_icon_svg(),
            ];
        }

        return $features;
    }

    private function sanitize_icon( $icon ) {
        if ( filter_var( $icon, FILTER_VALIDATE_URL ) ) {
            return '<img src="' . esc_url( $icon ) . '" alt="" loading="lazy" />';
        }

        // Assume inline SVG markup. Allow only whitelisted tags and attributes.
        $allowed = [
            'svg'  => [
                'xmlns'       => true,
                'viewBox'     => true,
                'fill'        => true,
                'stroke'      => true,
                'stroke-width'=> true,
                'aria-hidden' => true,
                'focusable'   => true,
            ],
            'path' => [
                'd'            => true,
                'fill'         => true,
                'stroke'       => true,
                'stroke-linecap' => true,
                'stroke-linejoin'=> true,
                'stroke-width' => true,
            ],
            'circle' => [
                'cx' => true,
                'cy' => true,
                'r'  => true,
                'fill' => true,
                'stroke' => true,
                'stroke-width' => true,
            ],
            'rect' => [
                'x' => true,
                'y' => true,
                'width' => true,
                'height' => true,
                'rx' => true,
                'fill' => true,
            ],
            'g' => [ 'fill' => true, 'stroke' => true, 'stroke-width' => true ],
        ];

        return wp_kses( $icon, $allowed );
    }

    private function get_default_features() {
        return [
            [
                'title'       => __( 'Conversion-ready landing pages', 'service-feature-cta' ),
                'description' => __( 'Launch pages that balance story, social proof, and clear CTAs in under two weeks.', 'service-feature-cta' ),
                'icon'        => $this->get_default_icon_svg(),
            ],
            [
                'title'       => __( 'Lifecycle email journeys', 'service-feature-cta' ),
                'description' => __( 'Own onboarding, retention, and win-back flows with research-backed messaging.', 'service-feature-cta' ),
                'icon'        => $this->get_default_icon_svg(),
            ],
            [
                'title'       => __( 'Revenue analytics cockpit', 'service-feature-cta' ),
                'description' => __( 'See the metrics that matter via Looker dashboards curated for operators.', 'service-feature-cta' ),
                'icon'        => $this->get_default_icon_svg(),
            ],
        ];
    }

    private function get_default_icon_svg() {
        return '<svg viewBox="0 0 40 40" aria-hidden="true" focusable="false"><rect x="2" y="2" width="36" height="36" rx="12" fill="rgba(37,99,235,0.12)" /><path d="M13 20.5l5 4.5 9-10" fill="none" stroke="#2563eb" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    }
}

new SFC_Service_Feature_CTA();
