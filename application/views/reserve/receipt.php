<?php $this->load->view("partial/header"); ?>
<?php
$is_integrated_credit_reserve = is_reserve_integrated_cc_processing();
if (isset($error_message))
{
	echo '<h1 style="text-align: center;">'.$error_message.'</h1>';
	exit;
}
?>
<div id="receipt_wrapper" class="receipt_<?php echo $this->config->item('receipt_text_size');?>">
	<div id="receipt_header">
		<?php if($this->config->item('company_logo')) {?>
		<div id="company_logo"><?php echo img(array('src' => $this->Appconfig->get_logo_image())); ?></div>
		<?php } ?>
		<div id="company_address"><?php echo nl2br($this->Location->get_info_for_key('address')); ?></div>
		<div id="company_phone"><?php echo $this->Location->get_info_for_key('phone'); ?></div>
		<?php if($this->config->item('website')) { ?>
			<div id="website"><?php echo $this->config->item('website'); ?></div>
		<?php } ?>
		<div id="sale_time"><?php echo $transaction_time ?></div>
		<div class="pull-right"><button class="btn btn-primary text-white hidden-print" id="new_sale_button_1" onclick="window.location='<?php echo site_url('reserve'); ?>'" > <?php echo lang('reserve_new_reserve'); ?> </button></div>
	</div>
	<div id="receipt_general_info">
		<?php if(isset($customer))
		{
		?>
			<div id="customer"><?php echo lang('customers_customer').": ".$customer; ?></div>
			<?php if(!empty($customer_phone)){ ?><div><?php echo lang('common_phone_number'); ?> : <?php echo $customer_phone; ?></div><?php } ?>
			<?php if(!empty($customer_address_1)){ ?><div><?php echo lang('common_address'); ?> : <?php echo $customer_address_1. ' '.$customer_address_2; ?></div><?php } ?>
			<?php if (!empty($customer_city)) { echo $customer_city.' '.$customer_state.', '.$customer_zip;} ?>
			<?php if (!empty($customer_country)) { echo '<div>'.$customer_country.'</div>';} ?>
			<?php if(!empty($customer_email)){ ?><div><?php echo lang('common_email'); ?> : <?php echo $customer_email; ?></div><?php } ?>
		<?php
		}
		?>
		<div id="reservation_id"><?php echo lang('reserve_id').": ".$reservation_id; ?></div>
		<?php if (isset($reservation_type)) { ?>
			<div id="reservation_type"><?php echo $reservation_type; ?></div>
		<?php } ?>

		<?php
		if ($register_name)
		{

		}
		?>

		<div id="date"><?php echo lang('reserve_date_from_to').": ".$reservation_from." - ".$reservation_to; ?></div>



		<div id="employee"><?php echo lang('employees_employee').": ".$employee; ?></div>
		<?php
		if($this->Location->get_info_for_key('enable_credit_card_processing'))
		{
			echo '<div id="mercahnt_id">'.lang('config_merchant_id').': '.$this->Location->get_info_for_key('merchant_id').'</div>';
		}
		?>

	</div>
	<table id="receipt_items">
	<tr>
	<th class="left_text_align" style="width:<?php echo $discount_exists ? "33%" : "49%"; ?>;"><?php echo lang('bedrooms_room'); ?></th>
	<th class="gift_receipt_element left_text_align" style="width:20%;"><?php echo lang('common_price'); ?></th>
	<th class="left_text_align" style="width:15%;"><?php echo lang('reserve_quantity'); ?></th>
	<?php if($discount_exists)
    {
	?>
	<th class="gift_receipt_element left_text_align" style="width:16%;"><?php echo lang('reserve_discount'); ?></th>
	<?php
	}
	?>
	<th  class="gift_receipt_element left_right_align" style="width:16%;"><?php echo lang('reserve_total'); ?></th>
	</tr>
	<?php
	foreach(array_reverse($cart, true) as $line=>$room)
	{
	?>
		<tr>
		<td class="left_text_align"><?php echo $room['name']; ?><?php if ($room['beds']){ ?> (<?php echo lang('bedrooms_beds')." ".$room['beds']; ?>)<?php } ?></td>
		<td class="gift_receipt_element left_text_align"><?php echo to_currency($room['price']); ?></td>
		<td class="left_text_align"><?php echo to_quantity($room['quantity']); ?></td>
		<?php if($discount_exists)
		{
		?>
		<td class="gift_receipt_element left_text_align"><?php echo $room['discount']; ?></td>
		<?php
		}
		?>
		<td class="gift_receipt_element right_text_align"><?php echo to_currency($room['price']*$room['quantity']-$room['price']*$room['quantity']*$room['discount']/100); ?></td>
		</tr>

	    <tr>
	    <td colspan="3" align="left"><?php echo $room['description']; ?></td>
		<td colspan="1" ><?php echo isset($room['serialnumber']) ? $room['serialnumber'] : ''; ?></td>

		<?php if($discount_exists) {?>
		<td colspan="1"><?php echo '&nbsp;'; ?></td>
		<?php } ?>
	    </tr>

	<?php
	}
	?>
	<tr class="gift_receipt_element">
	<td class="right_text_align" colspan="<?php echo $discount_exists ? '4' : '3'; ?>" style='border-top:2px solid #000000;'><?php echo lang('reserve_sub_total'); ?></td>
	<td class="right_text_align" colspan="1" style='border-top:2px solid #000000;'><?php echo to_currency($subtotal); ?></td>
	</tr>

	<?php if ($this->config->item('group_all_taxes_on_receipt')) { ?>
		<?php
		$total_tax = 0;
		foreach($taxes as $name=>$value)
		{
			$total_tax+=$value;
	 	}
		?>
		<tr class="gift_receipt_element">
			<td class="right_text_align" colspan="<?php echo $discount_exists ? '4' : '3'; ?>"><?php echo lang('reports_tax'); ?>:</td>
			<td class="right_text_align" colspan="1"><?php echo to_currency($total_tax); ?></td>
		</tr>

	<?php }else {?>
		<?php foreach($taxes as $name=>$value) { ?>
			<tr class="gift_receipt_element">
				<td class="right_text_align" colspan="<?php echo $discount_exists ? '4' : '3'; ?>"><?php echo $name; ?>:</td>
				<td class="right_text_align" colspan="1"><?php echo to_currency($value); ?></td>
			</tr>
		<?php }; ?>
	<?php } ?>

	<tr class="gift_receipt_element">
	<td class="right_text_align "colspan="<?php echo $discount_exists ? '4' : '3'; ?>"><?php echo lang('reserve_total'); ?></td>
	<td class="right_text_align" colspan="1"><?php echo $this->config->item('round_cash_on_sales') && $is_reserve_cash_payment ?  to_currency(round_to_nearest_05($total)) : to_currency($total); ?></td>
	</tr>

    <tr><td colspan="<?php echo $discount_exists ? '5' : '4'; ?>">&nbsp;</td></tr>

	<?php
		foreach($payments as $payment_id=>$payment)
	{ ?>
		<tr class="gift_receipt_element">
		<td class="right_text_align" colspan="<?php echo $discount_exists ? '3' : '2'; ?>"><?php echo (isset($show_payment_times) && $show_payment_times) ?  date(get_date_format().' '.get_time_format(), strtotime($payment['payment_date'])) : lang('reserve_payment'); ?></td>

		<?php if ($is_integrated_credit_reserve || reserve_has_partial_credit_card_payment()) { ?>
			<td class="right_text_align" colspan="1"><?php $splitpayment=explode(':',$payment['payment_type']); echo $splitpayment[0]; ?>: <?php echo $payment['card_issuer']. ' '.$payment['truncated_card']; ?></td>
		<?php } else { ?>
			<td class="right_text_align" colspan="1"><?php $splitpayment=explode(':',$payment['payment_type']); echo $splitpayment[0]; ?> </td>
		<?php } ?>
		<td class="right_text_align" colspan="1"><?php echo $this->config->item('round_cash_on_sales') && $payment['payment_type'] == lang('reserve_cash') ?  to_currency(round_to_nearest_05($payment['payment_amount'])) : to_currency($payment['payment_amount']); ?>  </td>
		</tr>
	<?php
	}
	?>
    <tr><td colspan="<?php echo $discount_exists ? '5' : '4'; ?>">&nbsp;</td></tr>

	<?php foreach($payments as $payment) {?>
		<?php if (strpos($payment['payment_type'], lang('reserve_giftcard'))!== FALSE) {?>
	<tr class="gift_receipt_element">
		<td class="right_text_align" colspan="<?php echo $discount_exists ? '3' : '2'; ?>"><?php echo lang('reserve_giftcard_balance'); ?></td>
		<td class="right_text_align" colspan="1"><?php echo $payment['payment_type'];?> </td>
		<?php $giftcard_payment_row = explode(':', $payment['payment_type']); ?>
		<td class="right_text_align" colspan="1"><?php echo to_currency($this->Giftcard->get_giftcard_value(end($giftcard_payment_row))); ?></td>
	</tr>
		<?php }?>
	<?php }?>

	<?php if ($amount_change >= 0) {?>
	<tr class="gift_receipt_element">
		<td class="right_text_align" colspan="<?php echo $discount_exists ? '4' : '3'; ?>"><?php echo lang('reserve_change_due'); ?></td>
		<td class="right_text_align" colspan="1">
		<?php echo $this->config->item('round_cash_on_sales')  && $is_reserve_cash_payment ?  to_currency(round_to_nearest_05($amount_change)) : to_currency($amount_change); ?> </td>
	</tr>
	<?php
	}
	else
	{
	?>
	<tr>
		<td class="right_text_align" colspan="<?php echo $discount_exists ? '4' : '3'; ?>"><?php echo lang('reserve_amount_due'); ?></td>
		<td class="right_text_align" colspan="1"><?php echo $this->config->item('round_cash_on_sales')  && $is_reserve_cash_payment ?  to_currency(round_to_nearest_05($amount_change * -1)) : to_currency($amount_change * -1); ?></td>
	</tr>
	<?php
	}
	?>
	<?php if (isset($customer_balance_for_sale) && $customer_balance_for_sale !== FALSE) {?>
	<tr>
		<td class="right_text_align" colspan="<?php echo $discount_exists ? '4' : '3'; ?>"><?php echo lang('reserve_customer_account_balance'); ?></td>
		<td class="right_text_align" colspan="1">
		<?php echo to_currency($customer_balance_for_sale); ?> </td>
	</tr>
	<?php
	}
	?>

	<?php
	if ($ref_no)
	{
	?>
	<tr>
		<td class="right_text_align" colspan="<?php echo $discount_exists ? '4' : '3'; ?>"><?php echo lang('reserve_ref_no'); ?></td>
		<td class="right_text_align" colspan="1"><?php echo $ref_no; ?></td>
	</tr>
	<?php
	}
	if (isset($auth_code) && $auth_code)
	{
	?>
	<tr>
		<td class="right_text_align" colspan="<?php echo $discount_exists ? '4' : '3'; ?>"><?php echo lang('reserve_auth_code'); ?></td>
		<td class="right_text_align" colspan="1"><?php echo $auth_code; ?></td>
	</tr>
	<?php
	}
	?>

	<tr>
		<td colspan="<?php echo $discount_exists ? '5' : '4'; ?>" align="right">
		<?php if($show_comment_on_receipt==1)
			{
				echo $comment ;
			}
		?>
		</td>
	</tr>
	</table>

	<div id="sale_return_policy">
	<?php echo nl2br($this->config->item('return_policy')); ?>
   <br />

	</div>

	<?php if (!$this->config->item('hide_barcode_on_sales_and_recv_receipt')) {?>
		<div id='barcode'>
		<?php echo "<img src='".site_url('barcode')."?barcode=$reservation_id&text=$reservation_id' />"; ?>
		</div>
	<?php } ?>
	<?php if(!$this->config->item('hide_signature')) { ?>

	<div id="signature">

	<?php foreach($payments as $payment) {?>
		<?php  if ( !empty($payment['payment_type']) && lang('reserve_credit')!='' && strpos($payment['payment_type'], lang('reserve_credit'))!== FALSE) {?>
			<?php echo lang('reserve_signature'); ?> --------------------------------- <br />
			<?php
			echo lang('reserve_card_statement');
			break;
			?>

		<?php }?>
	<?php }?>

	</div>
	<?php } ?>

	<?php
	 if (!$store_account_payment && $this->Employee->has_module_action_permission('reserve', 'edit_sale', $this->Employee->get_logged_in_employee_info()->person_id)){

	echo form_open("reserve/change_reservation/".$reservation_id_raw,array('id'=>'reservations_change_form')); ?>
	<button class="btn btn-primary text-white hidden-print" id="edit_reservation" onclick="submit()" > <?php echo lang('reserve_edit'); ?> </button>

	<?php }	?>
	</form>

