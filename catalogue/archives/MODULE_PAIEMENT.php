<!--<form data-action="validation" id="commande-valide">-->
									<form action="https://paiement.systempay.fr/vads-payment/">
										<?php 
											$transaction_id = getTransactionId($b->id);
											if(empty($af->entreprise)) {$vads_cust_status = 'PRIVATE';} else {$vads_cust_status = 'COMPANY';}
											if(empty($al->entreprise)) {$vads_ship_to_status = 'PRIVATE';} else {$vads_ship_to_status = 'COMPANY';}
											date_default_timezone_set("UTC");
											$vads_trans_date = date("YmdHis",time());
											$parametres = array(
												"vads_action_mode" => "INTERACTIVE",
												"vads_amount" => intval($b->prix*100),
												"vads_ctx_mode" => "PRODUCTION",
												"vads_currency" => "978",
												"vads_cust_address" => $af->adresse." ".$af->adressec,
												"vads_cust_cell_phone" => $af->phone,
												"vads_cust_city" => $af->ville,
												"vads_cust_email" => $u->email,
												"vads_cust_first_name" => $af->prenom,
												"vads_cust_last_name" => $af->nom,
												"vads_cust_legal_name" => $af->entreprise,
												"vads_cust_status" => $vads_cust_status,
												"vads_cust_zip" => $af->codepostal,
												"vads_order_id" => $b->id,
												"vads_order_info" => $u->message_livraison,
												"vads_order_info2" => $u->option_livraison,
												"vads_order_info3" => $al->distance,
												"vads_ship_to_city" => $al->ville,
												"vads_ship_to_first_name" => $al->prenom,
												"vads_ship_to_last_name" => $al->nom,
												"vads_ship_to_legal_name" => $al->entreprise,
												"vads_ship_to_phone_num" => $al->phone,
												"vads_ship_to_status" => $vads_ship_to_status,
												"vads_ship_to_street" => $al->adresse,
												"vads_ship_to_street2" => $al->adressec,
												"vads_ship_to_zip" => $al->codepostal,
												"vads_page_action" => "PAYMENT",
												"vads_payment_config" => "SINGLE",
												"vads_site_id" => "80124299",
												"vads_trans_date" => $vads_trans_date,
												"vads_trans_id" => $transaction_id,
												"vads_version" => "V2"
											);
										?>
										<input type="hidden" name="vads_action_mode" value="INTERACTIVE" />
										<input type="hidden" name="vads_amount" value="<?php echo intval($b->prix*100); ?>" />
										<input type="hidden" name="vads_ctx_mode" value="PRODUCTION" />
										<input type="hidden" name="vads_currency" value="978" />
										<input type="hidden" name="vads_cust_address" value="<?php echo $af->adresse; ?> <?php echo $af->adressec; ?>" />
										<input type="hidden" name="vads_cust_cell_phone" value="<?php echo $af->phone; ?>" />
										<input type="hidden" name="vads_cust_city" value="<?php echo $af->ville; ?>" />
										<input type="hidden" name="vads_cust_email" value="<?php echo $u->email; ?>" />
										<input type="hidden" name="vads_cust_first_name" value="<?php echo $af->prenom; ?>" />
										<input type="hidden" name="vads_cust_last_name" value="<?php echo $af->nom; ?>" />
										<input type="hidden" name="vads_cust_legal_name" value="<?php echo $af->entreprise; ?>" />
										<input type="hidden" name="vads_cust_status" value="<?php echo $vads_cust_status; ?>" />
										<input type="hidden" name="vads_cust_zip" value="<?php echo $af->codepostal; ?>" />
										<input type="hidden" name="vads_order_id" value="<?php echo $b->id; ?>" />
										<input type="hidden" name="vads_order_info" value="<?php echo $u->message_livraison; ?>" />
										<input type="hidden" name="vads_order_info2" value="<?php echo $u->option_livraison; ?>" />
										<input type="hidden" name="vads_order_info3" value="<?php echo $al->distance; ?>" />
										<input type="hidden" name="vads_ship_to_city" value="<?php echo $al->ville; ?>" />
										<input type="hidden" name="vads_ship_to_first_name" value="<?php echo $al->prenom; ?>" />
										<input type="hidden" name="vads_ship_to_last_name" value="<?php echo $al->nom; ?>" />
										<input type="hidden" name="vads_ship_to_legal_name" value="<?php echo $al->entreprise; ?>" />
										<input type="hidden" name="vads_ship_to_phone_num" value="<?php echo $al->phone; ?>" />
										<input type="hidden" name="vads_ship_to_status" value="<?php echo $vads_ship_to_status; ?>" />
										<input type="hidden" name="vads_ship_to_street" value="<?php echo $al->adresse; ?>" />
										<input type="hidden" name="vads_ship_to_street2" value="<?php echo $al->adressec; ?>" />
										<input type="hidden" name="vads_ship_to_zip" value="<?php echo $al->codepostal; ?>" />
										<input type="hidden" name="vads_page_action" value="PAYMENT" />
										<input type="hidden" name="vads_payment_config" value="SINGLE" />
										<input type="hidden" name="vads_site_id" value="80124299" />
										<input type="hidden" name="vads_trans_date" value="<?php echo $vads_trans_date; ?>" />
										<input type="hidden" name="vads_trans_id" value="<?php echo $transaction_id; ?>" />
										<input type="hidden" name="vads_version" value="V2" />
										<input type="hidden" name="signature" value="<?php echo getSignature($parametres,"8YdNzzNccUfAlYZ5"); ?>"/>
