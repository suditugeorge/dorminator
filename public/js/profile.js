(function() {
  $(function() {});

  $('#change-user-profile').click(function(e) {
    var email, emailRegex, formHasErrors, name, phone, phoneRegex, sex, token;
    e.preventDefault();
    $('#error-box-profile').addClass('hidden');
    email = $('#email').val();
    token = $('[name="_token"]').val();
    name = $('#name').val();
    phone = $('#phone').val();
    sex = $('input[name=gender]:checked').attr('id');
    formHasErrors = false;
    emailRegex = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
    if (!emailRegex.test(email)) {
      console.log('eorare email');
      $('#email').addClass('invalid');
      formHasErrors = true;
    }
    phoneRegex = /^[0-9]+$/;
    if (phone !== "" && !phoneRegex.test(phone)) {
      $('#phone').addClass('invalid');
      formHasErrors = true;
    }
    if (formHasErrors) {
      toastr.error("Unul sau mai multe câmpuri sunt goale sau conțin erori!");
      return;
    }
    $.post('/change-user-profile', {
      _token: token,
      email: email,
      sex: sex,
      phone: phone
    }, function(json) {
      if (!json.success) {
        toastr.error(json.message);
        return;
      } else {
        location.reload();
      }
    }).fail(function() {
      toastr.error('A intervenit o problemă care nu ține de noi');
    });
  });

}).call(this);

//# sourceMappingURL=profile.js.map
