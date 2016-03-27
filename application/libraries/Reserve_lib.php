<?php
class Reserve_lib
{
	var $CI;

	//This is used when we need to change the reserve state and restore it before changing it (The case of showing a receipt in the middle of a reserve)
	var $reserve_state;
  	function __construct()
	{
		$this->CI =& get_instance();
		$this->reserve_state = array();
	}

	function get_cart()
	{
		if($this->CI->session->userdata('reserve_cart') === false)
			$this->set_cart(array());

		return $this->CI->session->userdata('reserve_cart');
	}

	function set_cart($cart_data)
	{
		$this->CI->session->set_userdata('reserve_cart',$cart_data);
	}

	//Alain Multiple Payments
	function get_payments()
	{
		if($this->CI->session->userdata('reserve_payments') === false)
			$this->set_payments(array());

		return $this->CI->session->userdata('reserve_payments');
	}

	//Alain Multiple Payments
	function set_payments($payments_data)
	{
		$this->CI->session->set_userdata('reserve_payments',$payments_data);
	}

	function change_credit_card_payments_to_partial()
	{
		$payments=$this->get_payments();

		foreach($payments as $payment_id=>$payment)
		{
			//If we have a credit payment, change it to partial credit card so we can process again
			if ($payment['payment_type'] == lang('reserve_credit'))
			{
				$payments[$payment_id] =  array(
					'payment_type'=>lang('reserve_partial_credit'),
					'payment_amount'=>$payment['payment_amount'],
					'payment_date' => $payment['payment_date'] !== FALSE ? $payment['payment_date'] : date('Y-m-d H:i:s'),
					'truncated_card' => $payment['truncated_card'],
					'card_issuer' => $payment['card_issuer'],
				);
			}
		}

		$this->set_payments($payments);
	}

	function get_change_reserve_date()
	{
		return $this->CI->session->userdata('change_reserve_date') ? $this->CI->session->userdata('change_reserve_date') : '';
	}
	
	function get_star_date()
	{
		return $this->CI->session->userdata('star_date') ? $this->CI->session->userdata('star_date') : '';
	}
	
	function get_end_date()
	{
		return $this->CI->session->userdata('end_date') ? $this->CI->session->userdata('end_date') : '';
	}
	
	function clear_star_date()
	{
		$this->CI->session->unset_userdata('star_date');
	}
	
	function clear_end_date()
	{
		$this->CI->session->unset_userdata('end_date');
	}
	
	function clear_change_reserve_date()
	{
		$this->CI->session->unset_userdata('change_reserve_date');

	}
	function clear_change_reserve_date_enable()
	{
		$this->CI->session->unset_userdata('change_reserve_date_enable');
	}
	function set_change_reserve_date_enable($change_reserve_date_enable)
	{
		$this->CI->session->set_userdata('change_reserve_date_enable',$change_reserve_date_enable);
	}

	function get_change_reserve_date_enable()
	{
		return $this->CI->session->userdata('change_reserve_date_enable') ? $this->CI->session->userdata('change_reserve_date_enable') : '';
	}

	function set_change_reserve_date($change_reserve_date)
	{
		$this->CI->session->set_userdata('change_reserve_date',$change_reserve_date);
	}

	function get_comment()
	{
		return $this->CI->session->userdata('comment') ? $this->CI->session->userdata('comment') : '';
	}

	function get_comment_on_receipt()
	{
		return $this->CI->session->userdata('show_comment_on_receipt') ? $this->CI->session->userdata('show_comment_on_receipt') : '';
	}

	function set_comment($comment)
	{
		$this->CI->session->set_userdata('comment', $comment);
	}

	function get_selected_tier_id()
	{
		return $this->CI->session->userdata('selected_tier_id') ? $this->CI->session->userdata('selected_tier_id') : FALSE;
	}

	function get_previous_tier_id()
	{
		return $this->CI->session->userdata('previous_tier_id') ? $this->CI->session->userdata('previous_tier_id') : FALSE;
	}

	function set_selected_tier_id($tier_id)
	{
		$this->CI->session->set_userdata('previous_tier_id', $this->get_selected_tier_id());
		$this->CI->session->set_userdata('selected_tier_id', $tier_id);
		$this->change_price();
	}

	function clear_selected_tier_id()
	{
		$this->CI->session->unset_userdata('previous_tier_id');
		$this->CI->session->unset_userdata('selected_tier_id');
	}


