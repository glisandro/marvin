<?php
class Reservation extends CI_Model
{
	public function get_info($reservation_id)
	{
		$this->db->from('reservations');
		$this->db->where('reservation_id',$reservation_id);
		return $this->db->get();
	}

	function get_cash_reservation_total_for_shift($shift_start, $shift_end)
    {
		$reservation_totals = $this->get_reservation_totaled_by_id($shift_start, $shift_end);
		$register_id = $this->Employee->get_logged_in_employee_current_register_id();

		$this->db->select('reservations_payments.reservation_id, reservations_payments.payment_type, payment_amount', false);
      $this->db->from('reservations_payments');
      $this->db->join('reservations','reservations_payments.reservation_id=reservations.reservation_id');
		$this->db->where('reservations_payments.payment_date >=', $shift_start);
		$this->db->where('reservations_payments.payment_date <=', $shift_end);
		$this->db->where('register_id', $register_id);
		$this->db->where($this->db->dbprefix('reservations').'.deleted', 0);

		$payments_by_reservation = array();
		$reservations_payments = $this->db->get()->result_array();

		foreach($reservations_payments as $row)
		{
        	$payments_by_reservation[$row['reservation_id']][] = $row;
		}

		$payment_data = $this->Reserve->get_payment_data($payments_by_reservation,$reservation_totals);

		if (isset($payment_data[lang('reserve_cash')]))
		{
			return $payment_data[lang('reserve_cash')]['payment_amount'];
		}

		return 0.00;
    }

	function get_payment_data($payments_by_reservation,$reservation_totals)
	{
		$payment_data = array();

		foreach($payments_by_reservation as $reservation_id => $payment_rows)
		{
			if (isset($reservation_totals[$reservation_id]))
			{
				$total_reservation_balance = $reservation_totals[$reservation_id];
				usort($payment_rows, array('Reserve', '_sort_payments_for_reservation'));

				foreach($payment_rows as $row)
				{
					if ($row['payment_amount'] >=0)
					{
						$payment_amount = $row['payment_amount'] <= $total_reservation_balance ? $row['payment_amount'] : $total_reservation_balance;
					}
					else
					{
						$payment_amount = $row['payment_amount'] >= $total_reservation_balance ? $row['payment_amount'] : $total_reservation_balance;
					}
					if (!isset($payment_data[$row['payment_type']]))
					{
						$payment_data[$row['payment_type']] = array('payment_type' => $row['payment_type'], 'payment_amount' => 0 );
					}

					if ($total_reservation_balance != 0)
					{
						$payment_data[$row['payment_type']]['payment_amount'] += $payment_amount;
					}

					$total_reservation_balance-=$payment_amount;
				}
			}
		}

		return $payment_data;
	}

	function get_payment_data_grouped_by_reservation($payments_by_reservation,$reservation_totals)
	{
		$payment_data = array();

		foreach($payments_by_reservation as $reservation_id => $payment_rows)
		{
			if (isset($reservation_totals[$reservation_id]))
			{
				$total_reservation_balance = $reservation_totals[$reservation_id];
				usort($payment_rows, array('Reserve', '_sort_payments_for_reservation'));

				foreach($payment_rows as $row)
				{
					if ($row['payment_amount'] >=0)
					{
						$payment_amount = $row['payment_amount'] <= $total_reservation_balance ? $row['payment_amount'] : $total_reservation_balance;
					}
					else
					{
						$payment_amount = $row['payment_amount'] >= $total_reservation_balance ? $row['payment_amount'] : $total_reservation_balance;
					}

					if (!isset($payment_data[$reservation_id][$row['payment_type']]))
					{
						$payment_data[$reservation_id][$row['payment_type']] = array('reservation_id' => $reservation_id,'payment_type' => $row['payment_type'], 'payment_amount' => 0,'payment_date' => $row['payment_date'], 'reservation_time' => $row['reservation_time'] );
					}

					if ($total_reservation_balance != 0)
					{
						$payment_data[$reservation_id][$row['payment_type']]['payment_amount'] += $payment_amount;
					}

					$total_reservation_balance-=$payment_amount;
				}
			}
		}

		return $payment_data;
	}


	static function _sort_payments_for_reservation($a,$b)
	{
		if ($a['payment_amount'] == $b['payment_amount']);
		{
			return 0;
		}

		if ($a['payment_amount']< $b['payment_amount'])
		{
			return -1;
		}

		return 1;
	}

	function get_reservation_totaled_by_id($shift_start, $shift_end)
	{
		$register_id = $this->Employee->get_logged_in_employee_current_register_id();

		$this->db->select('reservations.reservation_id', false);
      $this->db->from('reservations');
      $this->db->join('reservations_payments','reservations_payments.reservation_id=reservations.reservation_id');
		$this->db->where('reservations_payments.payment_date >=', $shift_start);
		$this->db->where('reservations_payments.payment_date <=', $shift_end);
		$this->db->where('register_id', $register_id);
		$this->db->where($this->db->dbprefix('reservations').'.deleted', 0);

		$reservation_ids = array();
		$result = $this->db->get()->result();
		foreach($result as $row)
		{
			$reservation_ids[] = $row->reservation_id;
		}

		$reservation_totals = array();

		if (count($reservation_ids) > 0)
		{
			$where = 'WHERE '.$this->db->dbprefix('reservations').'.reservation_id IN('.implode(',',$reservation_ids).')';
			$this->_create_reservations_bedrooms_temp_table_query($where);
			$this->db->select('reservation_id, SUM(total) as total', false);
			$this->db->from('reservations_bedrooms_temp');
			$this->db->group_by('reservation_id');

			foreach($this->db->get()->result_array() as $reservation_total_row)
			{
				$reservation_totals[$reservation_total_row['reservation_id']] = $reservation_total_row['total'];
			}
		}

		return $reservation_totals;
	}

