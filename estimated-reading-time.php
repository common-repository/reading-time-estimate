<?php
/*
  Plugin Name: Estimated Time To Read
  Plugin URI: http://plugin-boutique.com/estimated-reading-time/
  Description: Estimated time it takes to read a post based upon min and max words-per-minute values on your site and show a progressbar. Responsive. Seo Optimized
  Text Domain: hg_time_to_read
  Author: HP
  Author URI: http://plugin-boutique.com/estimated-reading-time/
  Version: 1.0
*/

add_action('admin_init', 'wpttr_register_settings');

define('WPTTR_PLUGIN_SELF_DIRNAME', basename(dirname(__FILE__)), true);
if (!defined('WPTTR_PLUGIN_BASE_DIR')) {
    define('WPTTR_PLUGIN_BASE_DIR', WP_PLUGIN_DIR . '/' . WPTTR_PLUGIN_SELF_DIRNAME, true);
}

add_action('plugins_loaded', 'wpttr_load_textdomain');

add_action('admin_menu', 'wpttr_menu');
add_action('wp_footer', 'wpttr_frontend');
//add_action('wp_enqueue_scripts', 'enqueue_frontend_dependencies');
add_action('admin_enqueue_scripts', 'enqueue_admin_dependencies');

function wpttr_load_textdomain() {
    load_plugin_textdomain('hg-ttr', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}

function enqueue_frontend_dependencies() {
    wp_enqueue_script('hg-time-to-read', plugins_url('/scripts/js/wp-time-to-read.js', __FILE__), array('jquery'));
    wp_enqueue_style('hg-time-to-read', plugins_url('/scripts/css/wp-time-to-read.css', __FILE__));
}

function enqueue_admin_dependencies() {
    $screen = get_current_screen();
    if ($screen->id == 'settings_page_hg-ttr') {
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_style('wp-color-picker');
    }
}

function wpttr_menu() {
    add_options_page('Time To Read', 'Time To Read', 'manage_options', 'hg-ttr', 'wpttr_setting_page');
}

function wpttr_register_settings() {
    register_setting('wpttr-settings-group', 'wpttr_options');
}

function wpttr_setting_page() {
    $default_options = array(
        'enable' => '',
        'progressbar_enable' => '',
        'progressbar_color' => '#ffbb33',
        'progressbar_on_homepage' => 0,
        'progressbar_on_archives' => 0,
        'progressbar_on_posts' => 1,
        'words_per_minute_min' => 100,
        'words_per_minute_max' => 120,
        'min_max_interval' => 10,
        'format' => __('%s minutes to read', 'hg-ttr'),
        'format_lt' => __('%s minute to read', 'hg-ttr'),
        'format_lt_val' => 2
    );
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'hg-ttr'));
    }
    $options = get_option('wpttr_options');



    if (empty($options)) {
        //update_option('wpttr_options', $options = $default_options);
        $options = $default_options;
    }

    $enable = (isset($options['enable']) && $options['enable'] != '') ? 'checked=checked' : '';
    $progressbar_enable = (isset($options['progressbar_enable']) && $options['progressbar_enable'] != '') ? 'checked=checked' : '';
    $progressbar_color = !empty($options['progressbar_color']) ? $options['progressbar_color'] : '#ffbb33';
    $progressbar_on_homepage = !empty($options['progressbar_on_homepage']) ? true : false;
    $progressbar_on_archives = !empty($options['progressbar_on_archives']) ? true : false;
    $progressbar_on_posts = !empty($options['progressbar_on_posts']) ? true : false;

    $words_per_minute_min = !empty($options['words_per_minute_min']) ? $options['words_per_minute_min'] : 100;
    $words_per_minute_max = !empty($options['words_per_minute_max']) ? $options['words_per_minute_max'] : 120;
    $min_max_interval = isset($options['min_max_interval']) ? $options['min_max_interval'] : 10;
    
    $auto_archives_title = !empty($options['auto_archives_title']) ? true : false;
    $auto_excerpts = !empty($options['auto_excerpts']) ? true : false;

    $format = !empty($options['format']) ? $options['format'] : __('%s minutes to read', 'hg-ttr');
    $format_lt_val = !empty($options['format_lt_val']) ? $options['format_lt_val'] : 2;
    $format_lt = !empty($options['format_lt']) ? $options['format_lt'] : __('%s minute to read', 'hg-ttr');
    ?>
    <div id="icon-options-general" class="icon32"></div><h2><?php _e('Time To Read', 'hg-ttr'); ?></h2>
    <div id="poststuff">
        <div class="postbox">
            <div class="inside less">
                <h3><?php _e('Settings', 'hg-ttr'); ?></h3>
                <form method="post" action="options.php">
                    <?php settings_fields('wpttr-settings-group'); ?>
                    <table class="form-table">

                        <tr>
                            <th><h4><label for="wpttr_enable"><?php echo __('Enable Time To Read: ', 'hg-ttr') ?></label></h4></th>
                            <td> 
                                <p class="description"><input type="checkbox" name="wpttr_options[enable]" id="wpttr_enable" <?php echo $enable; ?>> <label for="wpttr_enable"><?php _e('Display <strong>time to read</strong> values using the template tag or the shortcode.', 'hg-ttr') ?></label></p>
                            </td>
                        </tr>
                        <tr class="wpttr-text-options">
                            <th><label for="words_per_minute_min"><?php echo __('Words per minute: ', 'hg-ttr') ?></label></th>
                          <td> 
                                <input type="number" min="1" step="1" placeholder="Min" class="small-text" id="words_per_minute_min" name="wpttr_options[words_per_minute_min]" value="<?php echo $words_per_minute_min; ?>" /> &ndash; <input type="number" min="1" step="1"  placeholder="Max" class="small-text" id="words_per_minute_max" name="wpttr_options[words_per_minute_max]" value="<?php echo $words_per_minute_max; ?>" />
                                <p class="description"><?php _e('The average adult reading speed for English text in the United States is around 250 to 300 words per minute.', 'hg-ttr') ?></p>
                            </td>
                        </tr>
                        <tr class="wpttr-text-options">
                            <th><label for="min_max_interval"><?php echo __('<em>Min&ndash;max</em> interval above: ', 'hg-ttr') ?></label></th>
                            <td> 
                                <input type="number" min="-1" step="1" class="small-text" id="min_max_interval" name="wpttr_options[min_max_interval]" value="<?php echo $min_max_interval; ?>" />
                                <p class="description"><?php _e('Show interval (eg. <strong>10&ndash;12 minutes</strong>) if the average is above this number. Set to 0 to always show interval, or to -1 to never show as interval.', 'hg-ttr') ?></p>
                            </td>
                        </tr>
                        <tr class="wpttr-text-options">
                            <th><label for="wpttr_format"><?php echo __('Format: ', 'hg-ttr') ?></label></th>
                            <td> 
                                <input type="text" id="wpttr_format" name="wpttr_options[format]" value="<?php echo $format; ?>" style="width: 300px;" /> 
                                <p class="description">(<?php _e('<code>%s</code> will be replaced by the calculated minutes', 'hg-ttr'); ?>)</p>
                            </td>
                        </tr>
                        <tr class="wpttr-text-options">
                            <th><?php echo __('<em>Lower Than</em> Format', 'hg-ttr'); ?></th>
                            <td> 
                                <?php echo sprintf(__('If average is lower than %1$s use format %2$s', 'hg-ttr'), '<input type="number" min="1" step="1" class="small-text" id="format_lt_val" name="wpttr_options[format_lt_val]" value="'.$format_lt_val.'" />', '<input type="text" id="wpttr_format_lt" name="wpttr_options[format_lt]" value="'.$format_lt.'" style="width: 300px;" />'); ?>
                                <p class="description"><?php _e('Use different format if the average is below a certain value.', 'hg-ttr') ?></p>
                            </td>
                        </tr>
                        <!-- <tr class="wpttr-text-options">
                            <th><?php echo __('Show automatically: ', 'hg-ttr') ?></th>
                            <td> 
                                <input type="checkbox" name="wpttr_options[auto_archives_title]" id="auto_archives_title" <?php checked( $auto_archives_title ); ?>> <label for="auto_archives_title"><?php _e('On archives &amp; homepage, after titles', 'hg-ttr'); ?></label>
                                <br /><input type="checkbox" name="wpttr_options[auto_excerpts]" id="auto_excerpts" <?php checked( $auto_excerpts ); ?>> <label for="auto_excerpts"><?php _e('After post excerpts', 'hg-ttr'); ?></label>
                            </td>
                        </tr> -->

                        <tr style="border-top: 1px solid #ddd;">
                            <th><h4><label for="progressbar_enable"><?php echo __('Enable Scroll Progress Bar: ', 'hg-ttr') ?></label></h4></th>
                            <td> 
                                <p class="description"><input type="checkbox" name="wpttr_options[progressbar_enable]" id="progressbar_enable" <?php echo $progressbar_enable; ?>> <label for="progressbar_enable"><?php _e('Show a progress bar at the top of your site\'s pages that fills in as the users scroll down.', 'hg-ttr') ?></label></p>
                            </td>
                        </tr>
                        <tr class="wpttr-progressbar-options">
                            <th><label for="progressbar_color"><?php echo __('Design', 'hg-ttr') ?>:</label></th>
                            <td> 
                               <p><strong>Please upgrade to the $5 version to unlock this feature. You can upgrade <a href="http://plugin-boutique.com/estimated-reading-time/">here</a></strong></p>
                            </td>
                        </tr>
                        <tr class="wpttr-progressbar-options">
                            <th><label for="progressbar_on_homepage"><?php echo __('Show on homepage: ', 'hg-ttr') ?></label></th>
                            <td> 
                                <input type="checkbox" name="wpttr_options[progressbar_on_homepage]" id="progressbar_on_homepage" <?php checked( $progressbar_on_homepage ); ?>>
                            </td>
                        </tr>
                        <tr class="wpttr-progressbar-options">
                            <th><label for="progressbar_on_archives"><?php echo __('Show on archives: ', 'hg-ttr') ?></label></th>
                            <td> 
                                <input type="checkbox" name="wpttr_options[progressbar_on_archives]" id="progressbar_on_archives" <?php checked( $progressbar_on_archives ); ?>>
                            </td>
                        </tr>
                        <tr class="wpttr-progressbar-options">
                            <th><label for="progressbar_on_posts"><?php echo __('Show on single posts: ', 'hg-ttr') ?></label></th>
                            <td> 
                                <input type="checkbox" name="wpttr_options[progressbar_on_posts]" id="progressbar_on_posts" <?php checked( $progressbar_on_posts ); ?>>
                            </td>
                        </tr>
                    </table>
                    <input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Update options') ?>" />
                </form>
            </div>
        </div>
        <div class="postbox">
            <div class="inside less"><h3><?php _e('How to use it', 'hg-ttr'); ?></h3>
                <p><strong>Remember to enable "Display time to read values using the template tag or the shortcode" above, for this to work.</strong></p>
                <p><?php _e('Add the following template tag to your theme template files:', 'hg-ttr'); ?></p>
                <p><code>&lt;?php if (function_exists('wp_time_to_read')) wp_time_to_read(); ?&gt;</code></p>
                <p>Or, insert the <code>[wp_time_to_read]</code> shortcode in any individual post or page in editor.</p>
            </div>
        </div>
        <div class="postbox">
            <div class="inside less"><h3><?php _e('Boost your SEO', 'hg-ttr'); ?></h3>
            <p>We have boosted 300+ websites to top 10 in Google, <strong>100+ of them are in top 3.</strong> </p>
            <p>You can see our <a href="http://seo-servicen.dk/en/">SEO service here</a></p>
            </div>
        </div>
    </div>
    <script type='text/javascript'>
        jQuery(document).ready(function($) {
            $('#progressbar_color').wpColorPicker();

            $('#wpttr_enable').change(function(event) {
                $('.wpttr-text-options').toggle(this.checked);
            }).change();
            $('#progressbar_enable').change(function(event) {
                $('.wpttr-progressbar-options').toggle(this.checked);
            }).change();
        });
    </script>

    <?php
}

