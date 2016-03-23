<?php
class Reserve extends CI_Model
{
	public function get_info($reserve_id)
	{
		$this->db->from('reservations');
		$this->db->where('reserve_id',$reserve_id);
		return $this->db->get();
	}
	
	function get_cash_reserve_total_for_shift($shift_start, $shift_end)
    {
		$reserve_totals = $this->get_reserve_totaled_by_id($shift_start, $shift_end);
		$register_id = $this->Employee->get_logged_in_employee_current_register_id();
        
		$this->db->select('reserve_payments.reserve_id, reserve_payments.payment_type, payment_amount', false);
      $this->db->from('reserve_payments');
      $this->db->join('reservations','reserve_payments.reserve_id=reservations.reserve_id');
		$this->db->where('reserve_payments.payment_date >=', $shift_start);
		$this->db->where('reserve_payments.payment_date <=', $shift_end);
		$this->db->where('register_id', $register_id);
		$this->db->where($this->db->dbprefix('reservations').'.deleted', 0);
		
		$payments_by_reserve = array();
		$reserve_payments = $this->db->get()->result_array();
		
		foreach($reserve_payments as $row)
		{
        	$payments_by_reserve[$row['reserve_id']][] = $row;
		}
				
		$payment_data = $this->Reserve->get_payment_data($payments_by_reserve,$reserve_totals);
		
		if (isset($payment_data[lang('reserve_cash')]))
		{
			return $payment_data[lang('reserve_cash')]['payment_amount'];
		}
		
		return 0.00;
    }
	
	function get_payment_data($payments_by_reserve,$reserve_totals)
	{
		$payment_data = array();
				
		foreach($payments_by_reserve as $reserve_id => $payment_rows)
		{
			if (isset($reserve_totals[$reserve_id]))
			{
				$total_reserve_balance = $reserve_totals[$reserve_id];
				usort($payment_rows, array('Reserve', '_sort_payments_for_reserve'));
			
				foreach($payment_rows as $row)
				{
					if ($row['payment_amount'] >=0)
					{
						$payment_amount = $row['payment_amount'] <= $total_reserve_balance ? $row['payment_amount'] : $total_reserve_balance;
					}
					else
					{
						$payment_amount = $row['payment_amount'] >= $total_reserve_balance ? $row['payment_amount'] : $total_reserve_balance;						
					}
					if (!isset($payment_data[$row['payment_type']]))
					{
						$payment_data[$row['payment_type']] = array('payment_type' => $row['payment_type'], 'payment_amount' => 0 );
					}
				
					if ($total_reserve_balance != 0)
					{
						$payment_data[$row['payment_type']]['payment_amount'] += $payment_amount;
					}
				
					$total_reserve_balance-=$payment_amount;
				}
			}
		}
		
		return $payment_data;
	}
	
	function get_payment_data_grouped_by_reserve($payments_by_reserve,$reserve_totals)
	{
		$payment_data = array();
				
		foreach($payments_by_reserve as $reserve_id => $payment_rows)
		{
			if (isset($reserve_totals[$reserve_id]))
			{
				$total_reserve_balance = $reserve_totals[$reserve_id];
				usort($payment_rows, array('Reserve', '_sort_payments_for_reserve'));
			
				foreach($payment_rows as $row)
				{
					if ($row['payment_amount'] >=0)
					{
						$payment_amount = $row['payment_amount'] <= $total_reserve_balance ? $row['payment_amount'] : $total_reserve_balance;
					}
					else
					{
						$payment_amount = $row['payment_amount'] >= $total_reserve_balance ? $row['payment_amount'] : $total_reserve_balance;						
					}
				
					if (!isset($payment_data[$reserve_id][$row['payment_type']]))
					{
						$payment_data[$reserve_id][$row['payment_type']] = array('reserve_id' => $reserve_id,'payment_type' => $row['payment_type'], 'payment_amount' => 0,'payment_date' => $row['payment_date'], 'reserve_time' => $row['reserve_time'] );
					}
				
					if ($total_reserve_balance != 0)
					{
						$payment_data[$reserve_id][$row['payment_type']]['payment_amount'] += $payment_amount;
					}
				
					$total_reserve_balance-=$payment_amount;
				}
			}
		}
		
		return $payment_data;
	}
	
	
	static function _sort_payments_for_reserve($a,$b)
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
	
