<div id="content-header" class="hidden-print reserve_header_container">
	<h1 class="headigs"> <i class="icon fa fa-shopping-cart"></i>
		<?php echo lang('reserve_register'); ?> <span id="ajax-loader"><?php echo img(array('src' => base_url().'/img/ajax-loader.gif')); ?></span>
		<?php if($this->reserve_lib->get_change_reservation_id()) { ?>
		<small>
			<?php echo lang('reserve_editing_reserve'); ?> <b> ss<?php echo $this->config->item('reserve_prefix').' '.$this->reserve_lib->get_change_reservation_id(); ?> </b>
		</small>
		<?php } ?>
	</h1>



</div>

<div class="clear"></div>
	<!--Left small box-->
	<div class="row">
		<div class="reserve_register_leftbox col-md-9">
			<div class="row forms-area">
				<?php if ($mode != 'store_account_payment') { ?>
						<div class="col-md-12 no-padd">
							<div class="input-append">
								<?php echo form_open("reserve/add",array('id'=>'add_room_form','class'=>'form-inline', 'autocomplete'=> 'off')); ?>

								<?php echo form_input(array('name'=>'item','id'=>'item','class'=>'input-xlarge', 'accesskey' => 'k', 'placeholder' => lang('reserve_start_typing_room_name')));
								?>

								<?php echo anchor("bedrooms/view/-1/1/reserve",
									lang('reserve_new_room'),
									array('class'=>'btn btn-primary none new_room_btn','title'=>lang('reserve_new_room')));
								?>

								</form>
							</div>
						</div>
					<?php } ?>



			</div>

		<div class="row">

			<?php if ($mode != 'store_account_payment')
			{ ?>
			<div class="table-responsive">
				<table id="register" class="table table-bordered">

					<thead>
						<tr>
							<th ></th>
							<th class="room_name_heading" ><?php echo lang('reserve_room_name'); ?></th>
							<th class="reserve_room reserve_bedrooms_number">

								<?php
								switch($this->config->item('id_to_show_on_reserve_interface'))
								{
									case 'number':
									echo lang('reserve_room_number') . $this->config->item('id_to_show_on_reserve_interface');
									break;

									case 'id':
									echo lang('reserve_room_id') . $this->config->item('id_to_show_on_reserve_interface');
									break;

									default:
									echo lang('reserve_room_number') . $this->config->item('id_to_show_on_reserve_interface');
									break;
								}
								?>

							</th>
							<th class="reserve_stock"><?php echo lang('reserve_stock'); ?></th>
							<th class="reserve_price"><?php echo lang('reserve_price'); ?></th>
							<th class="reserve_quality"><?php echo lang('reserve_quantity'); ?></th>
							<th class="reserve_discount"><?php echo lang('reserve_discount'); ?></th>
							<th ><?php echo lang('reserve_total'); ?></th>
						</tr>
					</thead>
					<tbody id="cart_contents" class="sa">
						<?php if(count($cart)==0)	{ ?>
						<tr class="cart_content_area">
							<td colspan='8'>
								<div class='text-center text-warning' > <h3><?php echo lang('reserve_no_bedrooms_in_cart'); ?></h3></div>
							</td>
						</tr>
						<?php
						}
						else
						{
							foreach(array_reverse($cart, true) as $line=>$item)
							{
								$cur_room_location_info = isset($item['room_id']) ? $this->Item_location->get_info($item['room_id']) : $this->Item_kit_location->get_info($item['item_kit_id']);
								?>
								<tr id="reg_room_top" bgcolor="#eeeeee" >
									<td><?php echo anchor("reserve/delete_room/$line",'<i class="fa fa-trash-o fa fa-2x text-error"></i>', array('class' => 'delete_room'));?></td>
									<td class="text text-success"><a href="<?php echo isset($item['room_id']) ? site_url('home/view_room_modal/'.$item['room_id']) : site_url('home/view_room_kit_modal/'.$item['item_kit_id']) ; ?>" data-toggle="modal" data-target="#myModal" ><?php echo H($item['name']); ?></a></td>
									<td class="text text-info reserve_room" id="reg_room_number">
										<?php
										switch($this->config->item('id_to_show_on_reserve_interface'))
										{
											case 'number':
											echo H($item['room_number']);
											break;

											case 'id':
											echo H($item['room_id']);
											break;

											default:
											echo H($item['room_number']);
											break;
										}
										?>

									</td>
									<td class="text text-warning reserve_stock" id="reg_room_stock" ><?php echo property_exists($cur_room_location_info, 'quantity') ? to_quantity($cur_room_location_info->quantity) : ''; ?></td>

									<?php if ($this->Employee->has_module_action_permission('reserve', 'edit_reserve_price', $this->Employee->get_logged_in_employee_info()->person_id)){ ?>
									<td>
										<?php
										echo form_open("reserve/edit_room/$line", array('class' => 'line_room_form', 'autocomplete'=> 'off'));


										echo form_input(array('name'=>'price','value'=>to_currency_no_money($item['price'], 10),'class'=>'input-small', 'id' => 'price_'.$line));?>

										</form>

									</td>
									<?php }else{
									?><td>
									<?php
										echo form_open("reserve/edit_room/$line", array('class' => 'line_room_form', 'autocomplete'=> 'off'));

											echo $item['price'];
											echo form_hidden('price',$item['price']); ?>


										</form>

									</td>
									<?php }	?>

									<td>
										<?php
										echo form_open("reserve/edit_room/$line", array('class' => 'line_room_form', 'autocomplete'=> 'off'));

												if(isset($item['is_serialized']) && $item['is_serialized']==1){
													echo to_quantity($item['quantity']);
													echo form_hidden('quantity',to_quantity($item['quantity']));
												}else{
													echo form_input(array('name'=>'quantity','value'=>to_quantity($item['quantity']),'class'=>'input-small', 'id' => 'quantity_'.$line));
												}?>

										</form>
									</td>

									<?php if ($this->Employee->has_module_action_permission('reserve', 'give_discount', $this->Employee->get_logged_in_employee_info()->person_id)){ ?>
									<td>
										<?php
										echo form_open("reserve/edit_room/$line", array('class' => 'line_room_form', 'autocomplete'=> 'off'));
											echo form_input(array('name'=>'discount','value'=>$item['discount'],'class'=>'input-small', 'id' => 'discount_'.$line));?>
										  </form>
									</td>
									<?php }else{ ?>
									<td>
										<?php
										echo form_open("reserve/edit_room/$line", array('class' => 'line_room_form', 'autocomplete'=> 'off'));

											echo $item['discount'];
											echo form_hidden('discount',$item['discount']);
								 ?>
										</form>
									</td>

									<?php }	?>


									<td class="text text-main"><?php echo to_currency($item['price']*$item['quantity']-$item['price']*$item['quantity']*$item['discount']/100); ?></td>
								</tr>

								<tr id="reg_room_bottom">
									<td ><?php echo lang('reserve_description_abbrv').':';?></td>
									<td  colspan="4" class="edit_description">
										<?php
										echo form_open("reserve/edit_room/$line", array('class' => 'line_room_form', 'autocomplete'=> 'off'));


										if(isset($item['allow_alt_description']) && $item['allow_alt_description']==1){
											echo form_input(array('name'=>'description','value'=>$item['description'],'size'=>'20', 'id' => 'description_'.$line, 'class' =>'description', 'maxlength' => 255));
										}else{
											if ($item['description']!=''){
												echo $item['description'];
												echo form_hidden('description',$item['description']);
											}else{
												echo 'None';
												echo form_hidden('description','');
											}
										}?>
										</form>
									</td>
									<td >

										<?php if(isset($item['is_serialized']) && $item['is_serialized']==1  && $item['name']!=lang('reserve_giftcard')){
											echo lang('reserve_serial').':';
										}?>
									</td>
									<td colspan="2" class="edit_serialnumber">
										<?php
										echo form_open("reserve/edit_room/$line", array('class' => 'line_room_form', 'autocomplete'=> 'off'));


										    if(isset($item['is_serialized']) && $item['is_serialized']==1  && $item['name']!=lang('reserve_giftcard'))	{
												echo form_input(array('name'=>'serialnumber','value'=>$item['serialnumber'], 'class' => 'serial_room','size'=>'20', 'id' => 'serialnumber_'.$line, 'maxlength' => 255));
											}else{
												echo form_hidden('serialnumber', '');
											}?>
										</form>
									</td>
								</tr>


							<?php
						}
					}?>
				</tbody>
			</table>
			</div>
		<?php }
		else
		{ /*Store Account Mode*/ ?>
			<table id="register"  class="tablesorter table table-bordered ">

				<thead>
					<tr>
						<th ><?php echo lang('reserve_room_name'); ?></th>
						<th ><?php echo lang('reserve_payment_amount'); ?></th>
					</tr>
				</thead>
				<tbody id="cart_contents">
					<?php
					foreach(array_reverse($cart, true) as $line=>$item)
					{
						$cur_room_location_info = isset($item['room_id']) ? $this->Item_location->get_info($item['room_id']) : $this->Item_kit_location->get_info($item['item_kit_id']);
						?>





						<tr id="reg_room_top" bgcolor="#eeeeee" >
							<td class="text text-success"><a href="<?php echo site_url('bedrooms/view_modal/'.$item['room_id']) ; ?>" data-toggle="modal" data-target="#myModal" ><?php echo H($item['name']); ?></a></td>
							<td>
								<?php
								echo form_open("reserve/edit_room/$line", array('class' => 'line_room_form', 'autocomplete'=> 'off'));

									echo form_input(array('name'=>'price','value'=>to_currency_no_money($item['price'], 10),'class'=>'input-small', 'id' => 'price_'.$line));

									echo form_hidden('quantity',to_quantity($item['quantity']));
									echo form_hidden('description','');
									echo form_hidden('serialnumber', '');
								?>

								</form>
							</td>
						</tr>



				<?php } /*Foreach*/?>
			</tbody>
		</table>

		<?php } ?>
		<ul class="list-inline pull-left">
			<?php if ($this->config->item('track_cash')) { ?>
			<li>
				<?php echo anchor(site_url('reserve/closeregister?continue=home'), lang('reserve_close_register'),array('class'=>'btn btn-primary')); ?>
			</li>
			<?php } ?>

			<?php
			if ($this->Register->count_all($this->Employee->get_logged_in_employee_current_location_id()) > 1)
			{
			?>
				<li>
					<?php echo anchor(site_url('reserve/clear_register'), lang('reserve_change_register'),array('class'=>'btn btn-primary')); ?>
				</li>
			<?php
			}
			?>
			<li>

			<?php if ($mode != 'store_account_payment') { ?>


				<?php
					if ($this->Employee->has_module_action_permission('reports', 'view_reserve_generator', $this->Employee->get_logged_in_employee_info()->person_id))
					{
					?>
						<li>
						<?php echo
						anchor("reports/reserve_generator",
						lang('reserve_search_reports'),
						array('class'=>'btn btn-primary ',
							'title'=>lang('reserve_search_reports')));
						?>
						</li>
					<?php } ?>
			<?php } ?>


			</ul>
				<?php
				if ($this->Employee->has_module_action_permission('reserve', 'give_discount', $this->Employee->get_logged_in_employee_info()->person_id) && $mode != 'store_account_payment'){ ?>
					<ul class="list-inline pull-right" id="global_discount">
						<li>
							<?php
							echo form_open("reserve/discount_all", array('id' => 'discount_all_form', 'autocomplete'=> 'off'));
							echo '<label id="discount_all_percent_label" for="discount_all_percent">';
							echo lang('reserve_global_reserve_discount').': ';
							echo '</label>';
							echo '&nbsp;&nbsp;';
							echo form_input(array('name'=>'discount_all_percent','value'=> '','size'=>'3', 'id' => 'discount_all_percent'));
							echo '%&nbsp;&nbsp;';
							echo form_submit('submit_discount_form',lang('common_submit'),'class="btn btn-primary"');
							?>
							</form>

						</li>
					</ul>
				<?php } ?>
						</div>


						<?php
						if (!$this->config->item('hide_customer_recent_reserve') && isset($customer))
						{
						?>
						<div class="row hidden-xs">
							<h1><?php echo lang('reserve_recent_reserve').' '.H($customer);?></h1>
							<table id="recent_reserve" class="table">
								<tr>
									<th align="center"><?php echo lang('bedrooms_date');?></th>
									<th align="center"><?php echo lang('reports_payments');?></th>
									<th align="center"><?php echo lang('reports_bedrooms_purchased');?></th>
									<th align="center"><?php echo lang('reserve_receipt');?></th>
								</tr>

								<?php foreach($recent_reserve as $reserve) {?>
									<tr>
										<td align="center"><?php echo date(get_date_format().' @ '.get_time_format(), strtotime($reserve['reserve_time']));?></td>
										<td align="center"><?php echo $reserve['payment_type'];?></td>
										<td align="center"><?php echo to_quantity($reserve['bedrooms_purchased']);?></td>
										<td align="center"><?php echo anchor('reserve/receipt/'.$reserve['reserve_id'], lang('reserve_receipt'), array('target' =>'_blank')); ?></td>
									</tr>
								<?php } ?>
							</table>
						</div>
						<?php
						}
						?>

					</div>
					<!-- Right small box  -->
				<div class="col-md-3 reserve_register_rightbox">
					<ul class="list-group">
						<li class="list-group-item nopadding">
							<!-- Cancel and suspend buttons -->
							<div <?php if(count($cart) > 0){ echo "class='reserve_form_main'";}?>>
								<?php if(count($cart) > 0){ ?>
								<?php echo form_open("reserve/cancel_reserve",array('id'=>'cancel_reserve_form', 'autocomplete'=> 'off')); ?>
								<?php if ($mode != 'store_account_payment') { ?>
									<input type="button" class="btn btn-warning warning-buttons" id="layaway_reserve_button" style="display: none;" value="<?php echo lang('reserve_layaway');?>" />
									<input type="button" class="btn btn-warning warning-buttons" id="estimate_reserve_button" style="display: none;" value="<?php echo lang('reserve_estimate');?>" />
								<?php } ?>
								<input type="button" class="btn btn-danger button_dangers" id="cancel_reserve_button" value="<?php echo lang('reserve_cancel_reserve');?>" />
							</form>
							<?php } ?>
						</div>
					</li>
					<li class="list-group-item room_tier">
						<!-- Customer info starts here-->
						<h5 class="customer-basic-information"><?php if(isset($customer))
							{
							 	echo lang('customers_basic_information');
							}
							else
							{
								if ($this->config->item('require_customer_for_reserve'))
								{
									echo lang('reserve_select_customer_required');
								}
								else
								{
									echo lang('reserve_select_customer');
								}
							}
							?>
							</h5>
						<div class="row nomargin">
						<div class="clearfix" id="customer_info_shell">
							<?php if(isset($customer)) {
								$full_width_col = "full_width_imporant";
								if ($avatar != '' )
								{
								 $full_width_col = "";
								?>

									<div id="customer-avatar">
										<img src='<?php echo $avatar; ?>' alt="Customer" class=' img-polaroid ' width="100px;" />
									</div>
							<?php
								}
							?>
								<div id="customer-info" class=" <?php echo $full_width_col?>">
								<div class="clear">
									<ul class="list-unstyled">
										<li id="customer_view_modal"><strong><a href="<?php echo isset($customer_id) ? site_url('customers/view_modal/'.$customer_id) : site_url('customers/view_modal/'.$customer_id) ; ?>" data-toggle="modal" data-target="#myModal" ><?php echo character_limiter(H($customer), 25); ?></a></strong>
											<?php if ($this->config->item('customers_store_accounts') && isset($customer_balance)) {?>
												<span class="<?php echo $is_over_credit_limit ? 'credit_limit_warning' : 'credit_limit_ok'; ?>">(<?php echo lang('customers_balance').': '.to_currency($customer_balance); ?>)</span>
										<?php } ?>
										</li>
										<?php if(!empty($customer_email)) {
											echo "<li>";
											echo form_checkbox(array(
												'name'        => 'email_receipt',
												'id'          => 'email_receipt',
												'value'       => '1',
												'class'       => 'email_receipt_checkbox',
												'checked'     => (boolean)$email_receipt,
												)).'&nbsp;'.lang('reserve_email_receipt').': <i>'.character_limiter(H($customer_email), 25).'</i>';
											echo "</li>";
										} ?>
									</ul>
								</div>
						<?php
							echo anchor("customers/view/$customer_id/1", lang('common_edit'),  array('id' => 'edit_customer','class'=>'none btn-sm btn-primary ','title'=>lang('customers_update'))).'';
							echo ''.anchor("reserve/delete_customer", lang('reserve_detach'),array('id' => 'delete_customer','class'=>'btn-sm btn-warning'));

							?>
							</div>



					<?php
						}
						else
							{ ?>
						<?php echo form_open("reserve/select_customer",array('id'=>'select_customer_form', 'autocomplete'=> 'off')); ?>
						<?php echo form_input(array('name'=>'customer','id'=>'customer','size'=>'30','value'=>lang('reserve_start_typing_customer_name'), 'placeholder'=>lang('reserve_start_typing_customer_name'),  'accesskey' => 'c'));?>
					</form>
					<div id="add_customer_info">
							<div id="common_or" class="common_or">
								<?php echo lang('common_or'); ?>
								<?php
								echo anchor("customers/view/-1/1",
									"<div class='small_button'> <span>".lang('reserve_new_customer')."</span> </div>", array('class'=>'btn btn-primary none','title'=>lang('reserve_new_customer'), 'id' => 'new-customer'));
								?>
							</div>
					</div>

						<?php }
						?>
					</div>
				</div>
				</li>
				<li class="list-group-item spacing">
				</li>
				<li class="list-group-item nopadding">

					<div id='reserve_details'>
						<table id="reserve_bedrooms" class="table">
							<tr class="warning">
								<td class="left"><?php echo lang('reserve_bedrooms_in_cart'); ?>:</td>
								<td class="right"><?php echo $bedrooms_in_cart; ?></td>
							</tr>
							<?php foreach($payments as $payment) {?>
							<?php if (strpos($payment['payment_type'], lang('reserve_giftcard'))!== FALSE) {?>
							<tr class="error">
								<td class="left"><?php echo $payment['payment_type']. ' '.lang('reserve_balance') ?>:</td>
								<?php $giftcard_payment_row = explode(':', $payment['payment_type']); ?>
								<td class="right"><?php echo to_currency($this->Giftcard->get_giftcard_value(end($giftcard_payment_row)) - $payment['payment_amount']);?></td>
							</tr>
							<?php }?>
							<?php }?>
							<tr class="info">
								<td class="left"><?php echo lang('reserve_sub_total'); ?>:</td>
								<td class="right"><?php echo to_currency($subtotal); ?></td>
							</tr>
							<?php foreach($taxes as $name=>$value) { ?>
							<tr class="color1">
								<td class="left">

									<?php if (!$is_tax_inclusive && $this->Employee->has_module_action_permission('reserve', 'delete_taxes', $this->Employee->get_logged_in_employee_info()->person_id)){ ?>
										<?php echo anchor("reserve/delete_tax/".rawurlencode($name),'['.lang('common_delete').']', array('class' => 'delete_tax'));?></span>
									<?php } ?>
									<?php echo $name; ?>:</td>
								<td class="right"><?php echo to_currency($value); ?></td>
							</tr>
							<?php }; ?>
							<tr class="success">
								<td ><h3 class="reserve_totals"><?php echo lang('reserve_total'); ?>:</h3></td>
								<td ><h3 class="currency_totals"><?php echo to_currency($total); ?></h3></td>
							</tr>
						</table>
					</div>
				</li>
				<li class="list-group-item spacing">
				</li>

				<li class="list-group-item nopadding">
					<?php
					// Only show this part if there are Items already in the reserve.
					if(count($cart) > 0){ ?>

					<div id="Payment_Types">

						<?php
							// Only show this part if there is at least one payment entered.
						if(count($payments) > 0)
						{
						?>
							<table id="register" class="table">
								<thead>
									<tr>
										<th id="pt_delete"></th>
										<th id="pt_type"><?php echo lang('reserve_type'); ?></th>
										<th id="pt_amount"><?php echo lang('reserve_amount'); ?></th>


									</tr>
								</thead>
								<tbody id="payment_contents">
									<?php
									foreach($payments as $payment_id=>$payment)
									{
										?>
										<tr class="warning">
											<td id="pt_delete"><?php echo anchor("reserve/delete_payment/$payment_id",'['.lang('common_delete').']', array('class' => 'delete_payment'));?></td>
											<td id="pt_type"><?php echo $payment['payment_type']; ?> </td>
											<td id="pt_amount"><?php echo  to_currency($payment['payment_amount']); ?>  </td>
										</tr>
										<?php
									}
									?>
								</tbody>
							</table>
							<?php } ?>

							<table id="amount_due" class="table">
								<tr class="<?php if($payments_cover_total) { echo 'success'; } else { echo 'error'; }?>">
									<td>
										<h4 class="reserve_amount_due"><?php echo lang('reserve_amount_due'); ?>:</h4>
									</td>
									<td>
										<h3 class="amount_dues"><?php echo to_currency($amount_due); ?></h3>
									</td>
								</tr>
							</table>
							<?php if ($customer_required_check) { ?>
							<div id="make_payment">
								<?php echo form_open("reserve/add_payment",array('id'=>'add_payment_form', 'autocomplete'=> 'off')); ?>
								<table id="make_payment_table" class="table">
									<tr id="mpt_top">
										<td id="add_payment_text">
											<label accesskey="y" for="payment_types"><?php echo lang('reserve_add_payment'); ?>:</label>
										</td>
										<td>
											<?php echo form_dropdown('payment_type',$payment_options,$this->config->item('default_payment_type'), 'id="payment_types" class="input-medium"');?>
										</td>
									</tr>
									<tr id="mpt_bottom" >
										<td id="tender" colspan="2">
											<div class="input-append">
												<?php echo form_input(array('name'=>'amount_tendered','id'=>'amount_tendered','value'=>to_currency_no_money($amount_due),'class'=>'input-medium input_mediums', 'accesskey' => 'p'));	?>
												<input type="button" class="btn btn-primary" id="add_payment_button" value="<?php echo lang('reserve_add_payment'); ?>" />
											</div>

										</td>
									</tr>

								</table>
							</form>
						</div>
						<?php } ?>
					</div>
				</li>
				<li class="list-group-item">
					<?php
					echo '<label id="comment_label" for="comment">';
					echo lang('common_comments');
					echo ':</label><br />';
					echo form_textarea(array('name'=>'comment', 'id' => 'comment', 'value'=>$comment,'rows'=>'4',  'accesskey' => 'o'));
					echo '<br />';
					echo '<label id="show_comment_on_receipt_label" for="show_comment_on_receipt" class="checkbox">';
					echo lang('reserve_comments_receipt');
					echo form_checkbox(array(
						'name'=>'show_comment_on_receipt',
						'id'=>'show_comment_on_receipt',
						'value'=>'1',
						'checked'=>(boolean)$show_comment_on_receipt)
					);
					echo '</label>  ';


					// Only show this part if there is at least one payment entered.
					if((count($payments) > 0 && !is_reserve_integrated_cc_processing())){?>
					<div id="finish_reserve">
						<?php echo form_open("reserve/complete",array('id'=>'finish_reserve_form', 'autocomplete'=> 'off')); ?>
						<?php
						if ($payments_cover_total && $customer_required_check)
						{
							echo "<input type='button' class='btn btn-success btn-large btn-block' id='finish_reserve_button' value='".lang('reserve_complete_reserve')."' />";
						}
						?>
					</div>
				</form>
				<?php }elseif(count($payments) > 0)	{?>
				<div id="finish_reserve">
					<?php echo form_open("reserve/start_cc_processing",array('id'=>'finish_reserve_form', 'autocomplete'=> 'off')); ?>
					<?php
					if ($payments_cover_total && $customer_required_check || (is_reserve_integrated_cc_processing()))
					{
						echo "<input type='button' class='btn btn-success btn-large btn-block' id='finish_reserve_button' value='".lang('reserve_process_credit_card')."' />";

						if (is_reserve_integrated_cc_processing())
						{
							if (isset($customer) && $customer_cc_token && $customer_cc_preview)
							{
								echo '<label id="reserve_use_saved_cc_label" for="use_saved_cc_info" class="checkbox">';
								echo lang('reserve_use_saved_cc_info'). ' '.$customer_cc_preview;
								echo form_checkbox(array(
									'name'=>'use_saved_cc_info',
									'id'=>'use_saved_cc_info',
									'value'=>'1',
									'checked'=>(boolean)$use_saved_cc_info)
								);
								echo '</label>  ';
							}
							elseif(isset($customer))
							{
								echo '<label id="reserve_save_credit_card_label" for="save_credit_card_info" class="checkbox">';
								echo lang('reserve_save_credit_card_info');
								echo form_checkbox(array(
									'name'=>'save_credit_card_info',
									'id'=>'save_credit_card_info',
									'value'=>'1',
									'checked'=>(boolean)$save_credit_card_info)
								);
								echo '</label>  ';
							}
						}
					}
					?>
				</div>
			</form>
			<?php }
			?>
			<?php
			if($this->reserve_lib->get_change_reservation_id()) {
				echo '<br />';
				echo '<label id="comment_label" for="change_reserve_date_enable" class="checkbox">';
				echo lang('reserve_change_date');
				echo form_checkbox(array(
					'name'=>'change_reserve_date_enable',
					'id'=>'change_reserve_date_enable',
					'value'=>'1',
					'checked'=>(boolean)$change_reserve_date_enable)
				);
				echo ':</label>  ';

				?>
				<div class="field_row clearfix" id="change_reserve_input">
					<div class='form_field'>

					<div id="change_reserve_date_picker" class="input-group date datepicker" data-date="date(get_date_format())" data-date-format=<?php echo json_encode(get_js_date_format()); ?>>
						<span class="input-group-addon"><i class="fa fa-calendar"></i></span>

					<?php echo form_input(array(
						'name'=>'change_reserve_date',
						'id' => 'change_reserve_date',
						'size'=>'8',
						 'value'=> date(get_date_format())
						)
					);?>
					</div>
					</div>
				</div>
				<?php
			}
		} ?>
			</li>
		</ul>

		</div>