	function set_comment_on_receipt($comment_on_receipt)
	{
		$this->CI->session->set_userdata('show_comment_on_receipt', $comment_on_receipt);
	}

	function clear_comment()
	{
		$this->CI->session->unset_userdata('comment');

	}

	function clear_show_comment_on_receipt()
	{
		$this->CI->session->unset_userdata('show_comment_on_receipt');

	}

	function get_email_receipt()
	{
		return $this->CI->session->userdata('email_receipt');
	}

	function set_email_receipt($email_receipt)
	{
		$this->CI->session->set_userdata('email_receipt', $email_receipt);
	}

	function clear_email_receipt()
	{
		$this->CI->session->unset_userdata('email_receipt');
	}

	function get_deleted_taxes()
	{
		$deleted_taxes = $this->CI->session->userdata('deleted_taxes') ? $this->CI->session->userdata('deleted_taxes') : array();
		return $deleted_taxes;
	}

	function add_deleted_tax($name)
	{
		$deleted_taxes = $this->CI->session->userdata('deleted_taxes') ? $this->CI->session->userdata('deleted_taxes') : array();

		if (!in_array($name, $deleted_taxes))
		{
			$deleted_taxes[] = $name;
		}
		$this->CI->session->set_userdata('deleted_taxes', $deleted_taxes);
	}

	function set_deleted_taxes($deleted_taxes)
	{
		$this->CI->session->set_userdata('deleted_taxes', $deleted_taxes);
	}

	function clear_deleted_taxes()
	{
		$this->CI->session->unset_userdata('deleted_taxes');
	}

	function get_save_credit_card_info()
	{
		return $this->CI->session->userdata('save_credit_card_info');
	}

	function set_save_credit_card_info($save_credit_card_info)
	{
		$this->CI->session->set_userdata('save_credit_card_info', $save_credit_card_info);
	}

	function clear_save_credit_card_info()
	{
		$this->CI->session->unset_userdata('save_credit_card_info');
	}

	function get_use_saved_cc_info()
	{
		return $this->CI->session->userdata('use_saved_cc_info');
	}

	function set_use_saved_cc_info($use_saved_cc_info)
	{
		$this->CI->session->set_userdata('use_saved_cc_info', $use_saved_cc_info);
	}

	function clear_use_saved_cc_info()
	{
		$this->CI->session->unset_userdata('use_saved_cc_info');
	}

	function get_partial_transactions()
	{
		return $this->CI->session->userdata('partial_transactions');
	}

	function set_partial_transactions($partial_transactions)
	{
		$this->CI->session->set_userdata('partial_transactions', $partial_transactions);
	}

	function add_partial_transaction($partial_transaction)
	{
		$partial_transactions = $this->CI->session->userdata('partial_transactions');
		$partial_transactions[] = $partial_transaction;
		$this->CI->session->set_userdata('partial_transactions', $partial_transactions);
	}

	function delete_partial_transactions()
	{
		$this->CI->session->unset_userdata('partial_transactions');
	}


	function get_sold_by_employee_id()
	{
		if ($this->CI->config->item('default_reserves_person') != 'not_set' && !$this->CI->session->userdata('sold_by_employee_id'))
		{
			$employee_id=$this->CI->Employee->get_logged_in_employee_info()->person_id;
			return $employee_id;
		}
		return $this->CI->session->userdata('sold_by_employee_id') ? $this->CI->session->userdata('sold_by_employee_id') : NULL;
	}

	function set_sold_by_employee_id($sold_by_employee_id)
	{
		$this->CI->session->set_userdata('sold_by_employee_id', $sold_by_employee_id);
	}
	
	function set_star_and_end_date($star_date, $end_date)
	{
		$this->CI->session->set_userdata('star_date', $star_date);
		$this->CI->session->set_userdata('end_date', $end_date);
	}

	function clear_sold_by_employee_id()
	{
		$this->CI->session->unset_userdata('sold_by_employee_id');
	}

	function add_payment($payment_type,$payment_amount,$payment_date = false, $truncated_card = '', $card_issuer = '')
	{
			$payments=$this->get_payments();
			$payment = array(
				'payment_type'=>$payment_type,
				'payment_amount'=>$payment_amount,
				'payment_date' => $payment_date !== FALSE ? $payment_date : date('Y-m-d H:i:s'),
				'truncated_card' => $truncated_card,
				'card_issuer' => $card_issuer,
			);

			$payments[]=$payment;
			$this->set_payments($payments);
			return true;
	}

