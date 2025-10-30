<?php
/**
 * Plugin Name: Google Reviews Marquee (Pro Slider)
 * Description: Fetches Google reviews (via Places API), caches the highest-rated ones, and displays a horizontal auto-sliding marquee. Nightly sync + manual sync.
 * Version: 1.0.0
 * Author: BuiltByPasan + ChatGPT
 * License: GPLv2 or later
 * Text Domain: google-reviews-marquee
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class GRM_Plugin {
    const OPTS_KEY = 'grm_settings';
    const TABLE = 'grm_reviews_cache';
    const CRON_HOOK = 'grm_daily_sync_event';
    private static $instance = null;

    public static function init(){ if ( self::$instance === null ) self::$instance = new self(); return self::$instance; }

    private function __construct(){
        add_action( 'admin_menu', array($this,'admin_menu') );
        add_action( 'admin_init', array($this,'register_settings') );
        add_action( 'wp_ajax_grm_sync_now', array($this,'ajax_sync_now') );
        add_action( 'wp_enqueue_scripts', array($this,'register_assets') );
        add_shortcode( 'google_reviews_marquee', array($this,'shortcode') );
        add_action( self::CRON_HOOK, array($this,'run_sync') );
        register_activation_hook( __FILE__, array( __CLASS__, 'activate' ) );
        register_deactivation_hook( __FILE__, array( __CLASS__, 'deactivate' ) );
    }

    public static function activate(){
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE;
        $charset = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            review_id VARCHAR(191) NOT NULL,
            author_name VARCHAR(191) NULL,
            author_photo TEXT NULL,
            rating FLOAT NOT NULL,
            text MEDIUMTEXT NULL,
            time_created DATETIME NULL,
            photo_url TEXT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY review_idx (review_id),
            KEY rating_idx (rating),
            KEY time_idx (time_created)
        ) {$charset};";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
        if ( ! wp_next_scheduled( self::CRON_HOOK ) ){
            $timestamp = strtotime('tomorrow 03:10');
            wp_schedule_event( $timestamp, 'daily', self::CRON_HOOK );
        }
        add_option( 'grm_last_sync', '' );
    }
    public static function deactivate(){
        $timestamp = wp_next_scheduled( self::CRON_HOOK );
        if ( $timestamp ) wp_unschedule_event( $timestamp, self::CRON_HOOK );
    }

    public function register_settings(){
        register_setting( 'grm_group', self::OPTS_KEY, array($this,'sanitize_settings') );
        add_settings_section( 'grm_main', 'Google Reviews Settings', function(){
            echo '<p>Enter your Google Places API details. The plugin will cache top reviews nightly and on demand.</p>';
        }, 'grm_settings_page' );
        add_settings_field( 'api_key', 'Google Places API Key', array($this,'field_api_key'), 'grm_settings_page', 'grm_main' );
        add_settings_field( 'place_id', 'Place ID', array($this,'field_place_id'), 'grm_settings_page', 'grm_main' );
        add_settings_field( 'min_rating', 'Minimum Rating to Cache', array($this,'field_min_rating'), 'grm_settings_page', 'grm_main' );
        add_settings_field( 'max_reviews', 'Max Reviews to Cache', array($this,'field_max_reviews'), 'grm_settings_page', 'grm_main' );
        add_settings_field( 'include_place_photos', 'Include Place Photos', array($this,'field_include_place_photos'), 'grm_settings_page', 'grm_main' );
    }
    public function sanitize_settings($o){
        $o['api_key'] = isset($o['api_key']) ? sanitize_text_field($o['api_key']) : '';
        $o['place_id'] = isset($o['place_id']) ? sanitize_text_field($o['place_id']) : '';
        $o['min_rating'] = isset($o['min_rating']) ? floatval($o['min_rating']) : 4.5;
        $o['max_reviews'] = isset($o['max_reviews']) ? intval($o['max_reviews']) : 30;
        $o['include_place_photos'] = !empty($o['include_place_photos']) ? 1 : 0;
        return $o;
    }
    public function field_api_key(){ $o=get_option(self::OPTS_KEY,[]); $v=esc_attr($o['api_key']??''); echo "<input type='text' name='".self::OPTS_KEY."[api_key]' value='{$v}' class='regular-text' placeholder='AIza...'>"; }
    public function field_place_id(){ $o=get_option(self::OPTS_KEY,[]); $v=esc_attr($o['place_id']??''); echo "<input type='text' name='".self::OPTS_KEY."[place_id]' value='{$v}' class='regular-text' placeholder='ChIJ...'>"; }
    public function field_min_rating(){ $o=get_option(self::OPTS_KEY,[]); $v=esc_attr($o['min_rating']??'4.5'); echo "<input type='number' step='0.1' min='1' max='5' name='".self::OPTS_KEY."[min_rating]' value='{$v}' class='small-text'>"; }
    public function field_max_reviews(){ $o=get_option(self::OPTS_KEY,[]); $v=esc_attr($o['max_reviews']??'30'); echo "<input type='number' min='1' max='100' name='".self::OPTS_KEY."[max_reviews]' value='{$v}' class='small-text'>"; }
    public function field_include_place_photos(){ $o=get_option(self::OPTS_KEY,[]); $v=!empty($o['include_place_photos'])?'checked':''; echo "<label><input type='checkbox' name='".self::OPTS_KEY."[include_place_photos]' value='1' {$v}> Also pull a few place photos</label>"; }

    public function admin_menu(){ add_options_page('Google Reviews Marquee','Google Reviews','manage_options','grm_settings',array($this,'settings_page')); }
    public function settings_page(){
        if ( ! current_user_can('manage_options') ) return;
        $last=get_option('grm_last_sync','Never'); $nonce=wp_create_nonce('grm_sync'); ?>
        <div class="wrap">
            <h1>Google Reviews Marquee</h1>
            <form method="post" action="options.php" style="max-width:800px;">
                <?php settings_fields('grm_group'); do_settings_sections('grm_settings_page'); submit_button(); ?>
            </form>
            <hr><p><strong>Last sync:</strong> <?php echo esc_html($last); ?></p>
            <button id="grm-sync-btn" class="button button-primary">Sync Now</button>
            <span id="grm-sync-status" style="margin-left:10px;"></span>
        </div>
        <script>
        (function(){
            var btn=document.getElementById('grm-sync-btn'); var status=document.getElementById('grm-sync-status');
            if(!btn) return;
            btn.addEventListener('click', function(){
                status.textContent='Syncing...';
                var d=new FormData(); d.append('action','grm_sync_now'); d.append('nonce','<?php echo esc_js($nonce); ?>');
                fetch(ajaxurl,{method:'POST', body:d, credentials:'same-origin'}).then(r=>r.json()).then(function(j){ status.textContent=(j&&j.message)?j.message:'Done'; }).catch(function(){ status.textContent='Error'; });
            });
        })();
        </script><?php
    }

    public function register_assets(){
        wp_register_style('swiper','https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css',[], '10.3.1');
        wp_register_script('swiper','https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js',[], '10.3.1', true);
        wp_register_style('grm', plugins_url('assets/css/grm.css', __FILE__), ['swiper'], '1.0.0');
        wp_register_script('grm', plugins_url('assets/js/grm.js', __FILE__), ['swiper'], '1.0.0', true);
    }
    private function enqueue_assets(){ wp_enqueue_style('swiper'); wp_enqueue_script('swiper'); wp_enqueue_style('grm'); wp_enqueue_script('grm'); }

    public function shortcode($atts){
        $atts=shortcode_atts(['title'=>'What Our Customers Say','limit'=>'24','min_rating'=>'','autoplay'=>'true','speed'=>'normal'],$atts,'google_reviews_marquee');
        $this->enqueue_assets();
        $items=$this->get_cached_reviews($atts);
        if(empty($items)) return '';
        $title=sanitize_text_field($atts['title']); $limit=max(1,intval($atts['limit'])); ob_start(); ?>
        <section class="grm-wrap">
            <?php if($title): ?><h3 class="grm-title"><?php echo esc_html($title); ?></h3><?php endif; ?>
            <div class="swiper grm-swiper" data-speed="<?php echo esc_attr($atts['speed']); ?>" data-autoplay="<?php echo esc_attr($atts['autoplay']); ?>">
                <div class="swiper-wrapper">
                    <?php foreach(array_slice($items,0,$limit) as $it): ?>
                    <div class="swiper-slide">
                        <figure class="grm-card">
                            <div class="grm-media">
                                <?php if(!empty($it->photo_url)): ?>
                                    <img src="<?php echo esc_url($it->photo_url); ?>" alt="" loading="lazy" decoding="async">
                                <?php elseif(!empty($it->author_photo)): ?>
                                    <img src="<?php echo esc_url($it->author_photo); ?>" alt="" loading="lazy" decoding="async">
                                <?php endif; ?>
                            </div>
                            <figcaption class="grm-caption">
                                <div class="grm-stars"><?php echo str_repeat('★', max(0, intval($it->rating))); ?></div>
                                <div class="grm-text"><?php echo esc_html( wp_trim_words( $it->text, 24, '…' ) ); ?></div>
                                <?php if(!empty($it->author_name)): ?><div class="grm-author">— <?php echo esc_html($it->author_name); ?></div><?php endif; ?>
                            </figcaption>
                        </figure>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php return ob_get_clean();
    }

    private function get_cached_reviews($atts){
        global $wpdb; $table=$wpdb->prefix.self::TABLE;
        $min = (isset($atts['min_rating']) && $atts['min_rating']!=='') ? floatval($atts['min_rating']) : null;
        $sql="SELECT review_id, author_name, author_photo, rating, text, time_created, photo_url FROM {$table}";
        if($min!==null) $sql.=$wpdb->prepare(" WHERE rating >= %f", $min);
        $sql.=" ORDER BY rating DESC, time_created DESC";
        return $wpdb->get_results($sql);
    }

    public function ajax_sync_now(){
        if(!current_user_can('manage_options')) wp_send_json_error(['message'=>'Forbidden'],403);
        if(!wp_verify_nonce($_POST['nonce']??'','grm_sync')) wp_send_json_error(['message'=>'Bad nonce'],400);
        $count=$this->run_sync(); wp_send_json_success(['message'=>"Synced {$count} items."]);
    }

    public function run_sync(){
        $opts = get_option(self::OPTS_KEY, []);
        $api_key=$opts['api_key']??''; $place_id=$opts['place_id']??'';
        $min_rating=isset($opts['min_rating'])?floatval($opts['min_rating']):4.5;
        $max_reviews=isset($opts['max_reviews'])?intval($opts['max_reviews']):30;
        $include_place_photos=!empty($opts['include_place_photos']);
        if(!$api_key || !$place_id){ update_option('grm_last_sync', current_time('mysql').' (missing API key / Place ID)'); return 0; }

        $details_url=add_query_arg(['place_id'=>$place_id,'fields'=>'rating,reviews,user_ratings_total,photos','key'=>$api_key],'https://maps.googleapis.com/maps/api/place/details/json');
        $response=wp_remote_get($details_url, ['timeout'=>20]);
        $count=0;
        if(is_wp_error($response)){ update_option('grm_last_sync', current_time('mysql').' (HTTP error)'); return 0; }
        $data=json_decode(wp_remote_retrieve_body($response), true);
        if(!is_array($data) || ($data['status']??'')!=='OK'){ update_option('grm_last_sync', current_time('mysql').' (API status not OK)'); return 0; }

        $reviews=$data['result']['reviews'] ?? [];
        usort($reviews,function($a,$b){ $ra=$a['rating']??0; $rb=$b['rating']??0; if($ra==$rb){ $ta=$a['time']??0; $tb=$b['time']??0; return $tb<=>$ta; } return $rb<=>$ra; });
        $reviews=array_filter($reviews,function($r) use($min_rating){ return isset($r['rating']) && floatval($r['rating']) >= $min_rating; });
        $reviews=array_slice($reviews,0,$max_reviews);

        $photos=[];
        if($include_place_photos && !empty($data['result']['photos'])){
            foreach(array_slice($data['result']['photos'],0,10) as $p){
                if(empty($p['photo_reference'])) continue;
                $photo_url=add_query_arg(['maxwidth'=>800,'photoreference'=>$p['photo_reference'],'key'=>$api_key],'https://maps.googleapis.com/maps/api/place/photo');
                $photos[]=esc_url_raw($photo_url);
            }
        }

        global $wpdb; $table=$wpdb->prefix.self::TABLE;
        $wpdb->query("CREATE TABLE IF NOT EXISTS {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            review_id VARCHAR(191) NOT NULL,
            author_name VARCHAR(191) NULL,
            author_photo TEXT NULL,
            rating FLOAT NOT NULL,
            text MEDIUMTEXT NULL,
            time_created DATETIME NULL,
            photo_url TEXT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY review_idx (review_id),
            KEY rating_idx (rating),
            KEY time_idx (time_created)
        ) ".$wpdb->get_charset_collate());
        $wpdb->query("TRUNCATE TABLE {$table}");

        foreach($reviews as $r){
            $review_id=md5( ($r['author_url']??'').'|'.($r['time']??'').'|'.($r['text']??'') );
            $wpdb->insert($table,[
                'review_id'=>$review_id,
                'author_name'=>$r['author_name']??'',
                'author_photo'=>$r['profile_photo_url']??'',
                'rating'=>floatval($r['rating']??0),
                'text'=>$r['text']??'',
                'time_created'=> isset($r['time']) ? gmdate('Y-m-d H:i:s', intval($r['time'])) : null,
                'photo_url'=>'',
                'created_at'=> current_time('mysql')
            ],['%s','%s','%s','%f','%s','%s','%s','%s']);
            $count++;
        }
        foreach($photos as $ph){
            $wpdb->insert($table,[
                'review_id'=>md5($ph),
                'author_name'=>'',
                'author_photo'=>'',
                'rating'=>5.0,
                'text'=>'',
                'time_created'=> current_time('mysql'),
                'photo_url'=>$ph,
                'created_at'=> current_time('mysql')
            ],['%s','%s','%s','%f','%s','%s','%s','%s']);
            $count++;
        }
        update_option('grm_last_sync', current_time('mysql')." (items: {$count})");
        return $count;
    }
}
GRM_Plugin::init();
