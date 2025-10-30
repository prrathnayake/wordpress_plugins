<?php
/**
 * Plugin Name:       Case Study Carousel
 * Description:       Showcase customer wins with animated case study cards and hero metrics.
 * Version:           1.0.0
 * Author:            ChatGPT
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 5.8
 * Tested up to:      6.6
 * Text Domain:       case-study-carousel
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CSC_Case_Study_Carousel {
    const VERSION = '1.0.0';

    public function __construct() {
        add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
        add_shortcode( 'case_study_carousel', [ $this, 'render_shortcode' ] );
    }

    public function register_assets() {
        wp_register_style( 'swiper', 'https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css', [], '10.3.1' );
        wp_register_script( 'swiper', 'https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js', [], '10.3.1', true );

        wp_register_style(
            'csc-case-study-carousel',
            plugins_url( 'assets/css/case-study-carousel.css', __FILE__ ),
            [ 'swiper' ],
            self::VERSION
        );

        wp_register_script(
            'csc-case-study-carousel',
            plugins_url( 'assets/js/case-study-carousel.js', __FILE__ ),
            [ 'swiper' ],
            self::VERSION,
            true
        );
    }

    public function render_shortcode( $atts ) {
        $atts = shortcode_atts(
            [
                'title'       => __( 'Customer growth snapshots', 'case-study-carousel' ),
                'subtitle'    => __( 'See how modern teams ship measurable wins in under 90 days.', 'case-study-carousel' ),
                'cases'       => '',
                'autoplay'    => 'true',
                'speed'       => '6000',
                'interval'    => '0',
            ],
            $atts,
            'case_study_carousel'
        );

        $cases = $this->parse_cases( $atts['cases'] );
        if ( empty( $cases ) ) {
            $cases = $this->get_default_cases();
        }

        wp_enqueue_style( 'swiper' );
        wp_enqueue_script( 'swiper' );
        wp_enqueue_style( 'csc-case-study-carousel' );
        wp_enqueue_script( 'csc-case-study-carousel' );

        $autoplay = filter_var( $atts['autoplay'], FILTER_VALIDATE_BOOLEAN );
        $speed    = max( 800, intval( $atts['speed'] ) );
        $interval = max( 0, intval( $atts['interval'] ) );

        ob_start();
        ?>
        <section class="csc-wrap mkt-ui">
            <header class="csc-header">
                <div class="csc-header__copy">
                    <p class="csc-header__eyebrow"><?php esc_html_e( 'Case studies', 'case-study-carousel' ); ?></p>
                    <h2 class="csc-header__title"><?php echo esc_html( $atts['title'] ); ?></h2>
                    <p class="csc-header__subtitle"><?php echo esc_html( $atts['subtitle'] ); ?></p>
                </div>
                <div class="csc-header__metrics" data-animate="fade">
                    <?php foreach ( array_slice( $cases, 0, 2 ) as $metric_case ) : ?>
                        <div class="csc-metric">
                            <span class="csc-metric__value"><?php echo esc_html( $metric_case['metric'] ); ?></span>
                            <span class="csc-metric__label"><?php echo esc_html( $metric_case['name'] ); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </header>

            <div class="swiper csc-swiper"
                data-autoplay="<?php echo $autoplay ? 'true' : 'false'; ?>"
                data-speed="<?php echo esc_attr( $speed ); ?>"
                data-interval="<?php echo esc_attr( $interval ); ?>">
                <div class="swiper-wrapper">
                    <?php foreach ( $cases as $case ) : ?>
                        <div class="swiper-slide">
                            <article class="csc-card" data-animate="rise">
                                <figure class="csc-media">
                                    <?php if ( ! empty( $case['image'] ) ) : ?>
                                        <img src="<?php echo esc_url( $case['image'] ); ?>" alt="<?php echo esc_attr( $case['name'] ); ?>" loading="lazy" decoding="async" />
                                    <?php else : ?>
                                        <span class="csc-media__placeholder" aria-hidden="true"><?php echo esc_html( $case['initials'] ); ?></span>
                                    <?php endif; ?>
                                </figure>
                                <div class="csc-card__body">
                                    <h3 class="csc-card__name"><?php echo esc_html( $case['name'] ); ?></h3>
                                    <p class="csc-card__metric"><?php echo esc_html( $case['metric'] ); ?></p>
                                    <p class="csc-card__summary"><?php echo esc_html( $case['summary'] ); ?></p>
                                    <?php if ( ! empty( $case['url'] ) ) : ?>
                                        <a class="csc-card__link" href="<?php echo esc_url( $case['url'] ); ?>">
                                            <?php esc_html_e( 'View case study', 'case-study-carousel' ); ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="csc-nav csc-nav--prev" aria-label="<?php esc_attr_e( 'Previous case study', 'case-study-carousel' ); ?>"></button>
                <button class="csc-nav csc-nav--next" aria-label="<?php esc_attr_e( 'Next case study', 'case-study-carousel' ); ?>"></button>
                <div class="csc-pagination" aria-hidden="true"></div>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }

    private function parse_cases( $cases_string ) {
        if ( empty( $cases_string ) ) {
            return [];
        }

        $cases = [];
        $rows  = array_filter( array_map( 'trim', explode( ';', $cases_string ) ) );

        foreach ( $rows as $row ) {
            $parts = array_map( 'trim', explode( '|', $row ) );
            if ( empty( $parts[0] ) ) {
                continue;
            }

            $name    = $parts[0];
            $metric  = $parts[1] ?? '';
            $summary = $parts[2] ?? '';
            $image   = $parts[3] ?? '';
            $url     = $parts[4] ?? '';

            $cases[] = [
                'name'     => $name,
                'metric'   => $metric,
                'summary'  => $summary,
                'image'    => $image,
                'url'      => $url,
                'initials' => $this->initials_from_name( $name ),
            ];
        }

        return $cases;
    }

    private function initials_from_name( $name ) {
        $words = preg_split( '/\s+/', trim( $name ) );
        $initials = '';
        foreach ( $words as $word ) {
            if ( $word !== '' ) {
                $initials .= strtoupper( mb_substr( $word, 0, 1 ) );
            }
        }
        return mb_substr( $initials, 0, 2 );
    }

    private function get_default_cases() {
        return [
            [
                'name'    => __( 'Arcadia Commerce', 'case-study-carousel' ),
                'metric'  => __( '+182% pipeline value', 'case-study-carousel' ),
                'summary' => __( 'Rolled out conversion playbooks across 6 product lines in eight weeks.', 'case-study-carousel' ),
                'image'   => plugins_url( 'assets/img/arcadia.svg', __FILE__ ),
                'url'     => '#',
                'initials'=> 'AC',
            ],
            [
                'name'    => __( 'Northwind Analytics', 'case-study-carousel' ),
                'metric'  => __( '4.6x demo-to-close', 'case-study-carousel' ),
                'summary' => __( 'Launched a new narrative, routed signal-based journeys, and doubled expansion.', 'case-study-carousel' ),
                'image'   => plugins_url( 'assets/img/northwind.svg', __FILE__ ),
                'url'     => '#',
                'initials'=> 'NA',
            ],
            [
                'name'    => __( 'SignalOps', 'case-study-carousel' ),
                'metric'  => __( '90 day payback', 'case-study-carousel' ),
                'summary' => __( 'Unified lifecycle messaging and automated win-back motions across PLG + sales.', 'case-study-carousel' ),
                'image'   => plugins_url( 'assets/img/signalops.svg', __FILE__ ),
                'url'     => '#',
                'initials'=> 'SO',
            ],
        ];
    }
}

new CSC_Case_Study_Carousel();
