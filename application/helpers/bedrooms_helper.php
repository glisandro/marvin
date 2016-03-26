<?php
function get_bedrooms_barcode_data($room_ids)
{
	$CI =& get_instance();
	$result = array();

	$room_ids = explode('~', $room_ids);
	foreach ($room_ids as $room_id)
	{
		$room_info = $CI->Room->get_info($room_id);
		$room_location_info = $CI->Room_location->get_info($room_id);

		$today =  strtotime(date('Y-m-d'));
		$is_room_location_promo = ($room_location_info->start_date !== NULL && $room_location_info->end_date !== NULL) && (strtotime($room_location_info->start_date) <= $today && strtotime($room_location_info->end_date) >= $today);
		$is_room_promo = ($room_info->start_date !== NULL && $room_info->end_date !== NULL) && (strtotime($room_info->start_date) <= $today && strtotime($room_info->end_date) >= $today);

		$regular_room_price = $room_location_info->unit_price ? $room_location_info->unit_price : $room_info->unit_price;


		if ($is_room_location_promo)
		{
			$room_price = $room_location_info->promo_price;
		}
		elseif ($is_room_promo)
		{
			$room_price = $room_info->promo_price;
		}
		else
		{
			$room_price = $room_location_info->unit_price ? $room_location_info->unit_price : $room_info->unit_price;
		}

		if($CI->config->item('barcode_price_include_tax'))
		{
			if($room_info->tax_included)
			{
				$result[] = array('name' => ($is_room_location_promo || $is_room_promo ? '<span style="text-decoration: line-through;">'.to_currency($regular_room_price).'</span> ' : ' ').to_currency($room_price).'<br>'.$room_info->name, 'id'=> number_pad($room_id, 10));
			}
			else
			{
				$result[] = array('name' => ($is_room_location_promo || $is_room_promo ? '<span style="text-decoration: line-through;">'.to_currency(get_price_for_room_including_taxes($room_id,$regular_room_price)).'</span> ' : ' ').to_currency(get_price_for_room_including_taxes($room_id,$room_price)).'<br>'.$room_info->name, 'id'=> number_pad($room_id, 10));

	  	 	}
	  }
	  else
	  {

				$result[] = array('name' => ($is_room_location_promo || $is_room_promo ? '<span style="text-decoration: line-through;">'.to_currency($regular_room_price).'</span> ' : ' ').to_currency($room_price).'<br>'.$room_info->name, 'id'=> number_pad($room_id, 10));
		}
	}
	return $result;
}

function get_price_for_room_excluding_taxes($room_id_or_line, $room_price_including_tax, $recive_id = FALSE)
{
	$return = FALSE;
	$CI =& get_instance();

	if ($recive_id !== FALSE)
	{
		$tax_info = $CI->Sale->get_recive_bedrooms_taxes($recive_id, $room_id_or_line);
	}
	else
	{
		$tax_info = $CI->Room_taxes_finder->get_info($room_id_or_line);
	}

	if (count($tax_info) == 2 && $tax_info[1]['cumulative'] == 1)
	{
		$return = $room_price_including_tax/(1+($tax_info[0]['percent'] /100) + ($tax_info[1]['percent'] /100) + (($tax_info[0]['percent'] /100) * (($tax_info[1]['percent'] /100))));
	}
	else //0 or more taxes NOT cumulative
	{
		$total_tax_percent = 0;

		foreach($tax_info as $tax)
		{
			$total_tax_percent+=$tax['percent'];
		}

		$return = $room_price_including_tax/(1+($total_tax_percent /100));
	}

	if ($return !== FALSE)
	{
		return to_currency_no_money($return, 10);
	}

	return FALSE;
}

function get_price_for_room_including_taxes($room_id_or_line, $room_price_excluding_tax, $recive_id = FALSE)
{
	$return = FALSE;
	$CI =& get_instance();
	if ($recive_id !== FALSE)
	{
		$tax_info = $CI->Sale->get_recive_bedrooms_taxes($recive_id,$room_id_or_line);
	}
	else
	{
		$tax_info = $CI->Room_taxes_finder->get_info($room_id_or_line);
	}

	if (count($tax_info) == 2 && $tax_info[1]['cumulative'] == 1)
	{
		$first_tax = ($room_price_excluding_tax*($tax_info[0]['percent']/100));
		$second_tax = ($room_price_excluding_tax + $first_tax) *($tax_info[1]['percent']/100);
		$return = $room_price_excluding_tax + $first_tax + $second_tax;
	}
	else //0 or more taxes NOT cumulative
	{
		$total_tax_percent = 0;

		foreach($tax_info as $tax)
		{
			$total_tax_percent+=$tax['percent'];
		}

		$return = $room_price_excluding_tax*(1+($total_tax_percent /100));
	}


	if ($return !== FALSE)
	{
		return to_currency_no_money($return, 10);
	}

	return FALSE;
}

function get_commission_for_room($room_id, $price, $quantity,$discount)
{
	$CI =& get_instance();
	$CI->load->library('reserve_lib');

	$employee_id=$CI->reserve_lib->get_sold_by_employee_id();
	$reservation_person_info = $CI->Employee->get_info($employee_id);
	$employee_id=$CI->Employee->get_logged_in_employee_info()->person_id;
	$logged_in_employee_info = $CI->Employee->get_info($employee_id);

	$room_info = $CI->Room->get_info($room_id);

	if ($room_info->commission_fixed > 0)
	{
		return $quantity*$room_info->commission_fixed;
	}
	elseif($room_info->commission_percent > 0)
	{
		return to_currency_no_money(($price*$quantity-$price*$quantity*$discount/100)*($room_info->commission_percent/100));
	}
	elseif($CI->config->item('select_reservation_person_during_sale'))
	{
		if($reservation_person_info->commission_percent > 0)
		{
			return to_currency_no_money(($price*$quantity-$price*$quantity*$discount/100)*((float)($reservation_person_info->commission_percent)/100));
		}
		return to_currency_no_money(($price*$quantity-$price*$quantity*$discount/100)*((float)($CI->config->item('commission_default_rate'))/100));
	}
	elseif($logged_in_employee_info->commission_percent > 0)
	{
		return to_currency_no_money(($price*$quantity-$price*$quantity*$discount/100)*((float)($logged_in_employee_info->commission_percent)/100));
	}
	else
	{
		return to_currency_no_money(($price*$quantity-$price*$quantity*$discount/100)*((float)($CI->config->item('commission_default_rate'))/100));
	}
}
?>
