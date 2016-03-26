<?php
function is_reserve_integrated_cc_processing()
{
	$CI =& get_instance();
	$cc_payment_amount = $CI->reserve_lib->get_payment_amount(lang('reserve_credit'));
	return $CI->Location->get_info_for_key('enable_credit_card_processing') && $cc_payment_amount != 0;
}

function reserve_has_partial_credit_card_payment()
{
	$CI =& get_instance();
	$cc_partial_payment_amount = $CI->reserve_lib->get_payment_amount(lang('reserve_partial_credit'));
	return $cc_partial_payment_amount != 0;
}
?>