	function edit_payment($payment_id, $payment_type, $payment_amount,$payment_date = false, $truncated_card = '', $card_issuer = '')
	{
		$payments=$this->get_payments();
		$payment = array(
			'payment_type'=>$payment_type,
			'payment_amount'=>$payment_amount,
			'payment_date' => $payment_date !== FALSE ? $payment_date : date('Y-m-d H:i:s'),
			'truncated_card' => $truncated_card,
			'card_issuer' => $card_issuer,
		);

		$payments[$payment_id]=$payment;
		$this->set_payments($payments);
		return true;
	}

	public function get_payment_ids($payment_type)
	{
		$payment_ids = array();

		$payments=$this->get_payments();

		for($k=0;$k<count($payments);$k++)
		{
			if ($payments[$k]['payment_type'] == $payment_type)
			{
				$payment_ids[] = $k;
			}
		}

		return $payment_ids;
	}

	public function get_payment_amount($payment_type)
	{
		$payment_amount = 0;
		if (($payment_ids = $this->get_payment_ids($payment_type)) !== FALSE)
		{
			$payments=$this->get_payments();

			foreach($payment_ids as $payment_id)
			{
				$payment_amount += $payments[$payment_id]['payment_amount'];
			}
		}

		return $payment_amount;
	}

	//Alain Multiple Payments
	function delete_payment($payment_ids)
	{
		$payments=$this->get_payments();
		if (is_array($payment_ids))
		{
			foreach($payment_ids as $payment_id)
			{
				unset($payments[$payment_id]);
			}
		}
		else
		{
			unset($payments[$payment_ids]);
		}
		$this->set_payments(array_values($payments));
	}

	function get_price_for_room($room_id, $tier_id = FALSE)
	{
		if ($tier_id === FALSE)
		{
			$tier_id = $this->get_selected_tier_id();
		}

		$room_info = $this->CI->Room->get_info($room_id);
		$room_location_info = $this->CI->Room_location->get_info($room_id);

		$room_tier_row = $this->CI->Room->get_tier_price_row($tier_id, $room_id);
		$room_location_tier_row = $this->CI->Room_location->get_tier_price_row($tier_id, $room_id, $this->CI->Employee->get_logged_in_employee_current_location_id());

		if (!empty($room_location_tier_row) && $room_location_tier_row->unit_price)
		{
			return to_currency_no_money($room_location_tier_row->unit_price, $this->CI->config->item('round_tier_prices_to_2_decimals') ? 2 : 10);
		}
		elseif (!empty($room_location_tier_row) && $room_location_tier_row->percent_off)
		{
			$room_unit_price = $room_location_info->unit_price ? $room_location_info->unit_price : $room_info->unit_price;
			return to_currency_no_money($room_unit_price *(1-($room_location_tier_row->percent_off/100)), $this->CI->config->item('round_tier_prices_to_2_decimals') ? 2 : 10);
		}
		elseif (!empty($room_tier_row) && $room_tier_row->unit_price)
		{
			return to_currency_no_money($room_tier_row->unit_price, $this->CI->config->item('round_tier_prices_to_2_decimals') ? 2 : 10);
		}
		elseif (!empty($room_tier_row) && $room_tier_row->percent_off)
		{
			$room_unit_price = $room_location_info->unit_price ? $room_location_info->unit_price : $room_info->unit_price;
			return to_currency_no_money($room_unit_price *(1-($room_tier_row->percent_off/100)), $this->CI->config->item('round_tier_prices_to_2_decimals') ? 2 : 10);
		}
		else
		{
			$today =  strtotime(date('Y-m-d'));
			$is_room_location_promo = ($room_location_info->start_date !== NULL && $room_location_info->end_date !== NULL) && (strtotime($room_location_info->start_date) <= $today && strtotime($room_location_info->end_date) >= $today);
			$is_room_promo = ($room_info->start_date !== NULL && $room_info->end_date !== NULL) && (strtotime($room_info->start_date) <= $today && strtotime($room_info->end_date) >= $today);

			if ($is_room_location_promo)
			{
				return to_currency_no_money($room_location_info->promo_price, 10);
			}
			elseif ($is_room_promo)
			{
				return to_currency_no_money($room_info->promo_price, 10);
			}
			else
			{
				$room_unit_price = $room_location_info->unit_price ? $room_location_info->unit_price : $room_info->unit_price;
				return to_currency_no_money($room_unit_price, 10);
			}
		}

	}



