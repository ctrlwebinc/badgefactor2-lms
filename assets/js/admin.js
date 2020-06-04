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
  tinymce.init({
    selector: "textarea.bf2_tinymce",
    menubar: false,
    toolbar:
      "undo redo styleselect bold italic alignleft aligncenter alignright bullist numlist outdent indent code",
    plugins: "code",
    extended_valid_elements:
      "iframe[src|frameborder|style|scrolling|class|width|height|name|align|allowfullscreen]"
  });
});