</div>
<div id="keyboardhelp" style="display: none;" title="<?php echo lang('reserve_keyboard_help_title');?>">
  <p>
  <div>
  	<span>[ESC] => <?php echo lang('reserve_set_focus_cancel')?>[Enter] <?php echo lang('reserve_will_trigger');?></span><br>
  	<span>[F7]  => <?php echo lang('reserve_set_focus_payment');?> [ALT][Down Arrow] <?php echo lang('reserve_will_trigger_dropdown');?>, <?php echo lang('reserve_and'); ?> [TAB] <?php echo lang('reserve_will_focus_amount');?>.</span><br>
  	<span>[F4]  => <?php echo lang('reserve_set_focus_complete');?>. [Enter] <?php echo lang('reserve_will_trigger');?></span>
  	<br><br>
  	<span> <?php echo lang('reserve_in_addtion_shortcut_keys');?> [SHIFT][ALT] <?php echo lang('reserve_on_the_keyboard'); ?>. <?php echo lang('reserve_you_will_see_letters');?>.</span><br>
  	<span>[SHIFT][ALT][o] <?php echo lang('reserve_set_focus_comments');?>.</span>
  	<span>[SHIFT][ALT][p] <?php echo lang('reserve_set_focus_amount');?>.</span>

  </div>
  </p>