	function empty_payments()
	{
		$this->CI->session->unset_userdata('reserve_payments');
	}

	//Alain Multiple Payments
	function get_payments_totals_excluding_store_account()
	{
		$subtotal = 0;
		foreach($this->get_payments() as $payments)
		{
		    if($payments['payment_type'] != lang('reserve_store_account'))
			{
		    	$subtotal+=$payments['payment_amount'];
			}
		}
		return to_currency_no_money($subtotal);
	}

	function get_payments_totals()
	{
		$subtotal = 0;
		foreach($this->get_payments() as $payments)
		{
			$subtotal+=$payments['payment_amount'];
		}

		return to_currency_no_money($subtotal);
	}

	//Alain Multiple Payments
	function get_amount_due($reservation_id = false)
	{
		$amount_due=0;
		$payment_total = $this->get_payments_totals();
		$reserves_total=$this->get_total($reservation_id);
		$amount_due=to_currency_no_money($reserves_total - $payment_total);
		return $amount_due;
	}

	function get_amount_due_round($reservation_id = false)
	{
		$amount_due=0;
		$payment_total = $this->get_payments_totals();
		$reserves_total= $this->CI->config->item('round_cash_on_reserves') ?  round_to_nearest_05($this->get_total($reservation_id)) : $this->get_total($reservation_id);
		$amount_due=to_currency_no_money($reserves_total - $payment_total);
		return $amount_due;
	}

	function get_customer()
	{
		if(!$this->CI->session->userdata('customer'))
			$this->set_customer(-1);

		return $this->CI->session->userdata('customer');
	}

	function set_customer($customer_id)
	{
		if (is_numeric($customer_id))
		{
			$this->CI->session->set_userdata('customer',$customer_id);
			$this->change_price();
		}
	}

	function get_mode()
	{
		if(!$this->CI->session->userdata('reserve_mode'))
			$this->set_mode('reserve');

		return $this->CI->session->userdata('reserve_mode');
	}

	function set_mode($mode)
	{
		$this->CI->session->set_userdata('reserve_mode',$mode);
	}

	/*
	* This function is called when a customer added or tier changed
	* It scans item and item kits to see if there price is at a default value
	* If a price is at a default value, it is changed to match the tier
	*/
	function change_price()
	{
		$bedrooms = $this->get_cart();
		foreach ($bedrooms as $room )
		{
			if (isset($room['room_id']))
			{
				$line=$room['line'];
				$price=$room['price'];
				$room_id=$room['room_id'];
				$room_info = $this->CI->Room->get_info($room_id);
				$room_location_info = $this->CI->Room_location->get_info($room_id);
				$previous_price = FALSE;

				if ($previous_tier_id = $this->get_previous_tier_id())
				{
					$previous_price = $this->get_price_for_room($room_id, $previous_tier_id);
				}
				$previous_price = to_currency_no_money($previous_price, 10);
				$price = to_currency_no_money($price, 10);

				if($price==$room_info->unit_price || $price == $room_location_info->unit_price || $price == $previous_price )
				{
					$bedrooms[$line]['price']= $this->get_price_for_room($room_id);
				}
			}

		}
		$this->set_cart($bedrooms);
	}
	function add_room($room_id,$quantity=1,$discount=0,$price=null,$description=null,$serialnumber=null, $force_add = FALSE, $line = FALSE)
	{
		$store_account_room_id = $this->CI->Room->get_store_account_room_id();

		//Do NOT allow item to get added unless in store_account_payment mode
		if (!$force_add && $this->get_mode() !=='store_account_payment' && $store_account_room_id == $room_id)
		{
			return FALSE;
		}

		//make sure item exists
		if(!$this->CI->Room->exists(is_numeric($room_id) ? (int)$room_id : -1))
		{
			//try to get item id given an room_number
			$room_id = $this->CI->Room->get_room_id($room_id);

			if(!$room_id)
				return false;
		}
		else
		{
			$room_id = (int)$room_id;
		}

		$room_info = $this->CI->Room->get_info($room_id);

		//Alain Serialization and Description

		//Get all items in the cart so far...
		$bedrooms = $this->get_cart();

        //We need to loop through all items in the cart.
        //If the item is already there, get it's key($updatekey).
        //We also need to get the next key that we are going to use in case we need to add the
        //item to the cart. Since items can be deleted, we can't use a count. we use the highest key + 1.

        $maxkey=0;                       //Highest key so far
        $roomalreadyinreserve=FALSE;        //We did not find the item yet.
		$insertkey=0;                    //Key to use for new entry.
		$updatekey=0;                    //Key to use to update(quantity)

		foreach ($bedrooms as $room)
		{
            //We primed the loop so maxkey is 0 the first time.
            //Also, we have stored the key in the element itself so we can compare.

			if($maxkey <= $room['line'])
			{
				$maxkey = $room['line'];
			}

			if(isset($room['room_id']) && $room['room_id']==$room_id)
			{
				$roomalreadyinreserve=TRUE;
				$updatekey=$room['line'];

				if($room_info->description==$bedrooms[$updatekey]['description'] && $room_info->name==lang('reserve_giftcard'))
				{
					return false;
				}
			}
		}

		$insertkey=$maxkey+1;

	     $today =  strtotime(date('Y-m-d'));
	     $price_to_use= $this->get_price_for_room($room_id);

		//array/cart records are identified by $insertkey and room_id is just another field.
		$room = array(($line === FALSE ? $insertkey : $line)=>
		array(
			'room_id'=>$room_id,
			'line'=>$line === FALSE ? $insertkey : $line,
			'name'=>$room_info->name,
			'beds' => $room_info->beds,
			'room_number'=>$room_info->room_number,
			'description'=>$description!=null ? $description: $room_info->description,
			'serialnumber'=>$serialnumber!=null ? $serialnumber: '',
			'quantity'=>$quantity,
            'discount'=>$discount,
			'price'=>$price!=null ? $price:$price_to_use
			)
		);

		//Item already exists and is not serialized, add to quantity
		if($roomalreadyinreserve  )
		{
			$bedrooms[$line === FALSE ? $updatekey : $line]['quantity']+=$quantity;
		}
		else
		{
			//add to existing array
			$bedrooms+=$room;
		}

		$this->set_cart($bedrooms);
		return true;

	}