function wpttr_frontend() {
    $options = get_option('wpttr_options');
    $plugin_activated = (isset($options['enable'])) && ($options['enable'] == 'on') ? true : false;
    $progressbar_activated = (isset($options['progressbar_enable'])) && ($options['progressbar_enable'] == 'on') ? true : false;
    $progressbar_on_homepage = !empty($options['progressbar_on_homepage']) ? true : false;
    $progressbar_on_archives = !empty($options['progressbar_on_archives']) ? true : false;
    $progressbar_on_posts = !empty($options['progressbar_on_posts']) ? true : false;

    $allowed_post_types = apply_filters( 'wpttr_progressbar_post_types', array('post', 'page') );

    $style = 'progress.reading-progress::-webkit-progress-value {background-color: ' . $options['progressbar_color'] . ';}progress.reading-progress::-moz-progress-bar {background-color: ' . $options['progressbar_color'] . ';}';
    if (!empty($options) && $progressbar_activated) {
        $show_progressbar = false;
        wp_reset_postdata();
        if (is_front_page() && $progressbar_on_homepage) $show_progressbar = true;
        if (is_archive() && in_array(get_post_type(), $allowed_post_types) && $progressbar_on_archives) $show_progressbar = true;
        if (!is_front_page() && is_singular() && in_array(get_post_type(), $allowed_post_types) && $progressbar_on_posts) $show_progressbar = true;
        if (apply_filters( 'wpttr_progressbar_display', $show_progressbar ) ) {
            ?><style><?php echo $style; ?></style><progress class="reading-progress" value="0" max="0"></progress><?php
            
            // this enqueues them in the footer
            wp_enqueue_script('hg-time-to-read', plugins_url('/scripts/js/wp-time-to-read.js', __FILE__), array('jquery'));
            wp_enqueue_style('hg-time-to-read', plugins_url('/scripts/css/wp-time-to-read.css', __FILE__));
            wp_localize_script( 'hg-time-to-read', 'hg_ttr', array( 
                'progressbar_content_selector' => apply_filters( 'wpttr_progressbar_content_selector', '' )
            ) );
        }
    }
}