<button class="btn btn-primary text-white hidden-print" id="print_button" onclick="print_receipt()" > <?php echo lang('reserve_print'); ?> </button>

<span class="hidden-print">
	<?php
	echo form_checkbox(array(
		'name'        => 'print_duplicate_receipt',
		'id'          => 'print_duplicate_receipt',
		'value'       => '1',
	)).'&nbsp;'.lang('reserve_duplicate_receipt');
		?>
</span>

<br />

<button class="btn btn-primary text-white hidden-print" id="fufillment_sheet_button" onclick="window.open('<?php echo site_url("reserve/fulfillment/$reservation_id_raw"); ?>', 'blank');" > <?php echo lang('reserve_fulfillment_sheet'); ?></button>
<br />

<button class="btn btn-primary text-white hidden-print gift_receipt" id="gift_receipt_button" onclick="toggle_gift_receipt()" > <?php echo lang('reserve_gift_receipt'); ?> </button>
<br />

	<?php if (!empty($customer_email)) { ?>
		<?php echo anchor('reserve/email_receipt/'.$reservation_id_raw, lang('reserve_email_receipt'), array('id' => 'email_receipt','class' => 'btn btn-primary hidden-print'));?>
		<br />
	<?php }?>

<button class="btn btn-primary text-white hidden-print" id="new_sale_button_2" onclick="window.location='<?php echo site_url('reserve'); ?>'" > <?php echo lang('reserve_new_reserve'); ?> </button>

