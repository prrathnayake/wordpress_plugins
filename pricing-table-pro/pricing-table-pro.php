<?php
/**
 * Plugin Name:       Pricing Table Pro
 * Description:       Launch conversion-ready pricing tables with badges, feature lists, and CTA buttons via shortcode.
 * Version:           1.0.0
 * Author:            ChatGPT
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 5.8
 * Tested up to:      6.6
 * Text Domain:       pricing-table-pro
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class PTP_Pricing_Table_Pro {
    const VERSION = '1.0.0';

    public function __construct() {
        add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
        add_shortcode( 'pricing_table', [ $this, 'render_shortcode' ] );
    }

    public function register_assets() {
        $handle = 'ptp-pricing-table';
        wp_register_style(
            $handle,
            plugins_url( 'assets/css/pricing-table-pro.css', __FILE__ ),
            [],
            self::VERSION
        );
        wp_register_script(
            $handle,
            plugins_url( 'assets/js/pricing-table-pro.js', __FILE__ ),
            [],
            self::VERSION,
            true
        );
    }

    public function render_shortcode( $atts, $content = null ) {
        $atts = shortcode_atts(
            [
                'plans'          => '',
                'currency'       => '$',
                'billing_period' => __( '/month', 'pricing-table-pro' ),
                'highlight'      => '2',
                'columns'        => 3,
                'cta_text'       => __( 'Get started', 'pricing-table-pro' ),
                'theme'          => 'yellow',
                'theme_switcher' => 'false',
            ],
            $atts,
            'pricing_table'
        );

        $theme          = $this->normalize_theme( $atts['theme'] );
        $theme_switcher = filter_var( $atts['theme_switcher'], FILTER_VALIDATE_BOOLEAN );

        $plans = $this->parse_plans( $atts['plans'], $atts['currency'], $atts['billing_period'], $atts['cta_text'] );
        if ( empty( $plans ) ) {
            $plans = $this->get_default_plans( $atts['currency'], $atts['billing_period'], $atts['cta_text'] );
        }

        $highlight_index = max( 0, (int) $atts['highlight'] - 1 );
        $columns         = max( 1, min( 4, (int) $atts['columns'] ) );

        wp_enqueue_style( 'ptp-pricing-table' );
        wp_enqueue_script( 'ptp-pricing-table' );

        $section_id      = wp_unique_id( 'ptp-pricing-' );
        $theme_prefixes  = 'ptp-pricing--theme-,ptp-theme--,mkt-theme--';
        $container_class = [
            'ptp-pricing',
            'ptp-pricing--cols-' . $columns,
            'ptp-pricing--theme-' . $theme,
            'ptp-theme--' . $theme,
            'mkt-theme--' . $theme,
            'mkt-ui',
        ];

        ob_start();
        ?>
        <section
            id="<?php echo esc_attr( $section_id ); ?>"
            class="<?php echo esc_attr( implode( ' ', $container_class ) ); ?>"
            data-mkt-theme="<?php echo esc_attr( $theme ); ?>"
            data-theme-prefixes="<?php echo esc_attr( $theme_prefixes ); ?>"
        >
            <?php if ( $theme_switcher ) : ?>
                <?php echo $this->render_theme_switcher( $section_id, $theme ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <?php endif; ?>
            <?php foreach ( $plans as $index => $plan ) :
                $is_highlighted = $index === $highlight_index;
                ?>
                <article class="ptp-plan<?php echo $is_highlighted ? ' ptp-plan--highlighted' : ''; ?>" data-animate="rise">
                    <?php if ( ! empty( $plan['badge'] ) ) : ?>
                        <span class="ptp-plan__badge"><?php echo esc_html( $plan['badge'] ); ?></span>
                    <?php endif; ?>
                    <h3 class="ptp-plan__name"><?php echo esc_html( $plan['name'] ); ?></h3>
                    <p class="ptp-plan__tagline"><?php echo esc_html( $plan['tagline'] ); ?></p>
                    <div class="ptp-plan__price">
                        <span class="ptp-plan__currency"><?php echo esc_html( $plan['currency'] ); ?></span>
                        <span class="ptp-plan__amount"><?php echo esc_html( $plan['price'] ); ?></span>
                        <span class="ptp-plan__period"><?php echo esc_html( $plan['period'] ); ?></span>
                    </div>
                    <ul class="ptp-plan__features">
                        <?php foreach ( $plan['features'] as $feature ) : ?>
                            <li><?php echo esc_html( $feature ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php if ( ! empty( $plan['button_url'] ) ) : ?>
                        <a class="ptp-plan__button" href="<?php echo esc_url( $plan['button_url'] ); ?>">
                            <?php echo esc_html( $plan['button_text'] ); ?>
                        </a>
                    <?php endif; ?>
                    <?php if ( ! empty( $plan['footnote'] ) ) : ?>
                        <p class="ptp-plan__footnote"><?php echo esc_html( $plan['footnote'] ); ?></p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </section>
        <?php
        return ob_get_clean();
    }

    private function normalize_theme( $theme ) {
        $allowed = [ 'light', 'dark', 'yellow' ];

        $theme = strtolower( trim( (string) $theme ) );

        if ( ! in_array( $theme, $allowed, true ) ) {
            $theme = 'yellow';
        }

        return $theme;
    }

    private function render_theme_switcher( $section_id, $current_theme ) {
        $options = [
            'light'  => __( 'Light', 'pricing-table-pro' ),
            'dark'   => __( 'Dark', 'pricing-table-pro' ),
            'yellow' => __( 'Yellow', 'pricing-table-pro' ),
        ];

        $select_id = $section_id . '-theme';

        ob_start();
        ?>
        <div class="mkt-theme-switcher" data-target="<?php echo esc_attr( $section_id ); ?>">
            <label for="<?php echo esc_attr( $select_id ); ?>"><?php esc_html_e( 'Theme', 'pricing-table-pro' ); ?></label>
            <select id="<?php echo esc_attr( $select_id ); ?>" aria-controls="<?php echo esc_attr( $section_id ); ?>">
                <?php foreach ( $options as $value => $label ) : ?>
                    <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $current_theme, $value ); ?>>
                        <?php echo esc_html( $label ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php

        return ob_get_clean();
    }

    private function parse_plans( $plans_string, $currency, $period, $default_cta ) {
        if ( empty( $plans_string ) ) {
            return [];
        }

        $plans = [];
        $raw_plans = array_filter( array_map( 'trim', explode( ';', $plans_string ) ) );

        foreach ( $raw_plans as $raw_plan ) {
            $parts = array_map( 'trim', explode( '|', $raw_plan ) );
            if ( empty( $parts[0] ) ) {
                continue;
            }

            $plans[] = [
                'name'        => $parts[0],
                'price'       => $parts[1] ?? '0',
                'tagline'     => $parts[2] ?? '',
                'features'    => $this->parse_features( $parts[3] ?? '' ),
                'badge'       => $parts[4] ?? '',
                'button_text' => $parts[5] ?? $default_cta,
                'button_url'  => $parts[6] ?? '#',
                'footnote'    => $parts[7] ?? '',
                'currency'    => $currency,
                'period'      => $period,
            ];
        }

        return $plans;
    }

    private function parse_features( $features_string ) {
        if ( empty( $features_string ) ) {
            return [];
        }

        $features = array_filter( array_map( 'trim', explode( ',', $features_string ) ) );

        return $features;
    }

    private function get_default_plans( $currency, $period, $default_cta ) {
        return [
            [
                'name'        => __( 'Starter', 'pricing-table-pro' ),
                'price'       => '29',
                'tagline'     => __( 'Perfect for early teams validating traction.', 'pricing-table-pro' ),
                'features'    => [
                    __( 'Up to 3 workspaces', 'pricing-table-pro' ),
                    __( 'Email support', 'pricing-table-pro' ),
                    __( 'Core analytics dashboards', 'pricing-table-pro' ),
                ],
                'badge'       => '',
                'button_text' => $default_cta,
                'button_url'  => '#',
                'footnote'    => '',
                'currency'    => $currency,
                'period'      => $period,
            ],
            [
                'name'        => __( 'Growth', 'pricing-table-pro' ),
                'price'       => '79',
                'tagline'     => __( 'Scale collaboration with automation and insights.', 'pricing-table-pro' ),
                'features'    => [
                    __( 'Unlimited workspaces', 'pricing-table-pro' ),
                    __( 'Priority chat + email support', 'pricing-table-pro' ),
                    __( 'Advanced reporting templates', 'pricing-table-pro' ),
                    __( 'Quarterly strategy review', 'pricing-table-pro' ),
                ],
                'badge'       => __( 'Most popular', 'pricing-table-pro' ),
                'button_text' => __( 'Start free trial', 'pricing-table-pro' ),
                'button_url'  => '#',
                'footnote'    => __( '14-day free trial, no credit card required.', 'pricing-table-pro' ),
                'currency'    => $currency,
                'period'      => $period,
            ],
            [
                'name'        => __( 'Enterprise', 'pricing-table-pro' ),
                'price'       => '149',
                'tagline'     => __( 'Dedicated success team and compliance controls.', 'pricing-table-pro' ),
                'features'    => [
                    __( 'Custom contract & invoicing', 'pricing-table-pro' ),
                    __( 'SAML SSO + SCIM provisioning', 'pricing-table-pro' ),
                    __( 'Dedicated customer success manager', 'pricing-table-pro' ),
                    __( 'Quarterly onsite workshop', 'pricing-table-pro' ),
                ],
                'badge'       => __( 'Best value', 'pricing-table-pro' ),
                'button_text' => __( 'Talk to sales', 'pricing-table-pro' ),
                'button_url'  => '#',
                'footnote'    => __( 'Custom onboarding timeline included.', 'pricing-table-pro' ),
                'currency'    => $currency,
                'period'      => $period,
            ],
        ];
    }
}

new PTP_Pricing_Table_Pro();