function wp_time_to_read( $echo = true ) {
    $hg_options = get_option('wpttr_options');
    $output = '';
    $enable = (isset($hg_options['enable'])) && ($hg_options['enable'] == 'on') ? true : false;
    
    if (empty($hg_options) || !$enable) 
        return;

    global $post;
    $format = $hg_options['format'];
    $format_lt = $hg_options['format_lt'];
    $format_lt_val = $hg_options['format_lt_val'];
    $words_per_minute_min = $hg_options['words_per_minute_min'];
    $words_per_minute_max = $hg_options['words_per_minute_max'];
    $words_per_minute_avg = round(($words_per_minute_min + $words_per_minute_max) / 2);
    $word_count = str_word_count(strip_tags(get_post_field('post_content', $post->ID))); // strip_shortcodes() ?
    $read_min = ceil($word_count / $words_per_minute_max);
    $read_max = ceil($word_count / $words_per_minute_min);
    $read_avg = ceil($word_count / $words_per_minute_avg);

    $interval_above = ($hg_options['min_max_interval'] == -1 ? 9999 : $hg_options['min_max_interval']);

    if ($read_avg < $format_lt_val) $format = $format_lt;

    if ($read_avg > $interval_above && $read_min != $read_max) {
        $output = sprintf($format, $read_min . '&ndash;' . $read_max);
    } else {
        $output = sprintf($format, $read_avg);
    }
    
    $output = apply_filters( 'wpttr_output', $output, $read_min, $read_max, $read_avg );

    if (!$echo) 
        return $output;

    echo $output;
}

