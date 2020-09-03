jQuery(document).ready(function($) {
  $(document).on("submit", ".badge-request-form", function(e) {
    var $form = $(this);
    e.preventDefault();
    $.post(ajaxurl, $form.serialize(), function(response) {
      var message_class = "error";
      if (response.success === true) {
        message_class = "success";
      }
      $form
        .find("input[type='submit']")
        .replaceWith(
          "<p class='" + message_class + "'>" + response.message + "</p>"
        );
    });
  });
});
