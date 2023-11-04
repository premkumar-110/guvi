$(document).ready(function () {
  const passwordInput = $("#regPass");
  const visibilityToggle = $("#visibility-toggle");
  const visibility_on = $("#visibility-on");
  const visibility_off = $("#visibility-off");

  visibilityToggle.click(function () {
    if (passwordInput.attr("type") === "password") {
      passwordInput.attr("type", "text");
      visibility_on.show();
      visibility_off.hide();
    } else {
      passwordInput.attr("type", "password");
      visibility_on.hide();
      visibility_off.show();
    }
  });

  const loginPasswordInput = $("#loginPass");
  const loginvisibilityToggle = $("#login-visibility-toggle");
  const loginvisibility_on = $("#login-visibility-on");
  const loginvisibility_off = $("#login-visibility-off");

  loginvisibilityToggle.click(function () {
    if (loginPasswordInput.attr("type") === "password") {
      loginPasswordInput.attr("type", "text");
      loginvisibility_on.show();
      loginvisibility_off.hide();
    } else {
      loginPasswordInput.attr("type", "password");
      loginvisibility_on.hide();
      loginvisibility_off.show();
    }
  });

  const profilePasswordInput = $("#profilePass");
  const profilevisibilityToggle = $("#profile-visibility-toggle");
  const profilevisibility_on = $("#profile-visibility-on");
  const profilevisibility_off = $("#profile-visibility-off");

  profilevisibilityToggle.click(function () {
    if (profilePasswordInput.attr("type") === "password") {
      profilePasswordInput.attr("type", "text");
      profilevisibility_on.show();
      profilevisibility_off.hide();
    } else {
      profilePasswordInput.attr("type", "password");
      profilevisibility_on.hide();
      profilevisibility_off.show();
    }
  });

  const newProfilePasswordInput = $("#newPass");
  const newProfilevisibilityToggle = $("#profile-new-visibility-toggle");
  const newProfilevisibility_on = $("#profile-new-visibility-on");
  const newProfilevisibility_off = $("#profile-new-visibility-off");

  newProfilevisibilityToggle.click(function () {
    if (newProfilePasswordInput.attr("type") === "password") {
      newProfilePasswordInput.attr("type", "text");
      newProfilevisibility_on.show();
      newProfilevisibility_off.hide();
    } else {
      newProfilePasswordInput.attr("type", "password");
      newProfilevisibility_on.hide();
      newProfilevisibility_off.show();
    }
  });

  $("#reg").submit(function (e) {
    e.preventDefault();
    var formData = new FormData(this);
    formData.append("save_reg", true);
    console.log("Called")
    $("#registertxt").text("Registering...");
    $.ajax({
      type: "POST",
      url: "./php/register.php",
      data: formData,
      processData: false,
      contentType: false,
      success: function (response) {
        console.log(response);
        var res = jQuery.parseJSON(response);
        console.log(res);
        if (res.status == 422) {
          $("#errorMessage").text(res.message);
          $("#errorMessage").removeClass("d-none");
        } else if (res.status == 200) {
          Swal.fire({
            icon: "success",
            title: "Success",
            text: "Registered Successful",
            timer: 2000, // Specify the time in milliseconds (e.g., 2000ms = 2 seconds)
            showConfirmButton: false, // Hide the "OK" button
          }).then(function () {
            window.location = "login.html";
          });
        } else if (res.status == 500) {
          $("#errorMessage").addClass("d-none");
          $("#reg")[0].reset();
          alertify.set("notifier", "position", "top-right");
          alertify.success(res.message);
        }
      },
      complete: function () {
        // Hide the spinner and show the text after the request is complete
        $("#registertxt").text("REGISTER");
    },
    });
    
  });
});