	/**
	 * added for cash register
	 * insert a log for track_cash_log
	 * @param array $data
	 */

	function update_register_log($data) {
		$register_id = $this->Employee->get_logged_in_employee_current_register_id();

		$this->db->where('shift_end','0000-00-00 00:00:00');
		$this->db->where('register_id', $register_id);
		return $this->db->update('register_log', $data) ? true : false;
	}
	function insert_register($data) {
		return $this->db->insert('register_log', $data) ? $this->db->insert_id() : false;
	}

	function is_register_log_open()
	{
		$register_id = $this->Employee->get_logged_in_employee_current_register_id();

		$this->db->from('register_log');
		$this->db->where('shift_end','0000-00-00 00:00:00');
		$this->db->where('register_id',$register_id);
		$query = $this->db->get();
		if($query->num_rows())
		return true	;
		else
		return false;

	 }

	function get_current_register_log()
	{
		$register_id = $this->Employee->get_logged_in_employee_current_register_id();

		$this->db->from('register_log');
		$this->db->where('shift_end','0000-00-00 00:00:00');
		$this->db->where('register_id',$register_id);

		$query = $this->db->get();
		if($query->num_rows())
		return $query->row();
		else
		return false;

	 }
	function exists($reservation_id)
	{
		$this->db->from('reservations');
		$this->db->where('reservation_id',$reservation_id);
		$query = $this->db->get();

		return ($query->num_rows()==1);
	}

	function update($reservation_data, $reservation_id)
	{
		$this->db->where('reservation_id', $reservation_id);
		$success = $this->db->update('reservations',$reservation_data);

		return $success;
	}

