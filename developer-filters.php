<?php

/**
* Filter: wpttr_progressbar_post_types
* Choose post types to show scroll progress bar on
*/

// Example 1: add bar to 'product' CPT
add_filter( 'wpttr_progressbar_post_types', 'hg_progressbar_on_products' );
function hg_progressbar_on_products( $post_types ) {
    $post_types[] = 'product';
    return $post_types;
}

// Example 2: hide bar on pages
add_filter( 'wpttr_progressbar_post_types', 'hg_progressbar_hide_on_pages' );
function hg_progressbar_hide_on_pages( $post_types ) {
    if (in_array('page', $post_types)) {
        $k = array_search('page', $post_types);
    }
    unset($post_types[$k]);
    return $post_types;
}


/**
* Filter: wpttr_progressbar_display
* Add custom condition to show/hide progress bar
*/

// Example 1: hide progress bar on specific page
add_filter( 'wpttr_progressbar_display', 'hg_progressbar_hide_on_about' );
function hg_progressbar_hide_on_about( $display ) {
    if (is_page('about-us')) 
        $display = false;
    
    return $display;
}

// Example 2: hide progress bar if post word count is below 100
add_filter( 'wpttr_progressbar_display', 'hg_progressbar_hide_if_short' );
function hg_progressbar_hide_if_short( $display ) {
    // only apply to posts & pages
    if (!is_single() && !is_page()) return $display;

    global $post;
    $word_count = str_word_count(strip_tags(get_post_field('post_content', $post->ID))); // strip_shortcodes() ?
    if ($word_count < 100) $display = false;

    return $display;
}


/**
* Filter: wpttr_progressbar_content_selector
* Calculate scroll progress based on specific DOM element instead of the whole page
*/
add_filter( 'wpttr_progressbar_content_selector', 'hg_progressbar_content' );
function hg_progressbar_content( $selector ) {
    return '#main_content';
}


/**
* Filter: wpttr_output
* Modify time to read text output
*/

// Example 1: wrap in <span>, add icon
add_filter( 'wpttr_output', 'hg_ttr_wrapper' );
function hg_ttr_wrapper( $output, $minutes_min, $minutes_max, $minutes_avg ) {
    return '<span class="wpttr-output"><i class="fa fa-clock-o"></i> '.$output.'</span>';
}