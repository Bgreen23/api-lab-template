$(document).ready(function() {
  $("#jediForm").submit(function(event) {
    var form = $(this);
    event.preventDefault();
    $.ajax({
      type: "POST",
      url: "http://localhost:1234/api/jedi/",
      data: form.serialize(), // serializes the form's elements.
      success: function(data) {
        window.location.replace("http://localhost:1234/interface/");
      }
    });
  });
  $("#jediEditForm").submit(function(event) {
    var form = $(this);
    var submit =$(this).attr("data-id");
    event.preventDefault();
    $.ajax({
      type: "PUT",
      url: "http://localhost:1234/api/jedi/" + submit,
      data: form.serialize(),
      success: function(data) {
        window.location.replace("http://localhost:1234/interface/");
      }
    });
  });
  $(".deletebtn").click(function() {
    var delButton = $(this).attr("data-id");
    if (window.confirm("Execute Order 66.")) {
      $.ajax({
        type: "DELETE",
        url: "http://localhost:1234/api/jedi/" + delButton,
        success: function(data) {
          window.location.reload();
        }
      });
    }
  });
});
