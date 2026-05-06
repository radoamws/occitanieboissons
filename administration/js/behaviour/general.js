var App = function () {

  var config = {
    actualites:true,
    brasseries:true,
    boutique:true,
    login:true,
    accueil: true,
    catalogue: true
  }; 
  
  var voice_methods = [];
  
  /*DASHBOARD*/
  var dashboard = function(){
    var skycons = new Skycons({"color": "#FFFFFF"});
    skycons.add($("#sun-icon")[0], Skycons.PARTLY_CLOUDY_DAY);
    skycons.play();

    /*Dashboard Charts*/
    function showTooltip(x, y, contents) {
      $("<div id='tooltip'>" + contents + "</div>").css({
        position: "absolute",
        display: "none",
        top: y + 5,
        left: x + 5,
        border: "1px solid #000",
        padding: "5px",
        'color':'#fff',
        'border-radius':'2px',
        'font-size':'11px',
        "background-color": "#000",
        opacity: 0.80
      }).appendTo("body").fadeIn(200);
    }
  
    /*if($('#site_statistics').size() != 0) {
	  var previousPoint = null;
      $("#site_statistics").bind("plothover", function(event, pos, item) {
        var str = "(" + pos.x.toFixed(2) + ", " + pos.y.toFixed(2) + ")";
        if (item) {
          if (previousPoint != item.dataIndex) {
            previousPoint = item.dataIndex;
            $("#tooltip").remove();
            var x = item.datapoint[0].toFixed(2),
            y = item.datapoint[1].toFixed(2);
            showTooltip(item.pageX, item.pageY,
            item.series.label + " : "+ Math.round(y));
          }
        } else {
          $("#tooltip").remove();
          previousPoint = null;
        }
      }); 	
		
	  $.ajax({
		url: "ajax/statistiques/stats.php?u=visiteurs",
		type: "POST",
		dataType: 'json',
		success: function(json) {
		  $("h2.visit-jour").html(json.visit_jour);
		  $("h2.visit-mois").html(json.visit_mois);
		  $(".mois_visiteurs").html(json.mois_graph);
		  
		  $("h2.pages-jour").html(json.pages_jour);
		  $("h2.pages-mois").html(json.pages_mois);
		  
		  var plot_statistics = $.plot($("#site_statistics"), [
		    {
			  data: json.p_graph,
			  label: "Pages vues"
			}, {
			  data: json.v_graph,
			  label: "Visiteurs"
		    }
		  ], {
			series: {
			  bars: {
				show: true,
				barWidth: 0.5,
				lineWidth: 1,
				fill: true,
				hoverable: true,
				fillColor: {
				  colors: [{
					opacity: 0.85
				  }, {
					opacity: 0.85
				  }]
				} 
			  },
			  shadowSize: 2
			},
			legend:{
			  show: false
			},
			grid: {
			  labelMargin: 10,
			  axisMargin: 500,
			  hoverable: true,
			  clickable: true,
			  tickColor: "rgba(255,255,255,0.22)",
			  borderWidth: 0
			},
			colors: ["#e67653", "#FFFFFF", "#52e136"],
			xaxis: {
			  ticks: 31,
			  tickDecimals: 0,
			  min: 1,
			},
			yaxis: {
			  ticks: 10,
			  min: 0,
			  tickDecimals: 0
			}
		  });
		},
		error : function() {
		  $("#message", "form#visiteurs_form").fadeIn();
		  $("select", "form#visiteurs_form").addClass("erreur");
		}
	  });
	  
	  $("form#visiteurs_form").on('change', function() {
		var mois = $("select[name='mois']", this).val();
		var annee = $("select[name='annee']", this).val();
	    $.ajax({
		  url: "ajax/statistiques/stats.php?u=visiteurs&mois="+mois+"&annee="+annee,
		  type: "POST",
		  dataType: 'json',
		  success: function(json) {
		    $("h2.visit-jour").html(json.visit_jour);
		    $("h2.visit-mois").html(json.visit_mois);
		    $(".mois_visiteurs").html(json.mois_graph);
			$("h2.pages-jour").html(json.pages_jour);
		    $("h2.pages-mois").html(json.pages_mois);
			
			$("#message", "form#visiteurs_form").fadeOut(0);
			$("select", "form#visiteurs_form").removeClass("erreur");
		  
		    delete plot_statistics;
		    var plot_statistics = $.plot($("#site_statistics"), [
		      {
			    data: json.p_graph,
			    label: "Pages vues"
			  }, {
			    data: json.v_graph,
			    label: "Visiteurs"
		      }
		    ], {
			  series: {
			    bars: {
				  show: true,
				  barWidth: 0.5,
				  lineWidth: 1,
				  fill: true,
				  hoverable: true,
				  fillColor: {
				    colors: [{
					  opacity: 0.85
				    }, {
					  opacity: 0.85
				    }]
				  } 
			    },
			    shadowSize: 2
			  },
			  legend:{
			    show: false
			  },
			  grid: {
			    labelMargin: 10,
			    axisMargin: 500,
			    hoverable: true,
			    clickable: true,
			    tickColor: "rgba(255,255,255,0.22)",
			    borderWidth: 0
			  },
			  colors: ["#e67653", "#FFFFFF", "#52e136"],
			  xaxis: {
			    ticks: 31,
			    tickDecimals: 0,
			    min: 1,
			  },
			  yaxis: {
			  	min: 0,
			    ticks: 10,
			    tickDecimals: 0
			  }
			});
		  },
		  error : function() {
			$("#message", "form#visiteurs_form").fadeIn();
			$("select", "form#visiteurs_form").addClass("erreur");
		  }
	    });
	  });
	}*/

	if($('#catalogue_statistics').size() != 0) {
	  var previousPoint = null;
      $("#catalogue_statistics").bind("plothover", function(event, pos, item) {
        var str = "(" + pos.x.toFixed(2) + ", " + pos.y.toFixed(2) + ")";
        if (item) {
          if (previousPoint != item.dataIndex) {
            previousPoint = item.dataIndex;
            $("#tooltip").remove();
            var x = item.datapoint[0].toFixed(2),
            y = item.datapoint[1].toFixed(2);
            showTooltip(item.pageX, item.pageY,
            item.series.label + " : "+ Math.round(y));
          }
        } else {
          $("#tooltip").remove();
          previousPoint = null;
        }
      }); 	
		
	  $.ajax({
		url: "ajax/statistiques/stats.php?u=catalogue",
		type: "POST",
		dataType: 'json',
		success: function(json) {
		  $("h2.telechargement-jour").html(json.tel_jour);
		  $("h2.telechargement-mois").html(json.tel_mois);
		  $(".mois_telechargement").html(json.mois_tel);
		  
		  $(".catalogue-list .list-group-item.mail").remove("");
		  $.each(json.contact_mois, function(index, value) {
		  	$(".catalogue-list").append("<li class='list-group-item mail'>"+value+"</li>");
		  	$(".catalogue-list #chargement").hide();
		  });
		  if(json.contact_mois.length === 0) $(".catalogue-list #chargement").fadeIn().html("Aucun téléchargement");

		  var plot_statistics = $.plot($("#catalogue_statistics"), 
		  [{
			data: json.c_graph,
			label: "Téléchargements"
		  }], 
		  {
			series: {
			  lines: {
				show: true,
				lineWidth: 2, 
				fill: true,
				fillColor: {
				  colors: [{
					opacity: 0.2
				  }, {
					opacity: 0.01
				  }]
				} 
			  },
			  points: {
			    show: true
			  },
			  shadowSize: 2
			},
			legend:{
			  show: false
			},
			grid: {
			  labelMargin: 10,
			  axisMargin: 500,
			  hoverable: true,
			  clickable: true,
			  tickColor: "rgba(255,255,255,0.22)",
			  borderWidth: 0
			},
			colors: ["#FFFFFF", "#4A8CF7", "#52e136"],
			xaxis: {
			  ticks: 31,
			  tickDecimals: 0,
			  min : 1
			},
			yaxis: {
			  ticks: 10,
			  tickDecimals: 0,
			  min: 0
			}
		  });
		},
		error : function() {
		  $("#message", "form#catalogue_form").fadeIn();
		  $("select", "form#catalogue_form").addClass("erreur");
		}
	  });
	  
	  $("form#catalogue_form").on('change', function() {
		var mois = $("select[name='mois']", this).val();
		var annee = $("select[name='annee']", this).val();
	    $.ajax({
		  url: "ajax/statistiques/stats.php?u=catalogue&mois="+mois+"&annee="+annee,
		  type: "POST",
		  dataType: 'json',
		  success: function(json) {
		    $("h2.telechargement-jour").html(json.tel_jour);
		  	$("h2.telechargement-mois").html(json.tel_mois);
		  	$(".mois_telechargement").html(json.mois_tel);
			$("#message", "form#catalogue_form").fadeOut(0);
			$("select", "form#catalogue_form").removeClass("erreur");

			$(".catalogue-list .list-group-item.mail").remove("");
			$.each(json.contact_mois, function(index, value) {
			  $(".catalogue-list").append("<li class='list-group-item mail'>"+value+"</li>");
			  $(".catalogue-list #chargement").hide();
			});
			if(json.contact_mois.length === 0) $(".catalogue-list #chargement").fadeIn().html("Aucun téléchargement");

		    delete plot_statistics;
		    var plot_statistics = $.plot($("#catalogue_statistics"), 
	    	[{
	    		data: json.c_graph,
	    		label: "Téléchargements"
	    	}], 
	    	{
	    		series: {
	    			lines: {
	    				show: true,
	    				lineWidth: 2, 
	    				fill: true,
	    				fillColor: {
	    					colors: [{
	    						opacity: 0.2
	    					}, {
	    						opacity: 0.01
	    					}]
	    				} 
	    			},
	    			points: {
	    				show: true
	    			},
	    			shadowSize: 2
	    		},
	    		legend:{
	    			show: false
	    		},
	    		grid: {
	    			labelMargin: 10,
	    			axisMargin: 500,
	    			hoverable: true,
	    			clickable: true,
	    			tickColor: "rgba(255,255,255,0.22)",
	    			borderWidth: 0
	    		},
	    		colors: ["#FFFFFF", "#4A8CF7", "#52e136"],
	    		xaxis: {
	    			ticks: 31,
	    			tickDecimals: 0,
	    			min : 1
	    		},
	    		yaxis: {
	    			ticks: 10,
	    			tickDecimals: 0,
	    			min: 0
	    		}
	    	});
		},
		error : function() {
			$("#message", "form#catalogue_form").fadeIn();
			$("select", "form#catalogue_form").addClass("erreur");
		}
	    });
	  });
	}
	
    if($('#site_statistics2').size() != 0) {
      var previousPoint = null;
      $("#site_statistics2").bind("plothover", function(event, pos, item) {
        var str = "(" + pos.x.toFixed(2) + ", " + pos.y.toFixed(2) + ")";
        if (item) {
          if (previousPoint != item.dataIndex) {
            previousPoint = item.dataIndex;
            $("#tooltip").remove();
            var x = item.datapoint[0].toFixed(2),
            y = item.datapoint[1].toFixed(2);
            showTooltip(item.pageX, item.pageY,
            item.series.label + " : "+ Math.round(y));
          }
        } else {
          $("#tooltip").remove();
          previousPoint = null;
        }
      }); 
	  
	  $.ajax({
		url: "ajax/statistiques/stats.php?u=inscription",
		type: "POST",
		dataType: 'json',
		success: function(json) {
		  $("h2.inscr-jour").html(json.inscr_jour);
		  $("h2.inscr-mois").html(json.inscr_mois);
		  $(".mois_inscr").html(json.mois_inscr);
		  
		  $(".inscription-list .list-group-item.mail").remove("");
		  $.each(json.contact_mois, function(index, value) {
		  	$(".inscription-list").append("<li class='list-group-item mail'>"+value+"</li>");
		  	$(".inscription-list #chargement").hide();
		  });
		  if(json.contact_mois.length === 0) $(".inscription-list #chargement").fadeIn().html("Aucune inscription");

		  var plot_statistics = $.plot($("#site_statistics2"), 
		  [{
			data: json.i_graph,
			label: "Inscriptions"
		  }], 
		  {
			series: {
			  lines: {
				show: true,
				lineWidth: 2, 
				fill: true,
				fillColor: {
				  colors: [{
					opacity: 0.2
				  }, {
					opacity: 0.01
				  }]
				} 
			  },
			  points: {
			    show: true
			  },
			  shadowSize: 2
			},
			legend:{
			  show: false
			},
			grid: {
			  labelMargin: 10,
			  axisMargin: 500,
			  hoverable: true,
			  clickable: true,
			  tickColor: "rgba(255,255,255,0.22)",
			  borderWidth: 0
			},
			colors: ["#FFFFFF", "#4A8CF7", "#52e136"],
			xaxis: {
			  ticks: 31,
			  tickDecimals: 0,
			  min : 1
			},
			yaxis: {
			  ticks: 10,
			  tickDecimals: 0,
			  min: 0
			}
		  });
		},
		error : function() {
		  $("#message", "form#inscription_form").fadeIn();
		  $("select", "form#inscription_form").addClass("erreur");
		}
	  });
	  
	  $("form#inscription_form").on('change', function() {
		var mois = $("select[name='mois']", this).val();
		var annee = $("select[name='annee']", this).val();
	    $.ajax({
		  url: "ajax/statistiques/stats.php?u=inscription&mois="+mois+"&annee="+annee,
		  type: "POST",
		  dataType: 'json',
		  success: function(json) {
		    $("h2.inscr-jour").html(json.inscr_jour);
		    $("h2.inscr-mois").html(json.news_mois);
		    $(".mois_inscr").html(json.mois_inscr);
			$("#message", "form#inscription_form").fadeOut(0);
			$("select", "form#inscription_form").removeClass("erreur");

			$(".inscription-list .list-group-item.mail").remove("");
			$.each(json.contact_mois, function(index, value) {
			  $(".inscription-list").append("<li class='list-group-item mail'>"+value+"</li>");
			  $(".inscription-list #chargement").hide();
			});
			if(json.contact_mois.length === 0) $(".inscription-list #chargement").fadeIn().html("Aucun abonnement");
		  
		    var plot_statistics = $.plot($("#site_statistics2"), 
		    [{
			   data: json.i_graph,
			   label: "Inscriptions"
		    }], 
		    {
			  series: {
				lines: {
				  show: true,
				  lineWidth: 2, 
				  fill: true,
				  fillColor: {
					colors: [{
					  opacity: 0.2
					}, {
					  opacity: 0.01
					}]
				  } 
				},
				points: {
				  show: true
				},
				shadowSize: 2
			  },
			  legend:{
				show: false
			  },
			  grid: {
				labelMargin: 10,
				axisMargin: 500,
				hoverable: true,
				clickable: true,
				tickColor: "rgba(255,255,255,0.22)",
				borderWidth: 0
			  },
			  colors: ["#FFFFFF", "#4A8CF7", "#52e136"],
			  xaxis: {
				ticks: 31,
				tickDecimals: 0,
				min: 1
			  },
			  yaxis: {
				ticks: 10,
				tickDecimals: 0,
				min: 0
			  }
			});
		  },
		  error : function() {
			$("#message", "form#inscription_form").fadeIn();
			$("select", "form#inscription_form").addClass("erreur");
		  }
	    });
	  });
	}

	if($('#site_statistics4').size() != 0) {
      var previousPoint = null;
      $("#site_statistics4").bind("plothover", function(event, pos, item) {
        var str = "(" + pos.x.toFixed(2) + ", " + pos.y.toFixed(2) + ")";
        if (item) {
          if (previousPoint != item.dataIndex) {
            previousPoint = item.dataIndex;
            $("#tooltip").remove();
            var x = item.datapoint[0].toFixed(2),
            y = item.datapoint[1].toFixed(2);
            showTooltip(item.pageX, item.pageY,
            item.series.label + " : "+ Math.round(y));
          }
        } else {
          $("#tooltip").remove();
          previousPoint = null;
        }
      }); 
	  
	  $.ajax({
		url: "ajax/statistiques/stats.php?u=location",
		type: "POST",
		dataType: 'json',
		success: function(json) {
		  $("h2.location-jour").html(json.location_jour);
		  $("h2.location-mois").html(json.location_mois);
		  $(".mois_location").html(json.mois_location);
		  
		  var plot_statistics = $.plot($("#site_statistics4"), 
		  [{
			data: json.l_graph,
			label: "Demande"
		  }], 
		  {
			series: {
			  lines: {
				show: true,
				lineWidth: 2, 
				fill: true,
				fillColor: {
				  colors: [{
					opacity: 0.2
				  }, {
					opacity: 0.01
				  }]
				} 
			  },
			  points: {
			    show: true
			  },
			  shadowSize: 2
			},
			legend:{
			  show: false
			},
			grid: {
			  labelMargin: 10,
			  axisMargin: 500,
			  hoverable: true,
			  clickable: true,
			  tickColor: "rgba(255,255,255,0.22)",
			  borderWidth: 0
			},
			colors: ["#FFFFFF", "#4A8CF7", "#52e136"],
			xaxis: {
			  ticks: 31,
			  tickDecimals: 0,
			  min : 1
			},
			yaxis: {
			  ticks: 10,
			  tickDecimals: 0,
			  min: 0
			}
		  });
		},
		error : function() {
		  $("#message", "form#location_form").fadeIn();
		  $("select", "form#location_form").addClass("erreur");
		}
	  });
	  
	  $("form#location_form").on('change', function() {
		var mois = $("select[name='mois']", this).val();
		var annee = $("select[name='annee']", this).val();
	    $.ajax({
		  url: "ajax/statistiques/stats.php?u=location&mois="+mois+"&annee="+annee,
		  type: "POST",
		  dataType: 'json',
		  success: function(json) {
		    $("h2.location-jour").html(json.location_jour);
		    $("h2.location-mois").html(json.location_mois);
		    $(".mois_location").html(json.mois_location);
			$("#message", "form#location_form").fadeOut(0);
			$("select", "form#location_form").removeClass("erreur");
		  
		    var plot_statistics = $.plot($("#site_statistics4"), 
		    [{
			   data: json.l_graph,
			   label: "Demande"
		    }], 
		    {
			  series: {
				lines: {
				  show: true,
				  lineWidth: 2, 
				  fill: true,
				  fillColor: {
					colors: [{
					  opacity: 0.2
					}, {
					  opacity: 0.01
					}]
				  } 
				},
				points: {
				  show: true
				},
				shadowSize: 2
			  },
			  legend:{
				show: false
			  },
			  grid: {
				labelMargin: 10,
				axisMargin: 500,
				hoverable: true,
				clickable: true,
				tickColor: "rgba(255,255,255,0.22)",
				borderWidth: 0
			  },
			  colors: ["#FFFFFF", "#4A8CF7", "#52e136"],
			  xaxis: {
				ticks: 31,
				tickDecimals: 0,
				min: 1
			  },
			  yaxis: {
				ticks: 10,
				tickDecimals: 0,
				min: 0
			  }
			});
		  },
		  error : function() {
			$("#message", "form#location_form").fadeIn();
			$("select", "form#location_form").addClass("erreur");
		  }
	    });
	  });
	}
	/*
	if($('#support_stats1').size() != 0) {
	  $.ajax({
		url: "ajax/statistiques/stats.php?u=support",
		type: "POST",
		dataType: 'json',
		success: function(json) {
		  $("#support_non").html(json.support_non);
		  $("#support_oui").html(json.support_oui);
		  $("#support_archive").html(json.support_archive);
		}
	  });
	}
	*/
	if($('#site_statistics3').size() != 0) {
      $.ajax({
		url: "ajax/statistiques/stats.php?u=experience",
		type: "POST",
		dataType: 'json',
		success: function(json) {
		  $("#mois_experience").html(json.mois_experience);
		  var site_statistics3 = $.plot('#site_statistics3', json.e_graph, {
			series: {
			  pie: {
                show: true,
				innerRadius: 0.27,
				shadow:{
				  top: 5,
				  left: 15,
				  alpha:0.3
				},
				stroke:{
				  width:0
				},
				label: {
				  show: true,
				  formatter: function (label, series) {
					return '<div style="font-size:12px;text-align:center;padding:2px;color:#333;">' + label + '</div>';
				  }
				},
				highlight:{
				  opacity: 0.08
				}
			  }
			},
			grid: {
			  hoverable: true,
			  clickable: true
			},
			colors: ["#5793f3", "#dd4d79", "#bd3b47","#dd4444","#fd9c35","#fec42c","#d4df5a","#5578c2"],
			legend: {
			  show: false
			}
		  });
		},
		error : function() {
		  $("#message", "form#experience_form").fadeIn();
		  $("select", "form#experience_form").addClass("erreur");
		}
	  });
	  
	  $("form#experience_form").on('change', function() {
		var mois = $("select[name='mois']", this).val();
		var annee = $("select[name='annee']", this).val();
	    $.ajax({
		  url: "ajax/statistiques/stats.php?u=experience&mois="+mois+"&annee="+annee,
		  type: "POST",
		  dataType: 'json',
		  success: function(json) {
			$("#message", "form#experience_form").fadeOut(0);
			$("select", "form#experience_form").removeClass("erreur");
			$("#mois_experience").html(json.mois_experience);
		  
		    delete site_statistics3;
		    var site_statistics3 = $.plot('#site_statistics3', json.e_graph, {
			  series: {
			    pie: {
                  show: true,
				  innerRadius: 0.27,
				  shadow:{
				    top: 5,
				    left: 15,
				    alpha:0.3
				  },
				  stroke:{
				    width:0
				  },
				  label: {
				    show: true,
				    formatter: function (label, series) {
					  return '<div style="font-size:12px;text-align:center;padding:2px;color:#333;">' + label + '</div>';
				    }
				  },
				  highlight:{
				    opacity: 0.08
				  }
			    }
			  },
			  grid: {
			    hoverable: true,
			    clickable: true
			  },
			  colors: ["#5793f3", "#dd4d79", "#bd3b47","#dd4444","#fd9c35","#fec42c","#d4df5a","#5578c2"],
			  legend: {
			    show: false
			  }
		    });
		  },
		  error : function() {
			$("#message", "form#experience_form").fadeIn();
			$("select", "form#experience_form").addClass("erreur");
		  }
	    });
	  });
    }
  }
  /*END OF DASHBOARD*/
  
  /*Nestable Lists*/
  var nestable = function() {
    $('.dd').nestable();
    //Watch for list changes and show serialized output
    function update_out(selector, sel2){
      var out = $(selector).nestable('serialize');
      $(sel2).val(window.JSON.stringify(out));
    }
    
    update_out('#list1',"#out1");
	update_out('#list2',"#out2");
    
    $('#list1').on('change', function() {
      update_out('#list1',"#out1");
    });
	$('#list2').on('change', function() {
      update_out('#list2',"#out2");
    });
  };
  //End of Nestable Lists
  
  
  /*Form Wizard*/
  var wizard = function() {
    //Fuel UX
    $('.wizard-ux').wizard();

    $('.wizard-ux').on('changed',function() {
      //delete $.fn.slider;
      $('.bslider').slider();
    });
    
    $(".wizard-next").click(function(e) {
      var id = $(this).data("wizard");
	  $(id).wizard('next');
	  e.preventDefault();
    });

    $(".wizard-previous").click(function(e){
      var id = $(this).data("wizard");
      $(id).wizard('previous');
      e.preventDefault();
    });
  };
  //End of wizard
  
  /*Form Masks*/
  var masks = function() {
    $("[data-mask='date']").mask("99/99/9999");
    $("[data-mask='phone']").mask("(999) 999-9999");
    $("[data-mask='phone-ext']").mask("(999) 999-9999? x99999");
    $("[data-mask='phone-int']").mask("+33 999 999 999");
    $("[data-mask='taxid']").mask("99-9999999");
    $("[data-mask='ssn']").mask("999-99-9999");
    $("[data-mask='product-key']").mask("a*-999-a999");
    $("[data-mask='percent']").mask("99%");
    $("[data-mask='tva']").mask("99.99%");
    $("[data-mask='alcool']").mask("99,9°");
    $("[data-mask='currency']").mask("$999,999,999.99");
  };//End of masks
  
  /*Text Editors*/
  var textEditor = function() {
  	//Ckeditor
    $('textarea.ckeditor').ckeditor();
    CKEDITOR.disableAutoInline = true;
    $(".inline-editable").each(function(){
      CKEDITOR.inline($(this)[0]);
    });
  };
  //End of textEditor
  
  /*Data Tables*/
  var dataTables = function() {
  	//Basic Instance
    $("#datatable").dataTable();
    $("#datatable2").dataTable();
    $("#datatable3").dataTable();
    $("#datatable4").dataTable();

    //Search input style
    $('.dataTables_filter input').addClass('form-control').attr('placeholder','Rechercher');
    $('.dataTables_length select').addClass('form-control');    
  };
  //End of dataTables
   
  /*Widgets*/
  var widgets = function(){
    var skycons = new Skycons({"color": "#FFFFFF"});
    skycons.add($("#sun-icon")[0], Skycons.PARTLY_CLOUDY_DAY);
    skycons.play();
    
  };//End of widgets
  
 
  /*Speech Recognition*/
  var speech_commands = [];
  if(('webkitSpeechRecognition' in window)){
    var rec = new webkitSpeechRecognition();  
  }
  
  var speech = function(options){
   
    if(('webkitSpeechRecognition' in window)){
    
      if(options=="start"){
        rec.start();
      }else if(options=="stop"){
        rec.stop();
      }else{
        var config = {
          continuous: true,
          interim: true,
          lang: false,
          onEnd: false,
          onResult: false,
          onNoMatch: false,
          onSpeechStart: false,
          onSpeechEnd: false
        };
        $.extend( config, options );
        
        if(config.continuous){rec.continuous = true;}
        if(config.interim){rec.interimResults = true;}
        if(config.lang){rec.lang = config.lang;}        
        var values = false;
        var val_command = "";
        
        rec.onresult = function(event){
          for (var i = event.resultIndex; i < event.results.length; ++i) {
            if (event.results[i].isFinal) {
              var command = event.results[i][0].transcript;//Return the voice command
              command = command.toLowerCase();//Lowercase
              command = command.replace(/^\s+|\s+$/g,'');//Trim spaces
              console.log(command);
              if(config.onResult){
                config.onResult(command);
              }   
              
              $.each(speech_commands,function(i, v){
                if(values){//Second command
                  if(val_command == v.command){
                    if(v.dictation){
                      if(command == v.dictationEndCommand){
                        values = false;
                        v.dictationEnd(command);
                      }else{
                        v.listen(command);
                      }
                    }else{
                      values = false;
                      v.listen(command);
                    }
                  }
                }else{//Primary command
                  if(v.command == command){
                    v.action(command);
                    if(v.listen){
                      values = true;
                      val_command = v.command;
                    }
                  }
                }
              });
            }else{
              var res = event.results[i][0].transcript;//Return the interim results
              $.each(speech_commands,function(i, v){
                if(v.interim !== false){
                  if(values){                
                    if(val_command == v.command){
                      v.interim(res);
                    }
                  }
                }
              });
            }
          }
        };      
        
        
        if(config.onNoMatch){rec.onnomatch = function(){config.onNoMatch();};}
        if(config.onSpeechStart){rec.onspeechstart = function(){config.onSpeechStart();};}
        if(config.onSpeechEnd){rec.onspeechend = function(){config.onSpeechEnd();};}
        rec.onaudiostart = function(){ $(".speech-button i").addClass("blur"); }
        rec.onend = function(){
          $(".speech-button i").removeClass("blur");
          if(config.onEnd){config.onEnd();}
        };
      }    
      
    }else{ 
      alert("Only Chrome25+ browser support voice recognition.");
    }
   
    
  };
  
  var speechCommand = function(command, options){
    var config = {
      action: false,
      dictation: false,
      interim: false,
      dictationEnd: false,
      dictationEndCommand: 'final.',
      listen: false
    };
    
    $.extend( config, options );
    if(command){
      if(config.action){
        speech_commands.push({
          command: command, 
          dictation: config.dictation,
          dictationEnd: config.dictationEnd,
          dictationEndCommand: config.dictationEndCommand,
          interim: config.interim,
          action: config.action, 
          listen: config.listen 
        });
      }else{
        alert("Must have an action function");
      }
    }else{
      alert("Must have a command text");
    }
  };
  
      function toggleSideBar(_this){
        var b = $("#sidebar-collapse")[0];
        var w = $("#cl-wrapper");
        var s = $(".cl-sidebar");
        
        if(w.hasClass("sb-collapsed")){
        
          $(".fa",b).addClass("fa-angle-left").removeClass("fa-angle-right");
          w.removeClass("sb-collapsed");
          
        }else{
        
          $(".fa",b).removeClass("fa-angle-left").addClass("fa-angle-right");
          w.addClass("sb-collapsed");
          
        }
      }
      
      function updateHeight(){
        if(!$("#cl-wrapper").hasClass("fixed-menu")){
          var button = $("#cl-wrapper .collapse-button").outerHeight();
          var navH = $("#head-nav").height();
          var cont = $("#pcont").height();
          var sidebar = ($(window).width() > 755 && $(window).width() < 963)?0:$("#cl-wrapper .menu-space .content").height();
          var windowH = $(window).height();
          
          if(sidebar < windowH && cont < windowH){
            if(($(window).width() > 755 && $(window).width() < 963)){
              var height = windowH;
            }else{
              var height = windowH - button - navH;
            }
          }else if((sidebar < cont && sidebar > windowH) || (sidebar < windowH && sidebar < cont)){
            var height = cont + button + navH;
          }else if(sidebar > windowH && sidebar > cont){
            var height = sidebar + button;
          }  
          
          $("#cl-wrapper .menu-space").css("min-height",height);
          
        }else{
          
          $("#cl-wrapper .nscroller").nanoScroller({ preventPageScrolling: true });
          
        }
      }
        
  return {
   
    init: function (options) {
      //Extends basic config with options
      $.extend( config, options );
      
      /*VERTICAL MENU*/
      $(".cl-vnavigation li ul").each(function(){
        $(this).parent().addClass("parent");
      });
      
      $(".cl-vnavigation li ul li.active").each(function(){
        $(this).parent().show().parent().addClass("open");
      });
      
      $(".cl-vnavigation").delegate(".parent > a","click",function(e){
      
        $(".cl-vnavigation .parent.open > ul").not($(this).parent().find("ul")).slideUp(300, 'swing',function(){
           $(this).parent().removeClass("open");
        });
        
        var ul = $(this).parent().find("ul");
        ul.slideToggle(300, 'swing', function () {
          var p = $(this).parent();
          
          if(p.hasClass("open")){
            p.removeClass("open");
          }else{
            p.addClass("open");
          }

         $("#cl-wrapper .nscroller").nanoScroller({ preventPageScrolling: true });
         
        });
        
        e.preventDefault();
      });
      
      /*Small devices toggle*/
      $(".cl-toggle").click(function(e){
        var ul = $(".cl-vnavigation");
          ul.slideToggle(300, 'swing', function () {
        });
          
        e.preventDefault();
      });
      
      /*Collapse sidebar*/
      $("#sidebar-collapse").click(function(){
          toggleSideBar();
      });
      
      
      if($("#cl-wrapper").hasClass("fixed-menu")){
        var scroll =  $("#cl-wrapper .menu-space");
        scroll.addClass("nano nscroller");
 
        function update_height(){
          var button = $("#cl-wrapper .collapse-button");
          var collapseH = button.outerHeight();
          var navH = $("#head-nav").height();
          var height = $(window).height() - ((button.is(":visible"))?collapseH:0) - navH;
          scroll.css("height",height);
          $("#cl-wrapper .nscroller").nanoScroller({ preventPageScrolling: true });
        }
        
        $(window).resize(function() {
          update_height();
        });    
            
        update_height();
        $("#cl-wrapper .nscroller").nanoScroller({ preventPageScrolling: true });
        
      }
      
      /*SubMenu hover */
        var tool = $("<div id='sub-menu-nav' style='position:fixed;z-index:9999;'></div>");
        
        function showMenu(_this, e){
          if(($("#cl-wrapper").hasClass("sb-collapsed") || ($(window).width() > 755 && $(window).width() < 963)) && $("ul",_this).length > 0){   
            $(_this).removeClass("ocult");
            var menu = $("ul",_this);
            if(!$(".dropdown-header",_this).length){
              var head = '<li class="dropdown-header">' +  $(_this).children().html()  + "</li>" ;
              menu.prepend(head);
            }
            
            tool.appendTo("body");
            var top = ($(_this).offset().top + 8) - $(window).scrollTop();
            var left = $(_this).width();
            
            tool.css({
              'top': top,
              'left': left + 8
            });
            tool.html('<ul class="sub-menu">' + menu.html() + '</ul>');
            tool.show();
            
            menu.css('top', top);
          }else{
            tool.hide();
          }
        }

        $(".cl-vnavigation li").hover(function(e){
          showMenu(this, e);
        },function(e){
          
          tool.removeClass("over");
          setTimeout(function(){
            if(!tool.hasClass("over") && !$(".cl-vnavigation li:hover").length > 0){
              tool.hide();
            }
          },500);
        });
        
        tool.hover(function(e){
          $(this).addClass("over");
        },function(){
          $(this).removeClass("over");
          setTimeout(function(){
            if(!tool.hasClass("over") && !$(".cl-vnavigation li:hover").length > 0){
              tool.fadeOut("fast");
            }
          },500);
        });
        
        
        $(document).click(function(){
          tool.hide();
        });
        $(document).on('touchstart click', function(e){
          tool.fadeOut("fast");
        });
        
        tool.click(function(e){
          e.stopPropagation();
        });
     
        $(".cl-vnavigation li").click(function(e){
          if((($("#cl-wrapper").hasClass("sb-collapsed") || ($(window).width() > 755 && $(window).width() < 963)) && $("ul",this).length > 0) && !($(window).width() < 755)){
            showMenu(this, e);
            e.stopPropagation();
          }
        });
      
      /*Return to top*/
      var offset = 220;
      var duration = 500;
      var button = $('<a href="#" class="back-to-top"><i class="fa fa-angle-up"></i></a>');
      button.appendTo("body");
      
      jQuery(window).scroll(function() {
        if (jQuery(this).scrollTop() > offset) {
            jQuery('.back-to-top').fadeIn(duration);
        } else {
            jQuery('.back-to-top').fadeOut(duration);
        }
      });
    
      jQuery('.back-to-top').click(function(event) {
          event.preventDefault();
          jQuery('html, body').animate({scrollTop: 0}, duration);
          return false;
      });
      
      /*Datepicker UI*/
      $( ".ui-datepicker" ).datepicker();
      
      /*Tooltips*/
      if(config.tooltip){
        $('.ttip, [data-toggle="tooltip"]').tooltip();
      }
      
      /*Popover*/
      if(config.popover){
        $('[data-popover="popover"]').popover();
      }

      /*NanoScroller*/      
      if(config.nanoScroller){
        $(".nscroller").nanoScroller();     
      }
      
      /*Nestable Lists*/
      if(config.nestableLists){
        $('.dd').nestable();
      }
      
      /*Switch*/
      if(config.bootstrapSwitch){
        $('.switch').bootstrapSwitch();
      }
      
      /*DateTime Picker*/
      if(config.dateTime){
        $(".datetime").datetimepicker({autoclose: true});
      }
      
      /*Select2*/
      if(config.select2){
         $(".select2").select2({
          width: '100%'
         });
      }
      if(config.accueil) {
      	// AJOUT ENQUETE
      	$("form#enquete_ajouter").on('submit', function(e) {
      		$('button#valider', this).prop('disabled', true);
      		var type = $("input[name^='type']", this).val();
			$.ajax({
				url: "./ajax/statistiques/create_enquete.php",
				type: "POST",
				data: {"type": type},
				dataType: 'json',
				success: function(json) {
					if(json.couleur == "rouge") {
						$("#mod-message h4#titre").html("Erreur!");
						$("#mod-message #icon").removeClass("success").addClass("danger");
						$("#mod-message p#message").html(json.message);
						$('button#valider').prop('disabled', false);
					} else if(json.couleur == "vert") {
						$("#mod-message h4#titre").html("Succès!");
						$("#mod-message #icon").removeClass("danger").addClass("success");
						$("#mod-message p#message").html(json.message);	
					}
					if(json.redirect) {
						setTimeout(function(){location.href=json.redirect}, 3000);   
					}
					$("#mod-message").niftyModal();
				}
			});
			e.preventDefault();
		});
      	// SUPPRESSION ENQUETE TYPE
		$(".delete-enquete").click(function() {
			var id = $(this).data("id");
			$("#mod-confirm").niftyModal();
			$("button#confirm-enquete").data("id",id);
		});
		$("button#confirm-enquete").click(function() {
			var button = this;
			var id = $(button).data("id");
			$(button).prop('disabled', true);
			$.ajax({
				url: "./ajax/statistiques/delete_enquete.php",
				type: "POST",
				data: {'id': id},
				success: function() {
					$("#mod-confirm").niftyModal("hide");
					$(button).prop('disabled', false);
					$("tr.enquete#"+id).remove();
				},
				error: function() {
					$(button).prop('disabled', false);
				}				
			});
		});
      }

      /* ACTUALITES */
      if(config.actualites) {
      	// SUPPRESSION ACTUALITES
		$(".delete-article").click(function() {
			var id = $(this).data("id");
			$("#mod-confirm").niftyModal();
			$("button#confirm-actualites").data("id",id);
		});
		$("button#confirm-actualites").click(function() {
			var button = this;
			var id = $(button).data("id");
			$(button).prop('disabled', true);
			$.ajax({
				url: "./ajax/actualites/delete_actualites.php",
				type: "POST",
				data: {'id': id},
				success: function() {
					$("#mod-confirm").niftyModal("hide");
					$(button).prop('disabled', false);
					$("tr.articles#"+id).remove();
				},
				error: function() {
					$(button).prop('disabled', false);
				}				
			});
		});

		// INITIALISATION DES TAGS
      	$.ajax({
      		url: "ajax/actualites/actualites.php",
      		dataType: 'json',
      		success: function(json) {
      			$('.tags').select2({tags: json.tags,width: '100%',multiple:true}); 
      			$('.brasseries').select2({data: json.brasseries,width: '100%',multiple:true}); 
      		}
      	});

		// ACTUALITES PRODUIT
		$('.wizard-ux#actualites_redac').on('change', function(e, data) {
			if(data.direction==='next') {
				var form = $("form#actualites_produit");
				switch(data.step) {
					case 1:
						var inputs = [$("input[name^=titre]", form).val() == "", $("input[name^=brasseries]", form).val() == "", $("input[name^=tags]", form).val() == "", $('#contenu').summernote('isEmpty')];
					break;
					case 2:
						var inputs = [$("input[name^=image]", form).val() == ""];
					break;
					case 3:
						var inputs = [];
					break;
				}
				$.each(inputs, function(index, value) {
					if(value) {
						$("#mod-message h4#titre").html("Erreur!");
						$("#mod-message #icon").removeClass("success").addClass("danger");
						$("#mod-message p#message").html("Veuillez remplir les champs vides.");
						$("#mod-message").niftyModal();
						e.preventDefault();
					}
				});
				form.change();
			}
		});
  		$("form#actualites_produit").change(function() {
  			$("#prev_newsletter #titre_newsletter").html($("input[name^=titre]", this).val());
  			$("#prev_newsletter #contenu_newsletter").html($("textarea[name^=contenu]", this).val());
  			$.ajax({
  				url: "ajax/brasseries/brasseries_texte.php",
  				type: "POST",
  				dataType : 'html',
  				data: {"brasseries": $("input[name^=brasseries]", this).val()},
  				success: function(code_html) {
  					$("#prev_newsletter #brasseries_newsletter").html(code_html);
  				}
  			})
  		});
      	$("form#actualites_produit").on('submit', function(e) {
      		$('button#valider', this).prop('disabled', true);
			if($(this).attr("name") == "publier") {
				var lien = "ajax/actualites/create_actualites_produit.php";
			} else if($(this).attr("name") == "modifier") {
				var lien = "ajax/actualites/modif_actualites_produit.php";
			}
			$.ajax({
				url: lien,
				type: "POST",
				data: new FormData(this),
				cache: false,
				contentType: false,
				processData: false,
				dataType: 'json',
				success: function(json) {
					if(json.couleur == "rouge") {
						$("#mod-message h4#titre").html("Erreur!");
						$("#mod-message #icon").removeClass("success").addClass("danger");
						$("#mod-message p#message").html(json.message);
						$('button#valider').prop('disabled', false);
					} else if(json.couleur == "vert") {
						$("#mod-message h4#titre").html("Succès!");
						$("#mod-message #icon").removeClass("danger").addClass("success");
						$("#mod-message p#message").html(json.message);	
					}
					$("#mod-message").niftyModal();
					if(json.redirect) {
						setTimeout(function(){location.href=json.redirect}, 3000);   
					}
				}
			});
			e.preventDefault();
		});

      	// ACTUALITES
      	$("form#actualites").change(function() {
  			$("#prev_newsletter #titre_newsletter").html($("input[name^=titre]", this).val());
  			$("#prev_newsletter #contenu_newsletter").html($("textarea[name^=contenu]", this).val());
  		});
      	$("form#actualites").on('submit', function(e) {
      		$('button#valider', this).prop('disabled', true);
			if($(this).attr("name") == "publier") {
				var lien = "ajax/actualites/create_actualites.php";
			} else if($(this).attr("name") == "modifier") {
				var lien = "ajax/actualites/modif_actualites.php";
			}
			$.ajax({
				url: lien,
				type: "POST",
				data: new FormData(this),
				cache: false,
				contentType: false,
				processData: false,
				dataType: 'json',
				success: function(json) {
					if(json.couleur == "rouge") {
						$("#mod-message h4#titre").html("Erreur!");
						$("#mod-message #icon").removeClass("success").addClass("danger");
						$("#mod-message p#message").html(json.message);
						$('button#valider').prop('disabled', false);
					} else if(json.couleur == "vert") {
						$("#mod-message h4#titre").html("Succès!");
						$("#mod-message #icon").removeClass("danger").addClass("success");
						$("#mod-message p#message").html(json.message);	
					}
					$("#mod-message").niftyModal();
					if(json.redirect) {
						setTimeout(function(){location.href=json.redirect}, 3000);   
					}
				}
			});
			e.preventDefault();
		});
      	$('.wizard-ux#actualites_redac').on('change', function(e, data) {
			if(data.direction==='next') {
				var form = $("form#actualites");
				switch(data.step) {
					case 1:
						var inputs = [$("input[name^=titre]", form).val() == "", $("input[name^=descr]", form).val() == "", $("input[name^=tags]", form).val() == "", $('#contenu').summernote('isEmpty')];
					break;
					case 2:
						var inputs = [$("input[name^=image]", form).val() == ""];
					break;
					case 3:
						var inputs = [];
					break;
				}
				$.each(inputs, function(index, value) {
					if(value) {
						$("#mod-message h4#titre").html("Erreur!");
						$("#mod-message #icon").removeClass("success").addClass("danger");
						$("#mod-message p#message").html("Veuillez remplir les champs vides.");
						$("#mod-message").niftyModal();
						e.preventDefault();
					}
				});
				form.change();
			}
		});
		// GESTION IMAGE ACTUALITES
		$('form.actualites').find('input[name="image"]').on('change', function(e) {
			var files = $(this)[0].files;
			if (files.length > 0) {
				var file = this.files[0];
				var imagefile = file.type;
				var match= ["image/jpeg","image/png","image/jpg"];
				if(!((imagefile==match[0]) || (imagefile==match[1]) || (imagefile==match[2]))) {
					alert("Veuillez séléctionner un autre format de fichier. Seul le png, le jpg et le jpeg sont autorisés.");
					return false;
				} else {
					$("#prev_newsletter #image_newsletter").attr('src', window.URL.createObjectURL(file));
					$image_preview = $('.image_preview');
					$image_preview.find('.thumbnail').removeClass('hidden');
					$image_preview.find('img').attr('src', window.URL.createObjectURL(file));
					$image_preview.find('h4').html(file.name);
					$image_preview.find('.caption p:first').html(file.size +' bytes');
				}
			}
		});
		$('.image_preview').find('button[type="button"]').on('click', function(e) {
			e.preventDefault();

			$('form#actualites').find('input[name="image"]').val('');
			$('.image_preview').find('.thumbnail').addClass('hidden');
		});   
      }

      // BRASSERIES
      if(config.brasseries) {
      	// INITIALISATION DES PAYS
      	$.ajax({
      		url: "./ajax/brasseries/brasseries.php",
      		dataType: 'json',
      		success: function(json) {
      			$('.pays_b').select2({data: json.pays,width: '100%'}); 
      		}
      	});

      	// SUPPRESSION BRASSERIES
		$(".delete-brasserie").click(function() {
			var id = $(this).data("id");
			$("#mod-confirm").niftyModal();
			$("button#confirm-brasseries").data("id",id)
		});
		$("button#confirm-brasseries").click(function() {
			var button = this;
			var id = $(button).data("id");
			$(button).prop('disabled', true);
			$.ajax({
				url: "./ajax/brasseries/delete_brasseries.php",
				type: "POST",
				data: {'id': id},
				success: function() {
					$("#mod-confirm").niftyModal("hide");
					$(button).prop('disabled', false);
					$("tr.articles#"+id).remove();
				},
				error: function() {
					$(button).prop('disabled', false);
				}				
			});
		});

      	// AJOUT BRASSERIES
		$('.wizard-ux#brasseries_redac').on('change', function(e, data) {
			if(data.direction==='next') {
				var form = $("form#brasseries");
				switch(data.step) {
					case 1:
						var inputs = [$("input[name^=nom]", form).val() == "", $("input[name^=pays]", form).val() == "", $('#contenu').summernote('isEmpty')];
					break;
					case 2:
						var inputs = [$("input[name^=image]", form).val() == "", $("input[name^=logo]", form).val() == ""];
					break;
					case 3:
						var inputs = [];
					break;
				}
				$.each(inputs, function(index, value) {
					if(value) {
						$("#mod-message h4#titre").html("Erreur!");
						$("#mod-message #icon").removeClass("success").addClass("danger");
						$("#mod-message p#message").html("Veuillez remplir les champs vides.");
						$("#mod-message").niftyModal();
						e.preventDefault();
					}
				});
				form.change();
			}
		});
      	$("form#brasseries").on('submit', function(e) {
      		$('button#valider', this).prop('disabled', true);
			if($(this).attr("name") == "publier") {
				var lien = "ajax/brasseries/create_brasseries.php";
			} else if($(this).attr("name") == "modifier") {
				var lien = "ajax/brasseries/modif_brasseries.php";
			}
			$.ajax({
				url: lien,
				type: "POST",
				data: new FormData(this),
				cache: false,
				contentType: false,
				processData: false,
				dataType: 'json',
				success: function(json) {
					if(json.couleur == "rouge") {
						$("#mod-message h4#titre").html("Erreur!");
						$("#mod-message #icon").removeClass("success").addClass("danger");
						$("#mod-message p#message").html(json.message);
						$('button#valider').prop('disabled', false);
					} else if(json.couleur == "vert") {
						$("#mod-message h4#titre").html("Succès!");
						$("#mod-message #icon").removeClass("danger").addClass("success");
						$("#mod-message p#message").html(json.message);	
					}
					$("#mod-message").niftyModal();
					if(json.redirect) {
						setTimeout(function(){location.href=json.redirect}, 3000);   
					}
				}
			});
			e.preventDefault();
		});
      	// GESTION DES IMAGES
		  $('form#brasseries').find('input[name="image"]').on('change', function(e) {
			var files = $(this)[0].files;
			if (files.length > 0) {
			  var file = this.files[0];
			  var imagefile = file.type;
			  var match= ["image/jpeg","image/png","image/jpg"];
			  if(!((imagefile==match[0]) || (imagefile==match[1]) || (imagefile==match[2]))) {
			    alert("Veuillez séléctionner un autre format de fichier. Seul le png, le jpg et le jpeg sont autorisés.");
			    return false;
			  } else {
			    $image_preview = $('.image_preview');
			    $image_preview.find('.thumbnail').removeClass('hidden');
			    $image_preview.find('img').attr('src', window.URL.createObjectURL(file));
			    $image_preview.find('h4').html(file.name);
			    $image_preview.find('.caption p:first').html(file.size +' bytes');
			  }
			}
		  });
		  $('.image_preview').find('button[type="button"]').on('click', function(e) {
			e.preventDefault();
			 
			$('form#brasseries').find('input[name="image"]').val('');
			$('.image_preview').find('.thumbnail').addClass('hidden');
		  }); 

		  $('form#brasseries').find('input[name="logo"]').on('change', function(e) {
			var files = $(this)[0].files;
			if (files.length > 0) {
			  var file = this.files[0];
			  var imagefile = file.type;
			  var match = ["image/jpeg","image/png","image/jpg"];
			  if(!((imagefile==match[0]) || (imagefile==match[1]) || (imagefile==match[2]))) {
			    alert("Veuillez séléctionner un autre format de fichier. Seul le png, le jpg et le jpeg sont autorisés.");
			    return false;
			  } else {
			    $logo_preview = $('.logo_preview');
			    $logo_preview.find('.thumbnail').removeClass('hidden');
			    $logo_preview.find('img').attr('src', window.URL.createObjectURL(file));
			    $logo_preview.find('h4').html(file.name);
			    $logo_preview.find('.caption p:first').html(file.size +' bytes');
			  }
			}
		  });
		  $('.logo_preview').find('button[type="button"]').on('click', function(e) {
			e.preventDefault();
			 
			$('form#brasseries').find('input[name="logo"]').val('');
			$('.logo_preview').find('.thumbnail').addClass('hidden');
		  });
      }

      // CATALOGUE
      if(config.catalogue) {
		$("form#catalogue").on('submit', function(e) {
			$('button#valider', this).prop('disabled', true);
			$.ajax({
				url: "ajax/catalogue_/modif_catalogue.php",
				type: "POST",
				data: new FormData(this),
				cache: false,
				contentType: false,
				processData: false,
				dataType: 'json',
				success: function(json) {
					if(json.couleur == "rouge") {
						$("#mod-message h4#titre").html("Erreur!");
						$("#mod-message #icon").removeClass("success").addClass("danger");
						$("#mod-message p#message").html(json.message);
						$('button#valider').prop('disabled', false);
					} else if(json.couleur == "vert") {
						$("#mod-message h4#titre").html("Succès!");
						$("#mod-message #icon").removeClass("danger").addClass("success");
						$("#mod-message p#message").html(json.message);	
					}
					$("#mod-message").niftyModal();
					if(json.redirect) {
						setTimeout(function(){location.href=json.redirect}, 3000);   
					}
				}
			});
			e.preventDefault();
		});
		$("form#catalogue_access").on('submit', function(e) {
			$('button#valider', this).prop('disabled', true);
			$.ajax({
				url: "ajax/catalogue_/modif_access.php",
				type: "POST",
				data: new FormData(this),
				cache: false,
				contentType: false,
				processData: false,
				dataType: 'json',
				success: function(json) {
					if(json.couleur == "rouge") {
						$("#mod-message h4#titre").html("Erreur!");
						$("#mod-message #icon").removeClass("success").addClass("danger");
						$("#mod-message p#message").html(json.message);
						$('button#valider').prop('disabled', false);
					} else if(json.couleur == "vert") {
						$("#mod-message h4#titre").html("Succès!");
						$("#mod-message #icon").removeClass("danger").addClass("success");
						$("#mod-message p#message").html(json.message);	
					}
					$("#mod-message").niftyModal();
					if(json.redirect) {
						setTimeout(function(){location.href=json.redirect}, 3000);   
					}
				}
			});
			e.preventDefault();
		});
      }

      // BOUTIQUE
      if(config.boutique) {
      	// INITIALISATION DES ELEMENTS
      	$.ajax({
      		url: "./ajax/boutique_/element.php",
      		dataType: 'json',
      		success: function(json) {
      			$('.element').select2({data: json.element,width: '100%',multiple:true}); 
      		}
      	});
      	// INITIALISATION DES TAGS
      	$.ajax({
      		url: "./ajax/boutique_/categorie.php",
      		dataType: 'json',
      		success: function(json) {
      			$('.categorie').select2({data: json.categorie,width: '100%'}); 
      		}
      	});
      	// SUPPRESSION ACTUALITES
		$(".delete-produit").click(function() {
			var id = $(this).data("id");
			$("#mod-confirm").niftyModal();
			$("button#confirm-produit").data("id",id);
		});
		$("button#confirm-produit").click(function() {
			var button = this;
			var id = $(button).data("id");
			$(button).prop('disabled', true);
			$.ajax({
				url: "./ajax/boutique_/delete_produit.php",
				type: "POST",
				data: {'id': id},
				success: function() {
					$("#mod-confirm").niftyModal("hide");
					$(button).prop('disabled', false);
					$("tr.boutique#"+id).remove();
				},
				error: function() {
					$(button).prop('disabled', false);
				}				
			});
		});

      	$("form#boutique").on('submit', function(e) {
      		$('button#valider', this).prop('disabled', true);
			if($(this).attr("name") == "publier") {
				var lien = "ajax/boutique_/create_produits.php";
			} else if($(this).attr("name") == "modifier") {
				var lien = "ajax/boutique_/modif_produits.php";
			}
			$.ajax({
				url: lien,
				type: "POST",
				data: new FormData(this),
				cache: false,
				contentType: false,
				processData: false,
				dataType: 'json',
				success: function(json) {
					if(json.couleur == "rouge") {
						$("#mod-message h4#titre").html("Erreur!");
						$("#mod-message #icon").removeClass("success").addClass("danger");
						$("#mod-message p#message").html(json.message);
						$('button#valider').prop('disabled', false);
					} else if(json.couleur == "vert") {
						$("#mod-message h4#titre").html("Succès!");
						$("#mod-message #icon").removeClass("danger").addClass("success");
						$("#mod-message p#message").html(json.message);	
					}
					$("#mod-message").niftyModal();
					if(json.redirect) {
						setTimeout(function(){location.href=json.redirect}, 3000);   
					}
				}
			});
			e.preventDefault();
		});
      	$('.wizard-ux#boutique_produits').on('change', function(e, data) {
			if(data.direction==='next') {
				var form = $("form#boutique");
				switch(data.step) {
					case 1:
						var inputs = [$("input[name^=nom]", form).val() == "", $("input[name^=categorie]", form).val() == "", $("input[name^=element]", form).val() == ""];
					break;
					case 2:
						var inputs = [$("input[name^=image]", form).val() == ""];
					break;
				}
				$.each(inputs, function(index, value) {
					if(value) {
						$("#mod-message h4#titre").html("Erreur!");
						$("#mod-message #icon").removeClass("success").addClass("danger");
						$("#mod-message p#message").html("Veuillez remplir les champs vides.");
						$("#mod-message").niftyModal();
						e.preventDefault();
					}
				});
				form.change();
			}
		});
		// GESTION IMAGE ACTUALITES
		$('form.boutique').find('input[name="image"]').on('change', function(e) {
			var files = $(this)[0].files;
			if (files.length > 0) {
				var file = this.files[0];
				var imagefile = file.type;
				var match= ["image/jpeg","image/png","image/jpg"];
				if(!((imagefile==match[0]) || (imagefile==match[1]) || (imagefile==match[2]))) {
					alert("Veuillez séléctionner un autre format de fichier. Seul le png, le jpg et le jpeg sont autorisés.");
					return false;
				} else {
					$("#prev_newsletter #image_newsletter").attr('src', window.URL.createObjectURL(file));
					$image_preview = $('.image_preview');
					$image_preview.find('.thumbnail').removeClass('hidden');
					$image_preview.find('img').attr('src', window.URL.createObjectURL(file));
					$image_preview.find('h4').html(file.name);
					$image_preview.find('.caption p:first').html(file.size +' bytes');
				}
			}
		});
		$('.image_preview').find('button[type="button"]').on('click', function(e) {
			e.preventDefault();

			$('form#actualites').find('input[name="image"]').val('');
			$('.image_preview').find('.thumbnail').addClass('hidden');
		});  

		// INITIALISATION DES PRODUITS
      	$.ajax({
      		url: "./ajax/boutique_/produit.php",
      		dataType: 'json',
      		success: function(json) {
      			$('.produit').select2({data: json.produit,width: '100%',multiple:true}); 
      		}
      	});
      	$.ajax({
      		url: "ajax/boutique_/brasseries.php",
      		dataType: 'json',
      		success: function(json) {
      			$('.brasseriesid').select2({data: json.brasseries,width: '100%',multiple:true}); 
      		}
      	});
      	$("form#boutique_element").on('submit', function(e) {
      		$('button#valider', this).prop('disabled', true);
			if($(this).attr("name") == "publier") {
				var lien = "ajax/boutique_/create_produits_element.php";
			} else if($(this).attr("name") == "modifier") {
				var lien = "ajax/boutique_/modif_produits_element.php";
			}
			$.ajax({
				url: lien,
				type: "POST",
				data: new FormData(this),
				cache: false,
				contentType: false,
				processData: false,
				dataType: 'json',
				success: function(json) {
					if(json.couleur == "rouge") {
						$("#mod-message h4#titre").html("Erreur!");
						$("#mod-message #icon").removeClass("success").addClass("danger");
						$("#mod-message p#message").html(json.message);
						$('button#valider').prop('disabled', false);
					} else if(json.couleur == "vert") {
						$("#mod-message h4#titre").html("Succès!");
						$("#mod-message #icon").removeClass("danger").addClass("success");
						$("#mod-message p#message").html(json.message);	
					}
					$("#mod-message").niftyModal();
					if(json.redirect) {
						setTimeout(function(){location.href=json.redirect}, 3000);   
					}
				}
			});
			e.preventDefault();
		});
	      // SUPPRESSION ELEMENT
			$(".delete-element").click(function() {
				var id = $(this).data("id");
				$("#mod-confirm").niftyModal();
				$("button#confirm-produit").data("id-element",id);
			});
			$("button#confirm-produit").click(function() {
				var button = this;
				var id = $(button).data("id-element");
				$(button).prop('disabled', true);
				$.ajax({
					url: "./ajax/boutique_/delete_element.php",
					type: "POST",
					data: {'id': id},
					success: function() {
						$("#mod-confirm").niftyModal("hide");
						$(button).prop('disabled', false);
						$("tr.produit-element#"+id).remove();
					},
					error: function() {
						$(button).prop('disabled', false);
					}				
				});
			});
	      $('.wizard-ux#boutique_produits_element').on('change', function(e, data) {
			if(data.direction==='next') {
				var form = $("form#boutique_element");
				switch(data.step) {
					case 1:
						var inputs = [$("input[name^=nom]", form).val() == "", $("input[name^=contenance]", form).val() == "", $("input[name^=stock]", form).val() == "", $("input[name^=prix]", form).val() == "", $("input[name^=tva]", form).val() == "", $("input[name^=alcool]", form).val() == ""];
					break;
				}
				$.each(inputs, function(index, value) {
					if(value) {
						$("#mod-message h4#titre").html("Erreur!");
						$("#mod-message #icon").removeClass("success").addClass("danger");
						$("#mod-message p#message").html("Veuillez remplir les champs vides.");
						$("#mod-message").niftyModal();
						e.preventDefault();
					}
				});
				form.change();
			}
		  });
		  // MODIFIER COMMANDE BOUTIQUE
			$("select#deplace_commande").change(function() {
				var id_commande = $(this).data("id");
				var optionmoved = $(this, 'option:selected').val();
				var select = $(this);
				$.ajax({
					url: "ajax/catalogue_/commandes_.php",
					type: "POST",
					data: {"id_commande":id_commande, "optionmoved":optionmoved},
					dataType: 'json',
					success: function(json) {
						if(json.couleur == "rouge") {
							$("#mod-message h4#titre").html("Erreur!");
							$("#mod-message #icon").removeClass("success").addClass("danger");
							$("#mod-message p#message").html(json.message);
							$("#mod-message").niftyModal();
							select.addClass("erreur");
						} else if(json.couleur == "vert") {
							select.removeClass("erreur").css("border-color", "#54A754");
						}
					},
					error: function() {
						$("#mod-message h4#titre").html("Erreur!");
						$("#mod-message #icon").removeClass("success").addClass("danger");
						$("#mod-message p#message").html("Une erreur est survenue, veuillez réessayer plus tard.");
						$("#mod-message").niftyModal();
					}
				});
			});
			$("input.modification-element").change(function() {
				var input = $(this);
				var id = $(this).data("id");
				$.ajax({
					url: "ajax/boutique_/modif_element_table.php?action="+$(this).data("action"),
					type: "POST",
					data: {"id":id, "modif":input.val()},
					dataType: 'json',
					success: function(json) {
						if(json.couleur == "rouge") {
							$("#mod-message h4#titre").html("Erreur!");
							$("#mod-message #icon").removeClass("success").addClass("danger");
							$("#mod-message p#message").html(json.message);
							$("#mod-message").niftyModal();
							input.addClass("erreur");
						} else if(json.couleur == "vert") {
							input.removeClass("erreur").css("border-color", "#54A754");
						}
					},
					error: function() {
						$("#mod-message h4#titre").html("Erreur!");
						$("#mod-message #icon").removeClass("success").addClass("danger");
						$("#mod-message p#message").html("Une erreur est survenue, veuillez réessayer plus tard.");
						$("#mod-message").niftyModal();
					}
				});
			});
			$(".delete-commande").click(function() {
				var id = $(this).data("id");
				$("#mod-confirm").niftyModal();
				$("button#confirm-commande").data("id-element",id);
			});
			$("button#confirm-commande").click(function() {
				var button = this;
				var id = $(button).data("id-element");
				$(button).prop('disabled', true);
				$.ajax({
					url: "./ajax/catalogue_/delete_commande.php",
					type: "POST",
					data: {'id': id},
					success: function() {
						$("#mod-confirm").niftyModal("hide");
						$(button).prop('disabled', false);
						$("tr.commande#"+id).remove();
					},
					error: function() {
						$(button).prop('disabled', false);
					}				
				});
			});
		}

       /*Slider*/
      if(config.slider){
        $('.bslider').slider();     
      }
      
      /*Input & Radio Buttons*/
      if(jQuery().iCheck){
        $('.icheck').iCheck({
          checkboxClass: 'icheckbox_square-blue checkbox',
          radioClass: 'iradio_square-blue'
        });
      }
      
      /*Bind plugins on hidden elements*/
      if(config.hiddenElements){
      	/*Dropdown shown event*/
        $('.dropdown').on('shown.bs.dropdown', function () {
          $(".nscroller").nanoScroller();
        });
          
        /*Tabs refresh hidden elements*/
        $('.nav-tabs').on('shown.bs.tab', function (e) {
          $(".nscroller").nanoScroller();
        });
      }

      if(config.login){
      	$("form#login").on('submit', function(e) {
      		var email = $("input[name='email']").val();
      		var mdp = $("input[name='mdp']").val();
      		$.ajax({
      			data: {
      				'email': email,
      				'mdp' : mdp
      			},
      			url: 'ajax/login.php',
      			type: 'POST',
      			dataType: 'json',
      			success: function(json) {
      				if(json.redirect) {
      					window.location.href = json.redirect;
      				}
      				$("#message").fadeOut(0, function() {
      					$("#message").html(json.message);
      					$("#message").fadeIn();
      				});
      			}
      		});
      		e.preventDefault();
      	});
      }
    },
      
    /*Pages Javascript Methods*/
    dashBoard: function (){
      dashboard();
    },
    
    speech: function(options){
      speech(options);
    },
    
    speechCommand: function(com, options){
      speechCommand(com, options);
    },
    
    toggleSideBar: function(){
      toggleSideBar();
    },
    
    nestableLists: function(){
      nestable();
    },
 
    wizard: function(){
      wizard();
    },
    
    masks: function(){
      masks();
    },
    
    textEditor: function(){
      textEditor();
    },
    
    dataTables: function(){
      dataTables();
    },
    
    maps: function(){
      maps();
    },
    
    charts: function(){
      charts();
    },
    
    widgets: function(){
      widgets();
    }
    
  };
 
}();

$(function() {
  $("body").css({opacity:1,'margin-left':0});
});