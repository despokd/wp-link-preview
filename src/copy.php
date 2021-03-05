<?php

/**
 * Creates HTML container to display OpenGraph content
 * Also saves data in database and update data if necessary
 *
 * @version 2.0
 * @author Kilian Domaratius
 *
 * @param $atts
 * @param null $content
 */
function opengraph($atts, $content = null) {

    global $wpdb;

    //require_once('./code/FaviconGrabber/get-fav.php');

    //Variablen "deklarieren" und bereinigen
    $link_img = NULL;
    $link_title = NULL;
    $link_url = NULL;
    $link_description = NULL;
    $link_type = NULL;
    $link_site_name = NULL;

    //check if website already exists in database
    $result = $wpdb->get_results("SELECT * FROM code_opengraph WHERE linkShort = '$content' ORDER BY lastUpdated");

    //check result and update status (min 30 days old)
    if ($result != NULL && intval($result->lastUpdated) >= (time() - 60 * 60 * 24 * 30)) {

        //save given website data
        $link_img = $result->imagePath;
        $link_title = $result->title;
        $link_url = $result->linkLong;
        $link_description = $result->description;
        $link_type = $result->type;
        $link_site_name = $result->siteName;
    } else {
        //get required functions
        require_once('inc/openGraph.php');

        //get new website data
        $graph = OpenGraph::fetch($content);

        /*
        //Ausgabe des Arrays
        var_dump($graph->keys());
        var_dump($graph->schema);


        //Testausgabe auf der Website
        foreach ($graph as $key => $value) {
            echo "<p>$key => $value</p>";
        }
        */
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

        //save new website date in database
        if ($result != NULL && strtotime($result->lastUpdated) <= (time() - 60 * 60 * 24 * 30)) {
            //update
            $wpdb->query("REPLACE INTO code_opengraph(opengraphID, linkShort, linkLong, siteName, title, imagePath, type, description, lastUpdated) 
                                            VALUES ('$content', '$link_url', '" . $result->opengraphID . "', '$link_site_name', '$link_title', '$link_img', '$link_type', '$link_description', '" . time() . "') 
											WHERE linkShort = '$content'");
        } else {
            //insert new
            $wpdb->query("REPLACE INTO code_opengraph(linkShort, linkLong, siteName, title, imagePath, type, description, lastUpdated) 
                                            VALUES ('$content', '$link_url', '$link_site_name', '$link_title', '$link_img', '$link_type', '$link_description', '" . time() . "') ");
        }
    }


    //fehlende bzw. leere Variablen ersetzen

    //falls link url fehlt
    if ($link_url == NULL || $link_url == "") {
        $link_url = "http://" . $content;
    } elseif (strpos($link_url, "http") === false && strpos($link_url, "//") === false) {
        //http hinzufügen falls nicht vorhanden
        $link_url = "http://$link_url";
    }


    //Bild
    if ($link_img == NULL || $link_img == "") {
        //falls kein Bild vorhanden
        //$link_img = "/wp-content/uploads/2020/07/TEQUILIAN_placeholder_2020.png";
    } elseif (strpos($link_img, "http") === false && strpos($link_img, "//") === false) {
        //falls pfad relativ
        $temp_link_url = "$link_url";
        $temp_link_img = "$link_img";

        //avoid double slash
        // add slash to server domain
        if (substr($temp_link_url, -1) !== "/") {
            $temp_link_url .= "/";
        }

        // delete slash of file path
        if (preg_replace('/' . preg_quote("/", '/') . '$/', '', $str) === "/") {
            $temp_link_img = preg_replace('/' . preg_quote("/", '/') . '$/', '', $str);
        }

        $link_img = "$temp_link_url" . "$temp_link_img";
    }

    //falls name fehlt
    if ($link_site_name == NULL || $link_site_name == "") {
        $link_site_name = $content;
    }

    //falls beschreibung fehlt
    if ($link_description == NULL || $link_description == "") {
        $link_description = "<span style='font-style: italic; color: grey;'>Keine Informationen verfügbar</span>";
    }


    //Ausgabe
    //Anfang des link div erzeugen
    echo "<div class='link_wrapper'><div class='link_container'>";

    //Anfang des umschließenden Links erzeugen
    echo "<a class='link_container-link' href='$link_url' target='_blank' rel='noopener'>";

    //Bild ausgeben
    if ($link_img == NULL || $link_img == "") {
        echo "<div class='link_image' style='background-size: cover; background-position: center; min-height: 100px;' >";
        echo "</div>";
    } else {
        echo "<div class='link_image' style='background-image: url($link_img); background-size: cover; background-position: center; min-height: 100px;' >";
        echo "</div>";
    }


    //Ende des umschließenden Links erzeugen
    echo "</a>";

    //Anfang von div für Text nach Bild
    echo "<div class='link_text'>";

    //Website Titel ausgeben
    if (!($link_title == NULL or $link_title == "")) {
        echo "<p class='link_title'><a href='$link_url' target='_blank' rel='noopener'>$link_title</a></p>";
    } elseif (!($link_site_name == NULL or $link_site_name == "")) {
        echo "<p class='link_title'><a href='$link_url' target='_blank' rel='noopener'>$link_site_name</a></p>";
    }

    //Website-Beschreibung ausgeben
    if (!($link_description == NULL or $link_description == "")) {
        echo "<p class='link_description'>$link_description</p>";
    }

    //Ende von div für Text nach Bild
    echo "</div>";

    //Ende des link div erzeugen
    echo "</div>";

    /*
	//get favicon
	if ( !($link_url == NULL OR $link_url == "") ) {
        $url = $link_url;
    } else {
		$url = 'http://' . $content;
    }

	$grap_favicon = array(
		'URL' => $url,   // URL of the Page we like to get the Favicon from
		'SAVE'=> false,   // Save Favicon copy local (true) or return only favicon url (false)
		'DIR' => './',   // Local Dir the copy of the Favicon should be saved
		'TRY' => true,   // Try to get the Favicon frome the page (true) or only use the APIs (false)
		'DEV' => null,   // Give all Debug-Messages ('debug') or only make the work (null)
	);
	*/
    /*
	//Website URL / META ausgeben
	echo "<div class='link_meta'>";
	//echo "<img class='link_favicon' src='" . grap_favicon($grap_favicon) . "'>";
	
    if ( !($link_url == NULL OR $link_url == "") ) {
        echo "<p class='link_url'><a href='$link_url' target='_blank' rel='noopener'>$content</a>";
    } else {
        echo "<p class='link_url'>$content";
    }

    if ( $link_type == NULL OR $link_type == "" OR $link_type == "website") {
        echo "</p>";

    } else {
        //wenn Link zu etwas anderes als eine Website führt, z.B. ein pdf-Dokument zeigt er es hinter der URL an
        echo " | <span class='link_type'>$link_type</span>";
        echo "</p>";

    }
	echo "</div></div>";
	*/
    //maintaince
    echo "<a href='https://$content' target='_blank'>$content</a>";
}
add_shortcode('show_link', 'opengraph');