	function discount_all($percent_discount)
	{
		$bedrooms = $this->get_cart();

		foreach(array_keys($bedrooms) as $key)
		{
			$bedrooms[$key]['discount'] = $percent_discount;
		}
		$this->set_cart($bedrooms);
		return true;
	}

	function out_of_stock($room_id)
	{
		//make sure item exists
		if(!$this->CI->Room->exists($room_id))
		{
			//try to get item id given an room_number
			$room_id = $this->CI->Room->get_room_id($room_id);

			if(!$room_id)
				return false;
		}

		$room_location_quantity = $this->CI->Room_location->get_location_quantity($room_id);
		$quanity_added = $this->get_quantity_already_added($room_id);

		//If $room_location_quantity is NULL we don't track quantity
		if ($room_location_quantity !== NULL && $room_location_quantity - $quanity_added < 0)
		{
			return true;
		}

		return false;
	}



	function get_quantity_already_added($room_id)
	{
		$bedrooms = $this->get_cart();
		$quanity_already_added = 0;
		foreach ($bedrooms as $room)
		{
			if(isset($room['room_id']) && $room['room_id']==$room_id)
			{
				$quanity_already_added+=$room['quantity'];
			}
		}

		return $quanity_already_added;
	}



	function get_room_id($line_to_get)
	{
		$bedrooms = $this->get_cart();

		foreach ($bedrooms as $line=>$room)
		{
			if($line==$line_to_get)
			{
				return isset($room['room_id']) ? $room['room_id'] : -1;
			}
		}

		return -1;
	}





	function edit_room($line,$description = FALSE,$serialnumber = FALSE,$quantity = FALSE,$discount = FALSE,$price = FALSE)
	{
		$bedrooms = $this->get_cart();
		if(isset($bedrooms[$line]))
		{
			if ($description !== FALSE ) {
				$bedrooms[$line]['description'] = $description;
			}
			if ($serialnumber !== FALSE ) {
				$bedrooms[$line]['serialnumber'] = $serialnumber;
			}
			if ($quantity !== FALSE ) {
				$bedrooms[$line]['quantity'] = $quantity;
			}
			if ($discount !== FALSE ) {
				$bedrooms[$line]['discount'] = $discount;
			}
			if ($price !== FALSE ) {
				$bedrooms[$line]['price'] = $price;
			}

			$this->set_cart($bedrooms);

			return true;
		}

		return false;
	}