function wp_time_to_read_shortcode() {
    return wp_time_to_read( false );
}
add_shortcode('wp_time_to_read', 'wp_time_to_read_shortcode');
add_shortcode('time_to_read', 'wp_time_to_read_shortcode');

/*
$plugin_options = get_option('wpttr_options');
if (!empty($plugin_options) 
    && isset($plugin_options['enable']) 
    && $plugin_options['enable'] == 'on') {

    if (!empty($plugin_options['auto_archives_title'])) {
        add_filter( 'the_title', 'wpttr_after_title' );
    }
    if (!empty($plugin_options['auto_excerpts'])) {
        add_filter( 'get_the_excerpt', 'wpttr_after_excerpt' );
    }
}
    
function wpttr_after_title( $title ) {
    $plugin_options = get_option('wpttr_options');
    if (!is_archive() && !is_front_page()) 
        return $title;
    if (!in_the_loop())
        return $title;

    $allowed_post_types = apply_filters( 'wpttr_auto_post_types', array('post', 'page') );
    if (!in_array(get_post_type(), $allowed_post_types))
        return $title;

    return $title.'<span class="wpttr-auto wpttr-auto-after-title">'.wp_time_to_read( false ).'</span>';
}

function wpttr_after_excerpt( $excerpt ) {
    $plugin_options = get_option('wpttr_options');

    $allowed_post_types = apply_filters( 'wpttr_auto_post_types', array('post', 'page') );
    if (!in_array(get_post_type(), $allowed_post_types))
        return $excerpt;

    return $excerpt.'<span class="wpttr-auto wpttr-auto-after-excerpt">'.wp_time_to_read( false ).'</span>';
}
*/