	function get_reserve_totaled_by_id($shift_start, $shift_end)
	{
		$register_id = $this->Employee->get_logged_in_employee_current_register_id();
		
		$this->db->select('reserve.reserve_id', false);
      $this->db->from('reservations');
      $this->db->join('reserve_payments','reserve_payments.reserve_id=reservations.reserve_id');
		$this->db->where('reserve_payments.payment_date >=', $shift_start);
		$this->db->where('reserve_payments.payment_date <=', $shift_end);
		$this->db->where('register_id', $register_id);
		$this->db->where($this->db->dbprefix('reservations').'.deleted', 0);
		
		$reserve_ids = array();
		$result = $this->db->get()->result();
		foreach($result as $row)
		{
			$reserve_ids[] = $row->reserve_id;
		}
		
		$reserve_totals = array();
		
		if (count($reserve_ids) > 0)
		{
			$where = 'WHERE '.$this->db->dbprefix('reservations').'.reserve_id IN('.implode(',',$reserve_ids).')';
			$this->_create_reserve_items_temp_table_query($where);
			$this->db->select('reserve_id, SUM(total) as total', false);
			$this->db->from('reserve_items_temp');
			$this->db->group_by('reserve_id');
			
			foreach($this->db->get()->result_array() as $reserve_total_row)
			{
				$reserve_totals[$reserve_total_row['reserve_id']] = $reserve_total_row['total'];
			}
		}
		
		return $reserve_totals;
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
	function exists($reserve_id)
	{
		$this->db->from('reservations');
		$this->db->where('reserve_id',$reserve_id);
		$query = $this->db->get();

		return ($query->num_rows()==1);
	}
	
	function update($reserve_data, $reserve_id)
	{
		$this->db->where('reserve_id', $reserve_id);
		$success = $this->db->update('reservations',$reserve_data);
		
		return $success;
	}
	
	function save ($items,$customer_id,$employee_id, $sold_by_employee_id, $comment,$show_comment_on_receipt,$payments,$reserve_id=false, $suspended = 0, $cc_ref_no = '', $auth_code = '', $change_reserve_date=false,$balance=0, $store_account_payment = 0)
	{
		//we need to check the sale library for deleted taxes during sale
		$this->load->library('reserve_lib');
		
		if(count($items)==0)
			return -1;

		$payment_types='';
		foreach($payments as $payment_id=>$payment)
		{
			$payment_types=$payment_types.$payment['payment_type'].': '.to_currency($payment['payment_amount']).'<br />';
		}
		
		$tier_id = $this->reserve_lib->get_selected_tier_id();
		
		if (!$tier_id)
		{
			$tier_id = NULL;
		}
		
		$reserve_data = array(
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
			'tier_id' => $tier_id ? $tier_id : NULL,
		);
			
		if($reserve_id)
		{
			$old_date=$this->get_info($reserve_id)->row_array();
			$reserve_data['reserve_time']=$old_date['reserve_time'];
			
			if($change_reserve_date) 
			{
				$reserve_time = strtotime($change_reserve_date);
				if($reserve_time !== FALSE)
				{
					$reserve_data['reserve_time']=date('Y-m-d H:i:s', strtotime($change_reserve_date));
				}
			}
			
		}
		else
		{
			$reserve_data['reserve_time'] = date('Y-m-d H:i:s');
		}

		$this->db->query("SET autocommit=0");
		//Lock tables invovled in sale transaction so we don't have deadlock
		$this->db->query('LOCK TABLES '.$this->db->dbprefix('customers').' WRITE, '.$this->db->dbprefix('reservations').' WRITE, 
		'.$this->db->dbprefix('store_accounts').' WRITE, '.$this->db->dbprefix('reserve_payments').' WRITE, '.$this->db->dbprefix('reserve_items').' WRITE, 
		'.$this->db->dbprefix('giftcards').' WRITE, '.$this->db->dbprefix('location_items').' WRITE, 
		'.$this->db->dbprefix('inventory').' WRITE, '.$this->db->dbprefix('reserve_items_taxes').' WRITE,
		'.$this->db->dbprefix('reserve_item_kits').' WRITE, '.$this->db->dbprefix('reserve_item_kits_taxes').' WRITE,'.$this->db->dbprefix('people').' READ,'.$this->db->dbprefix('items').' READ
		,'.$this->db->dbprefix('employees_locations').' READ,'.$this->db->dbprefix('locations').' READ, '.$this->db->dbprefix('items_tier_prices').' READ
		, '.$this->db->dbprefix('location_items_tier_prices').' READ, '.$this->db->dbprefix('items_taxes').' READ, '.$this->db->dbprefix('item_kits').' READ
		, '.$this->db->dbprefix('location_item_kits').' READ, '.$this->db->dbprefix('item_kit_items').' READ, '.$this->db->dbprefix('employees').' READ , '.$this->db->dbprefix('item_kits_tier_prices').' READ
		, '.$this->db->dbprefix('location_item_kits_tier_prices').' READ, '.$this->db->dbprefix('location_items_taxes').' READ
		, '.$this->db->dbprefix('location_item_kits_taxes'). ' READ, '.$this->db->dbprefix('item_kits_taxes'). ' READ');
		
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

		 if ($reserve_id !== FALSE)
		 {
			 $previous_store_account_amount = $this->get_store_account_payment_total($reserve_id);
		 }
		 
		if ($reserve_id)
		{
			//Delete previoulsy sale so we can overwrite data
			if (!$this->delete($reserve_id, true))
			{
				$this->db->query("ROLLBACK");
				$this->db->query('UNLOCK TABLES');
				return -1;
			}
			
			$this->db->where('reserve_id', $reserve_id);
			if (!$this->db->update('reservations', $reserve_data))
			{
				$this->db->query("ROLLBACK");
				$this->db->query('UNLOCK TABLES');
				return -1;
			}
		}
		else
		{
			if (!$this->db->insert('reservations',$reserve_data))
			{
				$this->db->query("ROLLBACK");
				$this->db->query('UNLOCK TABLES');
				return -1;
			}
			$reserve_id = $this->db->insert_id();
		}
		
		
		//Only update store account payments if we are NOT an estimate (suspended = 2)
		if ($suspended != 2)
		{
			 //insert store account transaction 
			if($customer_id > 0 && $balance)
			{
			 	$store_account_transaction = array(
			      'customer_id'=>$customer_id,
			      'reserve_id'=>$reserve_id,
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
 			      'reserve_id'=>$reserve_id,
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
			        'reserve_id'=>$reserve_id,
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

			$reserve_payments_data = array
			(
				'reserve_id'=>$reserve_id,
				'payment_type'=>$payment['payment_type'],
				'payment_amount'=>$payment['payment_amount'],
				'payment_date' => $payment['payment_date'],
				'truncated_card' => $payment['truncated_card'],
				'card_issuer' => $payment['card_issuer'],
			);
			if (!$this->db->insert('reserve_payments',$reserve_payments_data))
			{
				$this->db->query("ROLLBACK");
				$this->db->query('UNLOCK TABLES');
				return -1;
			}
		}
	
		$has_added_giftcard_value_to_cost_price = $total_giftcard_payments > 0 ? false : true;
		$store_account_item_id = $this->Item->get_store_account_item_id();
		
		foreach($items as $line=>$item)
		{
			if (isset($item['item_id']))
			{
				$cur_item_info = $this->Item->get_info($item['item_id']);
				$cur_item_location_info = $this->Item_location->get_info($item['item_id']);
				
				if ($item['item_id'] != $store_account_item_id)
				{
					$cost_price = ($cur_item_location_info && $cur_item_location_info->cost_price) ? $cur_item_location_info->cost_price : $cur_item_info->cost_price;
				}
				else // Set cost price = price so we have no profit
				{
					$cost_price = $item['price'];
				}
				
				
				if (!$this->config->item('disable_subtraction_of_giftcard_amount_from_reserves'))
				{
					//Add to the cost price if we are using a giftcard as we have already recorded profit for sale of giftcard
					if (!$has_added_giftcard_value_to_cost_price)
					{
						$cost_price+= $total_giftcard_payments / $item['quantity'];
						$has_added_giftcard_value_to_cost_price = true;
					}
				}
				$reorder_level = ($cur_item_location_info && $cur_item_location_info->reorder_level) ? $cur_item_location_info->reorder_level : $cur_item_info->reorder_level;
				
				if ($cur_item_info->tax_included)
				{
					$item['price'] = get_price_for_item_excluding_taxes($item['item_id'], $item['price']);
				}
				
				$reserve_items_data = array
				(
					'reserve_id'=>$reserve_id,
					'item_id'=>$item['item_id'],
					'line'=>$item['line'],
					'description'=>$item['description'],
					'serialnumber'=>$item['serialnumber'],
					'quantity_purchased'=>$item['quantity'],
					'discount_percent'=>$item['discount'],
					'item_cost_price' =>  to_currency_no_money($cost_price,10),
					'item_unit_price'=>$item['price'],
					'commission' => get_commission_for_item($item['item_id'],$item['price'],$item['quantity'], $item['discount']),
				);

				if (!$this->db->insert('reserve_items',$reserve_items_data))
				{
					$this->db->query("ROLLBACK");
					$this->db->query('UNLOCK TABLES');
					return -1;
				}
				
				//Only update giftcard payments if we are NOT an estimate (suspended = 2)
				if ($suspended != 2)
				{
					//create giftcard from sales 
					if($item['name']==lang('reserve_giftcard') && !$this->Giftcard->get_giftcard_id($item['description'])) 
					{ 
						$giftcard_data = array(
							'giftcard_number'=>$item['description'],
							'value'=>$item['price'],
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
					if(!$cur_item_info->is_service && $cur_item_location_info->quantity > $reorder_level)
					{
						$stock_recorder_check=true;
					}
				
					//checks if the quantity is greater than 0
					if(!$cur_item_info->is_service && $cur_item_location_info->quantity > 0)
					{
						$out_of_stock_check=true;
					}
				
					//Update stock quantity IF not a service 
					if (!$cur_item_info->is_service)
					{
						$cur_item_location_info->quantity = $cur_item_location_info->quantity !== NULL ? $cur_item_location_info->quantity : 0;
					
						if (!$this->Item_location->save_quantity($cur_item_location_info->quantity - $item['quantity'], $item['item_id']))
						{
							$this->db->query("ROLLBACK");
							$this->db->query('UNLOCK TABLES');
							return -1;
						}
					}
				
					//Re-init $cur_item_location_info after updating quantity
					$cur_item_location_info = $this->Item_location->get_info($item['item_id']);
				
					//checks if the quantity is out of stock
					if($out_of_stock_check && $cur_item_location_info->quantity <= 0)
					{
						$message= $cur_item_info->name.' '.lang('reserve_is_out_stock').' '.to_quantity($cur_item_location_info->quantity);
						$email=true;
					
					}	
					//checks if the quantity hits reorder level 
					else if($stock_recorder_check && ($cur_item_location_info->quantity <= $reorder_level))
					{
						$message= $cur_item_info->name.' '.lang('reserve_hits_reorder_level').' '.to_quantity($cur_item_location_info->quantity);
						$email=true;
					}
				
					//send email 
					if($this->Location->get_info_for_key('receive_stock_alert') && $email)
					{			
						$this->load->library('email');
						$config = array();
						$config['mailtype'] = 'text';				
						$this->email->initialize($config);
						$this->email->from($this->Location->get_info_for_key('email') ? $this->Location->get_info_for_key('email') : 'no-reply@phppointofsale.com', $this->config->item('company'));
						$this->email->to($this->Location->get_info_for_key('stock_alert_email') ? $this->Location->get_info_for_key('stock_alert_email') : $this->Location->get_info_for_key('email')); 

						$this->email->subject(lang('reserve_stock_alert_item_name').$this->Item->get_info($item['item_id'])->name);
						$this->email->message($message);	
						$this->email->send();
					}
				
					if (!$cur_item_info->is_service)
					{
						$qty_buy = -$item['quantity'];
						$reserve_remarks =$this->config->item('reserve_prefix').' '.$reserve_id;
						$inv_data = array
						(
							'trans_date'=>date('Y-m-d H:i:s'),
							'trans_items'=>$item['item_id'],
							'trans_user'=>$employee_id,
							'trans_comment'=>$reserve_remarks,
							'trans_inventory'=>$qty_buy,
							'location_id' => $this->Employee->get_logged_in_employee_current_location_id() 
						);
						if (!$this->Inventory->insert($inv_data))
						{
							$this->db->query("ROLLBACK");
							$this->db->query('UNLOCK TABLES');
							return -1;
						}
					}
				}
			}
			else
			{
				$cur_item_kit_info = $this->Item_kit->get_info($item['item_kit_id']);
				$cur_item_kit_location_info = $this->Item_kit_location->get_info($item['item_kit_id']);
				
				$cost_price = ($cur_item_kit_location_info && $cur_item_kit_location_info->cost_price) ? $cur_item_kit_location_info->cost_price : $cur_item_kit_info->cost_price;
				
				
				if (!$this->config->item('disable_subtraction_of_giftcard_amount_from_reserves'))
				{
					//Add to the cost price if we are using a giftcard as we have already recorded profit for sale of giftcard
					if (!$has_added_giftcard_value_to_cost_price)
					{
						$cost_price+= $total_giftcard_payments / $item['quantity'];
						$has_added_giftcard_value_to_cost_price = true;
					}
				}
				
				if ($cur_item_kit_info->tax_included)
				{
					$item['price'] = get_price_for_item_kit_excluding_taxes($item['item_kit_id'], $item['price']);
				}
				
				$reserve_item_kits_data = array
				(
					'reserve_id'=>$reserve_id,
					'item_kit_id'=>$item['item_kit_id'],
					'line'=>$item['line'],
					'description'=>$item['description'],
					'quantity_purchased'=>$item['quantity'],
					'discount_percent'=>$item['discount'],
					'item_kit_cost_price' => $cost_price === NULL ? 0.00 : to_currency_no_money($cost_price,10),
					'item_kit_unit_price'=>$item['price'],
					'commission' => get_commission_for_item_kit($item['item_kit_id'],$item['price'],$item['quantity'], $item['discount']),
				);

				if (!$this->db->insert('reserve_item_kits',$reserve_item_kits_data))
				{
					$this->db->query("ROLLBACK");
					$this->db->query('UNLOCK TABLES');
					return -1;
				}
				
				foreach($this->Item_kit_items->get_info($item['item_kit_id']) as $item_kit_item)
				{
					$cur_item_info = $this->Item->get_info($item_kit_item->item_id);
					$cur_item_location_info = $this->Item_location->get_info($item_kit_item->item_id);
					
					$reorder_level = ($cur_item_location_info && $cur_item_location_info->reorder_level !== NULL) ? $cur_item_location_info->reorder_level : $cur_item_info->reorder_level;
					
					//Only do stock check + inventory update if we are NOT an estimate
					if ($suspended != 2)
					{
						$stock_recorder_check=false;
						$out_of_stock_check=false;
						$email=false;
						$message = '';


						//checks if the quantity is greater than reorder level
						if(!$cur_item_info->is_service && $cur_item_location_info->quantity > $reorder_level)
						{
							$stock_recorder_check=true;
						}

						//checks if the quantity is greater than 0
						if(!$cur_item_info->is_service && $cur_item_location_info->quantity > 0)
						{
							$out_of_stock_check=true;
						}

						//Update stock quantity IF not a service item and the quantity for item is NOT NULL
						if (!$cur_item_info->is_service)
						{
							$cur_item_location_info->quantity = $cur_item_location_info->quantity !== NULL ? $cur_item_location_info->quantity : 0;
								
							if (!$this->Item_location->save_quantity($cur_item_location_info->quantity - ($item['quantity'] * $item_kit_item->quantity),$item_kit_item->item_id))
							{
								$this->db->query("ROLLBACK");
								$this->db->query('UNLOCK TABLES');
								return -1;
							}
						}
					
						//Re-init $cur_item_location_info after updating quantity
						$cur_item_location_info = $this->Item_location->get_info($item_kit_item->item_id);
				
						//checks if the quantity is out of stock
						if($out_of_stock_check && !$cur_item_info->is_service && $cur_item_location_info->quantity <= 0)
						{
							$message= $cur_item_info->name.' '.lang('reserve_is_out_stock').' '.to_quantity($cur_item_location_info->quantity);
							$email=true;

						}	
						//checks if the quantity hits reorder level 
						else if($stock_recorder_check && ($cur_item_location_info->quantity <= $reorder_level))
						{
							$message= $cur_item_info->name.' '.lang('reserve_hits_reorder_level').' '.to_quantity($cur_item_location_info->quantity);
							$email=true;
						}

						//send email 
						if($this->Location->get_info_for_key('receive_stock_alert') && $email)
						{			
							$this->load->library('email');
							$config = array();
							$config['mailtype'] = 'text';				
							$this->email->initialize($config);
							$this->email->from($this->Location->get_info_for_key('email') ? $this->Location->get_info_for_key('email') : 'no-reply@phppointofsale.com', $this->config->item('company'));
							$this->email->to($this->Location->get_info_for_key('stock_alert_email') ? $this->Location->get_info_for_key('stock_alert_email') : $this->Location->get_info_for_key('email')); 

							$this->email->subject(lang('reserve_stock_alert_item_name').$cur_item_info->name);
							$this->email->message($message);	
							$this->email->send();
						}

						if (!$cur_item_info->is_service)
						{
							$qty_buy = -$item['quantity'] * $item_kit_item->quantity;
							$reserve_remarks =$this->config->item('reserve_prefix').' '.$reserve_id;
							$inv_data = array
							(
								'trans_date'=>date('Y-m-d H:i:s'),
								'trans_items'=>$item_kit_item->item_id,
								'trans_user'=>$employee_id,
								'trans_comment'=>$reserve_remarks,
								'trans_inventory'=>$qty_buy,
								'location_id' => $this->Employee->get_logged_in_employee_current_location_id()
							);
							if (!$this->Inventory->insert($inv_data))
							{
								$this->db->query("ROLLBACK");
								$this->db->query('UNLOCK TABLES');
								return -1;
							}				
						}
					}
				}
			}
			
			$customer = $this->Customer->get_info($customer_id);
 			if ($customer_id == -1 or $customer->taxable)
 			{
				if (isset($item['item_id']))
				{
					foreach($this->Item_taxes_finder->get_info($item['item_id']) as $row)
					{
						$tax_name = $row['percent'].'% ' . $row['name'];
				
						//Only save sale if the tax has NOT been deleted
						if (!in_array($tax_name, $this->reserve_lib->get_deleted_taxes()))
						{	
							$query_result = $this->db->insert('reserve_items_taxes', array(
								'reserve_id' 	=>$reserve_id,
								'item_id' 	=>$item['item_id'],
								'line'      =>$item['line'],
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
				else
				{
					foreach($this->Item_kit_taxes_finder->get_info($item['item_kit_id']) as $row)
					{
						$tax_name = $row['percent'].'% ' . $row['name'];
				
						//Only save sale if the tax has NOT been deleted
						if (!in_array($tax_name, $this->reserve_lib->get_deleted_taxes()))
						{
							$query_result = $this->db->insert('reserve_item_kits_taxes', array(
								'reserve_id' 		=>$reserve_id,
								'item_kit_id'	=>$item['item_kit_id'],
								'line'      	=>$item['line'],
								'name'			=>$row['name'],
								'percent' 		=>$row['percent'],
								'cumulative'	=>$row['cumulative']
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
	
		return $reserve_id;				
	}
	
	function update_store_account($reserve_id,$undelete=0)
	{
		//update if Store account payment exists
		$this->db->from('reserve_payments');
		$this->db->where('payment_type',lang('reserve_store_account'));
		$this->db->where('reserve_id',$reserve_id);
		$to_be_paid_result = $this->db->get();
		
		$customer_id=$this->get_customer($reserve_id)->person_id;
		
		
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
	
	function update_giftcard_balance($reserve_id,$undelete=0)
	{
		//if gift card payment exists add the amount to giftcard balance
			$this->db->from('reserve_payments');
			$this->db->like('payment_type',lang('reserve_giftcard'));
			$this->db->where('reserve_id',$reserve_id);
			$reserve_payment = $this->db->get();
			
			if($reserve_payment->num_rows >=1)
			{
				foreach($reserve_payment->result() as $row)
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
	
	function delete($reserve_id, $all_data = false)
	{
		$reserve_info = $this->get_info($reserve_id)->row_array();
		$suspended = $reserve_info['suspended'];
		$employee_id=$this->Employee->get_logged_in_employee_info()->person_id;
		
		//Only update stock quantity if we are NOT an estimate ($suspendd = 2)
		if ($suspended != 2)
		{
			$this->db->select('reserve.location_id, item_id, quantity_purchased');
			$this->db->from('reserve_items');
			$this->db->join('reservations', 'reserve.reserve_id = reserve_items.reserve_id');
			$this->db->where('reserve_items.reserve_id', $reserve_id);
		
			foreach($this->db->get()->result_array() as $reserve_item_row)
			{
				$reserve_location_id = $reserve_item_row['location_id'];
				$cur_item_info = $this->Item->get_info($reserve_item_row['item_id']);	
				$cur_item_location_info = $this->Item_location->get_info($reserve_item_row['item_id'], $reserve_location_id);
			
				$cur_item_quantity = $this->Item_location->get_location_quantity($reserve_item_row['item_id'], $reserve_location_id);
			
				if (!$cur_item_info->is_service)
				{
					//Update stock quantity
					$this->Item_location->save_quantity($cur_item_quantity + $reserve_item_row['quantity_purchased'],$reserve_item_row['item_id'], $reserve_location_id);
					
					$reserve_remarks =$this->config->item('reserve_prefix').' '.$reserve_id;
					$inv_data = array
					(
						'location_id' => $reserve_location_id,
						'trans_date'=>date('Y-m-d H:i:s'),
						'trans_items'=>$reserve_item_row['item_id'],
						'trans_user'=>$employee_id,
						'trans_comment'=>$reserve_remarks,
						'trans_inventory'=>$reserve_item_row['quantity_purchased']
					);
					$this->Inventory->insert($inv_data);
				}
			}
		}

		//Only update stock quantity + store accounts + giftcard balance if we are NOT an estimate ($suspended = 2)
		if ($suspended != 2)
		{		
			$this->db->select('reserve.location_id, item_kit_id, quantity_purchased');
			$this->db->from('reserve_item_kits');
			$this->db->join('reservations', 'reserve.reserve_id = reserve_item_kits.reserve_id');
			$this->db->where('reserve_item_kits.reserve_id', $reserve_id);
		
			foreach($this->db->get()->result_array() as $reserve_item_kit_row)
			{
				foreach($this->Item_kit_items->get_info($reserve_item_kit_row['item_kit_id']) as $item_kit_item)
				{
					$reserve_location_id = $reserve_item_kit_row['location_id'];
					$cur_item_info = $this->Item->get_info($item_kit_item->item_id);
					$cur_item_location_info = $this->Item_location->get_info($item_kit_item->item_id, $reserve_location_id);

					if (!$cur_item_info->is_service)
					{
						$cur_item_location_info->quantity = $cur_item_location_info->quantity !== NULL ? $cur_item_location_info->quantity : 0;
					
						$this->Item_location->save_quantity($cur_item_location_info->quantity + ($reserve_item_kit_row['quantity_purchased'] * $item_kit_item->quantity),$item_kit_item->item_id, $reserve_location_id);

						$reserve_remarks =$this->config->item('reserve_prefix').' '.$reserve_id;
						$inv_data = array
						(
							'location_id' => $reserve_location_id,
							'trans_date'=>date('Y-m-d H:i:s'),
							'trans_items'=>$item_kit_item->item_id,
							'trans_user'=>$employee_id,
							'trans_comment'=>$reserve_remarks,
							'trans_inventory'=>$reserve_item_kit_row['quantity_purchased'] * $item_kit_item->quantity
						);
						$this->Inventory->insert($inv_data);
					}				
				}
			}

			$this->update_store_account($reserve_id);
			$this->update_giftcard_balance($reserve_id);
			
			//Only insert store account transaction if we aren't deleting the whole sale.
			//When deleting the whole sale save() takes care of this
			if (!$all_data)
			{
		 		$previous_store_account_amount = $this->get_store_account_payment_total($reserve_id);
			
				if ($previous_store_account_amount)
				{	
					$store_account_transaction = array(
			   		'customer_id'=>$reserve_info['customer_id'],
			      	'reserve_id'=>$reserve_id,
						'comment'=>$reserve_info['comment'],
			      	'transaction_amount'=>-$previous_store_account_amount,
						'balance'=>$this->Customer->get_info($reserve_info['customer_id'])->balance,
						'date' => date('Y-m-d H:i:s')
					);
					$this->db->insert('store_accounts',$store_account_transaction);
				}
			}
		}
		
		if ($all_data)
		{
			$this->db->delete('reserve_payments', array('reserve_id' => $reserve_id)); 
			$this->db->delete('reserve_items_taxes', array('reserve_id' => $reserve_id)); 
			$this->db->delete('reserve_items', array('reserve_id' => $reserve_id)); 
			$this->db->delete('reserve_item_kits_taxes', array('reserve_id' => $reserve_id)); 
			$this->db->delete('reserve_item_kits', array('reserve_id' => $reserve_id)); 
		}

		$this->db->where('reserve_id', $reserve_id);
		return $this->db->update('reservations', array('deleted' => 1,'deleted_by'=>$employee_id));
	}
	
	function undelete($reserve_id)
	{
		$reserve_info = $this->get_info($reserve_id)->row_array();
		$suspended = $reserve_info['suspended'];
		$employee_id=$this->Employee->get_logged_in_employee_info()->person_id;
	
		//Only update stock quantity + store accounts + giftcard balance if we are NOT an estimate ($suspended = 2)
		if ($suspended != 2)
		{		
			$this->db->select('reserve.location_id, item_id, quantity_purchased');
			$this->db->from('reserve_items');
			$this->db->join('reservations', 'reserve.reserve_id = reserve_items.reserve_id');
			$this->db->where('reserve_items.reserve_id', $reserve_id);
		
			foreach($this->db->get()->result_array() as $reserve_item_row)
			{
				$reserve_location_id = $reserve_item_row['location_id'];
				$cur_item_info = $this->Item->get_info($reserve_item_row['item_id']);	
				$cur_item_location_info = $this->Item_location->get_info($reserve_item_row['item_id'], $reserve_location_id);

				if (!$cur_item_info->is_service && $cur_item_location_info->quantity !== NULL)
				{
					//Update stock quantity
					$this->Item_location->save_quantity($cur_item_location_info->quantity - $reserve_item_row['quantity_purchased'],$reserve_item_row['item_id']);
		
					$reserve_remarks =$this->config->item('reserve_prefix').' '.$reserve_id;
					$inv_data = array
					(
						'location_id' => $reserve_location_id,
						'trans_date'=>date('Y-m-d H:i:s'),
						'trans_items'=>$reserve_item_row['item_id'],
						'trans_user'=>$employee_id,
						'trans_comment'=>$reserve_remarks,
						'trans_inventory'=>-$reserve_item_row['quantity_purchased']
						);
					$this->Inventory->insert($inv_data);
				}
			}
		
			$this->update_store_account($reserve_id,1);
			$this->update_giftcard_balance($reserve_id,1);
			
		 	$previous_store_account_amount = $this->get_store_account_payment_total($reserve_id);
			
			if ($previous_store_account_amount)
			{	
			 	$store_account_transaction = array(
			      'customer_id'=>$reserve_info['customer_id'],
			      'reserve_id'=>$reserve_id,
					'comment'=>$reserve_info['comment'],
			      'transaction_amount'=>$previous_store_account_amount,
					'balance'=>$this->Customer->get_info($reserve_info['customer_id'])->balance,
					'date' => date('Y-m-d H:i:s')
				);
				$this->db->insert('store_accounts',$store_account_transaction);
			}
			
			
			$this->db->select('reserve.location_id, item_kit_id, quantity_purchased');
			$this->db->from('reserve_item_kits');
			$this->db->join('reservations', 'reserve.reserve_id = reserve_item_kits.reserve_id');
			$this->db->where('reserve_item_kits.reserve_id', $reserve_id);
		
			foreach($this->db->get()->result_array() as $reserve_item_kit_row)
			{
				foreach($this->Item_kit_items->get_info($reserve_item_kit_row['item_kit_id']) as $item_kit_item)
				{
					$reserve_location_id = $reserve_item_kit_row['location_id'];
					$cur_item_info = $this->Item->get_info($item_kit_item->item_id);
					$cur_item_location_info = $this->Item_location->get_info($item_kit_item->item_id, $reserve_location_id);
					if (!$cur_item_info->is_service && $cur_item_location_info->quantity !== NULL)
					{
						$this->Item_location->save_quantity($cur_item_location_info->quantity - ($reserve_item_kit_row['quantity_purchased'] * $item_kit_item->quantity),$item_kit_item->item_id, $reserve_location_id);
					
						$reserve_remarks =$this->config->item('reserve_prefix').' '.$reserve_id;
						$inv_data = array
						(
							'location_id' => $reserve_location_id,
							'trans_date'=>date('Y-m-d H:i:s'),
							'trans_items'=>$item_kit_item->item_id,
							'trans_user'=>$employee_id,
							'trans_comment'=>$reserve_remarks,
							'trans_inventory'=>-$reserve_item_kit_row['quantity_purchased'] * $item_kit_item->quantity
						);
						$this->Inventory->insert($inv_data);					
					}
				}
			}	
		}
		
		$this->db->where('reserve_id', $reserve_id);
		return $this->db->update('reservations', array('deleted' => 0, 'deleted_by' => NULL));
	}

	function get_reserve_items($reserve_id)
	{
		$this->db->from('reserve_items');
		$this->db->where('reserve_id',$reserve_id);
		$this->db->order_by('line');
		return $this->db->get();
	}
	
	function get_reserve_items_ordered_by_category($reserve_id)
	{
		$this->db->select('*, reserve_items.description as reserve_items_description');
		$this->db->from('reserve_items');
		$this->db->join('items', 'items.item_id = reserve_items.item_id');
		$this->db->where('reserve_id',$reserve_id);
		$this->db->order_by('category, name');
		return $this->db->get();		
	}

	function get_reserve_item_kits($reserve_id)
	{
		$this->db->from('reserve_item_kits');
		$this->db->where('reserve_id',$reserve_id);
		$this->db->order_by('line');
		return $this->db->get();
	}
	
	function get_reserve_item_kits_ordered_by_category($reserve_id)
	{
		$this->db->from('reserve_item_kits');
		$this->db->join('item_kits', 'item_kits.item_kit_id = reserve_item_kits.item_kit_id');
		$this->db->where('reserve_id',$reserve_id);
		$this->db->order_by('category, name');
		return $this->db->get();		
	}
	
	function get_reserve_items_taxes($reserve_id, $line = FALSE)
	{
		$item_where = '';
		
		if ($line)
		{
			$item_where = 'and '.$this->db->dbprefix('reserve_items').'.line = '.$line;
		}

		$query = $this->db->query('SELECT name, percent, cumulative, item_unit_price as price, quantity_purchased as quantity, discount_percent as discount '.
		'FROM '. $this->db->dbprefix('reserve_items_taxes'). ' JOIN '.
		$this->db->dbprefix('reserve_items'). ' USING (reserve_id, item_id, line) '.
		'WHERE '.$this->db->dbprefix('reserve_items_taxes').".reserve_id = $reserve_id".' '.$item_where.' '.
		'ORDER BY '.$this->db->dbprefix('reserve_items').'.line,'.$this->db->dbprefix('reserve_items').'.item_id,cumulative,name,percent');
		return $query->result_array();
	}
	
	function get_reserve_item_kits_taxes($reserve_id, $line = FALSE)
	{
		$item_kit_where = '';
		
		if ($line)
		{
			$item_kit_where = 'and '.$this->db->dbprefix('reserve_item_kits').'.line = '.$line;
		}
		
		$query = $this->db->query('SELECT name, percent, cumulative, item_kit_unit_price as price, quantity_purchased as quantity, discount_percent as discount '.
		'FROM '. $this->db->dbprefix('reserve_item_kits_taxes'). ' JOIN '.
		$this->db->dbprefix('reserve_item_kits'). ' USING (reserve_id, item_kit_id, line) '.
		'WHERE '.$this->db->dbprefix('reserve_item_kits_taxes').".reserve_id = $reserve_id".' '.$item_kit_where.' '.
		'ORDER BY '.$this->db->dbprefix('reserve_item_kits').'.line,'.$this->db->dbprefix('reserve_item_kits').'.item_kit_id,cumulative,name,percent');
		return $query->result_array();	
	}

	function get_reserve_payments($reserve_id)
	{
		$this->db->from('reserve_payments');
		$this->db->where('reserve_id',$reserve_id);
		return $this->db->get();
	}

	function get_customer($reserve_id)
	{
		$this->db->from('reservations');
		$this->db->where('reserve_id',$reserve_id);
		return $this->Customer->get_info($this->db->get()->row()->customer_id);
	}
	
	function get_comment($reserve_id)
	{
		$this->db->from('reservations');
		$this->db->where('reserve_id',$reserve_id);
		return $this->db->get()->row()->comment;
	}
	
	function get_comment_on_receipt($reserve_id)
	{
		$this->db->from('reservations');
		$this->db->where('reserve_id',$reserve_id);
		return $this->db->get()->row()->show_comment_on_receipt;
	}
		
	function get_sold_by_employee_id($reserve_id)
	{
		$this->db->from('reservations');
		$this->db->where('reserve_id',$reserve_id);
		return $this->db->get()->row()->sold_by_employee_id;
	}

	//We create a temp table that allows us to do easy report/sales queries
	public function create_reserve_items_temp_table($params)
	{
		$location_id = $this->Employee->get_logged_in_employee_current_location_id();		
		$where = '';
		
		if (isset($params['reserve_ids']))
		{
			if (!empty($params['reserve_ids']))
			{
				for($k=0;$k<count($params['reserve_ids']);$k++)
				{
					$params['reserve_ids'][$k] = $this->db->escape($params['reserve_ids'][$k]);
				}
				
				$where.='WHERE '.$this->db->dbprefix('reservations').".reserve_id IN(".implode(',', $params['reserve_ids']).")";
			}
			else
			{
				$where.='WHERE '.$this->db->dbprefix('reservations').".reserve_id IN(0)";
			}
		}
		elseif (isset($params['start_date']) && isset($params['end_date']))
		{
			$where = 'WHERE reserve_time BETWEEN '.$this->db->escape($params['start_date']).' and '.$this->db->escape($params['end_date']).' and '.$this->db->dbprefix('reservations').'.location_id='.$this->db->escape($location_id). (($this->config->item('hide_store_account_payments_in_reports') ) ? ' and '.$this->db->dbprefix('reservations').'.store_account_payment=0' : '');
		
			//Added for detailed_suspended_report, we don't need this for other reports as we are always going to have start + end date
			if (isset($params['force_suspended']) && $params['force_suspended'])
			{
				$where .=' and suspended != 0';				
			}
			elseif ($this->config->item('hide_layaways_reserve_in_reports'))
			{
				$where .=' and suspended = 0';
			}
			else
			{
				$where .=' and suspended != 2';					
			}
		}
		elseif ($this->config->item('hide_layaways_reserve_in_reports'))
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
	
		$return = $this->_create_reserve_items_temp_table_query($where);		
		return $return;
	}
	
	function _create_reserve_items_temp_table_query($where)
	{
		set_time_limit(0);

		return $this->db->query("CREATE TEMPORARY TABLE ".$this->db->dbprefix('reserve_items_temp')."
		(SELECT ".$this->db->dbprefix('reservations').".deleted as deleted,".$this->db->dbprefix('reservations').".deleted_by as deleted_by, reserve_time, date(reserve_time) as reserve_date, ".$this->db->dbprefix('registers').'.name as register_name,'.$this->db->dbprefix('reserve_items').".reserve_id, comment,payment_type, customer_id, employee_id, sold_by_employee_id, 
		".$this->db->dbprefix('items').".item_id, NULL as item_kit_id, supplier_id, quantity_purchased, item_cost_price, item_unit_price, category, 
		discount_percent, (item_unit_price*quantity_purchased-item_unit_price*quantity_purchased*discount_percent/100) as subtotal,
		".$this->db->dbprefix('reserve_items').".line as line, serialnumber, ".$this->db->dbprefix('reserve_items').".description as description,
		(item_unit_price*quantity_purchased-item_unit_price*quantity_purchased*discount_percent/100)+(item_unit_price*quantity_purchased-item_unit_price*quantity_purchased*discount_percent/100)*(SUM(CASE WHEN cumulative != 1 THEN percent ELSE 0 END)/100) 
		+(((item_unit_price*quantity_purchased-item_unit_price*quantity_purchased*discount_percent/100)*(SUM(CASE WHEN cumulative != 1 THEN percent ELSE 0 END)/100) + (item_unit_price*quantity_purchased-item_unit_price*quantity_purchased*discount_percent/100))
		*(SUM(CASE WHEN cumulative = 1 THEN percent ELSE 0 END))/100) as total,
		(item_unit_price*quantity_purchased-item_unit_price*quantity_purchased*discount_percent/100)*(SUM(CASE WHEN cumulative != 1 THEN percent ELSE 0 END)/100) 
		+(((item_unit_price*quantity_purchased-item_unit_price*quantity_purchased*discount_percent/100)*(SUM(CASE WHEN cumulative != 1 THEN percent ELSE 0 END)/100) + (item_unit_price*quantity_purchased-item_unit_price*quantity_purchased*discount_percent/100))
		*(SUM(CASE WHEN cumulative = 1 THEN percent ELSE 0 END))/100) as tax,
		(item_unit_price*quantity_purchased-item_unit_price*quantity_purchased*discount_percent/100) - (item_cost_price*quantity_purchased) as profit, commission, store_account_payment
		FROM ".$this->db->dbprefix('reserve_items')."
		INNER JOIN ".$this->db->dbprefix('reservations')." ON  ".$this->db->dbprefix('reserve_items').'.reserve_id='.$this->db->dbprefix('reservations').'.reserve_id'."
		INNER JOIN ".$this->db->dbprefix('items')." ON  ".$this->db->dbprefix('reserve_items').'.item_id='.$this->db->dbprefix('items').'.item_id'."
		LEFT OUTER JOIN ".$this->db->dbprefix('suppliers')." ON  ".$this->db->dbprefix('items').'.supplier_id='.$this->db->dbprefix('suppliers').'.person_id'."
		LEFT OUTER JOIN ".$this->db->dbprefix('reserve_items_taxes')." ON  "
		.$this->db->dbprefix('reserve_items').'.reserve_id='.$this->db->dbprefix('reserve_items_taxes').'.reserve_id'." and "
		.$this->db->dbprefix('reserve_items').'.item_id='.$this->db->dbprefix('reserve_items_taxes').'.item_id'." and "
		.$this->db->dbprefix('reserve_items').'.line='.$this->db->dbprefix('reserve_items_taxes').'.line'. "
		LEFT OUTER JOIN ".$this->db->dbprefix('registers')." ON  ".$this->db->dbprefix('registers').'.register_id='.$this->db->dbprefix('reservations').'.register_id'."
		$where
		GROUP BY reserve_id, item_id, line) 
		UNION ALL
		(SELECT ".$this->db->dbprefix('reservations').".deleted as deleted,".$this->db->dbprefix('reservations').".deleted_by as deleted_by, reserve_time, date(reserve_time) as reserve_date, ".$this->db->dbprefix('registers').'.name as register_name,'.$this->db->dbprefix('reserve_item_kits').".reserve_id, comment,payment_type, customer_id, employee_id, sold_by_employee_id,
		NULL as item_id, ".$this->db->dbprefix('item_kits').".item_kit_id, '' as supplier_id, quantity_purchased, item_kit_cost_price, item_kit_unit_price, category, 
		discount_percent, (item_kit_unit_price*quantity_purchased-item_kit_unit_price*quantity_purchased*discount_percent/100) as subtotal,
		".$this->db->dbprefix('reserve_item_kits').".line as line, '' as serialnumber, ".$this->db->dbprefix('reserve_item_kits').".description as description,
		(item_kit_unit_price*quantity_purchased-item_kit_unit_price*quantity_purchased*discount_percent/100)+(item_kit_unit_price*quantity_purchased-item_kit_unit_price*quantity_purchased*discount_percent/100)*(SUM(CASE WHEN cumulative != 1 THEN percent ELSE 0 END)/100) 
		+(((item_kit_unit_price*quantity_purchased-item_kit_unit_price*quantity_purchased*discount_percent/100)*(SUM(CASE WHEN cumulative != 1 THEN percent ELSE 0 END)/100) + (item_kit_unit_price*quantity_purchased-item_kit_unit_price*quantity_purchased*discount_percent/100))
		*(SUM(CASE WHEN cumulative = 1 THEN percent ELSE 0 END))/100) as total,
		(item_kit_unit_price*quantity_purchased-item_kit_unit_price*quantity_purchased*discount_percent/100)*(SUM(CASE WHEN cumulative != 1 THEN percent ELSE 0 END)/100) 
		+(((item_kit_unit_price*quantity_purchased-item_kit_unit_price*quantity_purchased*discount_percent/100)*(SUM(CASE WHEN cumulative != 1 THEN percent ELSE 0 END)/100) + (item_kit_unit_price*quantity_purchased-item_kit_unit_price*quantity_purchased*discount_percent/100))
		*(SUM(CASE WHEN cumulative = 1 THEN percent ELSE 0 END))/100) as tax,
		(item_kit_unit_price*quantity_purchased-item_kit_unit_price*quantity_purchased*discount_percent/100) - (item_kit_cost_price*quantity_purchased) as profit, commission, store_account_payment
		FROM ".$this->db->dbprefix('reserve_item_kits')."
		INNER JOIN ".$this->db->dbprefix('reservations')." ON  ".$this->db->dbprefix('reserve_item_kits').'.reserve_id='.$this->db->dbprefix('reservations').'.reserve_id'."
		INNER JOIN ".$this->db->dbprefix('item_kits')." ON  ".$this->db->dbprefix('reserve_item_kits').'.item_kit_id='.$this->db->dbprefix('item_kits').'.item_kit_id'."
		LEFT OUTER JOIN ".$this->db->dbprefix('reserve_item_kits_taxes')." ON  "
		.$this->db->dbprefix('reserve_item_kits').'.reserve_id='.$this->db->dbprefix('reserve_item_kits_taxes').'.reserve_id'." and "
		.$this->db->dbprefix('reserve_item_kits').'.item_kit_id='.$this->db->dbprefix('reserve_item_kits_taxes').'.item_kit_id'." and "
		.$this->db->dbprefix('reserve_item_kits').'.line='.$this->db->dbprefix('reserve_item_kits_taxes').'.line'. "
		LEFT OUTER JOIN ".$this->db->dbprefix('registers')." ON  ".$this->db->dbprefix('registers').'.register_id='.$this->db->dbprefix('reservations').'.register_id'."
		$where
		GROUP BY reserve_id, item_kit_id, line) ORDER BY reserve_id, line");
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
		$this->db->join('customers', 'reserve.customer_id = customers.person_id', 'left');
		$this->db->join('people', 'customers.person_id = people.person_id', 'left');
		$this->db->where('reserve.deleted', 0);
		$this->db->where_in('suspended', $suspended_types);
		$this->db->where_in('location_id', $location_id);
		$this->db->order_by('reserve_id');
		$reservations = $this->db->get()->result_array();

		for($k=0;$k<count($reservations);$k++)
		{
			$item_names = array();
			$this->db->select('name');
			$this->db->from('items');
			$this->db->join('reserve_items', 'reserve_items.item_id = items.item_id');
			$this->db->where('reserve_id', $reservations[$k]['reserve_id']);
		
			foreach($this->db->get()->result_array() as $row)
			{
				$item_names[] = $row['name'];
			}
			
			$this->db->select('name');
			$this->db->from('item_kits');
			$this->db->join('reserve_item_kits', 'reserve_item_kits.item_kit_id = item_kits.item_kit_id');
			$this->db->where('reserve_id', $reservations[$k]['reserve_id']);
		
			foreach($this->db->get()->result_array() as $row)
			{
				$item_names[] = $row['name'];
			}
			
			
			$reservations[$k]['items'] = implode(', ', $item_names);
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
	
	function get_recent_reserve_for_customer($customer_id)
	{
		$return = array();
		
		$this->db->select('reserve.*, SUM(quantity_purchased) as items_purchased');
		$this->db->from('reservations');
		$this->db->join('reserve_items', 'reserve.reserve_id = reserve_items.reserve_id');
		$this->db->where('customer_id', $customer_id);
		$this->db->where('deleted', 0);
		$this->db->order_by('reserve_time DESC');
		$this->db->group_by('reserve.reserve_id');
		$this->db->limit(10);
		
		foreach($this->db->get()->result_array() as $row)
		{
			$return[] = $row;
		}

		return $return;
	}
	
	function get_store_account_payment_total($reserve_id)
	{
		$this->db->select('SUM(payment_amount) as store_account_payment_total', false);
		$this->db->from('reserve_payments');
		$this->db->where('reserve_id', $reserve_id);
		$this->db->where('payment_type', lang('reserve_store_account'));
		
		$reserve_payments = $this->db->get()->row_array();	
		
		return $reserve_payments['store_account_payment_total'] ? $reserve_payments['store_account_payment_total'] : 0;
	}
}
?>