	function is_valid_receipt($receipt_reservation_id)
	{
		//Valid receipt syntax
		if(strpos(strtolower($receipt_reservation_id), strtolower($this->CI->config->item('reserve_prefix')).' ') !== FALSE)
		{
			//Extract the id
			$reservation_id = substr(strtolower($receipt_reservation_id), strpos(strtolower($receipt_reservation_id),$this->CI->config->item('reserve_prefix').' ') + strlen(strtolower($this->CI->config->item('reserve_prefix')).' '));
			return $this->CI->Reservation->exists($reservation_id);
		}

		return false;
	}



	function return_entire_reserve($receipt_reservation_id)
	{
		//POS #
		$reservation_id = substr(strtolower($receipt_reservation_id), strpos(strtolower($receipt_reservation_id),$this->CI->config->item('reserve_prefix').' ') + strlen(strtolower($this->CI->config->item('reserve_prefix')).' '));

		$this->empty_cart();
		$this->delete_customer();
		$reserve_taxes = $this->get_taxes($reservation_id);

		foreach($this->CI->Reservation->get_reservations_bedrooms($reservation_id)->result() as $row)
		{
			$room_info = $this->CI->Room->get_info($row->room_id);
			$price_to_use = $row->room_unit_price;
			//If we have tax included, but we don't have any taxes for reserve, pretend that we do have taxes so the right price shows up
			if (empty($reserve_taxes))
			{
				$price_to_use = get_price_for_room_including_taxes($row->room_id, $row->room_unit_price);
			}
			
			$this->add_room($row->room_id,-$row->quantity_purchased,$row->discount_percent,$price_to_use,$row->description,$row->serialnumber, TRUE, $row->line);
		}

		$this->set_customer($this->CI->Reservation->get_customer($reservation_id)->person_id);
	}

	function copy_entire_reserve($reservation_id, $is_receipt = false)
	{
		$this->empty_cart();
		$this->delete_customer();
		$reserve_taxes = $this->get_taxes($reservation_id);

		foreach($this->CI->Reservation->get_reservations_bedrooms($reservation_id)->result() as $row)
		{
			$room_info = $this->CI->Room->get_info($row->room_id);
			$price_to_use = $row->room_unit_price;

			//If we have tax included, but we don't have any taxes for reserve, pretend that we do have taxes so the right price shows up
			if (empty($reserve_taxes) && !$is_receipt)
			{
				$price_to_use = get_price_for_room_including_taxes($row->room_id, $row->room_unit_price);
			}
			
			$this->add_room($row->room_id,$row->quantity_purchased,$row->discount_percent,$price_to_use,$row->description,$row->serialnumber, TRUE, $row->line);
		}


		foreach($this->CI->Reservation->get_reservations_payments($reservation_id)->result() as $row)
		{
			$this->add_payment($row->payment_type,$row->payment_amount, $row->payment_date, $row->truncated_card, $row->card_issuer);
		}
		$customer_info = $this->CI->Reservation->get_customer($reservation_id);
		$this->set_customer($customer_info->person_id);

		$this->set_comment($this->CI->Reservation->get_comment($reservation_id));
		$this->set_comment_on_receipt($this->CI->Reservation->get_comment_on_receipt($reservation_id));

		$this->set_sold_by_employee_id($this->CI->Reservation->get_sold_by_employee_id($reservation_id));

	}

	function get_suspended_reservation_id()
	{
		return $this->CI->session->userdata('suspended_reservation_id');
	}

	function set_suspended_reservation_id($suspended_reservation_id)
	{
		$this->CI->session->set_userdata('suspended_reservation_id',$suspended_reservation_id);
	}

	function delete_suspended_reservation_id()
	{
		$this->CI->session->unset_userdata('suspended_reservation_id');
	}

	function get_change_reservation_id()
	{
		return $this->CI->session->userdata('change_reservation_id');
	}

	function set_change_reservation_id($change_reservation_id)
	{
		$this->CI->session->set_userdata('change_reservation_id',$change_reservation_id);
	}

	function delete_change_reservation_id()
	{
		$this->CI->session->unset_userdata('change_reservation_id');
	}
	function delete_room($line)
	{
		$bedrooms=$this->get_cart();
		$room_id=$this->get_room_id($line);
		if($this->CI->Giftcard->get_giftcard_id($this->CI->Room->get_info($room_id)->description))
		{
			$this->CI->Giftcard->delete_completely($this->CI->Room->get_info($room_id)->description);
		}
		unset($bedrooms[$line]);
		$this->set_cart($bedrooms);
	}

