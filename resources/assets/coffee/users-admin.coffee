$ ->

$('#add-admins').click (e) ->
	e.preventDefault();
	$('#error-box-users').addClass 'hidden'
	token = $('[name="_token"]').val()
	emails = $('#emails').val().split("\n")
	verified_emails = ""
	send = true
	emailRegex = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/
	$.each emails, (k) ->
		if !emailRegex.test(emails[k])
			toastr.error("Adresa de email "+emails[k]+ " nu este validă!")
			send = false
			return
		verified_emails = verified_emails + "\n" +emails[k]
	if send
		$.post '/add-admins', {_token: token, emails: verified_emails} , (json) ->
			if !json.success
				toastr.error(json.message)
				return
			else
				toastr.success(json.message)
			return
	return