</div>

<div id="duplicate_receipt_holder">

</div>
<?php $this->load->view("partial/footer"); ?>

<?php if ($this->config->item('print_after_sale'))
{
?>
<script type="text/javascript">
$(window).bind("load", function() {
	window.print();
});
</script>
<?php }  ?>

<script type="text/javascript">

$(document).ready(function(){
	$("#email_receipt").click(function()
	{
		$.get($(this).attr('href'), function()
		{
			gritter(<?php echo json_encode(lang('common_success')); ?>,'<?php echo lang('reserve_receipt_sent'); ?>','gritter-item-success',false,false);

		});

		return false;
	});
});

$('#print_duplicate_receipt').click(function()
{
	if ($('#print_duplicate_receipt').prop('checked'))
	{
	   var receipt = $('#receipt_wrapper').clone();
	   $('#duplicate_receipt_holder').html(receipt);
	}
	else
	{
		$("#duplicate_receipt_holder").empty();
	}
});

function print_receipt()
 {
 	window.print();
 }

 function toggle_gift_receipt()
 {
	 var gift_receipt_text = <?php echo json_encode(lang('reserve_gift_receipt')); ?>;
	 var regular_receipt_text = <?php echo json_encode(lang('reserve_regular_receipt')); ?>;

	 if ($("#gift_receipt_button").hasClass('regular_receipt'))
	 {
		 $('#gift_receipt_button').addClass('gift_receipt');
		 $('#gift_receipt_button').removeClass('regular_receipt');
		 $("#gift_receipt_button").text(gift_receipt_text);
		 $('.gift_receipt_element').show();
	 }
	 else
	 {
		 $('#gift_receipt_button').removeClass('gift_receipt');
		 $('#gift_receipt_button').addClass('regular_receipt');
		 $("#gift_receipt_button").text(regular_receipt_text);
		 $('.gift_receipt_element').hide();
	 }

 }