</div>

<div class="row ">


<?php if ($this->config->item('select_reserve_person_during_reserve')) {?>

	<div class="col-md-3">
			<div id="select_reserve_person">
		<?php echo lang('reserve_reserve_person'); ?>:
		<?php echo form_dropdown('sold_by_employee_id', $employees, $selected_sold_by_employee_id, 'class="form-control" id="sold_by_employee_id"'); ?>
	</div>
	</div>
	<?php } ?>


<?php
$reg_info = $this->Register->get_info($this->Employee->get_logged_in_employee_current_register_id());
$reg_name = $reg_info->name;

if ($this->Register->count_all($this->Employee->get_logged_in_employee_current_location_id()) > 1)
{
?>
<div class="col-md-3">
	<div style="margin-left: 10px;margin-top: 20px;" id="switch_register_container">
		<?php echo lang('locations_register_name');?>: <?php echo anchor('reserve/clear_register', $reg_name);?>
	</div>
	</div>
<?php
}
?>

<div class="col-md-3">
	<a href="#" class="pull-right" id="opener"><?php echo lang('reserve_keyboard_help_title');?></a>

</div>
</div>



<?php if (!$this->config->item('disable_reserve_notifications')) { ?>
	<script type="text/javascript">
		<?php
		if(isset($error))
		{
			echo "gritter(".json_encode(lang('common_error')).",".json_encode($error).",'gritter-item-error',false,false);";

		}

		if (isset($warning))
		{
			echo "gritter(".json_encode(lang('common_warning')).",".json_encode($warning).",'gritter-item-warning',false,false);";

		}

		if (isset($success))
		{
			echo "gritter(".json_encode(lang('common_success')).",".json_encode($success).",'gritter-item-success',false,false);";

		}
		?>
	</script>
<?php } ?>
<script type="text/javascript" language="javascript">

