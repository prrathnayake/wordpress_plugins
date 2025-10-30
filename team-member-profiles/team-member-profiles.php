<?php
/**
 * Plugin Name:       Team Member Profiles
 * Description:       Display polished team member profile cards with photos, bios, and social links using a simple shortcode.
 * Version:           1.0.0
 * Author:            ChatGPT
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 5.8
 * Tested up to:      6.6
 * Text Domain:       team-member-profiles
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'TMP_Team_Member_Profiles' ) ) {
    class TMP_Team_Member_Profiles {
        const VERSION = '1.0.0';

        public function __construct() {
            add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
            add_shortcode( 'team_member_profiles', [ $this, 'render_shortcode' ] );
        }

        public function register_assets() {
            $style_handle = 'tmp-team-member-profiles';
            wp_register_style(
                $style_handle,
                plugins_url( 'assets/css/team-member-profiles.css', __FILE__ ),
                [],
                self::VERSION
            );

            wp_register_script(
                $style_handle,
                plugins_url( 'assets/js/team-member-profiles.js', __FILE__ ),
                [],
                self::VERSION,
                true
            );
        }

        public function render_shortcode( $atts ) {
            $atts = shortcode_atts(
                [
                    'layout'  => 'grid',
                    'columns' => 3,
                    'members' => '',
                    'theme'   => 'light',
                ],
                $atts,
                'team_member_profiles'
            );

            $columns = (int) $atts['columns'];
            if ( $columns < 1 || $columns > 4 ) {
                $columns = 3;
            }

            $members = $this->parse_members( $atts['members'] );
            if ( empty( $members ) ) {
                $members = $this->get_default_members();
            }

            wp_enqueue_style( 'tmp-team-member-profiles' );
            wp_enqueue_script( 'tmp-team-member-profiles' );

            ob_start();
            ?>
            <section class="tmp-profile-grid tmp-profile-grid--<?php echo esc_attr( $atts['layout'] ); ?> tmp-profile-grid--cols-<?php echo esc_attr( $columns ); ?> tmp-profile-grid--theme-<?php echo esc_attr( $atts['theme'] ); ?> mkt-ui">
                <?php foreach ( $members as $member ) : ?>
                    <article class="tmp-profile-card" data-animate="fade-up">
                        <div class="tmp-profile-card__media">
                            <?php if ( ! empty( $member['photo'] ) ) : ?>
                                <img src="<?php echo esc_url( $member['photo'] ); ?>" alt="<?php echo esc_attr( $member['name'] ); ?>" loading="lazy" />
                            <?php else : ?>
                                <div class="tmp-profile-card__placeholder" aria-hidden="true">
                                    <span><?php echo esc_html( $this->get_initials( $member['name'] ) ); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="tmp-profile-card__body">
                            <h3 class="tmp-profile-card__name"><?php echo esc_html( $member['name'] ); ?></h3>
                            <?php if ( ! empty( $member['role'] ) ) : ?>
                                <p class="tmp-profile-card__role"><?php echo esc_html( $member['role'] ); ?></p>
                            <?php endif; ?>
                            <?php if ( ! empty( $member['bio'] ) ) : ?>
                                <p class="tmp-profile-card__bio"><?php echo esc_html( $member['bio'] ); ?></p>
                            <?php endif; ?>
                            <?php if ( ! empty( $member['social'] ) ) : ?>
                                <div class="tmp-profile-card__social" aria-label="<?php esc_attr_e( 'Social links', 'team-member-profiles' ); ?>">
                                    <?php foreach ( $member['social'] as $social ) : ?>
                                        <a href="<?php echo esc_url( $social['url'] ); ?>" class="tmp-profile-card__social-link" target="<?php echo esc_attr( $social['target'] ); ?>" rel="noopener">
                                            <span><?php echo esc_html( $social['label'] ); ?></span>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
            <?php
            return ob_get_clean();
        }

        private function parse_members( $members_string ) {
            if ( empty( $members_string ) ) {
                return [];
            }

            $members = [];
            $raw_members = array_filter( array_map( 'trim', explode( ';', $members_string ) ) );

            foreach ( $raw_members as $raw_member ) {
                $parts = array_map( 'trim', explode( '|', $raw_member ) );

                $members[] = [
                    'name'   => $parts[0] ?? '',
                    'role'   => $parts[1] ?? '',
                    'photo'  => $parts[2] ?? '',
                    'bio'    => $parts[3] ?? '',
                    'social' => $this->parse_social_links( $parts[4] ?? '' ),
                ];
            }

            return array_filter(
                $members,
                function ( $member ) {
                    return ! empty( $member['name'] );
                }
            );
        }

        private function parse_social_links( $social_string ) {
            if ( empty( $social_string ) ) {
                return [];
            }

            $links = [];
            $raw_links = array_filter( array_map( 'trim', explode( ',', $social_string ) ) );

            foreach ( $raw_links as $link ) {
                $parts = array_map( 'trim', explode( '~', $link ) );
                $links[] = [
                    'label'  => $parts[0] ?? __( 'Connect', 'team-member-profiles' ),
                    'url'    => $parts[1] ?? '#',
                    'target' => isset( $parts[2] ) && '_self' === $parts[2] ? '_self' : '_blank',
                ];
            }

            return $links;
        }

        private function get_initials( $name ) {
            $words = preg_split( '/\s+/', trim( $name ) );
            $initials = '';

            foreach ( $words as $word ) {
                if ( ! empty( $word ) ) {
                    $initials .= strtoupper( mb_substr( $word, 0, 1 ) );
                }
            }

            return mb_substr( $initials, 0, 2 );
        }

        private function get_default_members() {
            return [
                [
                    'name'  => __( 'Jordan Blake', 'team-member-profiles' ),
                    'role'  => __( 'Founder & CEO', 'team-member-profiles' ),
                    'photo' => plugins_url( 'assets/img/jordan.jpg', __FILE__ ),
                    'bio'   => __( 'Vision-led leader focused on customer experience and sustainable growth.', 'team-member-profiles' ),
                    'social'=> [
                        [
                            'label'  => __( 'LinkedIn', 'team-member-profiles' ),
                            'url'    => 'https://linkedin.com',
                            'target' => '_blank',
                        ],
                    ],
                ],
                [
                    'name'  => __( 'Priya Desai', 'team-member-profiles' ),
                    'role'  => __( 'Head of Product', 'team-member-profiles' ),
                    'photo' => plugins_url( 'assets/img/priya.jpg', __FILE__ ),
                    'bio'   => __( 'Owns the product roadmap, shipping delightful experiences every sprint.', 'team-member-profiles' ),
                    'social'=> [
                        [
                            'label'  => __( 'Dribbble', 'team-member-profiles' ),
                            'url'    => 'https://dribbble.com',
                            'target' => '_blank',
                        ],
                    ],
                ],
                [
                    'name'  => __( 'Miguel Alvarez', 'team-member-profiles' ),
                    'role'  => __( 'Lead Engineer', 'team-member-profiles' ),
                    'photo' => plugins_url( 'assets/img/miguel.jpg', __FILE__ ),
                    'bio'   => __( 'Architecting resilient infrastructure and guiding engineering excellence.', 'team-member-profiles' ),
                    'social'=> [
                        [
                            'label'  => __( 'GitHub', 'team-member-profiles' ),
                            'url'    => 'https://github.com',
                            'target' => '_blank',
                        ],
                    ],
                ],
            ];
        }
    }
}

new TMP_Team_Member_Profiles();
