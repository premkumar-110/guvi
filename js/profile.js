$(document).ready(function () {
  $(".other-div").hide();
  $(".other-div1").hide();
  if (!localStorage.getItem("sessionId")) {
    $("#errorMessage").text("Please login to continue...");
    window.location.href = "login.html";
  }
  var accountTab = $("#account-tab");
  var passwordTab = $("#password-tab");
  var accountContent = $("#account");
  var passwordContent = $("#password");

  // Set the initial tab to Account
  accountTab.addClass("active");
  passwordTab.removeClass("active");
  accountContent.addClass("show active");
  passwordContent.removeClass("show active");

  // Click event handler for the Account tab
  accountTab.click(function () {
    accountTab.addClass("active");
    passwordTab.removeClass("active");
    accountContent.addClass("show active");
    passwordContent.removeClass("show active");
  });

  // Click event handler for the Password tab
  passwordTab.click(function () {
    passwordTab.addClass("active");
    accountTab.removeClass("active");
    passwordContent.addClass("show active");
    accountContent.removeClass("show active");
  });

  var sessionId = localStorage.getItem("sessionId");
  var formData = new FormData();
  formData.append("sessionId", sessionId); // Send the sessionId to the PHP script
  formData.append("get_profile", true);

  $.ajax({
    type: "POST",
    url: "./php/profile.php",
    data: formData,
    processData: false,
    contentType: false,
    success: function (response) {
      var res = jQuery.parseJSON(response);

      if (res.status == 200) {
        $("#email").val(res.user.email);
        $("#fname").val(res.user.fname);
        $("#lname").val(res.user.lname);
        $("#age").val(res.user.age);
        $("#dob").val(res.user.dob);
        $("#phone").val(res.user.phone);
        $("#contact").val(res.user.contact);
        $("#fullname").text(res.user.fname + " " + res.user.lname);
      } else if (res.status == 422) {
        $("#errorMessage").text(res.message);
        $("#errorMessage").removeClass("d-none");
      } else {
        $("#errorMessage").text(res.message);
        $("#errorMessage").removeClass("d-none");
      }
    },
    error: function () {
      $("#errorMessage").text("An error occurred. Please try again later.");
      $("#errorMessage").removeClass("d-none");
    },
  });

  $("#logoutButton").click(function () {
    var logout = new FormData();
    logout.append("logout", true);
    logout.append("sessionId", localStorage.getItem("sessionId"));
    $.ajax({
      type: "post",
      url: "./php/profile.php",
      data: logout,
      cache: false,
      contentType: false,
      processData: false,
      success: function (response) {
        console.log(response);
        var res = jQuery.parseJSON(response);
        if (res.status == 200) {
          setTimeout(() => {
            localStorage.removeItem("sessionId"); // Remove the "sessionId" from localStorage
            window.location.href = "login.html"; // Redirect to "index.html"
            console.log(res);
          }, 1000);
        } else if (res.status == 422) {
          $("#errorMessage").text(res.message);
          $("#errorMessage").removeClass("d-none");
        } else {
          $("#errorMessage").text(res.message);
          $("#errorMessage").removeClass("d-none");
        }
      },
      error: function () {
        $("#errorMessage").text("An error occurred. Please try again later.");
        $("#errorMessage").removeClass("d-none");
      },
    });
  });

  $("#editForm").submit(function (e) {
    e.preventDefault();
    var updateData = new FormData();
    updateData.append("updateData", true);
    updateData.append("email", $("#email").val());
    updateData.append("fname", $("#fname").val());
    updateData.append("lname", $("#lname").val());
    updateData.append("contact", $("#contact").val());
    updateData.append("age", $("#age").val());
    updateData.append("dob", $("#dob").val());
    updateData.append("phone", $("#phone").val());
    updateData.append("sessionId", localStorage.getItem("sessionId"));

    $.ajax({
      type: "post",
      url: "./php/profile.php",
      data: updateData,
      contentType: false,
      processData: false,
      success: function (response) {
        $("#fullname").text($("#fname").val() + " " + $("#lname").val());
        var res = jQuery.parseJSON(response);
        if (res.status == 200) {
            $(".toast .toast-body").text(res.message);
            $(".toast").toast("show");
         }
         else{
          $(".toast .toast-body").text("An error occured while updating...");
            $(".toast").toast("show");
         }

      },
      error: function () {
        $("#errorMessage").text("An error occurred. Please try again later.");
        $("#errorMessage").removeClass("d-none");
      },
    });
  });

  $("#editPassForm").submit(function (e) {
    e.preventDefault();
    var passUpdateData = new FormData();
    passUpdateData.append("passUpdateData", true);
    passUpdateData.append("oldPassword", $("#profilePass").val());
    passUpdateData.append("newPassword1", $("#newPass").val());
    passUpdateData.append("newPassword2", $("#confirmnewPass").val());
    passUpdateData.append("email", $("#email").val());
    passUpdateData.append("sessionId", localStorage.getItem("sessionId"));
    $.ajax({
      type: "POST",
      url: "./php/profile.php",
      data: passUpdateData,
      contentType: false,
      processData: false,
      success: function (response) {
        var res = jQuery.parseJSON(response);
        console.log(res)
        if (res.status == 200) {
          $(".toast .toast-body").text(res.message);
          $(".toast").toast("show");
       }
       else{
        $(".toast .toast-body").text(res.message);
          $(".toast").toast("show");
       }
      },
      error: function () {
        $("#errorMessage").text("An error occurred. Please try again later.");
        $("#errorMessage").removeClass("d-none");
      },
    });
  });

  function togglePasswordVisibility(
    inputElement,
    visibilityIconOn,
    visibilityIconOff
  ) {
    const passwordInput = inputElement;
    const isVisible = passwordInput.attr("type") === "text";

    if (isVisible) {
      passwordInput.attr("type", "password");
      visibilityIconOn.hide();
      visibilityIconOff.show();
    } else {
      passwordInput.attr("type", "text");
      visibilityIconOn.show();
      visibilityIconOff.hide();
    }
  }

  $("#profile-visibility-toggle").click(function () {
    togglePasswordVisibility(
      $("#profilePass"),
      $("#profile-visibility-on"),
      $("#profile-visibility-off")
    );
  });

  $("#profile-new-visibility-toggle").click(function () {
    togglePasswordVisibility(
      $("#newPass"),
      $("#profile-new-visibility-on"),
      $("#profile-new-visibility-off")
    );
  });

  $(".edit-svg").click(function () {
    $(this).hide();
    $(this).siblings(".cancel-svg").show();
    $(".other-div").show();
  });

  $(".cancel-svg").click(function () {
    $(this).hide();
    $(this).siblings(".edit-svg").show();
    $(".other-div").hide();
  });
  $(".edit-svg1").click(function () {
    $(this).hide();
    $(this).siblings(".cancel-svg1").show();
    $(".other-div1").show();
  });

  $(".cancel-svg1").click(function () {
    $(this).hide();
    $(this).siblings(".edit-svg1").show();
    $(".other-div1").hide();
  });
});