</script>

<?php if($is_integrated_credit_reserve ) { ?>
<script type="text/javascript">
gritter(<?php echo json_encode(lang('common_success')); ?>, <?php echo json_encode(lang('reserve_credit_card_processing_success'))?>, 'gritter-item-success',false,false);
</script>
<?php } ?>

<!-- This is used for mobile apps to print receipt-->
<script type="text/print" id="print_output"><?php echo $this->config->item('company'); ?>

<?php echo $this->Location->get_info_for_key('address'); ?>

<?php echo $this->Location->get_info_for_key('phone'); ?>

<?php if($this->config->item('website')) { ?>
	<?php echo $this->config->item('website'); ?>
<?php } ?>

<?php echo $receipt_title; ?>

<?php echo $transaction_time; ?>

<?php if(isset($customer))
{
?>
<?php echo lang('customers_customer').": ".$customer; ?>

<?php if(!empty($customer_address_1)){ ?><?php echo lang('common_address'); ?>: <?php echo $customer_address_1. ' '.$customer_address_2; ?>

<?php } ?>
<?php if (!empty($customer_city)) { echo $customer_city.' '.$customer_state.', '.$customer_zip; ?>

<?php } ?>
<?php if (!empty($customer_country)) { echo $customer_country; ?>

<?php } ?>
<?php if(!empty($customer_phone)){ ?><?php echo lang('common_phone_number'); ?> : <?php echo $customer_phone; ?>

<?php } ?>
<?php if(!empty($customer_email)){ ?><?php echo lang('common_email'); ?> : <?php echo $customer_email; ?><?php } ?>

<?php
}
?>
<?php echo lang('reserve_id').": ".$reservation_id; ?>

