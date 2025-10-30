<?php
/**
 * Plugin Name:       Event Countdown Timer
 * Description:       Elegant hero countdown for launches or webinars with timezone-aware messaging and CTA.
 * Version:           1.0.0
 * Author:            ChatGPT
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 5.8
 * Tested up to:      6.6
 * Text Domain:       event-countdown-timer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ECT_Event_Countdown_Timer {
    const VERSION = '1.0.0';

    public function __construct() {
        add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
        add_shortcode( 'event_countdown', [ $this, 'render_shortcode' ] );
    }

    public function register_assets() {
        $handle = 'ect-countdown';
        wp_register_style(
            $handle,
            plugins_url( 'assets/css/event-countdown-timer.css', __FILE__ ),
            [],
            self::VERSION
        );
        wp_register_script(
            $handle,
            plugins_url( 'assets/js/event-countdown-timer.js', __FILE__ ),
            [],
            self::VERSION,
            true
        );
    }

    public function render_shortcode( $atts ) {
        $atts = shortcode_atts(
            [
                'title'       => __( 'Product Reveal Live Stream', 'event-countdown-timer' ),
                'date'        => gmdate( 'Y-m-d\TH:i:s\Z', strtotime( '+14 days' ) ),
                'timezone'    => 'UTC',
                'description' => __( 'Reserve your seat for the next-generation platform announcement.', 'event-countdown-timer' ),
                'button_text' => __( 'Save your seat', 'event-countdown-timer' ),
                'button_url'  => '#',
                'background'  => 'gradient',
                'badge'       => __( 'Live virtual event', 'event-countdown-timer' ),
            ],
            $atts,
            'event_countdown'
        );

        wp_enqueue_style( 'ect-countdown' );
        wp_enqueue_script( 'ect-countdown' );

        $target_date = sanitize_text_field( $atts['date'] );
        $timezone    = sanitize_text_field( $atts['timezone'] );
        $container_classes = [ 'ect-countdown', 'ect-countdown--' . sanitize_html_class( $atts['background'] ) ];

        ob_start();
        ?>
        <section class="<?php echo esc_attr( implode( ' ', $container_classes ) ); ?> mkt-ui" data-target-date="<?php echo esc_attr( $target_date ); ?>" data-timezone="<?php echo esc_attr( $timezone ); ?>">
            <div class="ect-countdown__inner" data-animate="pulse">
                <?php if ( ! empty( $atts['badge'] ) ) : ?>
                    <span class="ect-countdown__badge"><?php echo esc_html( $atts['badge'] ); ?></span>
                <?php endif; ?>
                <h2 class="ect-countdown__title"><?php echo esc_html( $atts['title'] ); ?></h2>
                <?php if ( ! empty( $atts['description'] ) ) : ?>
                    <p class="ect-countdown__description"><?php echo esc_html( $atts['description'] ); ?></p>
                <?php endif; ?>
                <div class="ect-countdown__timer" aria-live="polite">
                    <div class="ect-countdown__unit" data-unit="days">
                        <span class="ect-countdown__value">00</span>
                        <span class="ect-countdown__label"><?php esc_html_e( 'Days', 'event-countdown-timer' ); ?></span>
                    </div>
                    <div class="ect-countdown__unit" data-unit="hours">
                        <span class="ect-countdown__value">00</span>
                        <span class="ect-countdown__label"><?php esc_html_e( 'Hours', 'event-countdown-timer' ); ?></span>
                    </div>
                    <div class="ect-countdown__unit" data-unit="minutes">
                        <span class="ect-countdown__value">00</span>
                        <span class="ect-countdown__label"><?php esc_html_e( 'Minutes', 'event-countdown-timer' ); ?></span>
                    </div>
                    <div class="ect-countdown__unit" data-unit="seconds">
                        <span class="ect-countdown__value">00</span>
                        <span class="ect-countdown__label"><?php esc_html_e( 'Seconds', 'event-countdown-timer' ); ?></span>
                    </div>
                </div>
                <p class="ect-countdown__meta">
                    <?php printf( esc_html__( 'Starting %1$s (%2$s)', 'event-countdown-timer' ), esc_html( $target_date ), esc_html( $timezone ) ); ?>
                </p>
                <?php if ( ! empty( $atts['button_text'] ) && ! empty( $atts['button_url'] ) ) : ?>
                    <a class="ect-countdown__button" href="<?php echo esc_url( $atts['button_url'] ); ?>">
                        <?php echo esc_html( $atts['button_text'] ); ?>
                    </a>
                <?php endif; ?>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }
}

new ECT_Event_Countdown_Timer();
