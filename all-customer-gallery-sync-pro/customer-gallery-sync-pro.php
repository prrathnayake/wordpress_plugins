<?php
/**
 * Plugin Name: Customer Gallery Sync (Pro) — Global & Product Sliders
 * Description: Cache ACF 'customer_images' nightly + manual sync, choose featured images, side title layout, centered images, hover + background effects, autoplay, fullscreen.
 * Version: 1.2.0
 * Author: BuiltByPasan + ChatGPT
 * License: GPLv2 or later
 * Text Domain: customer-gallery-sync
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Customer_Gallery_Sync_Pro {
    const TABLE = 'customer_gallery_cache';
    const CRON_HOOK = 'cgs_daily_sync_event';
    const OPT_SELECTED = 'cgs_selected_image_ids';
    const OPT_LAST_SYNC = 'cgs_last_sync';
    private static $instance = null;

    public static function init(){ if(self::$instance===null) self::$instance=new self(); return self::$instance; }

    private function __construct(){
        add_shortcode('customer_gallery_slider', array($this,'shortcode_global'));
        add_shortcode('customer_product_slider', array($this,'shortcode_product'));
        add_action('wp_enqueue_scripts', array($this,'register_assets'));
        add_action('admin_menu', array($this,'admin_menu'));
        add_action('wp_ajax_cgs_sync_now', array($this,'ajax_sync_now'));
        add_action('wp_ajax_cgs_save_selection', array($this,'ajax_save_selection'));
        add_action(self::CRON_HOOK, array($this,'run_sync'));
        register_activation_hook(__FILE__, array(__CLASS__,'activate'));
        register_deactivation_hook(__FILE__, array(__CLASS__,'deactivate'));
    }

    public static function activate(){
        global $wpdb;
        $table=$wpdb->prefix.self::TABLE;
        $charset=$wpdb->get_charset_collate();
        $sql="CREATE TABLE IF NOT EXISTS {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            source VARCHAR(32) NOT NULL DEFAULT 'product',
            source_id BIGINT UNSIGNED NOT NULL,
            image_id BIGINT UNSIGNED NOT NULL,
            image_url TEXT NOT NULL,
            alt_text TEXT NULL,
            date_cached DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY source_idx (source, source_id),
            KEY image_idx (image_id)
        ) {$charset};";
        require_once ABSPATH.'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        if( ! wp_next_scheduled(self::CRON_HOOK) ){
            $timestamp=strtotime('tomorrow 03:15');
            wp_schedule_event($timestamp,'daily',self::CRON_HOOK);
        }
        add_option(self::OPT_LAST_SYNC,'');
        add_option(self::OPT_SELECTED, array(), '', false);
    }

    public static function deactivate(){
        $t=wp_next_scheduled(self::CRON_HOOK);
        if($t) wp_unschedule_event($t,self::CRON_HOOK);
    }

    public function register_assets(){
        wp_register_style('swiper','https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css',array(),'10.3.1');
        wp_register_script('swiper','https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js',array(),'10.3.1',true);
        wp_register_style('cgs', plugins_url('assets/css/cgs.css', __FILE__), array('swiper'), '1.2.0');
        wp_register_script('cgs', plugins_url('assets/js/cgs.js', __FILE__), array('swiper'), '1.2.0', true);
    }
    private function enqueue_assets(){ wp_enqueue_style('swiper'); wp_enqueue_script('swiper'); wp_enqueue_style('cgs'); wp_enqueue_script('cgs'); }

    public function shortcode_global($atts){
        $atts=shortcode_atts(array(
            'title'=>'Happy Customers','columns'=>'4','size'=>'large','limit'=>'0','order'=>'desc','use'=>'selected','autoplay'=>'true','delay'=>'2500','speed'=>'600','side_title'=>'true'
        ), $atts, 'customer_gallery_slider');
        $this->enqueue_assets();
        $use_selected = strtolower($atts['use'])==='selected';
        $images = $use_selected ? $this->get_selected_images() : $this->get_cached_images($atts);
        if(empty($images)) return '';
        $columns=max(1,intval($atts['columns'])); $count=count($images); $title=sanitize_text_field($atts['title']); $size=sanitize_text_field($atts['size']); $show_side = (strtolower($atts['side_title'])==='true');
        ob_start(); ?>
        <section class="cgs-wrap cgs-layout mkt-ui">
            <?php if($show_side): ?>
            <aside class="cgs-side-title">
                <div class="cgs-side-title-inner">
                    <span class="cgs-side-accent"></span>
                    <h3 class="cgs-side-text"><?php echo esc_html($title); ?></h3>
                </div>
            </aside>
            <?php endif; ?>
            <div class="cgs-slider-shell">
                <div class="cgs-bg-blur"></div>
                <div class="swiper cgs-swiper"
                    data-type="images"
                    data-columns="<?php echo esc_attr($columns); ?>"
                    data-count="<?php echo esc_attr($count); ?>"
                    data-space-between="24"
                    data-breakpoints='{"1280":{"slidesPerView":<?php echo esc_attr($columns); ?>},"1024":{"slidesPerView":3},"768":{"slidesPerView":2},"0":{"slidesPerView":1}}'
                    data-autoplay="<?php echo esc_attr($atts['autoplay']); ?>"
                    data-delay="<?php echo esc_attr($atts['delay']); ?>"
                    data-speed="<?php echo esc_attr($atts['speed']); ?>"
                >
                    <div class="swiper-wrapper">
                        <?php foreach($images as $row):
                            $img_id = intval($row['image_id']);
                            $alt = $row['alt_text'];
                            $full = wp_get_attachment_image_src($img_id,'full'); ?>
                            <div class="swiper-slide">
                                <figure class="cgs-figure is-center">
                                    <div class="cgs-img-wrap">
                                        <?php echo wp_get_attachment_image($img_id,$size,false,array('class'=>'cgs-img','loading'=>'lazy','decoding'=>'async','alt'=>esc_attr($alt),'data-full'=>$full?$full[0]:'')); ?>
                                        <span class="cgs-hover-overlay"></span>
                                    </div>
                                </figure>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button class="cgs-nav cgs-nav-prev" aria-label="<?php esc_attr_e('Previous','customer-gallery-sync'); ?>"></button>
                    <button class="cgs-nav cgs-nav-next" aria-label="<?php esc_attr_e('Next','customer-gallery-sync'); ?>"></button>
                    <div class="cgs-pagination" aria-hidden="true"></div>
                </div>
            </div>
        </section>
        <?php return ob_get_clean();
    }

    public function shortcode_product($atts){
        if(!function_exists('get_field')) return '<div class="cgs-notice">ACF required.</div>';
        $atts=shortcode_atts(array('title'=>'Happy Customers','columns'=>'3','size'=>'large','product_id'=>'','autoplay'=>'true','delay'=>'2500','speed'=>'600','side_title'=>'true'), $atts, 'customer_product_slider');
        $this->enqueue_assets();
        $pid=0;
        if($atts['product_id']) $pid=intval($atts['product_id']);
        elseif(function_exists('is_product') && is_product()){ global $product,$post; $pid=$product?$product->get_id():( $post?$post->ID:0 ); }
        else { global $post; if($post) $pid=$post->ID; }
        if(!$pid) return '';
        $images=get_field('customer_images',$pid);
        if(empty($images)) return '';
        $columns=max(1,intval($atts['columns'])); $count=is_array($images)?count($images):0; $title=sanitize_text_field($atts['title']); $size=sanitize_text_field($atts['size']); $show_side = (strtolower($atts['side_title'])==='true');
        ob_start(); ?>
        <section class="cgs-wrap cgs-layout mkt-ui">
            <?php if($show_side): ?>
            <aside class="cgs-side-title">
                <div class="cgs-side-title-inner">
                    <span class="cgs-side-accent"></span>
                    <h3 class="cgs-side-text"><?php echo esc_html($title); ?></h3>
                </div>
            </aside>
            <?php endif; ?>
            <div class="cgs-slider-shell">
                <div class="cgs-bg-blur"></div>
                <div class="swiper cgs-swiper" data-type="images" data-columns="<?php echo esc_attr($columns); ?>" data-count="<?php echo esc_attr($count); ?>" data-space-between="24" data-breakpoints='{"1280":{"slidesPerView":<?php echo esc_attr($columns); ?>},"1024":{"slidesPerView":3},"768":{"slidesPerView":2},"0":{"slidesPerView":1}}' data-autoplay="<?php echo esc_attr($atts['autoplay']); ?>" data-delay="<?php echo esc_attr($atts['delay']); ?>" data-speed="<?php echo esc_attr($atts['speed']); ?>">
                    <div class="swiper-wrapper">
                        <?php foreach($images as $img):
                            $img_id = is_array($img)&&isset($img['ID']) ? intval($img['ID']) : ( is_numeric($img)? intval($img):0 );
                            if(!$img_id) continue; $full=wp_get_attachment_image_src($img_id,'full'); ?>
                            <div class="swiper-slide">
                                <figure class="cgs-figure is-center">
                                    <div class="cgs-img-wrap">
                                        <?php echo wp_get_attachment_image($img_id,$size,false,array('class'=>'cgs-img','loading'=>'lazy','decoding'=>'async','alt'=>get_post_meta($img_id,'_wp_attachment_image_alt',true),'data-full'=>$full?$full[0]:'')); ?>
                                        <span class="cgs-hover-overlay"></span>
                                    </div>
                                </figure>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button class="cgs-nav cgs-nav-prev" aria-label="<?php esc_attr_e('Previous','customer-gallery-sync'); ?>"></button>
                    <button class="cgs-nav cgs-nav-next" aria-label="<?php esc_attr_e('Next','customer-gallery-sync'); ?>"></button>
                    <div class="cgs-pagination" aria-hidden="true"></div>
                </div>
            </div>
        </section>
        <?php return ob_get_clean();
    }

    public function admin_menu(){ add_options_page('Customer Gallery','Customer Gallery','manage_options','customer-gallery-sync',array($this,'admin_page')); }
    private function get_cached_all_rows(){ global $wpdb; $table=$wpdb->prefix.self::TABLE; return $wpdb->get_results("SELECT id,source_id,image_id,image_url,alt_text,date_cached FROM {$table} ORDER BY id DESC LIMIT 5000", ARRAY_A); }
    private function get_selected_ids(){ $ids=get_option(self::OPT_SELECTED, array()); if(!is_array($ids)) $ids=array(); return array_map('intval',$ids); }

    public function admin_page(){
        if(!current_user_can('manage_options')) return;
        $last=get_option(self::OPT_LAST_SYNC,'Never'); $sel_ids=$this->get_selected_ids(); $nonce_sync=wp_create_nonce('cgs_sync'); $nonce_save=wp_create_nonce('cgs_save'); $rows=$this->get_cached_all_rows(); ?>
        <div class="wrap">
            <h1>Customer Gallery Sync (Pro)</h1>
            <p><strong>Last sync:</strong> <?php echo esc_html($last); ?></p>
            <p><button id="cgs-sync-btn" class="button button-primary">Sync Now</button> <span id="cgs-sync-status" style="margin-left:10px;"></span></p>
            <hr>
            <h2>Featured Images (Shown when <code>use="selected"</code>)</h2>
            <p>Select images to feature on the homepage/global slider.</p>
            <input type="text" id="cgs-filter" placeholder="Filter by product or image id..." style="min-width:320px;">
            <p><button id="cgs-select-all" class="button">Select All</button> <button id="cgs-select-none" class="button">Select None</button> <button id="cgs-save-selection" class="button button-primary">Save Selection</button> <span id="cgs-save-status" style="margin-left:10px;"></span></p>
            <div class="cgs-grid">
                <?php foreach($rows as $r):
                    $checked = in_array(intval($r['image_id']), $sel_ids, true) ? 'checked':'';
                    $thumb = wp_get_attachment_image_src(intval($r['image_id']), 'thumbnail'); $src = $thumb ? $thumb[0] : esc_url($r['image_url']); ?>
                    <label class="cgs-item" data-product="<?php echo intval($r['source_id']); ?>" data-image="<?php echo intval($r['image_id']); ?>">
                        <input type="checkbox" class="cgs-check" value="<?php echo intval($r['image_id']); ?>" <?php echo $checked; ?>>
                        <img src="<?php echo esc_url($src); ?>" alt="" loading="lazy">
                        <span class="cgs-meta">PID: <?php echo intval($r['source_id']); ?> • IMG: <?php echo intval($r['image_id']); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <style>
        .cgs-grid{ display:grid; grid-template-columns:repeat(auto-fill,minmax(120px,1fr)); gap:12px; }
        .cgs-item{ position:relative; display:block; border:1px solid #e5e5e5; border-radius:10px; padding:8px; background:#fff; }
        .cgs-item img{ width:100%; height:100px; object-fit:cover; border-radius:6px; }
        .cgs-item input[type="checkbox"]{ position:absolute; top:8px; left:8px; }
        .cgs-item .cgs-meta{ position:absolute; right:8px; bottom:8px; background:rgba(0,0,0,.6); color:#fff; font-size:11px; padding:2px 6px; border-radius:4px; }
        </style>
        <script>
        (function(){
            var btnSync=document.getElementById('cgs-sync-btn');
            var statusSync=document.getElementById('cgs-sync-status');
            var btnSave=document.getElementById('cgs-save-selection');
            var statusSave=document.getElementById('cgs-save-status');
            var filter=document.getElementById('cgs-filter');
            var selectAll=document.getElementById('cgs-select-all');
            var selectNone=document.getElementById('cgs-select-none');
            var grid=document.querySelector('.cgs-grid');

            if(btnSync){
                btnSync.addEventListener('click', function(){
                    statusSync.textContent='Syncing...';
                    var d=new FormData(); d.append('action','cgs_sync_now'); d.append('nonce','<?php echo esc_js($nonce_sync); ?>');
                    fetch(ajaxurl,{method:'POST', body:d, credentials:'same-origin'}).then(r=>r.json()).then(function(j){ statusSync.textContent=(j&&j.message)?j.message:'Done'; }).catch(function(){ statusSync.textContent='Error'; });
                });
            }
            function applyFilter(){
                var q=(filter.value||'').toLowerCase();
                grid.querySelectorAll('.cgs-item').forEach(function(el){
                    var pid=el.getAttribute('data-product'); var img=el.getAttribute('data-image');
                    var txt=(pid+' '+img).toLowerCase();
                    el.style.display = txt.indexOf(q)>=0 ? '' : 'none';
                });
            }
            if(filter){ filter.addEventListener('input', applyFilter); }
            if(selectAll){ selectAll.addEventListener('click', function(){ grid.querySelectorAll('.cgs-item:not([style*="display: none"]) .cgs-check').forEach(function(cb){ cb.checked=true; }); }); }
            if(selectNone){ selectNone.addEventListener('click', function(){ grid.querySelectorAll('.cgs-item:not([style*="display: none"]) .cgs-check').forEach(function(cb){ cb.checked=false; }); }); }
            if(btnSave){
                btnSave.addEventListener('click', function(){
                    statusSave.textContent='Saving...';
                    var ids=[]; grid.querySelectorAll('.cgs-check:checked').forEach(function(cb){ ids.push(cb.value); });
                    var d=new FormData(); d.append('action','cgs_save_selection'); d.append('nonce','<?php echo esc_js($nonce_save); ?>'); d.append('ids', JSON.stringify(ids));
                    fetch(ajaxurl,{method:'POST', body:d, credentials:'same-origin'}).then(r=>r.json()).then(function(j){ statusSave.textContent=(j&&j.message)?j.message:'Saved'; }).catch(function(){ statusSave.textContent='Error'; });
                });
            }
        })();
        </script><?php
    }

    public function ajax_save_selection(){
        if(!current_user_can('manage_options')) wp_send_json_error(array('message'=>'Forbidden'),403);
        if(!wp_verify_nonce($_POST['nonce']??'','cgs_save')) wp_send_json_error(array('message'=>'Bad nonce'),400);
        $ids_json = wp_unslash($_POST['ids']??'[]'); $ids=json_decode($ids_json,true);
        if(!is_array($ids)) $ids=array(); $ids=array_map('intval',$ids);
        update_option(self::OPT_SELECTED,$ids,false);
        wp_send_json_success(array('message'=>'Selection saved.'));
    }

    public function ajax_sync_now(){
        if(!current_user_can('manage_options')) wp_send_json_error(array('message'=>'Forbidden'),403);
        if(!wp_verify_nonce($_POST['nonce']??'','cgs_sync')) wp_send_json_error(array('message'=>'Bad nonce'),400);
        $count=$this->run_sync(); wp_send_json_success(array('message'=>"Synced {$count} images."));
    }

    public function run_sync(){
        if(!function_exists('get_field')){ update_option(self::OPT_LAST_SYNC, current_time('mysql').' (ACF missing)'); return 0; }
        $product_ids=$this->get_all_product_ids(); $total=0;
        global $wpdb; $table=$wpdb->prefix.self::TABLE;
        $wpdb->query("TRUNCATE TABLE {$table}");
        foreach($product_ids as $pid){
            $images=get_field('customer_images',$pid);
            if(empty($images)||!is_array($images)) continue;
            foreach($images as $img){
                $img_id = (is_array($img)&&isset($img['ID'])) ? intval($img['ID']) : ( is_numeric($img)? intval($img):0 );
                if(!$img_id) continue;
                $src=wp_get_attachment_image_src($img_id,'full'); $alt=get_post_meta($img_id,'_wp_attachment_image_alt',true);
                $wpdb->insert($table,array( 'source'=>'product','source_id'=>$pid,'image_id'=>$img_id,'image_url'=>$src?$src[0]:'','alt_text'=>$alt,'date_cached'=>current_time('mysql') ), array('%s','%d','%d','%s','%s','%s'));
                $total++;
            }
        }
        update_option(self::OPT_LAST_SYNC, current_time('mysql')." (images: {$total})");
        return $total;
    }

    private function get_all_product_ids(){ global $wpdb; return $wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE post_type='product' AND post_status='publish'"); }
    private function get_cached_images($atts){
        global $wpdb; $table=$wpdb->prefix.self::TABLE;
        $order=strtolower($atts['order']??'desc'); $limit=intval($atts['limit']??0);
        $sql="SELECT image_id, alt_text FROM {$table} ORDER BY id " . ( $order==='asc' ? 'ASC' : ( $order==='random' ? 'RAND()' : 'DESC') );
        if($limit>0) $sql.=$wpdb->prepare(" LIMIT %d", $limit);
        $rows = $wpdb->get_results($sql, ARRAY_A);
        return $rows;
    }
    private function get_selected_images(){
        $sel=$this->get_selected_ids(); if(empty($sel)) return array();
        global $wpdb; $table=$wpdb->prefix.self::TABLE;
        $placeholders = implode(',', array_fill(0, count($sel), '%d'));
        $sql = $wpdb->prepare("SELECT image_id, alt_text FROM {$table} WHERE image_id IN ($placeholders)", $sel);
        $rows = $wpdb->get_results($sql, ARRAY_A);
        $map=[]; foreach($rows as $r){ $map[intval($r['image_id'])]=$r; }
        $ordered=[]; foreach($sel as $id){ if(isset($map[$id])) $ordered[]=$map[$id]; }
        return $ordered;
    }
}

Customer_Gallery_Sync_Pro::init();
