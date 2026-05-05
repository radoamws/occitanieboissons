$(function() {
	url_ob = "http://localhost/occitanieboissons/catalogue";

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

	/* BOUTIQUE */
		function AddPanier(nom, image, prix, alcool, contenance, quantite) {
			$("#backdrop").fadeIn();
			$("#boutique-panier #nom").html(nom);
			$("#boutique-panier img#image").attr("src",image);
			$("#boutique-panier #prix").html(prix);
			$("#boutique-panier #alcool").html(alcool);
			$("#boutique-panier #contenance").html(contenance);
			$("#boutique-panier #quantite").html(quantite);
			$("#boutique-panier").fadeIn();
			UpdatePanier();
		}
		function UpdatePanier() {
			$(".panier-prix").load(url_ob+"/gallery/ajax/panier-stats.php?type=prix_ttc");
			$(".panier-prix-ht").load(url_ob+"/gallery/ajax/panier-stats.php?type=prix_ht");
			$(".panier-prix-droits").load(url_ob+"/gallery/ajax/panier-stats.php?type=prix_droits");
			$(".panier-articles").load(url_ob+"/gallery/ajax/panier-stats.php?type=articles");
		}
		UpdatePanier();
		$("#boutique-panier button.close").on("click",function() {
			$("#backdrop").fadeOut();
			$("#boutique-panier").fadeOut();
		});

		$("#panier-contenu button.remove-panier").on('click', function(e) {
			var button = $(this);
      		$(button).prop('disabled', true);
      		var id = button.data("id");
			$.ajax({
				url: url_ob+"/gallery/ajax/panier-supprimer.php",
				type: "POST",
				dataType: 'json',
				data: {"id" : id},
				error: function() {
					$(button).prop('disabled', false);
				},
				success: function(json) {
					if(json.couleur == "rouge") {
						addMessage(json.message, "rouge");
					} else if(json.couleur == "vert") {
						$("tr.table-produit-"+id).remove();
						if($(".boutique-element").length < 1) {
							$("#panier-vide-msg").show();
							$("table.boutique-table").hide();
						}

						//addMessage(json.message, "vert");
					}
					if(json.redirect) {
      					window.location.href = json.redirect;
      				}
      				UpdatePanier();
      				$(button).prop('disabled', false);
				}
			});
			e.preventDefault();
		});
		$("#panier-contenu input.quantite").on('change',function(e) {
			var input = $(this);
      		var id = input.data("id");
      		var quantite = input.val();
			$.ajax({
				url: url_ob+"/gallery/ajax/panier-quantite.php",
				type: "POST",
				dataType: 'json',
				data: {"id" : id, "quantite" : quantite},
				success: function(json) {
					if(json.couleur == "rouge") {
						addMessage(json.message, "rouge");
						input.val(json.quantite);
					} else if(json.couleur == "vert") {
						//addMessage(json.message, "vert");
					}
					if(json.redirect) {
      					window.location.href = json.redirect;
      				}
      				UpdatePanier();
				}
			});
			e.preventDefault();
		});
		$("form.add-panier").on('submit', function(e) {
			var button = $('input[type="submit"]', this);
      		$(button).prop('disabled', true);
			$.ajax({
				url: url_ob+"/gallery/ajax/panier.php",
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
						//addMessage(json.message, "vert");
						AddPanier(json.nom,json.image,json.prix,json.alcool,json.contenance,json.quantite);
					}
					if(json.redirect) {
      					window.location.href = json.redirect;
      				}
      				$(button).prop('disabled', false);
				}
			});
			e.preventDefault();
		});
		$("button.add-panier").on('click', function(e) {
			var button = $(this);
			var id = button.data("id");
			var idproduit = button.data("idproduit");
			var quantite = $("input#quantite-"+id).val();
      		$(button).prop('disabled', true);
			$.ajax({
				url: url_ob+"/gallery/ajax/panier.php",
				type: "POST",
				dataType: 'json',
				data: {
					'id': id,
					'idproduit': idproduit,
					'quantite': quantite
				},
				error: function() {
					$(button).prop('disabled', false);
				},
				success: function(json) {
					if(json.couleur == "rouge") {
						addMessage(json.message, "rouge");
					} else if(json.couleur == "vert") {
						//addMessage(json.message, "vert");
						AddPanier(json.nom,json.image,json.prix,json.alcool,json.contenance,json.quantite);
					}
					if(json.redirect) {
      					window.location.href = json.redirect;
      				}
      				$(button).prop('disabled', false);
				}
			});
			e.preventDefault();
		});
		$(".checkout-etape form#updateinformations").on('submit', function(e) {
			e.preventDefault();
			var button = $('button[type="submit"]', this);
      		$(button).prop('disabled', true);
			$.ajax({
				url: url_ob+"/gallery/ajax/commande-infos.php",
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
		});
		$(".checkout-etape form#add-adresse").on('submit', function(e) {
			var button = $('button[type="submit"]', this);
      		$(button).prop('disabled', true);
      		var action = $(this).data("action");
			$.ajax({
				url: url_ob+"/gallery/ajax/commande-adresses.php?action="+action,
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
		$(".checkout-etape .ajouter-adresse-active").click(function(e) {
			$(".checkout-etape form#add-adresse").show();
			$("#selection-address").hide();
			$(".retour-adresses").show();
			e.preventDefault();
		});
		$(".checkout-etape .next-step-adress").on('click', function(e) {
			var button = $(this);
      		button.prop('disabled', true);
      		var id = button.data("id");
			$.ajax({
				url: url_ob+"/gallery/ajax/commande-adresses.php?action=verification",
				type: "POST",
				dataType: 'json',
				data: {"id": id},
				error: function() {
					button.prop('disabled', false);
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
      				button.prop('disabled', false);
				}
			});
			e.preventDefault();
		});
		$(".custom-radio.adresses input[type=radio]").on('click', function() {
			var button = $(this);
      		var type = button.data("type");
			$.ajax({
				url: url_ob+"/gallery/ajax/commande-adresses.php?action=selected&type="+type,
				type: "POST",
				dataType: 'json',
				data: {"id" : button.val(), "commandeid": button.data("commandeid")},
				error: function() {
					button.prop('checked', false);
				},
				success: function(json) {
					if(json.couleur == "rouge") {
						addMessage(json.message, "rouge");
						$(button).prop('checked', false);
					} else if(json.couleur == "vert") {
						$(".address-item."+type+" .custom-radio input[type=radio]").prop('checked', false);
						$(".address-item."+type).removeClass("selected");
						button.closest(".address-item").addClass("selected");
						button.prop('checked', true);
					}
					if(json.redirect) {
      					window.location.href = json.redirect;
      				}
      
				}
			});
		});
		$("form#modif-livraison").on('submit', function(e) {
			var button = $('button[type="submit"]', this);
      		$(button).prop('disabled', true);
      		var action = $(this).data("action");
			$.ajax({
				url: url_ob+"/gallery/ajax/commande-adresses.php?action="+action,
				type: "POST",
				dataType: 'json',
				data: $(this).serialize(),
				error: function() {
					$(button).prop('disabled', false);
				},
				success: function(json) {
					if(json.couleur == "rouge") {
						addMessage(json.message, "rouge");
						$(".custom-radio input[type=radio]").prop('checked', false);
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
		$("button#commande-valide").on('click', function(e) {
			var button = $(this);
      		button.prop('disabled', true);
			$.ajax({
				url: url_ob+"/gallery/ajax/commande-valide.php",
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
      				button.prop('disabled', false);
				}
			});
			e.preventDefault();
		});

	/* LOGIN */
		$("form#connexion").on('submit', function(e) {
			var button = $('button[type="submit"]', this);
      		$(button).prop('disabled', true);
			$.ajax({
				url: url_ob+"/gallery/ajax/identification/connexion_.php",
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
				url: url_ob+"/gallery/ajax/identification/inscription_.php",
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
				url: url_ob+"/gallery/ajax/identification/mot-de-passe_.php?action="+$(this).data("action"),
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

	/* INFORMATIONS PERSONNELLES */
	$(".contact form.updateinformations").on('submit', function(e) {
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

	/* BRASSERIES */
		// RECHERCHE BRASSERIES
		$('form#recherche_bc input[type="text"]').autocomplete({
			source: function(request, response) {
				$.ajax({
					url: url_ob+"/gallery/ajax/recherche_bc.php",
					dataType: "json",
					data: {term:$('form#recherche_bc input[type="text"]').val()},
					success: function(data) {
						response($.map(data, function(value, key) {
							return {
								label: value.nom,
								value: value.nom
							}
						}));
					},
					error: function() {

					}
				});
			},
			select: function(event, ui) {$("form#recherche_bc").submit();},
			focus: function(event, ui) {
				$(this).val(ui.item.value);
			},
			delay: 200,
			appendTo: "#recherche-content",
			minLength : 2
		});
		$("form#recherche_bc").on('submit', function(e) {
			if(!$('form#recherche_bc input[type="text"]').val()) {
				addMessage("Veuillez renseigner un nom de brasserie !", "rouge");	
			} else {
				$.ajax({
					url: url_ob+"/gallery/ajax/recherche_bc.php",
					type: "GET",
					dataType: 'json',
					data: $(this).serialize()+"&recherche=1",
					success: function(json) {
						var nom = json.id;
						var nom = nom.replace(/ /g, "-");
						var nom = nom.replace("&", "-");
						window.location.href = url_ob+"/"+nom;
					}
				});
			}
			e.preventDefault();
		});
		// TRIER PAR PAYS
		$("select#select_paysc").on('change',function() {
			if($(this).val() == "toutes") {
				window.location.href = url_ob;
			} else {
				var pays = $(this).val();
				window.location.href = url_ob+"/pays/"+pays;
			}
		});
		// TRIER PAR CATEGORIE
		$("select#select_categorie").on('change',function() {
			if($(this).val() == "toutes") {
				window.location.href = url_ob;
			} else {
				var categorie = $(this).val();
				window.location.href = url_ob+"/categorie/"+categorie;
			}
		});
		// NOM + LIBELLE
		$("input.libelle").on('change',function() {
			var input = $(this);
      		input.prop('disabled', true);
			$.ajax({
				url: url_ob+"/gallery/ajax/libelle.php",
				type: "POST",
				dataType: 'json',
				data: {"id":input.data("id"), "type":input.data("type"), "nom":input.val()},
				success: function(json) {
					if(json.couleur == "rouge") {
						addMessage(json.message, "rouge");
						input.prop('disabled', false);
					} else if(json.couleur == "vert") {
						addMessage(json.message, "vert");
						input.prop('disabled', false);
					}
				}
			});
		});
		// TRIER PAR PRIX
		$("select#tri-prix").on('change',function() {
			var nom = $(this).data("titre")+"-"+$(this).data("id");
			var nom = nom.replace(/ /g, "-");
			var nom = nom.replace("&", "-");
			var type = $(this).val();
			if(type == "aucun") {
				window.location.href = url_ob+"/"+nom;
			} else {
				window.location.href = url_ob+"/"+nom+"/trier-prix/"+type;
			}
		});
		// TRIER PAR PRIX - CATEGORIE
		$("select#tri-prix-categorie").on('change',function() {
			var categorie = $(this).data("categorie");
			var type = $(this).val();
			if(type == "aucun") {
				window.location.href = url_ob+"/categorie/"+categorie;
			} else {
				window.location.href = url_ob+"/categorie/"+categorie+"/trier-prix/"+type;
			}
		});
	/* CONSIGNE */
	function UpdateConsigne() {
		$(".consigne-prix").load(url_ob+"/gallery/ajax/consigne-stats.php");
	}
	/* LIVRAISON */
		function UpdateLivraison() {
			$("#livraison_prix").load(url_ob+"/gallery/ajax/livraison-stats.php");
		}
		$("form#livraison").on('submit', function(e) {
			var button = $('button[type="submit"]', this);
      		button.prop('disabled', true);
			$.ajax({
				url: url_ob+"/gallery/ajax/livraison.php",
				type: "POST",
				dataType: 'json',
				data: $(this).serialize(),
				success: function(json) {
					if(json.couleur == "rouge") {
						addMessage(json.message, "rouge");
						button.prop('disabled', false);
					} else {
						$(".form_livraison").hide();
						UpdateLivraison();
					}
				}
			});
			e.preventDefault();
		});
	/* PANIER */
		$(".ajouter-panier").on('change', function() {
			var input = $(this);
			var id = $(this).data("id");
			var qte = $(this).val();
			$.ajax({
				url: url_ob+"/gallery/ajax/panier.php",
				type: "POST",
				dataType: 'json',
				data: {"id":id, "quantite":qte},
				success: function(json) {
					if(json.couleur == "rouge") {
						addMessage(json.message, "rouge");
					} else {
						input.val(json.qte);
						UpdatePanier();
						UpdateLivraison();
						UpdateConsigne();
					}
				}
			});
		});
});