$(function() {
	function ObDeriveBaseUrl() {
		var urlFromDom = $("body").attr("data-catalogue-url");
		if(urlFromDom) {
			return urlFromDom;
		}
		var scriptSrc = $("script[src*='/gallery/js/general.js']").last().attr('src');
		if(!scriptSrc) {
			return null;
		}
		scriptSrc = scriptSrc.split('?')[0];
		var marker = "/gallery/js/general.js";
		var idx = scriptSrc.indexOf(marker);
		if(idx < 0) {
			return null;
		}
		return scriptSrc.substring(0, idx);
	}
	url_ob = ObDeriveBaseUrl() || "";
	if(url_ob && url_ob.charAt(url_ob.length - 1) === '/') {
		url_ob = url_ob.substring(0, url_ob.length - 1);
	}
	var baseFromDom = $("body").attr("data-catalogue-base");
	var universFromDom = $("body").attr("data-catalogue-univers") || "bieres";

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
		// Mega-menu (tabs -> panels) : activation au survol
		function setActiveMenuDim(panel, dim) {
			if(!panel || !dim) return;
			var $panel = $(panel);
			$panel.attr("data-active-dim", dim);
			$(".menu-dim-btn", $panel).removeClass("is-active");
			$(".menu-dim-btn[data-dim='"+dim+"']", $panel).addClass("is-active");
			$(".menu-dim-panel", $panel).removeClass("is-active");
			$(".menu-dim-panel[data-dim='"+dim+"']", $panel).addClass("is-active");
		}
		function setActiveUnivers(univers) {
			if(!univers) return;
			universFromDom = univers;
			$(".catalogue-tab").removeClass("is-active");
			$(".catalogue-tab[data-univers='"+univers+"']").addClass("is-active");
			$(".catalogue-panel").removeClass("is-active");
			var $panel = $(".catalogue-panel[data-panel='"+univers+"']").addClass("is-active");
			setActiveMenuDim($panel, "categories");
		}
		$(document).on("mouseenter focus", ".catalogue-tab", function() {
			setActiveUnivers($(this).data("univers"));
		});
		$(document).on("click", ".catalogue-tab", function(e) {
			var u = $(this).data("univers");
			if(!u) return;
			window.location.href = url_ob + "/univers/" + u;
			e.preventDefault();
		});
		$(document).on("mouseenter focus", ".catalogue-panel.is-active .menu-dim-btn", function() {
			setActiveMenuDim($(this).closest(".catalogue-panel"), $(this).data("dim"));
		});
		$(document).on("click", ".menu-dim-btn", function(e) {
			setActiveMenuDim($(this).closest(".catalogue-panel"), $(this).data("dim"));
			e.preventDefault();
		});
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
			var base = $(this).data("base") || baseFromDom || url_ob;
			var nom = $(this).data("titre")+"-"+$(this).data("id");
			var nom = nom.replace(/ /g, "-");
			var nom = nom.replace("&", "-");
			var type = $(this).val();
			if(type == "aucun") {
				window.location.href = base+"/"+nom;
			} else {
				window.location.href = base+"/"+nom+"/trier-prix/"+type;
			}
		});
		// TRIER PAR PRIX - CATEGORIE
		$("select#tri-prix-categorie").on('change',function() {
			var base = $(this).data("base") || baseFromDom || url_ob;
			var categorie = $(this).data("categorie");
			var type = $(this).val();
			if(type == "aucun") {
				window.location.href = base+"/categorie/"+categorie;
			} else {
				window.location.href = base+"/categorie/"+categorie+"/trier-prix/"+type;
			}
		});
		// TRIER PAR PRIX - FAMILLE
		$("select#tri-prix-famille").on('change',function() {
			var base = $(this).data("base") || baseFromDom || url_ob;
			var famille = $(this).data("famille");
			var type = $(this).val();
			if(type == "aucun") {
				window.location.href = base+"/famille/"+famille;
			} else {
				window.location.href = base+"/famille/"+famille+"/trier-prix/"+type;
			}
		});
		// TRIER PAR PRIX - SOUS-FAMILLE
		$("select#tri-prix-sous-famille").on('change',function() {
			var base = $(this).data("base") || baseFromDom || url_ob;
			var sousFamille = $(this).data("sousFamille") || $(this).data("sous-famille");
			var type = $(this).val();
			if(type == "aucun") {
				window.location.href = base+"/sous-famille/"+sousFamille;
			} else {
				window.location.href = base+"/sous-famille/"+sousFamille+"/trier-prix/"+type;
			}
		});
		// TRIER PAR PRIX - DEGRE
		$("select#tri-prix-degre").on('change',function() {
			var base = $(this).data("base") || baseFromDom || url_ob;
			var degre = $(this).data("degre");
			var type = $(this).val();
			if(type == "aucun") {
				window.location.href = base+"/degre/"+degre;
			} else {
				window.location.href = base+"/degre/"+degre+"/trier-prix/"+type;
			}
		});
		// TRIER PAR PRIX - CONTENANCE
		$("select#tri-prix-contenance").on('change',function() {
			var base = $(this).data("base") || baseFromDom || url_ob;
			var contenance = $(this).data("contenance");
			var type = $(this).val();
			if(type == "aucun") {
				window.location.href = base+"/contenance/"+contenance;
			} else {
				window.location.href = base+"/contenance/"+contenance+"/trier-prix/"+type;
			}
		});
		// TRIER PAR PRIX - PACK (bouteilles/futs/bib/...)
		$("select#tri-prix-pack").on('change',function() {
			var base = $(this).data("base") || baseFromDom || url_ob;
			var pack = $(this).data("pack");
			var type = $(this).val();
			if(type == "aucun") {
				window.location.href = base+"/pack/"+pack;
			} else {
				window.location.href = base+"/pack/"+pack+"/trier-prix/"+type;
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
		// Listing produits (cartes) : +/- et ajout au panier via bouton
		$(document).on('click', '.produit-qty-btn', function(e) {
			e.preventDefault();
			e.stopPropagation();
			var btn = $(this);
			var wrap = btn.closest('.produit-qty');
			var input = $('.produit-qty-input', wrap);
			if(input.length < 1) {
				return;
			}
			var step = parseInt(btn.data('step'), 10);
			if(isNaN(step) || step === 0) step = 1;
			var min = parseInt(input.attr('min'), 10);
			if(isNaN(min)) min = 1;
			var max = parseInt(input.attr('max'), 10);
			var val = parseInt(input.val(), 10);
			if(isNaN(val)) val = min;
			val = val + step;
			if(val < min) val = min;
			if(!isNaN(max) && val > max) val = max;
			input.val(val);
			input.trigger('change');
		});
		$(document).on('click', '.produit-add-to-cart', function(e) {
			var button = $(this);
			var card = button.closest('.produit-card-inner');
			var input = $('.produit-qty-input', card);
			var id = button.data('id') || card.data('id');
			if(!id) return;
			var qtyToAdd = parseInt(input.val(), 10);
			if(isNaN(qtyToAdd) || qtyToAdd < 1) {
				addMessage("Veuillez saisir une quantité valide.", "rouge");
				e.preventDefault();
				return;
			}
			var currentInCart = parseInt(card.attr('data-cart-qte'), 10);
			if(isNaN(currentInCart) || currentInCart < 0) currentInCart = 0;
			var newQty = currentInCart + qtyToAdd;
			var max = parseInt(input.attr('max'), 10);
			if(!isNaN(max) && newQty > max) newQty = max;

			button.prop('disabled', true);
			$.ajax({
				url: url_ob + "/gallery/ajax/panier.php",
				type: "POST",
				dataType: 'json',
				data: {"id": id, "quantite": newQty},
				success: function(json) {
					if(json && json.couleur == "rouge") {
						addMessage(json.message || "Une erreur est survenue.", "rouge");
					} else {
						card.attr('data-cart-qte', json && json.qte ? json.qte : newQty);
						input.val(1);
						UpdatePanier();
						UpdateLivraison();
						UpdateConsigne();
					}
					if(json && json.redirect) {
						window.location.href = json.redirect;
					}
					button.prop('disabled', false);
				},
				error: function() {
					button.prop('disabled', false);
					addMessage("Une erreur est survenue.", "rouge");
				}
			});
			e.preventDefault();
		});

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