<?php if (isset($reservation_type)) { ?>
<?php echo $reservation_type; ?>
<?php } ?>

<?php echo lang('employees_employee').": ".$employee; ?>

<?php
if($this->Location->get_info_for_key('enable_credit_card_processing'))
{
	echo lang('config_merchant_id').': '.$this->Location->get_info_for_key('merchant_id');
}
?>

<?php echo lang('bedrooms_room'); ?>            <?php echo lang('common_price'); ?> <?php echo lang('reserve_quantity'); ?><?php if($discount_exists){echo ' '.lang('reserve_discount');}?> <?php echo lang('reserve_total'); ?>

---------------------------------------
<?php
foreach(array_reverse($cart, true) as $line=>$room)
{
?>
<?php echo character_limiter($room['name'], 14,'...'); ?><?php echo strlen($room['name']) < 14 ? str_repeat(' ', 14 - strlen($room['name'])) : ''; ?> <?php echo str_replace('&#8209;', '-', to_currency($room['price'])); ?> <?php echo to_quantity($room['quantity']); ?><?php if($discount_exists){echo ' '.$room['discount'];}?> <?php echo str_replace('&#8209;', '-', to_currency($room['price']*$room['quantity']-$room['price']*$room['quantity']*$room['discount']/100)); ?>

  <?php echo $room['description']; ?>  <?php echo isset($room['serialnumber']) ? $room['serialnumber'] : ''; ?>


<?php
}
?>

<?php echo lang('reserve_sub_total'); ?>: <?php echo str_replace('&#8209;', '-', to_currency($subtotal)); ?>


