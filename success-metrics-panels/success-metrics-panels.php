<?php
/**
 * Plugin Name:       Success Metrics Panels
 * Description:       Animated KPI spotlight panels with trend descriptions and shared marketing UI styling.
 * Version:           1.0.0
 * Author:            ChatGPT
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 5.8
 * Tested up to:      6.6
 * Text Domain:       success-metrics-panels
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SMP_Success_Metrics_Panels {
    const VERSION = '1.0.0';

    public function __construct() {
        add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
        add_shortcode( 'success_metrics', [ $this, 'render_shortcode' ] );
    }

    public function register_assets() {
        wp_register_style(
            'smp-success-metrics',
            plugins_url( 'assets/css/success-metrics-panels.css', __FILE__ ),
            [],
            self::VERSION
        );

        wp_register_script(
            'smp-success-metrics',
            plugins_url( 'assets/js/success-metrics-panels.js', __FILE__ ),
            [],
            self::VERSION,
            true
        );
    }

    public function render_shortcode( $atts ) {
        $atts = shortcode_atts(
            [
                'heading'  => __( 'Momentum this quarter', 'success-metrics-panels' ),
                'subtitle' => __( 'Real-time KPIs we monitor to keep the growth flywheel spinning.', 'success-metrics-panels' ),
                'metrics'  => '',
            ],
            $atts,
            'success_metrics'
        );

        $metrics = $this->parse_metrics( $atts['metrics'] );
        if ( empty( $metrics ) ) {
            $metrics = $this->get_default_metrics();
        }

        wp_enqueue_style( 'smp-success-metrics' );
        wp_enqueue_script( 'smp-success-metrics' );

        ob_start();
        ?>
        <section class="smp-wrap mkt-ui">
            <header class="smp-header">
                <p class="smp-eyebrow"><?php esc_html_e( 'KPIs', 'success-metrics-panels' ); ?></p>
                <h2 class="smp-heading"><?php echo esc_html( $atts['heading'] ); ?></h2>
                <p class="smp-subtitle"><?php echo esc_html( $atts['subtitle'] ); ?></p>
            </header>
            <div class="smp-grid">
                <?php foreach ( $metrics as $index => $metric ) :
                    $progress = min( 100, max( 0, $metric['progress'] ) );
                    ?>
                    <article class="smp-card" data-animate="fade" style="--smp-progress: <?php echo esc_attr( $progress ); ?>%; --smp-delay: <?php echo esc_attr( $index * 70 ); ?>ms;">
                        <div class="smp-card__top">
                            <span class="smp-card__label"><?php echo esc_html( $metric['label'] ); ?></span>
                            <span class="smp-card__value"><?php echo esc_html( $metric['value'] ); ?></span>
                        </div>
                        <?php if ( ! empty( $metric['trend'] ) ) : ?>
                            <p class="smp-card__trend"><?php echo esc_html( $metric['trend'] ); ?></p>
                        <?php endif; ?>
                        <?php if ( ! empty( $metric['description'] ) ) : ?>
                            <p class="smp-card__description"><?php echo esc_html( $metric['description'] ); ?></p>
                        <?php endif; ?>
                        <div class="smp-card__progress" role="presentation">
                            <div class="smp-card__progress-bar"></div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }

    private function parse_metrics( $metrics_string ) {
        if ( empty( $metrics_string ) ) {
            return [];
        }

        $metrics = [];
        $rows    = array_filter( array_map( 'trim', explode( ';', $metrics_string ) ) );

        foreach ( $rows as $row ) {
            $parts = array_map( 'trim', explode( '|', $row ) );
            if ( empty( $parts[0] ) ) {
                continue;
            }

            $metrics[] = [
                'label'       => $parts[0],
                'value'       => $parts[1] ?? '',
                'trend'       => $parts[2] ?? '',
                'description' => $parts[3] ?? '',
                'progress'    => isset( $parts[4] ) ? floatval( $parts[4] ) : 100,
            ];
        }

        return $metrics;
    }

    private function get_default_metrics() {
        return [
            [
                'label'       => __( 'Activation rate', 'success-metrics-panels' ),
                'value'       => '74%',
                'trend'       => __( '+12 pts vs. last quarter', 'success-metrics-panels' ),
                'description' => __( 'Guided onboarding flows and intent scoring unlocked a new baseline.', 'success-metrics-panels' ),
                'progress'    => 74,
            ],
            [
                'label'       => __( 'Expansion ARR', 'success-metrics-panels' ),
                'value'       => '$428K',
                'trend'       => __( '+38% QoQ', 'success-metrics-panels' ),
                'description' => __( 'Strategic success reviews paired with new value packaging.', 'success-metrics-panels' ),
                'progress'    => 86,
            ],
            [
                'label'       => __( 'Support CSAT', 'success-metrics-panels' ),
                'value'       => '4.9/5',
                'trend'       => __( 'Top 5% in category', 'success-metrics-panels' ),
                'description' => __( 'Async help center + AI triage reduced wait times to under 4 minutes.', 'success-metrics-panels' ),
                'progress'    => 92,
            ],
        ];
    }
}

new SMP_Success_Metrics_Panels();
