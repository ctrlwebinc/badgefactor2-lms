jQuery(document).ready(function($) {
  $(document).on("submit", ".badge-request-form", function(e) {
    e.preventDefault();
    $.post(ajaxurl, $(this).serialize(), function(response) {
      // FIXME Do something with the response.
      console.log("The server responded: ", response);
    });
  });
});
