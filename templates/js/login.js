function validateEmail(email) { 
	var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	return re.test(email);
} 

function get_html_alert(type, message) {
	var HTML = '';
	HTML += '<div class="alert ' + type + ' fade in">';
	HTML += '	<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>';
	HTML += '	' + message;
	HTML += '</div>';
	return(HTML);
}

$("a#forgot_password_link").click(function(e) {
	e.preventDefault();
	e.stopPropagation();
	$("div#login_container").hide();
	$("div#new_account_container").hide();
	if ($("div.alert").length > 0) {
		$("div.alert").remove();
	}
	$("form#recover_account_form input").prop("disabled", false);
	$("form#recover_account_form input[name=email]").val('');
	$("div#recover_account_container").show();	
});

$("a#create_account_link").click(function(e) {
	e.preventDefault();
	e.stopPropagation();
	$("div#login_container").hide();
	$("div#recover_account_container").hide();	
	if ($("div.alert").length > 0) {
		$("div.alert").remove();
	}
	$("form#add_account_form input").prop('disabled', false);
	$("form#add_account_form input[name=email]").val('');
	$("form#add_account_form input[name=password]").val('');
	$("div#new_account_container").show();	
});

$("input.back_to_login_button").click(function(e) {
	e.preventDefault();
	e.stopPropagation();
	$("div#new_account_container").hide();
	$("div#recover_account_container").hide();	
	$("div#login_container").show();
});

$("form#login_form").submit(function(e) {
	e.preventDefault();
	e.stopPropagation();
	if ($("div.alert").length > 0) {
		$("div.alert").remove();
	}
	if (validateEmail($("form#login_form input[name=email]").val())) {
		$.post($(this).attr("action"), $(this).serialize(), function(data) {
			if (data.success) {
				window.location = window.location.href;
			}
			else {
				$("form#login_form").append(get_html_alert("alert-danger", data.error));
				$("form#login_form input:first").focus();
			}
		});
	}
	else {
		$(this).append(get_html_alert("alert-danger", "Invalid email"));
		$("form#add_account_form input:first").focus();
	}
});

$("form#add_account_form").submit(function(e) {
	e.preventDefault();
	e.stopPropagation();
	if ($("div.alert").length > 0) {
		$("div.alert").remove();
	}
	if (validateEmail($("form#add_account_form input[name=email]").val())) {
		$.post($(this).attr("action"), $(this).serialize(), function(data) {
			if (data.success) {
				$("form#add_account_form input").prop('disabled', true);
				$("form#add_account_form input.back_to_login_button").prop('disabled', false);			
				$("form#login_form input[name=email]").val($("form#add_account_form input[name=email]").val());
				$("form#login_form input[name=password]").val($("form#add_account_form input[name=password]").val());
				$("form#add_account_form").append(get_html_alert("alert-success", "Account created ok"));
			}
			else {
				$("form#add_account_form").append(get_html_alert("alert-danger", data.error));
				$("form#add_account_form input:first").focus();
			}
		});
	}
	else {
		$(this).append(get_html_alert("alert-danger", "Invalid email"));
		$("form#add_account_form input:first").focus();
	}
});


$("form#recover_account_form").submit(function(e) {
	e.preventDefault();
	e.stopPropagation();
	if ($("div.alert").length > 0) {
		$("div.alert").remove();
	}
	if (validateEmail($("form#recover_account_form input[name=email]").val())) {
		$.post($(this).attr("action"), $(this).serialize(), function(data) {
			if (data.success) {
				$("form#recover_account_form input").prop('disabled', true);
				$("form#recover_account_form input.back_to_login_button").prop('disabled', false);			
				$("form#login_form input[name=email]").val($("form#recover_account_form input[name=email]").val());
				$("form#recover_account_form").append(get_html_alert("alert-success", "Email sent"));
			}
			else {
				$("form#recover_account_form").append(get_html_alert("alert-danger", data.error));
				$("form#recover_account_form input:first").focus();
			}
		});
	}
	else {
		$(this).append(get_html_alert("alert-danger", "Invalid email"));
		$("form#recover_account_form input:first").focus();
	}
});

$("form#login_form input:first").focus();