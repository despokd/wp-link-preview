<?php

/**
 * Link Preview by Tequilian
 *
 * @package           TequilianLinkPreview
 * @author            Kilian Domaratius
 * @copyright         2021 Kilian Domaratius
 * @license           MIT License
 *
 * @wordpress-plugin
 * Plugin Name:       Link Preview by Tequilian
 * Plugin URI:        https://tequilian.de/projekte/link-preview-with-open-graph/
 * Description:       Fetches a website and creates a preview with OpenGraph.
 * Version:           3.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Tequilian
 * Author URI:        https://tequilian.de/
 * Text Domain:       /projekte/link-preview-with-open-graph/
 * License:           MIT License
 * License URI:       https://tldrlegal.com/license/mit-license
 */

// check system (absolute path)
defined('ABSPATH') or die('No access');

// define plugin path
define('PLUGIN_DIR', dirname(__FILE__));

/**
 * Creates HTML container to display OpenGraph content
 *
 * @version 2.0
 * @author Kilian Domaratius
 *
 * @param $atts
 * @param null $content
 */
function create_preview($atts, $content = null) {
    // return value
    $html = '';

    // declare" and clean up variables
    $link_img = NULL;
    $link_title = NULL;
    $link_url = NULL;
    $link_description = NULL;
    $link_type = NULL;
    $link_site_name = NULL;

    // get required functions
    include_once(PLUGIN_DIR . '/includes/openGraph.php');

    // get new website data
    $graph = OpenGraph::fetch($content);

    // if no response from site
    if (!$graph) {
        $html = "<p><a href='https://$content' target='_blank'>$content</a>";
        if (WP_DEBUG) $html .= "&nbsp;<small>&times; No preview available</small>";
        $html .= '</p>';
        return $html;
    }

    // save specific data
    foreach ($graph as $key => $value) {
        switch ($key) {
            case "image":
                $link_img = $value;
                break;

            case "title":
                $link_title = $value;
                break;

            case "url":
                $link_url = $value;
                break;

            case "description":
                $link_description = $value;
                break;

            case "type":
                $link_type = $value;
                break;

            case "site_name":
                $link_site_name = $value;
                break;
        }
    }


    // replace missing or empty variables

    // if link url is missing
    if ($link_url == NULL || $link_url == "") {
        $link_url = "http://" . $content;
    } elseif (strpos($link_url, "http") === false && strpos($link_url, "//") === false) {
        // add http if not present
        $link_url = "http://$link_url";
    }


    // image
    if (strpos($link_img, "http") === false && strpos($link_img, "//") === false) {
        // if path relative
        $temp_link_url = (string)$link_url;
        $temp_link_img = (string)$link_img;

        // avoid double slash
        // add slash to server domain
        if (substr($temp_link_url, -1) !== "/") $temp_link_url .= "/";

        // delete slash of file path
        if (preg_replace('/' . preg_quote("/", '/') . '$/', '', $str) === "/") {
            $temp_link_img = preg_replace('/' . preg_quote("/", '/') . '$/', '', $str);
        }

        $link_img = (string)$temp_link_url . (string)$temp_link_img;
    }

    // if name is missing
    if ($link_site_name == NULL || $link_site_name == "") $link_site_name = $content;

    // if description is missing
    if ($link_description == NULL || $link_description == "") {
        $link_description = "<span style='font-style: italic; color: grey;'>Keine Informationen verfügbar</span>";
    }


    // output
    // create beginning of link div
    $html .= "<div class='link_wrapper'>
            <div class='link_container'>";

    // create beginning of enclosing link
    $html .= "<a class='link_container-link' href='$link_url' target='_blank' rel='noopener'>";

    // output image
    if ($link_img == NULL || $link_img == "") {
        $html .= "<div class='link_image' style='background-size: cover; background-position: center; min-height: 250px;' >";
        $html .= "</div>";
    } else {
        $html .= "<div class='link_image' style='background-image: url($link_img); background-size: cover; background-position: center; min-height: 250px;' >";
        $html .= "</div>";
    }


    // create end of enclosing link
    $html .= "</a>";

    // start of div for text after image
    $html .= "<div class='link_text'>";

    // output website title
    if (!($link_title == NULL || $link_title == "")) {
        $html .= "<p class='link_title'><a href='$link_url' target='_blank' rel='noopener'>$link_title</a></p>";
    } elseif (!($link_site_name == NULL or $link_site_name == "")) {
        $html .= "<p class='link_title'><a href='$link_url' target='_blank' rel='noopener'>$link_site_name</a></p>";
    }

    // output website description
    if (!($link_description == NULL || $link_description == "")) {
        $html .= "<p class='link_description'>$link_description</p>";
    }

    // end of div for text after image
    $html .= "</div>";

    // create end of link div
    $html .= "</div>";

    // output website URL / META
    $html .= "<div class='link_meta'>";

    if (!($link_url == NULL or $link_url == "")) {
        $html .= "<p class='link_url'><a href='$link_url' target='_blank' rel='noopener'>$content</a>";
    } else {
        $html .= "<p class='link_url'>$content";
    }

    if ($link_type == NULL or $link_type == "" or $link_type == "website") {
        $html .= "</p>";
    } else {
        // if link leads to something other than a website, e.g. a pdf document it displays it after the URL
        $html .= " | <span class='link_type'>$link_type</span>";
        $html .= "</p>";
    }
    $html .= "</div></div>";

    wp_enqueue_style('preview');
    return $html;
}
add_shortcode('show_link', 'create_preview');


function setup_css() {
    wp_register_style('preview', plugins_url('/public/css/style.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'setup_css');