<?php foreach($taxes as $name=>$value) { ?>
<?php echo $name; ?>: <?php echo str_replace('&#8209;', '-', to_currency($value)); ?>

<?php }; ?>

<?php echo lang('reserve_total'); ?>: <?php echo $this->config->item('round_cash_on_sales') && $is_reserve_cash_payment ?  str_replace('&#8209;', '-', to_currency(round_to_nearest_05($total))) : str_replace('&#8209;', '-', to_currency($total)); ?>

<?php
	foreach($payments as $payment_id=>$payment)
{ ?>

<?php echo (isset($show_payment_times) && $show_payment_times) ?  date(get_date_format().' '.get_time_format(), strtotime($payment['payment_date'])) : lang('reserve_payment'); ?>  <?php if ($is_integrated_credit_reserve || reserve_has_partial_credit_card_payment()) { ?><?php $splitpayment=explode(':',$payment['payment_type']);echo $splitpayment[0]; ?>: <?php echo $payment['card_issuer']. ' '.$payment['truncated_card']; ?> <?php } else { ?><?php $splitpayment=explode(':',$payment['payment_type']); echo $splitpayment[0]; ?> <?php } ?><?php echo $this->config->item('round_cash_on_sales') && $payment['payment_type'] == lang('reserve_cash') ?  str_replace('&#8209;', '-', to_currency(round_to_nearest_05($payment['payment_amount']))) : str_replace('&#8209;', '-', to_currency($payment['payment_amount'])); ?>
<?php
}
?>

<?php foreach($payments as $payment) { $giftcard_payment_row = explode(':', $payment['payment_type']);?>
<?php if (strpos($payment['payment_type'], lang('reserve_giftcard'))!== FALSE) {?><?php echo lang('reserve_giftcard_balance'); ?>  <?php echo $payment['payment_type'];?>: <?php echo str_replace('&#8209;', '-', to_currency($this->Giftcard->get_giftcard_value(end($giftcard_payment_row)))); ?>
	<?php }?>
<?php }?>

<?php if ($amount_change >= 0) {?>
<?php echo lang('reserve_change_due'); ?>: <?php echo $this->config->item('round_cash_on_sales')  && $is_reserve_cash_payment ?  str_replace('&#8209;', '-', to_currency(round_to_nearest_05($amount_change))) : str_replace('&#8209;', '-', to_currency($amount_change)); ?>
<?php
}
else
{
?>
<?php echo lang('reserve_amount_due'); ?>: <?php echo $this->config->item('round_cash_on_sales')  && $is_reserve_cash_payment ?  str_replace('&#8209;', '-', to_currency(round_to_nearest_05($amount_change * -1))) : str_replace('&#8209;', '-', to_currency($amount_change * -1)); ?>
<?php
}
?>
<?php if (isset($customer_balance_for_sale) && $customer_balance_for_sale !== FALSE) {?>

<?php echo lang('reserve_customer_account_balance'); ?>: <?php echo to_currency($customer_balance_for_sale); ?>
<?php
}
?>
<?php
if ($ref_no)
{
?>

<?php echo lang('reserve_ref_no'); ?>: <?php echo $ref_no; ?>
<?php
}
if (isset($auth_code) && $auth_code)
{
?>

<?php echo lang('reserve_auth_code'); ?>: <?php echo $auth_code; ?>
<?php
}
?>
<?php if($show_comment_on_receipt==1){echo $comment;} ?>

<?php $this->config->item('return_policy'); ?>

<?php if(!$this->config->item('hide_signature')) { ?>
<?php foreach($payments as $payment) {?>
	<?php if (strpos($payment['payment_type'], lang('reserve_credit'))!== FALSE) {?>

	<?php echo lang('reserve_signature'); ?>:
---------------------------------------
<?php
echo lang('reserve_card_statement');
break;
?><?php }?><?php }?><?php } ?></script>
