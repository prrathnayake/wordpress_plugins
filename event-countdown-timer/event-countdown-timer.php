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
                'title'         => __( 'Product Reveal Live Stream', 'event-countdown-timer' ),
                'date'          => gmdate( 'Y-m-d\TH:i:s\Z', strtotime( '+14 days' ) ),
                'timezone'      => 'UTC',
                'description'   => __( 'Reserve your seat for the next-generation platform announcement.', 'event-countdown-timer' ),
                'button_text'   => __( 'Save your seat', 'event-countdown-timer' ),
                'button_url'    => '#',
                'background'    => '',
                'theme'         => 'yellow',
                'theme_switcher'=> 'false',
                'badge'         => __( 'Live virtual event', 'event-countdown-timer' ),
            ],
            $atts,
            'event_countdown'
        );

        $theme_source   = ! empty( $atts['theme'] ) ? $atts['theme'] : $atts['background'];
        $theme          = $this->normalize_theme( $theme_source );
        $theme_switcher = filter_var( $atts['theme_switcher'], FILTER_VALIDATE_BOOLEAN );

        wp_enqueue_style( 'ect-countdown' );
        wp_enqueue_script( 'ect-countdown' );

        $target_date = sanitize_text_field( $atts['date'] );
        $timezone    = sanitize_text_field( $atts['timezone'] );
        $container_classes = [
            'ect-countdown',
            'ect-countdown--theme-' . $theme,
            'ect-theme--' . $theme,
            'mkt-theme--' . $theme,
        ];

        if ( ! empty( $atts['background'] ) ) {
            $container_classes[] = 'ect-countdown--' . sanitize_html_class( $atts['background'] );
        }

        $section_id     = wp_unique_id( 'ect-countdown-' );
        $theme_prefixes = 'ect-countdown--theme-,ect-theme--,mkt-theme--';

        ob_start();
        ?>
        <section
            id="<?php echo esc_attr( $section_id ); ?>"
            class="<?php echo esc_attr( implode( ' ', $container_classes ) ); ?> mkt-ui"
            data-mkt-theme="<?php echo esc_attr( $theme ); ?>"
            data-theme-prefixes="<?php echo esc_attr( $theme_prefixes ); ?>"
            data-target-date="<?php echo esc_attr( $target_date ); ?>"
            data-timezone="<?php echo esc_attr( $timezone ); ?>"
        >
            <?php if ( $theme_switcher ) : ?>
                <?php echo $this->render_theme_switcher( $section_id, $theme ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <?php endif; ?>
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
            'light'  => __( 'Light', 'event-countdown-timer' ),
            'dark'   => __( 'Dark', 'event-countdown-timer' ),
            'yellow' => __( 'Yellow', 'event-countdown-timer' ),
        ];

        $select_id = $section_id . '-theme';

        ob_start();
        ?>
        <div class="mkt-theme-switcher" data-target="<?php echo esc_attr( $section_id ); ?>">
            <label for="<?php echo esc_attr( $select_id ); ?>"><?php esc_html_e( 'Theme', 'event-countdown-timer' ); ?></label>
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
}

new ECT_Event_Countdown_Timer();