	function empty_cart()
	{
		$this->CI->session->unset_userdata('reserve_cart');
	}

	function delete_customer()
	{
		$this->CI->session->unset_userdata('customer');
		$this->change_price();
	}

	function clear_mode()
	{
		$this->CI->session->unset_userdata('reserve_mode');
	}

	function clear_cc_info()
	{
		$this->CI->session->unset_userdata('ref_no');
		$this->CI->session->unset_userdata('auth_code');
		$this->CI->session->unset_userdata('masked_account');
		$this->CI->session->unset_userdata('card_issuer');
	}

	function clear_all()
	{
		$this->clear_mode();
		$this->empty_cart();
		$this->clear_comment();
		$this->clear_show_comment_on_receipt();
		$this->clear_change_reserve_date();
		$this->clear_change_reserve_date_enable();
		$this->clear_email_receipt();
		$this->empty_payments();
		$this->delete_customer();
		$this->delete_suspended_reservation_id();
		$this->delete_change_reservation_id();
		$this->delete_partial_transactions();
		$this->clear_save_credit_card_info();
		$this->clear_use_saved_cc_info();
		$this->clear_selected_tier_id();
		$this->clear_deleted_taxes();
		$this->clear_cc_info();
		$this->clear_sold_by_employee_id();
		$this->clear_star_date();
		$this->clear_end_date();
	}

	function save_current_reserve_state()
	{
		$this->reserve_state = array(
			'mode' => $this->get_mode(),
			'cart' => $this->get_cart(),
			'comment' => $this->get_comment(),
			'show_comment_on_receipt' => $this->get_comment_on_receipt(),
			'change_reserve_date' => $this->get_change_reserve_date(),
			'change_reserve_date_enable' => $this->get_change_reserve_date_enable(),
			'email_receipt' => $this->get_email_receipt(),
			'payments' => $this->get_payments(),
			'customer' => $this->get_customer(),
			'suspended_reservation_id' => $this->get_suspended_reservation_id(),
			'change_reservation_id' => $this->get_change_reservation_id(),
			'partial_transactions' => $this->get_partial_transactions(),
			'save_credit_card_info' => $this->get_save_credit_card_info(),
			'use_saved_cc_info' => $this->get_use_saved_cc_info(),
			'selected_tier_id' => $this->get_selected_tier_id(),
			'deleted_taxes' => $this->get_deleted_taxes(),
			'sold_by_employee_id' => $this->get_sold_by_employee_id(),
		);
	}

	function restore_current_reserve_state()
	{
		if (isset($this->reserve_state))
		{
			$this->set_mode($this->reserve_state['mode']);
			$this->set_cart($this->reserve_state['cart']);
			$this->set_comment($this->reserve_state['comment']);
			$this->set_comment_on_receipt($this->reserve_state['show_comment_on_receipt']);
			$this->set_change_reserve_date($this->reserve_state['change_reserve_date']);
			$this->set_change_reserve_date_enable($this->reserve_state['change_reserve_date_enable']);
			$this->set_email_receipt($this->reserve_state['email_receipt']);
			$this->set_payments($this->reserve_state['payments']);
			$this->set_customer($this->reserve_state['customer']);
			$this->set_suspended_reservation_id($this->reserve_state['suspended_reservation_id']);
			$this->set_change_reservation_id($this->reserve_state['change_reservation_id']);
			$this->set_partial_transactions($this->reserve_state['partial_transactions']);
			$this->set_save_credit_card_info($this->reserve_state['save_credit_card_info']);
			$this->set_use_saved_cc_info($this->reserve_state['use_saved_cc_info']);
			$this->set_selected_tier_id($this->reserve_state['selected_tier_id']);
			$this->set_deleted_taxes($this->reserve_state['deleted_taxes']);
			$this->set_sold_by_employee_id($this->reserve_state['sold_by_employee_id']);
		}
	}