$(document).keydown(function(event)
{
		var mycode = event.keyCode;

		if (mycode == 113)
		{
			$("#item").focus();
		}

		//F4
		if (mycode == 115)
		{
			event.preventDefault();
			$("#finish_reserve_button").focus();
			event.originalEvent.keyCode = 0;
		}

		//F7
		if (mycode == 118)
		{
			event.preventDefault();
			$("#payment_types").focus();
			event.originalEvent.keyCode = 0;
		}

		//ESC
		if (mycode == 27)
		{
	    	$("#cancel_reserve_button").focus();
		}
	});

    var submitting = false;
	$(document).ready(function()
	{
		$(function() { $.keyTips(); });

		$( "#keyboardhelp" ).dialog({
			autoOpen: false,
			show: {
				effect: "blind",
		  	 duration: 1000
			},
		width: 800,
		hide: {
		  	 effect: "explode",
			 duration: 1000
			}
		});

		$( "#opener" ).click(function(e) {
			e.preventDefault();
			$( "#keyboardhelp" ).dialog( "open" );
		});


		//Here just in case the loader doesn't go away for some reason
		$("#ajax-loader").hide();

		<?php if (!$this->agent->is_mobile()) { ?>
			<?php if (!$this->config->item('auto_focus_on_room_after_reserve_and_receiving'))
			{
			?>
				if (last_focused_id && last_focused_id != 'item' && $('#'+last_focused_id).is('input[type=text]'))
				{
					$('#'+last_focused_id).focus();
					$('#'+last_focused_id).select();
				}
				<?php
			}
			else
			{
			?>
				setTimeout(function(){$('#item').focus();}, 10);
			<?php
			}
			?>
			$(document).focusin(function(event)
			{
				last_focused_id = $(event.target).attr('id');
			});
		<?php } ?>
		$('#mode_form, #select_customer_form, #add_payment_form, .line_room_form, #discount_all_form').ajaxForm({target: "#register_container", beforeSubmit: reserveBeforeSubmit});
		$('#add_room_form').ajaxForm({target: "#register_container", beforeSubmit: reserveBeforeSubmit, success: itemScannedSuccess});
		$("#cart_contents input").change(function()
		{
			$(this.form).ajaxSubmit({target: "#register_container", beforeSubmit: reserveBeforeSubmit});
		});

		$( "#item" ).autocomplete({
			source: '<?php echo site_url("reserve/room_search"); ?>',
			delay: 300,
			autoFocus: false,
			minLength: 1,
			select: function(event, ui)
			{
				event.preventDefault();
				$( "#item" ).val(ui.item.value);
				$('#add_room_form').ajaxSubmit({target: "#register_container", beforeSubmit: reserveBeforeSubmit, success: itemScannedSuccess});
			}
		});

		$('#item,#customer').click(function()
		{
			$(this).attr('value','');
		});

		$( "#customer" ).autocomplete({
			source: '<?php echo site_url("reserve/customer_search"); ?>',
			delay: 300,
			autoFocus: false,
			minLength: 1,
			select: function(event, ui)
			{
				$("#customer").val(ui.item.value);
				$('#select_customer_form').ajaxSubmit({target: "#register_container", beforeSubmit: reserveBeforeSubmit});
			}
		});

		$('#customer').blur(function()
		{
			$(this).attr('value',<?php echo json_encode(lang('reserve_start_typing_customer_name')); ?>);
		});

		$('#item').blur(function()
		{
			$(this).attr('value',<?php echo json_encode(lang('reserve_start_typing_room_name')); ?>);
		});

		//Datepicker change
		$('#change_reserve_date_picker').datepicker().on('changeDate', function(ev) {
			$.post('<?php echo site_url("reserve/set_change_reserve_date");?>', {change_reserve_date: $('#change_reserve_date').val()});
		});

		//Input change
		$("#change_reserve_date").change(function(){
			$.post('<?php echo site_url("reserve/set_change_reserve_date");?>', {change_reserve_date: $('#change_reserve_date').val()});
		});

		$('#change_reserve_date_enable').change(function()
		{
			$.post('<?php echo site_url("reserve/set_change_reserve_date_enable");?>', {change_reserve_date_enable: $('#change_reserve_date_enable').is(':checked') ? '1' : '0'});
		});

		$('#comment').change(function()
		{
			$.post('<?php echo site_url("reserve/set_comment");?>', {comment: $('#comment').val()});
		});

		$('#show_comment_on_receipt').change(function()
		{
			$.post('<?php echo site_url("reserve/set_comment_on_receipt");?>', {show_comment_on_receipt:$('#show_comment_on_receipt').is(':checked') ? '1' : '0'});
		});

		$('#email_receipt').change(function()
		{
			$.post('<?php echo site_url("reserve/set_email_receipt");?>', {email_receipt: $('#email_receipt').is(':checked') ? '1' : '0'});
		});

		$('#save_credit_card_info').change(function()
		{
			$.post('<?php echo site_url("reserve/set_save_credit_card_info");?>', {save_credit_card_info:$('#save_credit_card_info').is(':checked') ? '1' : '0'});
		});

		$('#change_reserve_date_enable').is(':checked') ? $("#change_reserve_input").show() : $("#change_reserve_input").hide();

		$('#change_reserve_date_enable').click(function() {
			if( $(this).is(':checked')) {
				$("#change_reserve_input").show();
			} else {
				$("#change_reserve_input").hide();
			}
		});

		$('#use_saved_cc_info').change(function()
		{
			$.post('<?php echo site_url("reserve/set_use_saved_cc_info");?>', {use_saved_cc_info:$('#use_saved_cc_info').is(':checked') ? '1' : '0'});
		});

		$("#finish_reserve_button").click(function()
		{
			//Prevent double submission of form
			$("#finish_reserve_button").hide();
			$("#register_container").mask(<?php echo json_encode(lang('common_wait')); ?>);

			<?php if ($is_over_credit_limit) { ?>
				if (!confirm(<?php echo json_encode(lang('reserve_over_credit_limit_warning')); ?>))
				{
					//Bring back submit and unmask if fail to confirm
					$("#finish_reserve_button").show();
					$("#register_container").unmask();

					return;
				}
			<?php } ?>

			<?php if(!$payments_cover_total) { ?>

				if (!confirm(<?php echo json_encode(lang('reserve_payment_not_cover_total_confirmation')); ?>))
				{
					//Bring back submit and unmask if fail to confirm
					$("#finish_reserve_button").show();
					$("#register_container").unmask();

					return;
				}
			<?php } ?>

			<?php if (!$this->config->item('disable_confirmation_reserve')) { ?>
				if (confirm(<?php echo json_encode(lang("reserve_confirm_finish_reserve")); ?>))
				{
					<?php } ?>

					if ($("#comment").val())
					{
						$.post('<?php echo site_url("reserve/set_comment");?>', {comment: $('#comment').val()}, function()
						{
							$('#finish_reserve_form').submit();
						});
					}
					else
					{
						$('#finish_reserve_form').submit();
					}

					<?php if (!$this->config->item('disable_confirmation_reserve')) { ?>
					}
					else
					{
						//Bring back submit and unmask if fail to confirm
						$("#finish_reserve_button").show();
						$("#register_container").unmask();
					}
					<?php } ?>
				});

		$("#suspend_reserve_button").click(function()
		{
			$(this).hide();
			$("#layaway_reserve_button").show();
			$("#estimate_reserve_button").show();
		});

		$("#layaway_reserve_button").click(function()
		{
			if (confirm(<?php echo json_encode(lang("reserve_confirm_suspend_reserve")); ?>))
			{
				$.post('<?php echo site_url("reserve/set_comment");?>', {comment: $('#comment').val()}, function() {
					<?php if ($this->config->item('show_receipt_after_suspending_reserve')) { ?>
						window.location = '<?php echo site_url("reserve/suspend"); ?>';
						<?php }else { ?>
							$("#register_container").load('<?php echo site_url("reserve/suspend"); ?>');
					<?php } ?>
				});
			}
		});

		$("#estimate_reserve_button").click(function()
		{
			if (confirm(<?php echo json_encode(lang("reserve_confirm_suspend_reserve")); ?>))
			{
				$.post('<?php echo site_url("reserve/set_comment");?>', {comment: $('#comment').val()}, function() {
					<?php if ($this->config->item('show_receipt_after_suspending_reserve')) { ?>
						window.location = '<?php echo site_url("reserve/suspend/2"); ?>';
					<?php }else { ?>
						$("#register_container").load('<?php echo site_url("reserve/suspend/2"); ?>');
					<?php } ?>
				});
			}
		});


		$("#cancel_reserve_button").click(function()
		{
			if (confirm(<?php echo json_encode(lang("reserve_confirm_cancel_reserve")); ?>))
			{
				$('#cancel_reserve_form').ajaxSubmit({target: "#register_container", beforeSubmit: reserveBeforeSubmit});
			}
		});

		$("#add_payment_button").click(function()
		{
			$('#add_payment_form').ajaxSubmit({target: "#register_container", beforeSubmit: reserveBeforeSubmit});
		});

		$("#payment_types").change(checkPaymentTypeGiftcard).ready(checkPaymentTypeGiftcard);
		$('#mode').change(function()
		{
			if ($(this).val() == "store_account_payment") { // Hiding the category grid
				$('#show_hide_grid_wrapper, #category_room_selection_wrapper').fadeOut();
			}else { // otherwise, show the categories grid
				$('#show_hide_grid_wrapper, #show_grid').fadeIn();
				$('#hide_grid').fadeOut();
			}
			$('#mode_form').ajaxSubmit({target: "#register_container", beforeSubmit: reserveBeforeSubmit});
		});

		$('.delete_room, .delete_payment, #delete_customer, .delete_tax').click(function(event)
		{
			event.preventDefault();
			$("#register_container").load($(this).attr('href'));
		});

		$("#tier_id").change(function()
		{
			$.post('<?php echo site_url("reserve/set_tier_id");?>', {tier_id: $(this).val()}, function()
			{
				$("#register_container").load('<?php echo site_url("reserve/reload"); ?>');
			});
		});

		$("#sold_by_employee_id").change(function()
		{
			$.post('<?php echo site_url("reserve/set_sold_by_employee_id");?>', {sold_by_employee_id: $(this).val()}, function()
			{
				$("#register_container").load('<?php echo site_url("reserve/reload"); ?>');
			});
		});

		$("input[type=text]").not(".description").click(function() {
			$(this).select();
		});

		if(screen.width <= 768) //set the colspan on page load
		{
			jQuery('td.edit_description').attr('colspan', '2');
			jQuery('td.edit_serialnumber').attr('colspan', '4');
		}

		 $(window).resize(function() {
			var wi = $(window).width();

			if (wi <= 768){
				jQuery('td.edit_description').attr('colspan', '2');
				jQuery('td.edit_serialnumber').attr('colspan', '4');
			}
			else {
				jQuery('td.edit_description').attr('colspan', '4');
				jQuery('td.edit_serialnumber').attr('colspan', '2');
			}
		});

		$("#new-customer").click(function()
		{
			$("body").mask(<?php echo json_encode(lang('common_wait')); ?>);
		});
	});

function checkPaymentTypeGiftcard()
{
	if ($("#payment_types").val() == <?php echo json_encode(lang('reserve_giftcard')); ?>)
	{
		$("#amount_tendered").val('');

		<?php if (!$this->agent->is_mobile()) { ?>
			$("#amount_tendered").focus();
		<?php } ?>
		<?php if (!$this->config->item('disable_giftcard_detection')) { ?>
		giftcard_swipe_field($("#amount_tendered"));
		<?php
		}
		?>
	}
}

function reserveBeforeSubmit(formData, jqForm, options)
{
	if (submitting)
	{
		return false;
	}
	submitting = true;
	$("#ajax-loader").show();
	$("#add_payment_button").hide();
	$("#finish_reserve_button").hide();
}

function itemScannedSuccess(responseText, statusText, xhr, $form)
{
	setTimeout(function(){$('#item').focus();}, 10);
}


</script>
