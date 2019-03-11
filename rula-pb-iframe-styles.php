<?php
/*
 * Plugin Name: Pressbooks - iframe styles
 * Plugin URI: https://github.com/ryersonlibrary/rula_pb_iframe_styles
 * Author: Ryerson University Library & Archives
 * Author URI: https://github.com/ryersonlibrary
 * Description: Hides the header and footer when Pressbooks is loaded within an iframe.
 * GitHub Plugin URI: https://github.com/ryersonlibrary/rula_pb_iframe_styles
 * Version: 0.3.7
 */

// Include our custom settings page for the plugin
require_once plugin_dir_path( __FILE__ ).'/inc/rula-pb-iframe-styles-settings.php';

/**
 * Enqueues scripts and styles necessary to use media JavaScript APIs in the admin.
 */
function rula_pb_iframe_admin_enqueue_scripts() {
  wp_enqueue_media();
  wp_register_script( 'rula-pb-iframe-media-uploader-js', plugin_dir_url( __FILE__ ).'/inc/js/media-uploader.js', array('jquery'), '1.0.0', true );
  wp_enqueue_script( 'rula-pb-iframe-media-uploader-js' );
  wp_register_style( 'rula-pb-iframe-admin-style', plugin_dir_url( __FILE__ ).'/inc/css/admin-style.css', array(), '1.0.0' );
  wp_enqueue_style( 'rula-pb-iframe-admin-style' );
}
add_action( 'admin_enqueue_scripts', 'rula_pb_iframe_admin_enqueue_scripts' );

/**
 * Returns the HTML code for the watermark.
 */
function rula_pb_iframe_watermark_html() {
  $watermark = esc_attr( get_option( 'rula_pb_iframe-watermark' ) );
  return "<img id=\"rula_pb_iframe-watermark\" src=\"{$watermark}\">";
}

/**
 * Inserts the script to hide the unwanted elements at the end of the <head> tag and unhides the
 * watermark.
 */
function rula_pb_iframe_print_script() {
  $watermark_html = rula_pb_iframe_watermark_html();

  echo <<<script
  <style>
    .in-iframe .a11y-toolbar,
    .in-iframe .header__inside,
    .in-iframe .footer--home,
    .in-iframe .footer--reading,
    .in-iframe .block-reading-meta,
    .in-iframe .part-title,
    .in-iframe .reading-header,
    .in-iframe .nav-reading {
      display: none!important;
    }

    .in-iframe.show-nav .reading-header,
    .in-iframe.show-nav .nav-reading {
      display: block!important;
    }

    @media screen and (max-width: 959px) {
      .in-iframe .header {
        padding: 0!important;
      }
      .in-iframe:not(.home) .reading-header {
        margin-top: 0!important;
      }
      .in-iframe:not(.home) .site-content {
        margin-top: 1rem!important;
        margin-bottom: 7rem!important;
      }
    }
  </style>

  <script type="text/javascript">
    function urlParam(name) {
      var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
      if (results==null) {
         return null;
      }
      return decodeURI(results[1]) || 0;
    }

    function hostnameFromUrl(url) {
      var matches = url.match(/^https?\:\/\/([^\/?#]+)(?:[\/?#]|$)/i);
      return matches[1];
    }

    jQuery( document ).ready( function() {
      if ( window.self != window.top ) {
        jQuery("body").addClass("in-iframe");

        if ( urlParam('show_nav') == 'true' ) {
          jQuery("body").addClass("show-nav");

          jQuery("body").on("click", "a[href]", function(e) {
            var url = jQuery(this).attr("href")
            if ( hostnameFromUrl(url) == hostnameFromUrl(window.location.href) ) {
              e.preventDefault();
              window.location = url + "?show_nav=true";
            }
          });
        }

        jQuery("#content").append('{$watermark_html}');
      }
    });
  </script>
script;
}
add_action( 'wp_head', 'rula_pb_iframe_print_script');
