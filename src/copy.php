<?php

/**
 * Creates HTML container to display OpenGraph content
 *
 * @version 2.0
 * @author Kilian Domaratius
 *
 * @param $atts
 * @param null $content
 */
function opengraph($atts, $content = null) {

    global $wpdb;
    define('IS_DEBUG', false);

    // declare" and clean up variables
    $link_img = NULL;
    $link_title = NULL;
    $link_url = NULL;
    $link_description = NULL;
    $link_type = NULL;
    $link_site_name = NULL;

    // get required functions
    require_once('openGraph.php');

    // get new website data
    $graph = OpenGraph::fetch($content);

    if (IS_DEBUG) {
        // output of the array
        var_dump($graph->keys());
        var_dump($graph->schema);

        // test output on the website
        foreach ($graph as $key => $value) {
            echo "<p>$key => $value</p>";
        }
    }

    //save specific data
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
    echo "<div class='link_wrapper'>
        <div class='link_container'>";

    // create beginning of enclosing link
    echo "<a class='link_container-link' href='$link_url' target='_blank' rel='noopener'>";

    // output image
    if ($link_img == NULL || $link_img == "") {
        echo "<div class='link_image' style='background-size: cover; background-position: center; min-height: 100px;' >";
        echo "</div>";
    } else {
        echo "<div class='link_image' style='background-image: url($link_img); background-size: cover; background-position: center; min-height: 100px;' >";
        echo "</div>";
    }


    // create end of enclosing link
    echo "</a>";

    // start of div for text after image
    echo "<div class='link_text'>";

    // output website title
    if (!($link_title == NULL || $link_title == "")) {
        echo "<p class='link_title'><a href='$link_url' target='_blank' rel='noopener'>$link_title</a></p>";
    } elseif (!($link_site_name == NULL or $link_site_name == "")) {
        echo "<p class='link_title'><a href='$link_url' target='_blank' rel='noopener'>$link_site_name</a></p>";
    }

    // output website description
    if (!($link_description == NULL || $link_description == "")) {
        echo "<p class='link_description'>$link_description</p>";
    }

    // end of div for text after image
    echo "</div>";

    // create end of link div
    echo "</div>";
}

add_shortcode('show_link', 'opengraph');