	function save($bedrooms,$customer_id,$employee_id, $sold_by_employee_id, $comment,$show_comment_on_receipt,$payments,$reservation_id=false, $suspended = 0, $cc_ref_no = '', $auth_code = '', $change_reservation_date=false,$balance=0, $store_account_payment = 0)
	{
		//we need to check the sale library for deleted taxes during sale
		$this->load->library('reserve_lib');

		if(count($bedrooms)==0)
			return -1;

		$payment_types='';
		foreach($payments as $payment_id=>$payment)
		{
			$payment_types=$payment_types.$payment['payment_type'].': '.to_currency($payment['payment_amount']).'<br />';
		}


		$reservation_data = array(
			'customer_id'=> $customer_id > 0 ? $customer_id : null,
			'employee_id'=>$employee_id,
			'sold_by_employee_id' => $sold_by_employee_id,
			'payment_type'=>$payment_types,
			'comment'=>$comment,
			'show_comment_on_receipt'=> $show_comment_on_receipt ?  $show_comment_on_receipt : 0,
			'suspended'=>$suspended,
			'deleted' => 0,
			'deleted_by' => NULL,
			'cc_ref_no' => $cc_ref_no,
			'auth_code' => $auth_code,
			'location_id' => $this->Employee->get_logged_in_employee_current_location_id(),
			'register_id' => $this->Employee->get_logged_in_employee_current_register_id(),
			'store_account_payment' => $store_account_payment,
		);

		if($reservation_id)
		{
			$old_date=$this->get_info($reservation_id)->row_array();
			$reservation_data['reservation_time']=$old_date['reservation_time'];

			if($change_reservation_date)
			{
				$reservation_time = strtotime($change_reservation_date);
				if($reservation_time !== FALSE)
				{
					$reservation_data['reservation_time']=date('Y-m-d H:i:s', strtotime($change_reservation_date));
				}
			}

		}
		else
		{
			$reservation_data['reservation_time'] = date('Y-m-d H:i:s');
		}

		$this->db->query("SET autocommit=0");
		//Lock tables invovled in sale transaction so we don't have deadlock
		$this->db->query('LOCK TABLES '.$this->db->dbprefix('customers').' WRITE, '.$this->db->dbprefix('reservations').' WRITE,
		'.$this->db->dbprefix('store_accounts').' WRITE, '.$this->db->dbprefix('reservations_payments').' WRITE, '.$this->db->dbprefix('reservations_bedrooms').' WRITE,
		'.$this->db->dbprefix('giftcards').' WRITE, '.$this->db->dbprefix('location_bedrooms').' WRITE,
		'.$this->db->dbprefix('inventory').' WRITE, '.$this->db->dbprefix('reservations_bedrooms_taxes').' WRITE,
		'.$this->db->dbprefix('people').' READ,'.$this->db->dbprefix('bedrooms').' READ
		,'.$this->db->dbprefix('employees_locations').' READ,'.$this->db->dbprefix('locations').' READ, '.$this->db->dbprefix('bedrooms_tier_prices').' READ
		, '.$this->db->dbprefix('location_bedrooms_tier_prices').' READ, '.$this->db->dbprefix('bedrooms_taxes').' READ
		, '.$this->db->dbprefix('employees').' READ
		, '.$this->db->dbprefix('location_bedrooms_taxes').' READ');

		$store_account_payment_amount = 0;

		if ($store_account_payment)
		{
			$store_account_payment_amount = $this->reserve_lib->get_total();
		}

		//Only update balance + store account payments if we are NOT an estimate (suspended = 2)
		if ($suspended != 2)
		{
	   	  //Update customer store account balance
			  if($customer_id > 0 && $balance)
			  {
				  $this->db->set('balance','balance+'.$balance,false);
				  $this->db->where('person_id', $customer_id);
				  if (!$this->db->update('customers'))
				  {
						$this->db->query("ROLLBACK");
						$this->db->query('UNLOCK TABLES');
						return -1;
				  }
			  }

		     //Update customer store account if payment made
			if($customer_id > 0 && $store_account_payment_amount)
			{
				$this->db->set('balance','balance-'.$store_account_payment_amount,false);
				$this->db->where('person_id', $customer_id);
				if (!$this->db->update('customers'))
				{
					$this->db->query("ROLLBACK");
					$this->db->query('UNLOCK TABLES');
					return -1;
				}
			 }
		 }

		 $previous_store_account_amount = 0;

		 if ($reservation_id !== FALSE)
		 {
			 $previous_store_account_amount = $this->get_store_account_payment_total($reservation_id);
		 }

		if ($reservation_id)
		{
			//Delete previoulsy sale so we can overwrite data
			if (!$this->delete($reservation_id, true))
			{
				$this->db->query("ROLLBACK");
				$this->db->query('UNLOCK TABLES');
				return -1;
			}

			$this->db->where('reservation_id', $reservation_id);
			if (!$this->db->update('reservations', $reservation_data))
			{
				$this->db->query("ROLLBACK");
				$this->db->query('UNLOCK TABLES');
				return -1;
			}
		}
		else
		{
			if (!$this->db->insert('reservations',$reservation_data))
			{
				$this->db->query("ROLLBACK");
				$this->db->query('UNLOCK TABLES');
				return -1;
			}
			$reservation_id = $this->db->insert_id();
		}


		//Only update store account payments if we are NOT an estimate (suspended = 2)
		if ($suspended != 2)
		{
			 //insert store account transaction
			if($customer_id > 0 && $balance)
			{
			 	$store_account_transaction = array(
			      'customer_id'=>$customer_id,
			      'reservation_id'=>$reservation_id,
					'comment'=>$comment,
			      'transaction_amount'=>$balance - $previous_store_account_amount,
					'balance'=>$this->Customer->get_info($customer_id)->balance,
					'date' => date('Y-m-d H:i:s')
				);

				if ($balance - $previous_store_account_amount)
				{
					if (!$this->db->insert('store_accounts',$store_account_transaction))
					{
						$this->db->query("ROLLBACK");
						$this->db->query('UNLOCK TABLES');
						return -1;
					}
				}
			 }
			 elseif ($customer_id > 0 && $previous_store_account_amount) //We had a store account payment before has one...We need to log this
			 {
 			 	$store_account_transaction = array(
 			      'customer_id'=>$customer_id,
 			      'reservation_id'=>$reservation_id,
 					'comment'=>$comment,
 			      'transaction_amount'=> -$previous_store_account_amount,
 					'balance'=>$this->Customer->get_info($customer_id)->balance,
 					'date' => date('Y-m-d H:i:s')
 				);

 				if (!$this->db->insert('store_accounts',$store_account_transaction))
				{
					$this->db->query("ROLLBACK");
					$this->db->query('UNLOCK TABLES');
					return -1;
				}
			 }


			 //insert store account payment transaction
			if($customer_id > 0 && $store_account_payment)
			{
			 	$store_account_transaction = array(
			        'customer_id'=>$customer_id,
			        'reservation_id'=>$reservation_id,
					'comment'=>$comment,
			       	'transaction_amount'=> -$store_account_payment_amount,
					'balance'=>$this->Customer->get_info($customer_id)->balance,
					'date' => date('Y-m-d H:i:s')
				);

				if (!$this->db->insert('store_accounts',$store_account_transaction))
				{
					$this->db->query("ROLLBACK");
					$this->db->query('UNLOCK TABLES');
					return -1;
				}
			 }
		 }

		$total_giftcard_payments = 0;

		foreach($payments as $payment_id=>$payment)
		{
			//Only update giftcard payments if we are NOT an estimate (suspended = 2)
			if ($suspended != 2)
			{
				if ( substr( $payment['payment_type'], 0, strlen( lang('reserve_giftcard') ) ) == lang('reserve_giftcard') )
				{
					/* We have a gift card and we have to deduct the used value from the total value of the card. */
					$splitpayment = explode( ':', $payment['payment_type'] );
					$cur_giftcard_value = $this->Giftcard->get_giftcard_value( $splitpayment[1] );

					$this->Giftcard->update_giftcard_value( $splitpayment[1], $cur_giftcard_value - $payment['payment_amount'] );
					$total_giftcard_payments+=$payment['payment_amount'];
				}
			}

			$reservations_payments_data = array
			(
				'reservation_id'=>$reservation_id,
				'payment_type'=>$payment['payment_type'],
				'payment_amount'=>$payment['payment_amount'],
				'payment_date' => $payment['payment_date'],
				'truncated_card' => $payment['truncated_card'],
				'card_issuer' => $payment['card_issuer'],
			);
			if (!$this->db->insert('reservations_payments',$reservations_payments_data))
			{
				$this->db->query("ROLLBACK");
				$this->db->query('UNLOCK TABLES');
				return -1;
			}
		}

		$has_added_giftcard_value_to_cost_price = $total_giftcard_payments > 0 ? false : true;
		$store_account_room_id = $this->Room->get_store_account_room_id();

		foreach($bedrooms as $line=>$room)
		{
			if (isset($room['room_id']))
			{
				$cur_room_info = $this->Room->get_info($room['room_id']);
				$cur_room_location_info = $this->Room_location->get_info($room['room_id']);

				if ($room['room_id'] != $store_account_room_id)
				{
					$cost_price = ($cur_room_location_info && $cur_room_location_info->cost_price) ? $cur_room_location_info->cost_price : $cur_room_info->cost_price;
				}
				else // Set cost price = price so we have no profit
				{
					$cost_price = $room['price'];
				}


				if (!$this->config->item('disable_subtraction_of_giftcard_amount_from_reservations'))
				{
					//Add to the cost price if we are using a giftcard as we have already recorded profit for sale of giftcard
					if (!$has_added_giftcard_value_to_cost_price)
					{
						$cost_price+= $total_giftcard_payments / $room['quantity'];
						$has_added_giftcard_value_to_cost_price = true;
					}
				}
				//$reorder_level = ($cur_room_location_info && $cur_room_location_info->reorder_level) ? $cur_room_location_info->reorder_level : $cur_room_info->reorder_level;

				/*if ($cur_room_info->tax_included)
				{
					$room['price'] = get_price_for_room_excluding_taxes($room['room_id'], $room['price']);
				}*/

				$reservations_bedrooms_data = array
				(
					'reservation_id'=>$reservation_id,
					'room_id'=>$room['room_id'],
					'line'=>$room['line'],
					'description'=>$room['description'],
					'serialnumber'=>$room['serialnumber'],
					'quantity_purchased'=>$room['quantity'],
					'discount_percent'=>$room['discount'],
					'room_cost_price' =>  to_currency_no_money($cost_price,10),
					'room_unit_price'=>$room['price'],
					'commission' => get_commission_for_room($room['room_id'],$room['price'],$room['quantity'], $room['discount']),
				);

				if (!$this->db->insert('reservations_bedrooms',$reservations_bedrooms_data))
				{
					$this->db->query("ROLLBACK");
					$this->db->query('UNLOCK TABLES');
					return -1;
				}

				//Only update giftcard payments if we are NOT an estimate (suspended = 2)
				if ($suspended != 2)
				{
					//create giftcard from sales
					if($room['name']==lang('reserve_giftcard') && !$this->Giftcard->get_giftcard_id($room['description']))
					{
						$giftcard_data = array(
							'giftcard_number'=>$room['description'],
							'value'=>$room['price'],
							'customer_id'=>$customer_id > 0 ? $customer_id : null,
						);

						if (!$this->Giftcard->save($giftcard_data))
						{
							$this->db->query("ROLLBACK");
							$this->db->query('UNLOCK TABLES');
							return -1;
						}
					}
				}

				//Only do stock check + inventory update if we are NOT an estimate
				if ($suspended != 2)
				{
					$stock_recorder_check=false;
					$out_of_stock_check=false;
					$email=false;
					$message = '';

					//checks if the quantity is greater than reorder level
					/*if(!$cur_room_info->is_service && $cur_room_location_info->quantity > $reorder_level)
					{
						$stock_recorder_check=true;
					}*/

					//checks if the quantity is greater than 0
					if(!$cur_room_info->is_service && $cur_room_location_info->quantity > 0)
					{
						$out_of_stock_check=true;
					}

					//Update stock quantity IF not a service
					if (!$cur_room_info->is_service)
					{
						$cur_room_location_info->quantity = $cur_room_location_info->quantity !== NULL ? $cur_room_location_info->quantity : 0;

						if (!$this->Room_location->save_quantity($cur_room_location_info->quantity - $room['quantity'], $room['room_id']))
						{
							$this->db->query("ROLLBACK");
							$this->db->query('UNLOCK TABLES');
							return -1;
						}
					}

					//Re-init $cur_room_location_info after updating quantity
					$cur_room_location_info = $this->Room_location->get_info($room['room_id']);

					//checks if the quantity is out of stock
					if($out_of_stock_check && $cur_room_location_info->quantity <= 0)
					{
						$message= $cur_room_info->name.' '.lang('reserve_is_out_stock').' '.to_quantity($cur_room_location_info->quantity);
						$email=true;

					}
					//checks if the quantity hits reorder level
					/*else if($stock_recorder_check && ($cur_room_location_info->quantity <= $reorder_level))
					{
						$message= $cur_room_info->name.' '.lang('reserve_hits_reorder_level').' '.to_quantity($cur_room_location_info->quantity);
						$email=true;
					}*/

					//send email
					if($this->Location->get_info_for_key('receive_stock_alert') && $email)
					{
						$this->load->library('email');
						$config = array();
						$config['mailtype'] = 'text';
						$this->email->initialize($config);
						$this->email->from($this->Location->get_info_for_key('email') ? $this->Location->get_info_for_key('email') : 'no-reply@phppointofsale.com', $this->config->item('company'));
						$this->email->to($this->Location->get_info_for_key('stock_alert_email') ? $this->Location->get_info_for_key('stock_alert_email') : $this->Location->get_info_for_key('email'));

						$this->email->subject(lang('reserve_stock_alert_room_name').$this->Room->get_info($room['room_id'])->name);
						$this->email->message($message);
						$this->email->send();
					}

					if (!$cur_room_info->is_service)
					{
						$qty_buy = -$room['quantity'];
						$reservation_remarks =$this->config->item('reserve_prefix').' '.$reservation_id;
						$inv_data = array
						(
							'trans_date'=>date('Y-m-d H:i:s'),
							'trans_items'=>$room['room_id'],
							'trans_user'=>$employee_id,
							'trans_comment'=>$reservation_remarks,
							'trans_inventory'=>$qty_buy,
							'location_id' => $this->Employee->get_logged_in_employee_current_location_id()
						);
						// if (!$this->Inventory->insert($inv_data))
						// {
						// 	$this->db->query("ROLLBACK");
						// 	$this->db->query('UNLOCK TABLES');
						// 	return -1;
						// }
					}
				}
			}


			$customer = $this->Customer->get_info($customer_id);
 			if ($customer_id == -1 or $customer->taxable)
 			{
				if (isset($room['room_id']))
				{
					foreach($this->Room_taxes_finder->get_info($room['room_id']) as $row)
					{
						$tax_name = $row['percent'].'% ' . $row['name'];

						//Only save sale if the tax has NOT been deleted
						if (!in_array($tax_name, $this->reserve_lib->get_deleted_taxes()))
						{
							$query_result = $this->db->insert('reservations_bedrooms_taxes', array(
								'reservation_id' 	=>$reservation_id,
								'room_id' 	=>$room['room_id'],
								'line'      =>$room['line'],
								'name'		=>$row['name'],
								'percent' 	=>$row['percent'],
								'cumulative'=>$row['cumulative']
							));

							if (!$query_result)
							{
								$this->db->query("ROLLBACK");
								$this->db->query('UNLOCK TABLES');
								return -1;
							}
						}
					}
				}

			}
		}

		$this->db->query("COMMIT");
		$this->db->query('UNLOCK TABLES');

		return $reservation_id;
	}

	function update_store_account($reservation_id,$undelete=0)
	{
		//update if Store account payment exists
		$this->db->from('reservations_payments');
		$this->db->where('payment_type',lang('reserve_store_account'));
		$this->db->where('reservation_id',$reservation_id);
		$to_be_paid_result = $this->db->get();

		$customer_id=$this->get_customer($reservation_id)->person_id;


		if($to_be_paid_result->num_rows >=1)
		{
			foreach($to_be_paid_result->result() as $to_be_paid)
			{
				if($to_be_paid->payment_amount)
				{
					//update customer balance
					if($undelete==0)
					{
						$this->db->set('balance','balance-'.$to_be_paid->payment_amount,false);
					}
					else
					{
						$this->db->set('balance','balance+'.$to_be_paid->payment_amount,false);
					}
					$this->db->where('person_id', $customer_id);
					$this->db->update('customers');

				}
			}
		}
	}

	function update_giftcard_balance($reservation_id,$undelete=0)
	{
		//if gift card payment exists add the amount to giftcard balance
			$this->db->from('reservations_payments');
			$this->db->like('payment_type',lang('reserve_giftcard'));
			$this->db->where('reservation_id',$reservation_id);
			$reservation_payment = $this->db->get();

			if($reservation_payment->num_rows >=1)
			{
				foreach($reservation_payment->result() as $row)
				{
					$giftcard_number=str_ireplace(lang('reserve_giftcard').':','',$row->payment_type);
					$value=$row->payment_amount;
					if($undelete==0)
					{
						$this->db->set('value','value+'.$value,false);
					}
					else
					{
						$this->db->set('value','value-'.$value,false);
					}
					$this->db->where('giftcard_number', $giftcard_number);
					$this->db->update('giftcards');
				}
			}

	}

	function delete($reservation_id, $all_data = false)
	{
		$reservation_info = $this->get_info($reservation_id)->row_array();
		$suspended = $reservation_info['suspended'];
		$employee_id=$this->Employee->get_logged_in_employee_info()->person_id;

		//Only update stock quantity if we are NOT an estimate ($suspendd = 2)
		if ($suspended != 2)
		{
			$this->db->select('reservations.location_id, room_id, quantity_purchased');
			$this->db->from('reservations_bedrooms');
			$this->db->join('reservations', 'reservations.reservation_id = reservations_bedrooms.reservation_id');
			$this->db->where('reservations_bedrooms.reservation_id', $reservation_id);

			foreach($this->db->get()->result_array() as $reservation_room_row)
			{
				$reservation_location_id = $reservation_room_row['location_id'];
				$cur_room_info = $this->Room->get_info($reservation_room_row['room_id']);
				$cur_room_location_info = $this->Room_location->get_info($reservation_room_row['room_id'], $reservation_location_id);

				$cur_room_quantity = $this->Room_location->get_location_quantity($reservation_room_row['room_id'], $reservation_location_id);

				if (!$cur_room_info->is_service)
				{
					//Update stock quantity
					$this->Room_location->save_quantity($cur_room_quantity + $reservation_room_row['quantity_purchased'],$reservation_room_row['room_id'], $reservation_location_id);

					$reservation_remarks =$this->config->item('reserve_prefix').' '.$reservation_id;
					$inv_data = array
					(
						'location_id' => $reservation_location_id,
						'trans_date'=>date('Y-m-d H:i:s'),
						'trans_items'=>$reservation_room_row['room_id'],
						'trans_user'=>$employee_id,
						'trans_comment'=>$reservation_remarks,
						'trans_inventory'=>$reservation_room_row['quantity_purchased']
					);
					$this->Inventory->insert($inv_data);
				}
			}
		}



		if ($all_data)
		{
			$this->db->delete('reservations_payments', array('reservation_id' => $reservation_id));
			$this->db->delete('reservations_bedrooms_taxes', array('reservation_id' => $reservation_id));
			$this->db->delete('reservations_bedrooms', array('reservation_id' => $reservation_id));
		}

		$this->db->where('reservation_id', $reservation_id);
		return $this->db->update('reservations', array('deleted' => 1,'deleted_by'=>$employee_id));
	}

	function undelete($reservation_id)
	{
		$reservation_info = $this->get_info($reservation_id)->row_array();
		$suspended = $reservation_info['suspended'];
		$employee_id=$this->Employee->get_logged_in_employee_info()->person_id;

		//Only update stock quantity + store accounts + giftcard balance if we are NOT an estimate ($suspended = 2)
		if ($suspended != 2)
		{
			$this->db->select('reservations.location_id, room_id, quantity_purchased');
			$this->db->from('reservations_bedrooms');
			$this->db->join('reservations', 'reservations.reservation_id = reservations_bedrooms.reservation_id');
			$this->db->where('reservations_bedrooms.reservation_id', $reservation_id);

			foreach($this->db->get()->result_array() as $reservation_room_row)
			{
				$reservation_location_id = $reservation_room_row['location_id'];
				$cur_room_info = $this->Room->get_info($reservation_room_row['room_id']);
				$cur_room_location_info = $this->Room_location->get_info($reservation_room_row['room_id'], $reservation_location_id);

				if (!$cur_room_info->is_service && $cur_room_location_info->quantity !== NULL)
				{
					//Update stock quantity
					$this->Room_location->save_quantity($cur_room_location_info->quantity - $reservation_room_row['quantity_purchased'],$reservation_room_row['room_id']);

					$reservation_remarks =$this->config->item('reserve_prefix').' '.$reservation_id;
					$inv_data = array
					(
						'location_id' => $reservation_location_id,
						'trans_date'=>date('Y-m-d H:i:s'),
						'trans_items'=>$reservation_room_row['room_id'],
						'trans_user'=>$employee_id,
						'trans_comment'=>$reservation_remarks,
						'trans_inventory'=>-$reservation_room_row['quantity_purchased']
						);
					$this->Inventory->insert($inv_data);
				}
			}

			$this->update_store_account($reservation_id,1);
			$this->update_giftcard_balance($reservation_id,1);

		 	$previous_store_account_amount = $this->get_store_account_payment_total($reservation_id);

			if ($previous_store_account_amount)
			{
			 	$store_account_transaction = array(
			      'customer_id'=>$reservation_info['customer_id'],
			      'reservation_id'=>$reservation_id,
					'comment'=>$reservation_info['comment'],
			      'transaction_amount'=>$previous_store_account_amount,
					'balance'=>$this->Customer->get_info($reservation_info['customer_id'])->balance,
					'date' => date('Y-m-d H:i:s')
				);
				$this->db->insert('store_accounts',$store_account_transaction);
			}



		}

		$this->db->where('reservation_id', $reservation_id);
		return $this->db->update('reservations', array('deleted' => 0, 'deleted_by' => NULL));
	}

	function get_reservations_bedrooms($reservation_id)
	{
		$this->db->from('reservations_bedrooms');
		$this->db->where('reservation_id',$reservation_id);
		$this->db->order_by('line');
		return $this->db->get();
	}

	function get_reservations_bedrooms_ordered_by_category($reservation_id)
	{
		$this->db->select('*, reservations_bedrooms.description as reservations_bedrooms_description');
		$this->db->from('reservations_bedrooms');
		$this->db->join('bedrooms', 'bedrooms.room_id = reservations_bedrooms.room_id');
		$this->db->where('reservation_id',$reservation_id);
		$this->db->order_by('category, name');
		return $this->db->get();
	}





	function get_reservations_bedrooms_taxes($reservation_id, $line = FALSE)
	{
		$room_where = '';

		if ($line)
		{
			$room_where = 'and '.$this->db->dbprefix('reservations_bedrooms').'.line = '.$line;
		}

		$query = $this->db->query('SELECT name, percent, cumulative, room_unit_price as price, quantity_purchased as quantity, discount_percent as discount '.
		'FROM '. $this->db->dbprefix('reservations_bedrooms_taxes'). ' JOIN '.
		$this->db->dbprefix('reservations_bedrooms'). ' USING (reservation_id, room_id, line) '.
		'WHERE '.$this->db->dbprefix('reservations_bedrooms_taxes').".reservation_id = $reservation_id".' '.$room_where.' '.
		'ORDER BY '.$this->db->dbprefix('reservations_bedrooms').'.line,'.$this->db->dbprefix('reservations_bedrooms').'.room_id,cumulative,name,percent');
		return $query->result_array();
	}



	function get_reservations_payments($reservation_id)
	{
		$this->db->from('reservations_payments');
		$this->db->where('reservation_id',$reservation_id);
		return $this->db->get();
	}

	function get_customer($reservation_id)
	{
		$this->db->from('reservations');
		$this->db->where('reservation_id',$reservation_id);
		return $this->Customer->get_info($this->db->get()->row()->customer_id);
	}

	function get_comment($reservation_id)
	{
		$this->db->from('reservations');
		$this->db->where('reservation_id',$reservation_id);
		return $this->db->get()->row()->comment;
	}

	function get_comment_on_receipt($reservation_id)
	{
		$this->db->from('reservations');
		$this->db->where('reservation_id',$reservation_id);
		return $this->db->get()->row()->show_comment_on_receipt;
	}

	function get_sold_by_employee_id($reservation_id)
	{
		$this->db->from('reservations');
		$this->db->where('reservation_id',$reservation_id);
		return $this->db->get()->row()->sold_by_employee_id;
	}

	//We create a temp table that allows us to do easy report/sales queries
	public function create_reservations_bedrooms_temp_table($params)
	{
		$location_id = $this->Employee->get_logged_in_employee_current_location_id();
		$where = '';

		if (isset($params['reservation_ids']))
		{
			if (!empty($params['reservation_ids']))
			{
				for($k=0;$k<count($params['reservation_ids']);$k++)
				{
					$params['reservation_ids'][$k] = $this->db->escape($params['reservation_ids'][$k]);
				}

				$where.='WHERE '.$this->db->dbprefix('reservations').".reservation_id IN(".implode(',', $params['reservation_ids']).")";
			}
			else
			{
				$where.='WHERE '.$this->db->dbprefix('reservations').".reservation_id IN(0)";
			}
		}
		elseif (isset($params['start_date']) && isset($params['end_date']))
		{
			$where = 'WHERE reservation_time BETWEEN '.$this->db->escape($params['start_date']).' and '.$this->db->escape($params['end_date']).' and '.$this->db->dbprefix('reservations').'.location_id='.$this->db->escape($location_id). (($this->config->item('hide_store_account_payments_in_reports') ) ? ' and '.$this->db->dbprefix('reservations').'.store_account_payment=0' : '');

			//Added for detailed_suspended_report, we don't need this for other reports as we are always going to have start + end date
			if (isset($params['force_suspended']) && $params['force_suspended'])
			{
				$where .=' and suspended != 0';
			}
			elseif ($this->config->item('hide_layaways_reservation_in_reports'))
			{
				$where .=' and suspended = 0';
			}
			else
			{
				$where .=' and suspended != 2';
			}
		}
		elseif ($this->config->item('hide_layaways_reservation_in_reports'))
		{
			$where .='WHERE suspended = 0'.' and '.$this->db->dbprefix('reservations').'.location_id='.$this->db->escape($location_id).(($this->config->item('hide_store_account_payments_in_reports') ) ? ' and '.$this->db->dbprefix('reservations').'.store_account_payment=0' : '');
		}
		else
		{
			$where .='WHERE suspended != 2'.' and '.$this->db->dbprefix('reservations').'.location_id='.$this->db->escape($location_id).(($this->config->item('hide_store_account_payments_in_reports') ) ? ' and '.$this->db->dbprefix('reservations').'.store_account_payment=0' : '');
		}

		if ($where == '')
		{
			$where = 'WHERE suspended != 2 and '.$this->db->dbprefix('reservations').'.location_id='.$this->db->escape($location_id).(($this->config->item('hide_store_account_payments_in_reports') ) ? ' and '.$this->db->dbprefix('reservations').'.store_account_payment=0' : '');
		}

		$return = $this->_create_reservations_bedrooms_temp_table_query($where);
		return $return;
	}

	function _create_reservations_bedrooms_temp_table_query($where)
	{
		set_time_limit(0);

		return $this->db->query("CREATE TEMPORARY TABLE ".$this->db->dbprefix('reservations_bedrooms_temp')."
		(SELECT ".$this->db->dbprefix('reservations').".deleted as deleted,".$this->db->dbprefix('reservations').".deleted_by as deleted_by, reservation_time, date(reservation_time) as reserve_date, ".$this->db->dbprefix('registers').'.name as register_name,'.$this->db->dbprefix('reservations_bedrooms').".reservation_id, comment,payment_type, customer_id, employee_id, sold_by_employee_id,
		".$this->db->dbprefix('bedrooms').".room_id, NULL as room_kit_id, supplier_id, quantity_purchased, room_cost_price, room_unit_price, category,
		discount_percent, (room_unit_price*quantity_purchased-room_unit_price*quantity_purchased*discount_percent/100) as subtotal,
		".$this->db->dbprefix('reservations_bedrooms').".line as line, serialnumber, ".$this->db->dbprefix('reservations_bedrooms').".description as description,
		(room_unit_price*quantity_purchased-room_unit_price*quantity_purchased*discount_percent/100)+(room_unit_price*quantity_purchased-room_unit_price*quantity_purchased*discount_percent/100)*(SUM(CASE WHEN cumulative != 1 THEN percent ELSE 0 END)/100)
		+(((room_unit_price*quantity_purchased-room_unit_price*quantity_purchased*discount_percent/100)*(SUM(CASE WHEN cumulative != 1 THEN percent ELSE 0 END)/100) + (room_unit_price*quantity_purchased-room_unit_price*quantity_purchased*discount_percent/100))
		*(SUM(CASE WHEN cumulative = 1 THEN percent ELSE 0 END))/100) as total,
		(room_unit_price*quantity_purchased-room_unit_price*quantity_purchased*discount_percent/100)*(SUM(CASE WHEN cumulative != 1 THEN percent ELSE 0 END)/100)
		+(((room_unit_price*quantity_purchased-room_unit_price*quantity_purchased*discount_percent/100)*(SUM(CASE WHEN cumulative != 1 THEN percent ELSE 0 END)/100) + (room_unit_price*quantity_purchased-room_unit_price*quantity_purchased*discount_percent/100))
		*(SUM(CASE WHEN cumulative = 1 THEN percent ELSE 0 END))/100) as tax,
		(room_unit_price*quantity_purchased-room_unit_price*quantity_purchased*discount_percent/100) - (room_cost_price*quantity_purchased) as profit, commission, store_account_payment
		FROM ".$this->db->dbprefix('reservations_bedrooms')."
		INNER JOIN ".$this->db->dbprefix('reservations')." ON  ".$this->db->dbprefix('reservations_bedrooms').'.reservation_id='.$this->db->dbprefix('reservations').'.reservation_id'."
		INNER JOIN ".$this->db->dbprefix('bedrooms')." ON  ".$this->db->dbprefix('reservations_bedrooms').'.room_id='.$this->db->dbprefix('bedrooms').'.room_id'."
		LEFT OUTER JOIN ".$this->db->dbprefix('suppliers')." ON  ".$this->db->dbprefix('bedrooms').'.supplier_id='.$this->db->dbprefix('suppliers').'.person_id'."
		LEFT OUTER JOIN ".$this->db->dbprefix('reservations_bedrooms_taxes')." ON  "
		.$this->db->dbprefix('reservations_bedrooms').'.reservation_id='.$this->db->dbprefix('reservations_bedrooms_taxes').'.reservation_id'." and "
		.$this->db->dbprefix('reservations_bedrooms').'.room_id='.$this->db->dbprefix('reservations_bedrooms_taxes').'.room_id'." and "
		.$this->db->dbprefix('reservations_bedrooms').'.line='.$this->db->dbprefix('reservations_bedrooms_taxes').'.line'. "
		LEFT OUTER JOIN ".$this->db->dbprefix('registers')." ON  ".$this->db->dbprefix('registers').'.register_id='.$this->db->dbprefix('reservations').'.register_id'."
		$where
		GROUP BY reservation_id, room_id, line)
		");
	}


	public function get_giftcard_value( $giftcardNumber )
	{
		if ( !$this->Giftcard->exists( $this->Giftcard->get_giftcard_id($giftcardNumber)))
			return 0;

		$this->db->from('giftcards');
		$this->db->where('giftcard_number',$giftcardNumber);
		return $this->db->get()->row()->value;
	}

	function get_all_suspended($suspended_types = array(1,2))
	{
		$location_id = $this->Employee->get_logged_in_employee_current_location_id();

		$this->db->from('reservations');
		$this->db->join('customers', 'reservations.customer_id = customers.person_id', 'left');
		$this->db->join('people', 'customers.person_id = people.person_id', 'left');
		$this->db->where('reservations.deleted', 0);
		$this->db->where_in('suspended', $suspended_types);
		$this->db->where_in('location_id', $location_id);
		$this->db->order_by('reservation_id');
		$reservations = $this->db->get()->result_array();

		for($k=0;$k<count($reservations);$k++)
		{
			$room_names = array();
			$this->db->select('name');
			$this->db->from('bedrooms');
			$this->db->join('reservations_bedrooms', 'reservations_bedrooms.room_id = bedrooms.room_id');
			$this->db->where('reservation_id', $reservations[$k]['reservation_id']);

			foreach($this->db->get()->result_array() as $row)
			{
				$room_names[] = $row['name'];
			}



			$reservations[$k]['bedrooms'] = implode(', ', $room_names);
		}

		return $reservations;

	}

	function count_all()
	{
		$this->db->from('reservations');
		$this->db->where('deleted',0);

		if ($this->config->item('hide_store_account_payments_in_reports'))
		{
			$this->db->where('store_account_payment',0);
		}

		return $this->db->count_all_results();
	}

	function get_recent_reservation_for_customer($customer_id)
	{
		$return = array();

		$this->db->select('reservations.*, SUM(quantity_purchased) as bedrooms_purchased');
		$this->db->from('reservations');
		$this->db->join('reservations_bedrooms', 'reservations.reservation_id = reservations_bedrooms.reservation_id');
		$this->db->where('customer_id', $customer_id);
		$this->db->where('deleted', 0);
		$this->db->order_by('reservation_time DESC');
		$this->db->group_by('reservations.reservation_id');
		$this->db->limit(10);

		foreach($this->db->get()->result_array() as $row)
		{
			$return[] = $row;
		}

		return $return;
	}

	function get_store_account_payment_total($reservation_id)
	{
		$this->db->select('SUM(payment_amount) as store_account_payment_total', false);
		$this->db->from('reservations_payments');
		$this->db->where('reservation_id', $reservation_id);
		$this->db->where('payment_type', lang('reserve_store_account'));

		$reservations_payments = $this->db->get()->row_array();

		return $reservations_payments['store_account_payment_total'] ? $reservations_payments['store_account_payment_total'] : 0;
	}
}
?>
