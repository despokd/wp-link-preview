jQuery(document).ready(function ($) {
  // AJAX data
  var data = {
    action: "create_preview",
  };

  // AJAX request
  jQuery(".preview_url").each((i, element) => {
    data.url = jQuery(element).find("a")[0].innerHTML;

    jQuery.ajax({
      type: "post",
      url: ajax.ajax_url, // admin-ajax.php by wp_localize_script
      data: data,
      success: (response) => {
        if (response !== 0 && response !== -1) {
          // replace wordpress state value
          if (response.slice(-1) == "0") {
            response = response.substring(0, response.length - 1);
          }

          // place preview content
          jQuery(element).html(response);

          // trigger WordPress for new content
          jQuery(document.body).trigger("post-load");
        }
      },
    });
  });
});
