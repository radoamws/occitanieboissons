$(function() {
	var url_ob = "//logistique.occitanieboissons.com";

	/* MESSAGE */
	// CREATION DU MESSAGE
	var timer;
	function addMessage(message, couleur) {
		$("#message-content").fadeOut(0,function() {
			if(couleur == "vert") {
				$(this).removeClass("rouge").addClass("vert");
			} else if(couleur == "rouge") {
				$(this).removeClass("vert").addClass("rouge");
			}
			$(this).fadeIn().find("p").html(message);
			// TIMER
			clearTimeout(timer);
			timer = setTimeout(function() {$("#message-content .close").click()}, 6000);
		});
	}
	// FERMETURE DU MESSAGE
	$("#message-content .close").click(function() {
		$("#message-content").fadeOut(function() {
			$("#texte", this).html("");
			clearTimeout(timer);
		});
	});	

	/* LOGIN */
	$("form#connexion").on('submit', function(e) {
		var button = $('button[type="submit"]', this);
  		$(button).prop('disabled', true);
		$.ajax({
			url: url_ob+"/gallery/ajax/utilisateur/connexion_.php",
			type: "POST",
			dataType: 'json',
			data: $(this).serialize(),
			error: function() {
				$(button).prop('disabled', false);
			},
			success: function(json) {
				if(json.couleur == "rouge") {
					addMessage(json.message, "rouge");
				} else if(json.couleur == "vert") {
					addMessage(json.message, "vert");
				}
				if(json.redirect) {
  					window.location.href = json.redirect;
  				}
  				$(button).prop('disabled', false);
			}
		});
		e.preventDefault();
	});
	$("form#inscription").on('submit', function(e) {
		var button = $('button[type="submit"]', this);
  		$(button).prop('disabled', true);
		$.ajax({
			url: url_ob+"/gallery/ajax/utilisateur/inscription_.php",
			type: "POST",
			dataType: 'json',
			data: $(this).serialize(),
			error: function() {
				$(button).prop('disabled', false);
			},
			success: function(json) {
				if(json.couleur == "rouge") {
					addMessage(json.message, "rouge");
				} else if(json.couleur == "vert") {
					addMessage(json.message, "vert");
				}
				if(json.redirect) {
  					window.location.href = json.redirect;
  				}
  				$(button).prop('disabled', false);
			}
		});
		e.preventDefault();
	});
	$("form#motdepasse").on('submit', function(e) {
		var button = $('button[type="submit"]', this);
  		$(button).prop('disabled', true);
		$.ajax({
			url: url_ob+"/gallery/ajax/utilisateur/mot-de-passe_.php?action="+$(this).data("action"),
			type: "POST",
			dataType: 'json',
			data: $(this).serialize(),
			error: function() {
				$(button).prop('disabled', false);
			},
			success: function(json) {
				if(json.couleur == "rouge") {
					addMessage(json.message, "rouge");
				} else if(json.couleur == "vert") {
					addMessage(json.message, "vert");
				}
				if(json.redirect) {
  					window.location.href = json.redirect;
  				}
  				$(button).prop('disabled', false);
			}
		});
		e.preventDefault();
	});

	/* CONTACT */
	$("form#contact").on('submit', function(e) {
		var button = $('button[type="submit"]', this);
		var form = $(this);
  		$(button).prop('disabled', true);
		$.ajax({
			url: url_ob+"/gallery/ajax/contact_/contact_.php",
			type: "POST",
			dataType: 'json',
			data: $(this).serialize(),
			error: function() {
				$(button).prop('disabled', false);
			},
			success: function(json) {
				if(json.couleur == "rouge") {
					addMessage(json.message, "rouge");
				} else if(json.couleur == "vert") {
					addMessage(json.message, "vert");
					$('form#contact input').val("");
					$('form#contact textarea').val("");
				}
				if(json.redirect) {
  					window.location.href = json.redirect;
  				}
  				$(button).prop('disabled', false);
 
			}
		});
		e.preventDefault();
	});

	/* DOSSIER */
	$("form#dossier").on('submit', function(e) {
  		var button = $('button[type="submit"]', this);
		$.ajax({
			url: url_ob+"/gallery/ajax/dossier_/dossier_.php",
			type: "POST",
			data: new FormData(this),
			cache: false,
			contentType: false,
			processData: false,
			dataType: 'json',
			success: function(json) {
				if(json.couleur == "rouge") {
					addMessage(json.message, "rouge");
				} else if(json.couleur == "vert") {
					addMessage(json.message, "vert");
				}
				if(json.redirect) {
  					window.location.href = json.redirect;
  				}
  				$(button).prop('disabled', false);
			}
		});
		e.preventDefault();
	});

	/* INFORMATIONS PERSONNELLES */
	$("form.informations-personnelles").on('submit', function(e) {
		var button = $('button[type="submit"]', this);
  		$(button).prop('disabled', true);
  		var action = $(this).data("action");
		$.ajax({
			url: url_ob+"/gallery/ajax/utilisateur/informations-personnelles.php?action="+action,
			type: "POST",
			dataType: 'json',
			data: $(this).serialize(),
			error: function() {
				$(button).prop('disabled', false);
			},
			success: function(json) {
				if(json.couleur == "rouge") {
					addMessage(json.message, "rouge");
				} else if(json.couleur == "vert") {
					addMessage(json.message, "vert");
				}
				if(json.redirect) {
  					window.location.href = json.redirect;
  				}
  				$(button).prop('disabled', false);
			}
		});
		e.preventDefault();
	});
});
