jQuery(document).ready(function($) {
  function insertParam(key, value) {
    key = encodeURI(key);
    value = encodeURI(value);
    var kvp = document.location.search.substr(1).split("&");
    var i = kvp.length;
    var x;
    while (i--) {
      x = kvp[i].split("=");

      if (x[0] == key) {
        x[1] = value;
        kvp[i] = x.join("=");
        break;
      }
    }
    if (i < 0) {
      kvp[kvp.length] = [key, value].join("=");
    }
    return kvp.join("&");
  }
  function removeParam(key) {
    var sourceURL = window.location.href;
    var rtn = sourceURL.split("?")[0],
      param,
      params_arr = [],
      queryString =
        sourceURL.indexOf("?") !== -1 ? sourceURL.split("?")[1] : "";
    if (queryString !== "") {
      params_arr = queryString.split("&");
      for (var i = params_arr.length - 1; i >= 0; i -= 1) {
        param = params_arr[i].split("=")[0];
        if (param === key) {
          params_arr.splice(i, 1);
        }
      }
      rtn = rtn + "?" + params_arr.join("&");
    }
    return rtn;
  }

  $("body").on("click", "button.notice-dismiss", function() {
    var url = new URL(window.location.href);
    url.searchParams.delete("notice");
    window.history.pushState({}, "", url);
  });
  $(document).on(
    "change",
    "#bf2-admin-filter select[name='filter_type'], #bf2-admin-filter select[name='filter_value']",
    function() {
      var url = new URL(window.location.href),
        name = $(this).attr("name"),
        value = $(this).val();
      url.searchParams.set(name, value);
      if ("filter_type" === name) {
        url.searchParams.delete("filter_value");
      }
      window.location.href = url;
    }
  );
  $("#menu-posts-badge-page img, #menu-posts-course img").each(function() {
    var $img = $(this);
    var imgID = $img.attr("id");
    var imgClass = $img.attr("class");
    var imgURL = $img.attr("src");
    $.get(
      imgURL,
      function(data) {
        // Get the SVG tag, ignore the rest
        var $svg = $(data).find("svg");
        // Add replaced image's ID to the new SVG
        if (typeof imgID !== "undefined") {
          $svg = $svg.attr("id", imgID);
        }
        // Add replaced image's classes to the new SVG
        if (typeof imgClass !== "undefined") {
          $svg = $svg.attr("class", imgClass + " replaced-svg");
        }
        // Remove any invalid XML tags as per http://validator.w3.org
        $svg = $svg.removeAttr("xmlns:a");
        // Replace image with new SVG
        $img.replaceWith($svg);
      },
      "xml"
    );
  });
});