	function get_taxes($reservation_id = false)
	{
		$taxes = array();

		if ($reservation_id)
		{
			$taxes_from_reserve = $this->CI->Reservation->get_reservations_bedrooms_taxes($reservation_id);
			foreach($taxes_from_reserve as $key=>$tax_room)
			{
				$name = $tax_room['percent'].'% ' . $tax_room['name'];

				if ($tax_room['cumulative'])
				{
					$prev_tax = ($tax_room['price']*$tax_room['quantity']-$tax_room['price']*$tax_room['quantity']*$tax_room['discount']/100)*(($taxes_from_reserve[$key-1]['percent'])/100);
					$tax_amount=(($tax_room['price']*$tax_room['quantity']-$tax_room['price']*$tax_room['quantity']*$tax_room['discount']/100) + $prev_tax)*(($tax_room['percent'])/100);
				}
				else
				{
					$tax_amount=($tax_room['price']*$tax_room['quantity']-$tax_room['price']*$tax_room['quantity']*$tax_room['discount']/100)*(($tax_room['percent'])/100);
				}

				if (!isset($taxes[$name]))
				{
					$taxes[$name] = 0;
				}
				$taxes[$name] += $tax_amount;
			}
		}
		else
		{
			$customer_id = $this->get_customer();
			$customer = $this->CI->Customer->get_info($customer_id);

			//Do not charge reserves tax if we have a customer that is not taxable
			if (!$customer->taxable and $customer_id!=-1)
			{
			   return array();
			}

			foreach($this->get_cart() as $line=>$room)
			{
				$price_to_use = $this->_get_price_for_room_in_cart($room);

				$tax_info = isset($room['room_id']) ? $this->CI->Room_taxes_finder->get_info($room['room_id']) : array();
				foreach($tax_info as $key=>$tax)
				{
					$name = $tax['percent'].'% ' . $tax['name'];

					if ($tax['cumulative'])
					{
						$prev_tax = ($price_to_use*$room['quantity']-$price_to_use*$room['quantity']*$room['discount']/100)*(($tax_info[$key-1]['percent'])/100);
						$tax_amount=(($price_to_use*$room['quantity']-$price_to_use*$room['quantity']*$room['discount']/100) + $prev_tax)*(($tax['percent'])/100);
					}
					else
					{
						$tax_amount=($price_to_use*$room['quantity']-$price_to_use*$room['quantity']*$room['discount']/100)*(($tax['percent'])/100);
					}

					if (!in_array($name, $this->get_deleted_taxes()))
					{
						if (!isset($taxes[$name]))
						{
							$taxes[$name] = 0;
						}

						$taxes[$name] += $tax_amount;
					}
				}
			}
		}
		return $taxes;
	}

	function get_bedrooms_in_cart()
	{
		$bedrooms_in_cart = 0;
		foreach($this->get_cart() as $room)
		{
		    $bedrooms_in_cart+=$room['quantity'];
		}

		return $bedrooms_in_cart;
	}

	function get_subtotal($reservation_id = FALSE)
	{
		$subtotal = 0;
		foreach($this->get_cart() as $room)
		{
			$price_to_use = $this->_get_price_for_room_in_cart($room, $reservation_id);
		    $subtotal+=($price_to_use*$room['quantity']-$price_to_use*$room['quantity']*$room['discount']/100);
		}

		return to_currency_no_money($subtotal);
	}

	function _get_price_for_room_in_cart($room, $reservation_id = FALSE)
	{
		$price_to_use = $room['price'];

		if (isset($room['room_id']))
		{
			$room_info = $this->CI->Room->get_info($room['room_id']);
			
		}


		return $price_to_use;
	}

	function get_total($reservation_id = false)
	{
		$total = 0;
		foreach($this->get_cart() as $room)
		{
			$price_to_use = $this->_get_price_for_room_in_cart($room, $reservation_id);
		    $total+=($price_to_use*$room['quantity']-$price_to_use*$room['quantity']*$room['discount']/100);
		}

		foreach($this->get_taxes($reservation_id) as $tax)
		{
			$total+=$tax;
		}

		$total = $this->CI->config->item('round_cash_on_reserves') && $this->is_reserve_cash_payment() ?  round_to_nearest_05($total) : $total;
		return to_currency_no_money($total);
	}

	function is_reserve_cash_payment()
	{
		foreach($this->get_payments() as $payment)
		{
			if($payment['payment_type'] ==  lang('reserve_cash'))
			{
				return true;
			}
		}

		return false;
	}

	function is_over_credit_limit()
	{
		$customer_id=$this->get_customer();
		if($customer_id!=-1)
		{
			$cust_info=$this->CI->Customer->get_info($customer_id);
			$current_reserve_store_account_balance = $this->get_payment_amount(lang('reserve_store_account'));
			return $cust_info->credit_limit !== NULL && $cust_info->balance + $current_reserve_store_account_balance > $cust_info->credit_limit;
		}

		return FALSE;
	}
}
?>
