<?php
require_once ("secure_area.php");
class Reserve extends Secure_area
{
	function __construct()
	{
		parent::__construct('reserve');
		$this->load->library('reserve_lib');
	}

	function index()
	{
		if($this->config->item('automatically_show_comments_on_receipt'))
		{
			$this->reserve_lib->set_comment_on_receipt(1);
		}

		$location_id=$this->Employee->get_logged_in_employee_current_location_id();

		$register_count = $this->Register->count_all($location_id);

		if ($register_count > 0)
		{
			if ($register_count == 1)
			{
				$registers = $this->Register->get_all($location_id);
				$register = $registers->row_array();

				if (isset($register['register_id']))
				{
					$this->Employee->set_employee_current_register_id($register['register_id']);
				}
			}

			if (!$this->Employee->get_logged_in_employee_current_register_id())
			{
				$this->load->view('reserve/choose_register');
				return;
			}
		}

		if ($this->config->item('track_cash'))
		{
			if ($this->input->post('opening_amount') != '')
			{
				$now = date('Y-m-d H:i:s');

				$cash_register = new stdClass();
				$cash_register->register_id = $this->Employee->get_logged_in_employee_current_register_id();
				$cash_register->employee_id_open = $this->session->userdata('person_id');
				$cash_register->shift_start = $now;
				$cash_register->open_amount = $this->input->post('opening_amount');
				$cash_register->close_amount = 0;
				$cash_register->cash_reserve_amount = 0;
				$this->Reservation->insert_register($cash_register);

				redirect(site_url('reserve'));
			}
			else if ($this->Reservation->is_register_log_open())
			{
				$this->_reload(array(), false);
			}
			else
			{
				$this->load->view('reserve/opening_amount');
			}
		}
		else
		{
			$this->_reload(array(), false);
		}
	}

	function choose_register($register_id)
	{
		if ($this->Register->exists($register_id))
		{
			$this->Employee->set_employee_current_register_id($register_id);
		}

		redirect(site_url('reserve'));
		return;
	}

	function clear_register()
	{
		//Clear out logged in register when we switch locations
		$this->Employee->set_employee_current_register_id(false);

		redirect(site_url('reserve'));
		return;
	}

	function closeregister()
	{
		if (!$this->Reservation->is_register_log_open())
		{
			redirect(site_url('home'));
			return;
		}
		$cash_register = $this->Reservation->get_current_register_log();
		$continueUrl = $this->input->get('continue');
		if ($this->input->post('closing_amount') != '') {
			$now = date('Y-m-d H:i:s');
			$cash_register->register_id = $this->Employee->get_logged_in_employee_current_register_id();
			$cash_register->employee_id_close = $this->session->userdata('person_id');
			$cash_register->shift_end = $now;
			$cash_register->close_amount = $this->input->post('closing_amount');
			$cash_register->cash_reserve_amount = $this->Reservation->get_cash_reserve_total_for_shift($cash_register->shift_start, $cash_register->shift_end);
			unset($cash_register->register_log_id);
			$this->Reservation->update_register_log($cash_register);
			if ($continueUrl == 'logout') {
				redirect(site_url('home/logout'));
			} else {
				redirect(site_url('home'));
			}
		} else {
			$this->load->view('reserves/closing_amount', array(
				'continue'=>$continueUrl ? "?continue=$continueUrl" : '',
				'closeout'=>$cash_register->open_amount + $this->Reservation->get_cash_reserve_total_for_shift($cash_register->shift_start, date("Y-m-d H:i:s"))
			));
		}
	}

	function room_search()
	{
		$suggestions = $this->Room->get_room_search_suggestions($this->input->get('term'),100);
		//$suggestions = array_merge($suggestions, $this->Room_kit->get_room_kit_search_suggestions($this->input->get('term'),100));
		echo json_encode($suggestions);
	}
	

	function customer_search()
	{
		$suggestions = $this->Customer->get_customer_search_suggestions($this->input->get('term'),100);
		echo json_encode($suggestions);
	}

	function select_customer()
	{
		$data = array();
		$customer_id = $this->input->post("customer");

		if ($this->Customer->account_number_exists($customer_id))
		{
			$customer_id = $this->Customer->customer_id_from_account_number($customer_id);
		}

		if ($this->Customer->exists($customer_id))
		{
			$customer_info=$this->Customer->get_info($customer_id);

			if ($customer_info->tier_id)
			{
				$this->reserve_lib->set_selected_tier_id($customer_info->tier_id);
			}

			$this->reserve_lib->set_customer($customer_id);
			if($this->config->item('automatically_email_receipt'))
			{
				$this->reserve_lib->set_email_receipt(1);
			}
		}
		else
		{
			$data['error']=lang('reserve_unable_to_add_customer');
		}
		$this->_reload($data);
	}

	function change_mode()
	{
		$mode = $this->input->post("mode");
		$this->reserve_lib->set_mode($mode);

		// if ($mode == 'store_account_payment')
		// {
		// 	$store_account_payment_room_id = $this->Room->create_or_update_store_account_room();
		// 	$this->reserve_lib->empty_cart();
		// 	$this->reserve_lib->add_room($store_account_payment_room_id,1);
		// }

		$this->_reload();
	}

	function set_comment()
	{
 	  $this->reserve_lib->set_comment($this->input->post('comment'));
	}

	function set_change_reserve_date()
	{
 	  $this->reserve_lib->set_change_reserve_date($this->input->post('change_reserve_date'));
	}

	function set_change_reserve_date_enable()
	{
 	  $this->reserve_lib->set_change_reserve_date_enable($this->input->post('change_reserve_date_enable'));
	  if (!$this->reserve_lib->get_change_reserve_date())
	  {
	 	  $this->reserve_lib->set_change_reserve_date(date(get_date_format()));
	  }
	}

	function set_comment_on_receipt()
	{
 	  $this->reserve_lib->set_comment_on_receipt($this->input->post('show_comment_on_receipt'));
	}

	function set_email_receipt()
	{
 	  $this->reserve_lib->set_email_receipt($this->input->post('email_receipt'));
	}

	function set_save_credit_card_info()
	{
 	  $this->reserve_lib->set_save_credit_card_info($this->input->post('save_credit_card_info'));
	}

	function set_use_saved_cc_info()
	{
 	  $this->reserve_lib->set_use_saved_cc_info($this->input->post('use_saved_cc_info'));
	}

	function set_tier_id()
	{
 	  $this->reserve_lib->set_selected_tier_id($this->input->post('tier_id'));
	}

	function set_sold_by_employee_id()
	{
 	  $this->reserve_lib->set_sold_by_employee_id($this->input->post('sold_by_employee_id') ? $this->input->post('sold_by_employee_id') : NULL);
	}
	
	function set_star_and_end_date()
	{
 	  $this->reserve_lib->set_star_and_end_date($this->input->post('star_date') ? $this->input->post('star_date') : NULL, $this->input->post('end_date') ? $this->input->post('end_date') : NULL);
	}


	//Alain Multiple Payments
	function add_payment()
	{
		$data=array();
		$this->form_validation->set_rules('amount_tendered', 'lang:reserve_amount_tendered', 'required');

		if ($this->form_validation->run() == FALSE)
		{
			if ( $this->input->post('payment_type') == lang('reserve_giftcard') )
				$data['error']=lang('reserve_must_enter_numeric_giftcard');
			else
				$data['error']=lang('reserve_must_enter_numeric');

 			$this->_reload($data);
 			return;
		}

		if (($this->input->post('payment_type') == lang('reserve_store_account') && $this->reserve_lib->get_customer() == -1) ||
			($this->reserve_lib->get_mode() == 'store_account_payment' && $this->reserve_lib->get_customer() == -1)
			)
		{
				$data['error']=lang('reserve_customer_required_store_account');
				$this->_reload($data);
				return;
		}

		if ($this->config->item('select_reserve_person_during_reserve') && !$this->reserve_lib->get_sold_by_employee_id())
		{
			$data['error']=lang('reserve_must_select_reserve_person');
			$this->_reload($data);
			return;
		}


		$payment_type=$this->input->post('payment_type');


		if ( $payment_type == lang('reserve_giftcard') )
		{
			if(!$this->Giftcard->exists($this->Giftcard->get_giftcard_id($this->input->post('amount_tendered'))))
			{
				$data['error']=lang('reserve_giftcard_does_not_exist');
				$this->_reload($data);
				return;
			}

			$payment_type=$this->input->post('payment_type').':'.$this->input->post('amount_tendered');
			$current_payments_with_giftcard = $this->reserve_lib->get_payment_amount($payment_type);
			$cur_giftcard_value = $this->Giftcard->get_giftcard_value( $this->input->post('amount_tendered') ) - $current_payments_with_giftcard;
			if ( $cur_giftcard_value <= 0 && $this->reserve_lib->get_total() > 0)
			{
				$data['error']=lang('reserve_giftcard_balance_is').' '.to_currency( $this->Giftcard->get_giftcard_value( $this->input->post('amount_tendered') ) ).' !';
				$this->_reload($data);
				return;
			}
			elseif ( ( $this->Giftcard->get_giftcard_value( $this->input->post('amount_tendered') ) - $this->reserve_lib->get_total() ) > 0 )
			{
				$data['warning']=lang('reserve_giftcard_balance_is').' '.to_currency( $this->Giftcard->get_giftcard_value( $this->input->post('amount_tendered') ) - $this->reserve_lib->get_total() ).' !';
			}
			$payment_amount=min( $this->reserve_lib->get_amount_due(), $this->Giftcard->get_giftcard_value( $this->input->post('amount_tendered') ) );
		}
		else
		{
			$payment_amount=$this->input->post('amount_tendered');
		}

		if( !$this->reserve_lib->add_payment( $payment_type, $payment_amount))
		{
			$data['error']=lang('reserve_unable_to_add_payment');
		}

		$this->_reload($data);
	}

	//Alain Multiple Payments
	function delete_payment($payment_id)
	{
		$this->reserve_lib->delete_payment($payment_id);
		$this->_reload();
	}

	function add()
	{
		$data=array();
		$mode = $this->reserve_lib->get_mode();
		$room_id_or_number_or_room_kit_or_receipt = $this->input->post("room");
		$quantity = $mode=="reserve" ? 1:-1;

		if($this->reserve_lib->is_valid_receipt($room_id_or_number_or_room_kit_or_receipt) && $mode=='return')
		{
			$this->reserve_lib->return_entire_reserve($room_id_or_number_or_room_kit_or_receipt);
		}
		else if(!$this->Room->get_info($room_id_or_number_or_room_kit_or_receipt)->description=="" && $this->Giftcard->get_giftcard_id($this->Room->get_info($room_id_or_number_or_room_kit_or_receipt)->description,true))
		{
			$data['error']=lang('reserve_unable_to_add_room');
		}
		elseif($this->Room->get_info($room_id_or_number_or_room_kit_or_receipt)->deleted || $this->Room->get_info($this->Room->get_room_id($room_id_or_number_or_room_kit_or_receipt))->deleted || !$this->reserve_lib->add_room($room_id_or_number_or_room_kit_or_receipt,$quantity))
		{
			$data['error']=lang('reserve_unable_to_add_room');
		}

		if($this->reserve_lib->out_of_stock($room_id_or_number_or_room_kit_or_receipt))
		{
			$data['warning'] = lang('reserve_quantity_less_than_zero');
		}

		$this->_reload($data);
	}

	function edit_room($line)
	{
		$data= array();

		$this->form_validation->set_rules('price', 'lang:items_price', 'numeric');
		$this->form_validation->set_rules('quantity', 'lang:items_quantity', 'numeric');
		$this->form_validation->set_rules('discount', 'lang:reports_discount', 'integer');

		$description = $this->input->post("description");
		$serialnumber = $this->input->post("serialnumber");
		$price = $this->input->post("price");
		$quantity = $this->input->post("quantity");
		$discount = $this->input->post("discount");

		if ($discount !== FALSE && $this->input->post("discount") == '')
		{
			$discount = 0;
		}


		if ($this->form_validation->run() != FALSE)
		{
			$this->reserve_lib->edit_room($line,$description,$serialnumber,$quantity,$discount,$price);
		}
		else
		{
			$data['error']=lang('reserve_error_editing_room');
		}

		$this->_reload($data);
	}

	function delete_room($room_number)
	{
		$this->reserve_lib->delete_room($room_number);
		$this->_reload();
	}

	function delete_customer()
	{
		$this->reserve_lib->delete_customer();
   	  	$this->reserve_lib->set_selected_tier_id(0);
		$this->_reload();
	}

	function start_cc_processing()
	{
		require_once(APPPATH.'libraries/MercuryProcessor.php');
		$credit_card_processor = new MercuryProcessor($this);
		$credit_card_processor->start_cc_processing();

	}

	function finish_cc_processing()
	{
		require_once(APPPATH.'libraries/MercuryProcessor.php');
		$credit_card_processor = new MercuryProcessor($this);
		$credit_card_processor->finish_cc_processing();
	}

	function cancel_cc_processing()
	{
		require_once(APPPATH.'libraries/MercuryProcessor.php');
		$credit_card_processor = new MercuryProcessor($this);
		$credit_card_processor->cancel_cc_processing();
	}

	function complete()
	{
		$data['is_reserve'] = TRUE;
		$data['cart']=$this->reserve_lib->get_cart();

		if (empty($data['cart']))
		{
			redirect('reserve');
		}

		if (!$this->_payments_cover_total())
		{
			$this->_reload(array('error' => lang('reserve_cannot_complete_reserve_as_payments_do_not_cover_total')), false);
			return;
		}
		$data['register_name'] = $this->Register->get_register_name($this->Employee->get_logged_in_employee_current_register_id());

		$data['subtotal']=$this->reserve_lib->get_subtotal();
		$data['taxes']=$this->reserve_lib->get_taxes();
		$data['total']=$this->reserve_lib->get_total();
		$data['receipt_title']=lang('reserve_receipt');
		$customer_id=$this->reserve_lib->get_customer();
		$employee_id=$this->Employee->get_logged_in_employee_info()->person_id;
		$sold_by_employee_id=$this->reserve_lib->get_sold_by_employee_id();
		$data['comment'] = $this->reserve_lib->get_comment();
		$data['show_comment_on_receipt'] = $this->reserve_lib->get_comment_on_receipt();
		$emp_info=$this->Employee->get_info($employee_id);
		$reserve_emp_info=$this->Employee->get_info($sold_by_employee_id);
		$data['payments']=$this->reserve_lib->get_payments();
		$data['is_reserve_cash_payment'] = $this->reserve_lib->is_reserve_cash_payment();
		$data['amount_change']=$this->reserve_lib->get_amount_due() * -1;
		$data['balance']=$this->reserve_lib->get_payment_amount(lang('reserve_store_account'));
		$data['employee']=$emp_info->first_name.' '.$emp_info->last_name.($sold_by_employee_id && $sold_by_employee_id != $employee_id ? '/'. $reserve_emp_info->first_name.' '.$reserve_emp_info->last_name: '');
		$data['ref_no'] = $this->session->userdata('ref_no') ? $this->session->userdata('ref_no') : '';
		$data['auth_code'] = $this->session->userdata('auth_code') ? $this->session->userdata('auth_code') : '';
		$data['discount_exists'] = $this->_does_discount_exists($data['cart']);
		$masked_account = $this->session->userdata('masked_account') ? $this->session->userdata('masked_account') : '';
		$card_issuer = $this->session->userdata('card_issuer') ? $this->session->userdata('card_issuer') : '';
		$data['star_date'] = $this->session->userdata('star_date') ? $this->session->userdata('star_date') : '';
		$data['end_date'] = $this->session->userdata('end_date') ? $this->session->userdata('end_date') : '';
		
		if ($masked_account)
		{
			$cc_payment_id = current($this->reserve_lib->get_payment_ids(lang('reserve_credit')));
			$cc_payment = $data['payments'][$cc_payment_id];
			$this->reserve_lib->edit_payment($cc_payment_id, $cc_payment['payment_type'], $cc_payment['payment_amount'],$cc_payment['payment_date'], $masked_account, $card_issuer);

			//Make sure our payments has the latest change to masked_account
			$data['payments'] = $this->reserve_lib->get_payments();
		}

		$data['change_reserve_date'] =$this->reserve_lib->get_change_reserve_date_enable() ?  $this->reserve_lib->get_change_reserve_date() : false;

		$old_date = $this->reserve_lib->get_change_reservation_id()  ? $this->Reservation->get_info($this->reserve_lib->get_change_reservation_id())->row_array() : false;
		$old_date=  $old_date ? date(get_date_format().' '.get_time_format(), strtotime($old_date['reservation_time'])) : date(get_date_format().' '.get_time_format());
		$data['transaction_time']= $this->reserve_lib->get_change_reserve_date_enable() ?  date(get_date_format().' '.get_time_format(), strtotime($this->reserve_lib->get_change_reserve_date())) : $old_date;

		if($customer_id!=-1)
		{
			$cust_info=$this->Customer->get_info($customer_id);
			$data['customer']=$cust_info->first_name.' '.$cust_info->last_name.($cust_info->company_name==''  ? '' :' - '.$cust_info->company_name).($cust_info->account_number==''  ? '' :' - '.$cust_info->account_number);
			$data['customer_address_1'] = $cust_info->address_1;
			$data['customer_address_2'] = $cust_info->address_2;
			$data['customer_city'] = $cust_info->city;
			$data['customer_state'] = $cust_info->state;
			$data['customer_zip'] = $cust_info->zip;
			$data['customer_country'] = $cust_info->country;
			$data['customer_phone'] = $cust_info->phone_number;
			$data['customer_email'] = $cust_info->email;
		}

		 $suspended_change_reservation_id=$this->reserve_lib->get_change_reservation_id() ? $this->reserve_lib->get_change_reservation_id() : $this->reserve_lib->get_change_reservation_id() ;

		//If we have a previous sale make sure we get the ref_no unless we already have it set
		if ($suspended_change_reservation_id && !$data['ref_no'])
		{
			$reserve_info = $this->Reservation->get_info($suspended_change_reservation_id)->row_array();
			$data['ref_no'] = $reserve_info['cc_ref_no'];
		}

		//If we have a previous sale make sure we get the auth_code unless we already have it set
		if ($suspended_change_reservation_id && !$data['auth_code'])
		{
			$reserve_info = $this->Reservation->get_info($suspended_change_reservation_id)->row_array();
			$data['auth_code'] = $reserve_info['auth_code'];
		}

		//If we have a suspended sale, update the date for the sale
		if ($this->reserve_lib->get_change_reservation_id() && $this->config->item('change_sale_date_when_completing_suspended_sale'))
		{
			$data['change_reserve_date'] = date('Y-m-d H:i:s');
		}

		$data['store_account_payment'] = $this->reserve_lib->get_mode() == 'store_account_payment' ? 1 : 0;

		//SAVE sale to database
		$reservation_id_raw = $this->Reservation->save($data['cart'], $customer_id, $employee_id, $sold_by_employee_id, $data['comment'],$data['show_comment_on_receipt'],$data['payments'], $suspended_change_reservation_id, 0,$data['ref_no'],$data['auth_code'], $data['change_reserve_date'], $data['balance'], 0, $data['star_date'], $data['end_date']);
		$data['reservation_id']=$this->config->item('sale_prefix').' '.$reservation_id_raw;
		$data['reservation_id_raw']=$reservation_id_raw;

		if($customer_id != -1)
		{
			$cust_info=$this->Customer->get_info($customer_id);

			if ($cust_info->balance !=0)
			{
				$data['customer_balance_for_reserve'] = $cust_info->balance;
			}
		}

		//If we don't have any taxes, run a check for items so we don't show the price including tax on receipt
		if (empty($data['taxes']))
		{
			foreach(array_keys($data['cart']) as $key)
			{
				if (isset($data['cart'][$key]['room_id']))
				{
					$room_info = $this->Room->get_info($data['cart'][$key]['room_id']);

				}
			}

		}

		if ($data['reservation_id'] == $this->config->item('reserve_prefix').' -1')
		{
			$data['error_message'] = '';
			if (is_reserve_integrated_cc_processing())
			{
				$data['error_message'].=lang('reserve_credit_card_transaction_completed_successfully').'. ';
			}
			$data['error_message'] .= lang('reserve_transaction_failed');
		}
		else
		{
			if ($this->reserve_lib->get_email_receipt() && !empty($cust_info->email))
			{
				$this->load->library('email');
				$config['mailtype'] = 'html';
				$this->email->initialize($config);
				$this->email->from($this->Location->get_info_for_key('email') ? $this->Location->get_info_for_key('email') : 'no-reply@phppointofsale.com', $this->config->item('company'));
				$this->email->to($cust_info->email);

				$this->email->subject(lang('reserve_receipt'));
				$this->email->message($this->load->view("reserve/receipt_email",$data, true));
				$this->email->send();
			}
		}
		$this->load->view("reserve/receipt",$data);
		$this->reserve_lib->clear_all();
	}

	function email_receipt($reservation_id)
	{
		//Before changing the sale session data, we need to save our current state in case they were in the middle of a sale
		$this->reserve_lib->save_current_reserve_state();

		$reserve_info = $this->Reservation->get_info($reservation_id)->row_array();
		$this->reserve_lib->copy_entire_reserve($reservation_id, true);
		$data['cart']=$this->reserve_lib->get_cart();
		$data['payments']=$this->reserve_lib->get_payments();
		$data['is_reserve_cash_payment'] = $this->reserve_lib->is_reserve_cash_payment();
		$data['register_name'] = $this->Register->get_register_name($reserve_info['register_id']);
		$data['subtotal']=$this->reserve_lib->get_subtotal($reservation_id);
		$data['taxes']=$this->reserve_lib->get_taxes($reservation_id);
		$data['total']=$this->reserve_lib->get_total($reservation_id);
		$data['receipt_title']=lang('reserve_receipt');
		$data['transaction_time']= date(get_date_format().' '.get_time_format(), strtotime($reserve_info['reservation_time']));
		$customer_id=$this->reserve_lib->get_customer();
		$emp_info=$this->Employee->get_info($reserve_info['employee_id']);
		$sold_by_employee_id=$reserve_info['sold_by_employee_id'];
		$reserve_emp_info=$this->Employee->get_info($sold_by_employee_id);

		$data['payment_type']=$reserve_info['payment_type'];
		$data['amount_change']=$this->reserve_lib->get_amount_due_round($reservation_id) * -1;
		$data['employee']=$emp_info->first_name.' '.$emp_info->last_name.($sold_by_employee_id && $sold_by_employee_id != $reserve_info['employee_id'] ? '/'. $reserve_emp_info->first_name.' '.$reserve_emp_info->last_name: '');

		$data['ref_no'] = $reserve_info['cc_ref_no'];
		if($customer_id!=-1)
		{
			$cust_info=$this->Customer->get_info($customer_id);
			$data['customer']=$cust_info->first_name.' '.$cust_info->last_name.($cust_info->company_name==''  ? '' :' - '.$cust_info->company_name).($cust_info->account_number==''  ? '' :' - '.$cust_info->account_number);
			$data['customer_address_1'] = $cust_info->address_1;
			$data['customer_address_2'] = $cust_info->address_2;
			$data['customer_city'] = $cust_info->city;
			$data['customer_state'] = $cust_info->state;
			$data['customer_zip'] = $cust_info->zip;
			$data['customer_country'] = $cust_info->country;
			$data['customer_phone'] = $cust_info->phone_number;
			$data['customer_email'] = $cust_info->email;

			if ($cust_info->balance !=0)
			{
				$data['customer_balance_for_reserve'] = $cust_info->balance;
			}
		}

		$data['reservation_id']=$this->config->item('reserve_prefix').' '.$reservation_id;
		$data['reservation_id_raw']=$reservation_id;
		$data['store_account_payment'] = FALSE;

		// foreach($data['cart'] as $item)
		// {
		// 	if ($item['name'] == lang('reserve_store_account_payment'))
		// 	{
		// 		$data['store_account_payment'] = TRUE;
		// 		break;
		// 	}
		// }

		if ($reserve_info['suspended'] > 0)
		{
			if ($reserve_info['suspended'] == 1)
			{
				$data['reserve_type'] = lang('reserve_layaway');
			}
			elseif ($reserve_info['suspended'] == 2)
			{
				$data['reserve_type'] = lang('reserve_estimate');
			}
		}

		if (!empty($cust_info->email))
		{
			$this->load->library('email');
			$config['mailtype'] = 'html';
			$this->email->initialize($config);
			$this->email->from($this->Location->get_info_for_key('email') ? $this->Location->get_info_for_key('email') : 'no-reply@phppointofsale.com', $this->config->item('company'));
			$this->email->to($cust_info->email);

			$this->email->subject(lang('reserve_receipt'));
			$this->email->message($this->load->view("reserve/receipt_email",$data, true));
			$this->email->send();
		}

		$this->reserve_lib->clear_all();

		//Restore previous state saved above
		$this->reserve_lib->restore_current_reserve_state();
	}

	function receipt($reservation_id)
	{
		//Before changing the sale session data, we need to save our current state in case they were in the middle of a sale
		$this->reserve_lib->save_current_reserve_state();

		$data['is_reserve'] = FALSE;
		$reserve_info = $this->Reservation->get_info($reservation_id)->row_array();
		$this->reserve_lib->clear_all();
		$this->reserve_lib->copy_entire_reserve($reservation_id, true);
		$data['cart']=$this->reserve_lib->get_cart();
		$data['payments']=$this->reserve_lib->get_payments();
		$data['is_reserve_cash_payment'] = $this->reserve_lib->is_reserve_cash_payment();
		$data['show_payment_times'] = TRUE;


		$data['register_name'] = $this->Register->get_register_name($reserve_info['register_id']);

		$data['subtotal']=$this->reserve_lib->get_subtotal($reservation_id);
		$data['taxes']=$this->reserve_lib->get_taxes($reservation_id);
		$data['total']=$this->reserve_lib->get_total($reservation_id);
		$data['receipt_title']=lang('reserve_receipt');
		$data['comment'] = $this->Reservation->get_comment($reservation_id);
		$data['show_comment_on_receipt'] = $this->Reservation->get_comment_on_receipt($reservation_id);
		$data['transaction_time']= date(get_date_format().' '.get_time_format(), strtotime($reserve_info['reservation_time']));
		$customer_id=$this->reserve_lib->get_customer();

		$emp_info=$this->Employee->get_info($reserve_info['employee_id']);
		$sold_by_employee_id=$reserve_info['sold_by_employee_id'];
		$reserve_emp_info=$this->Employee->get_info($sold_by_employee_id);
		$data['payment_type']=$reserve_info['payment_type'];
		$data['amount_change']=$this->reserve_lib->get_amount_due($reservation_id) * -1;
		$data['employee']=$emp_info->first_name.' '.$emp_info->last_name.($sold_by_employee_id && $sold_by_employee_id != $reserve_info['employee_id'] ? '/'. $reserve_emp_info->first_name.' '.$reserve_emp_info->last_name: '');
		$data['ref_no'] = $reserve_info['cc_ref_no'];
		$data['auth_code'] = $reserve_info['auth_code'];
		$data['discount_exists'] = $this->_does_discount_exists($data['cart']);
		if($customer_id!=-1)
		{
			$cust_info=$this->Customer->get_info($customer_id);
			$data['customer']=$cust_info->first_name.' '.$cust_info->last_name.($cust_info->company_name==''  ? '' :' - '.$cust_info->company_name).($cust_info->account_number==''  ? '' :' - '.$cust_info->account_number);
			$data['customer_address_1'] = $cust_info->address_1;
			$data['customer_address_2'] = $cust_info->address_2;
			$data['customer_city'] = $cust_info->city;
			$data['customer_state'] = $cust_info->state;
			$data['customer_zip'] = $cust_info->zip;
			$data['customer_country'] = $cust_info->country;
			$data['customer_phone'] = $cust_info->phone_number;
			$data['customer_email'] = $cust_info->email;

			if ($cust_info->balance !=0)
			{
				$data['customer_balance_for_reserve'] = $cust_info->balance;
			}
		}
		$data['reservation_id']=$this->config->item('reserve_prefix').' '.$reservation_id;
		$data['reservation_id_raw']=$reservation_id;
		$data['store_account_payment'] = FALSE;

		// foreach($data['cart'] as $item)
		// {
		// 	if ($item['name'] == lang('reserve_store_account_payment'))
		// 	{
		// 		$data['store_account_payment'] = TRUE;
		// 		break;
		// 	}
		// }

		if ($reserve_info['suspended'] > 0)
		{
			if ($reserve_info['suspended'] == 1)
			{
				$data['reserve_type'] = lang('reserve_layaway');
			}
			elseif ($reserve_info['suspended'] == 2)
			{
				$data['reserve_type'] = lang('reserve_estimate');
			}
		}

		$this->load->view("reserve/receipt",$data);
		$this->reserve_lib->clear_all();

		//Restore previous state saved above
		$this->reserve_lib->restore_current_reserve_state();
	}

	function fulfillment($reservation_id)
	{
		$reserve_info = $this->Reservation->get_info($reservation_id)->row_array();
		$data['comment'] = $this->Reservation->get_comment($reservation_id);
		$data['show_comment_on_receipt'] = $this->Reservation->get_comment_on_receipt($reservation_id);
		$data['transaction_time']= date(get_date_format().' '.get_time_format(), strtotime($reserve_info['reservation_time']));
		$customer_id=$reserve_info['customer_id'];

		$emp_info=$this->Employee->get_info($reserve_info['employee_id']);
		$data['employee']=$emp_info->first_name.' '.$emp_info->last_name;
		if($customer_id!=-1)
		{
			$cust_info=$this->Customer->get_info($customer_id);
			$data['customer']=$cust_info->first_name.' '.$cust_info->last_name.($cust_info->company_name==''  ? '' :' - '.$cust_info->company_name).($cust_info->account_number==''  ? '' :' - '.$cust_info->account_number);
			$data['customer_address_1'] = $cust_info->address_1;
			$data['customer_address_2'] = $cust_info->address_2;
			$data['customer_city'] = $cust_info->city;
			$data['customer_state'] = $cust_info->state;
			$data['customer_zip'] = $cust_info->zip;
			$data['customer_country'] = $cust_info->country;
			$data['customer_phone'] = $cust_info->phone_number;
			$data['customer_email'] = $cust_info->email;
		}
		$data['reservation_id']=$this->config->item('reserve_prefix').' '.$reservation_id;
		$data['reservation_id_raw']=$reservation_id;
		$data['reserve_rooms'] = $this->Reservation->get_reserve_bedrooms_ordered_by_category($reservation_id)->result_array();
		//$data['reserve_room_kits'] = $this->Reservation->get_reserve_room_kits_ordered_by_category($reservation_id)->result_array();
		$data['discount_exists'] = $this->_does_discount_exists($data['reserve_bedrooms']) || $this->_does_discount_exists($data['reserve_room_kits']);
		$this->load->view("reserve/fulfillment",$data);
	}

	function _does_discount_exists($cart)
	{
		foreach($cart as $line=>$item)
		{
			if( (isset($item['discount']) && $item['discount']>0 ) || (isset($item['discount_percent']) && $item['discount_percent']>0 ) )
			{
				return TRUE;
			}
		}

		return FALSE;
	}

	function edit($reservation_id)
	{
		if(!$this->Employee->has_module_action_permission('reserve', 'edit_sale', $this->Employee->get_logged_in_employee_info()->person_id))
		{
			redirect('no_access/'.$this->module_id);
		}

		$data = array();

		$data['customers'] = array('' => 'No Customer');
		foreach ($this->Customer->get_all()->result() as $customer)
		{
			$data['customers'][$customer->person_id] = $customer->first_name . ' '. $customer->last_name;
		}

		$data['employees'] = array();
		foreach ($this->Employee->get_all()->result() as $employee)
		{
			$data['employees'][$employee->person_id] = $employee->first_name . ' '. $employee->last_name;
		}

		$data['reserve_info'] = $this->Reservation->get_info($reservation_id)->row_array();

		$data['store_account_payment'] = FALSE;

		// foreach($this->Reservation->get_reservations_bedrooms($reservation_id)->result_array() as $row)
		// {
		// 	$room_info = $this->Room->get_info($row['room_id']);

		// 	if ($room_info->name == lang('reserve_store_account_payment'))
		// 	{
		// 		$data['store_account_payment'] = TRUE;
		// 		break;
		// 	}
		// }

		$this->load->view('reserves/edit', $data);
	}

	function delete($reservation_id)
	{
		$this->check_action_permission('delete_reserve');
		$data = array();

		if ($this->Reservation->delete($reservation_id))
		{
			$data['success'] = true;
		}
		else
		{
			$data['success'] = false;
		}

		$this->load->view('reserves/delete', $data);

	}

	function undelete($reservation_id)
	{
		$data = array();

		if ($this->Reservation->undelete($reservation_id))
		{
			$data['success'] = true;
		}
		else
		{
			$data['success'] = false;
		}

		$this->load->view('reserves/undelete', $data);

	}

	function save($reservation_id)
	{
		$reserve_data = array(
			'reservation_time' => date('Y-m-d', strtotime($this->input->post('date'))),
			'customer_id' => $this->input->post('customer_id') ? $this->input->post('customer_id') : null,
			'employee_id' => $this->input->post('employee_id'),
			'comment' => $this->input->post('comment'),
			'show_comment_on_receipt' => $this->input->post('show_comment_on_receipt') ? 1 : 0
		);

		$reserve_info = $this->Reservation->get_info($reservation_id)->row_array();

		if (date('Y-m-d', strtotime($this->input->post('date')))== date('Y-m-d', strtotime($reserve_info['reservation_time'])))
		{
			unset($reserve_data['reservation_time']);
		}

		if ($this->Reservation->update($reserve_data, $reservation_id))
		{
			echo json_encode(array('success'=>true,'message'=>lang('reserve_successfully_updated')));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>lang('reserve_unsuccessfully_updated')));
		}
	}

	function _payments_cover_total()
	{
		$total_payments = 0;

		foreach($this->reserve_lib->get_payments() as $payment)
		{
			$total_payments += $payment['payment_amount'];
		}

		/* Changed the conditional to account for floating point rounding */
		if ( ( $this->reserve_lib->get_mode() == 'reserve' ) && ( ( to_currency_no_money( $this->reserve_lib->get_total() ) - $total_payments ) > 1e-6 ) )
		{
			return false;
		}

		return true;
	}
	function reload()
	{
		$this->_reload();
	}

	function _reload($data=array(), $is_ajax = true)
	{
		/*$data['is_tax_inclusive'] = $this->_is_tax_inclusive();
		if ($data['is_tax_inclusive'] && count($this->reserve_lib->get_deleted_taxes()) > 0)
		{
			$this->reserve_lib->clear_deleted_taxes();
		}*/

		$person_info = $this->Employee->get_logged_in_employee_info();
		$modes = array('reserve'=>lang('reserve_reserve'),'return'=>lang('reserve_return'));

		if($this->config->item('customers_store_accounts'))
		{
			$modes['store_account_payment'] = lang('reserve_store_account_payment');
		}

		$data['cart']=$this->reserve_lib->get_cart();
		$data['modes']= $modes;
		$data['mode']=$this->reserve_lib->get_mode();
		$data['bedrooms_in_cart'] = $this->reserve_lib->get_bedrooms_in_cart();
		$data['subtotal']=$this->reserve_lib->get_subtotal();
		$data['taxes']=$this->reserve_lib->get_taxes();
		$data['total']=$this->reserve_lib->get_total();
		$data['bedrooms_module_allowed'] = $this->Employee->has_module_permission('items', $person_info->person_id);
		$data['comment'] = $this->reserve_lib->get_comment();
		$data['show_comment_on_receipt'] = $this->reserve_lib->get_comment_on_receipt();
		$data['email_receipt'] = $this->reserve_lib->get_email_receipt();
		$data['payments_total']=$this->reserve_lib->get_payments_totals_excluding_store_account();
		$data['amount_due']=$this->reserve_lib->get_amount_due();
		$data['payments']=$this->reserve_lib->get_payments();
		$data['change_reserve_date_enable'] = $this->reserve_lib->get_change_reserve_date_enable();
		$data['change_reserve_date'] = $this->reserve_lib->get_change_reserve_date();
		$data['selected_tier_id'] = $this->reserve_lib->get_selected_tier_id();
		$data['is_over_credit_limit'] = false;
		$data['star_date'] = $this->reserve_lib->get_star_date();
		$data['end_date'] = $this->reserve_lib->get_end_date();

		$employees = array('' => lang('common_not_set'));

		foreach($this->Employee->get_all()->result() as $employee)
		{
			if ($this->Employee->is_employee_authenticated($employee->person_id, $this->Employee->get_logged_in_employee_current_location_id()))
			{
				$employees[$employee->person_id] = $employee->first_name.' '.$employee->last_name;
			}
		}
		$data['employees'] = $employees;

		$data['selected_sold_by_employee_id'] = $this->reserve_lib->get_sold_by_employee_id();
		// $tiers = array();

		// $tiers[0] = lang('items_none');
		// foreach($this->Tier->get_all()->result() as $tier)
		// {
		// 	$tiers[$tier->id]=$tier->name;
		// }

		//$data['tiers'] = $tiers;

		if ($this->Location->get_info_for_key('enable_credit_card_processing'))
		{
			$data['payment_options']=array(
				lang('reserve_cash') => lang('reserve_cash'),
				lang('reserve_check') => lang('reserve_check'),
				lang('reserve_credit') => lang('reserve_credit'),
				lang('reserve_giftcard') => lang('reserve_giftcard'));

				if($this->config->item('customers_store_accounts'))
				{
					$data['payment_options']=array_merge($data['payment_options'],	array(lang('reserve_store_account') => lang('reserve_store_account')
					));
				}
		}
		else
		{
			$data['payment_options']=array(
				lang('reserve_cash') => lang('reserve_cash'),
				lang('reserve_check') => lang('reserve_check'),
				lang('reserve_giftcard') => lang('reserve_giftcard'),
				lang('reserve_debit') => lang('reserve_debit'),
				lang('reserve_credit') => lang('reserve_credit')
				);

				if($this->config->item('customers_store_accounts') && $this->reserve_lib->get_mode() != 'store_account_payment')
				{
					$data['payment_options']=array_merge($data['payment_options'],	array(lang('reserve_store_account') => lang('reserve_store_account')
					));
				}
		}

		foreach($this->Appconfig->get_additional_payment_types() as $additional_payment_type)
		{
			$data['payment_options'][$additional_payment_type] = $additional_payment_type;
		}

		$customer_id=$this->reserve_lib->get_customer();
		if($customer_id!=-1)
		{
			$info=$this->Customer->get_info($customer_id);
			$data['customer']=$info->first_name.' '.$info->last_name.($info->company_name==''  ? '' :' ('.$info->company_name.')');
			$data['customer_email']=$info->email;
			$data['customer_balance'] = $info->balance;
			$data['customer_credit_limit'] = $info->credit_limit;
			$data['is_over_credit_limit'] = $this->reserve_lib->is_over_credit_limit();
			$data['customer_id']=$customer_id;
			$data['customer_cc_token'] = $info->cc_token;
			$data['customer_cc_preview'] = $info->cc_preview;
			$data['save_credit_card_info'] = $this->reserve_lib->get_save_credit_card_info();
			$data['use_saved_cc_info'] = $this->reserve_lib->get_use_saved_cc_info();
			$data['avatar']=$info->image_id ?  site_url('app_files/view/'.$info->image_id) : ""; //can be changed to  base_url()."/img/avatar.png" if it is required

			if (!$this->config->item('hide_customer_recent_sales'))
			{
				$data['recent_reserve'] = $this->Reservation->get_recent_reservation_for_customer($customer_id);
			}
		}

		$data['customer_required_check'] = (!$this->config->item('require_customer_for_reserve') || ($this->config->item('require_customer_for_reserve') && isset($customer_id) && $customer_id!=-1));

		$data['payments_cover_total'] = $this->_payments_cover_total();
		if ($is_ajax)
		{
			$this->load->view("reserve/register",$data);
		}
		else
		{
			$this->load->view("reserve/register_initial",$data);
		}
	}

    function cancel_reserve()
    {
		 if ($this->Location->get_info_for_key('enable_credit_card_processing'))
		 {
 			 require_once(APPPATH.'libraries/MercuryProcessor.php');
 			 $credit_card_processor = new MercuryProcessor($this);

			 if (method_exists($credit_card_processor, 'void_partial_transactions'))
			 {
				 if (!$credit_card_processor->void_partial_transactions())
				 {
		     		 $this->reserve_lib->clear_all();
					 $this->_reload(array('error' => lang('reserve_attempted_to_reverse_partial_transactions_failed_please_contact_support')), true);
					 return;
				 }
   		 }
		 }

     	$this->reserve_lib->clear_all();
     	$this->_reload();
	}
	


	function batch_reserve()
	{
		$this->load->view("reserve/batch");
	}

	function _excel_get_header_row()
	{
		return array(lang('room_id'),lang('unit_price'),lang('quantity'),lang('discount_percent'));
	}

	function excel()
	{
		$this->load->helper('report');
		$header_row = $this->_excel_get_header_row();

		$content = array_to_spreadsheet(array($header_row));
		force_download('batch_reserve_export.'.($this->config->item('spreadsheet_format') == 'XLSX' ? 'xlsx' : 'csv'), $content);
	}


	
	function new_giftcard()
	{
		if (!$this->Employee->has_module_action_permission('giftcards', 'add_update', $this->Employee->get_logged_in_employee_info()->person_id))
		{
			redirect('no_access/'.$this->module_id);
		}

		$data = array();
		$data['room_id']=$this->Room->get_room_id(lang('reserve_giftcard'));
		$this->load->view("reserve/giftcard_form",$data);
	}

	function suspended()
	{
		$data = array();
		$data['suspended_reserve'] = $this->Reservation->get_all_suspended();
		$this->load->view('reserves/suspended', $data);
	}

	function change_reservation($reservation_id)
	{
		$this->check_action_permission('edit_sale');
		$this->reserve_lib->clear_all();
		$this->reserve_lib->copy_entire_reserve($reservation_id);
		$this->reserve_lib->set_change_reservation_id($reservation_id);

		if ($this->Location->get_info_for_key('enable_credit_card_processing'))
		{
			$this->reserve_lib->change_credit_card_payments_to_partial();
		}
    	$this->_reload(array(), false);
	}

	function unsuspend()
	{
		$reservation_id = $this->input->post('suspended_reservation_id');
		$this->reserve_lib->clear_all();
		$this->reserve_lib->copy_entire_reserve($reservation_id);
		$this->reserve_lib->set_suspended_reservation_id($reservation_id);


		if ($this->reserve_lib->get_customer())
		{
			$customer_info=$this->Customer->get_info($this->reserve_lib->get_customer());

			if ($customer_info->tier_id)
			{
				$this->reserve_lib->set_selected_tier_id($customer_info->tier_id);
			}
		}

    	$this->_reload(array(), false);
	}

	function delete_suspended_reserve()
	{
		$this->check_action_permission('delete_suspended_reserve');
		$suspended_reservation_id = $this->input->post('suspended_reservation_id');
		if ($suspended_reservation_id)
		{
			$this->reserve_lib->delete_suspended_reservation_id();
			$this->Reservation->delete($suspended_reservation_id);
		}
    	redirect('reserve/suspended');
	}

	function discount_all()
	{
		$discount_all_percent = (int)$this->input->post('discount_all_percent');
		$this->reserve_lib->discount_all($discount_all_percent);
		$this->_reload();
	}

	function categories($offset = 0)
	{
		$categories = array();

		$room_categories = array();
		$room_categories_bedrooms_result = $this->Room->get_all_categories()->result();

		foreach($room_categories_bedrooms_result as $category)
		{
			if ($category->category != lang('reserve_giftcard') && $category->category != lang('reserve_store_account_payment'))
			{
				$room_categories[] = $category->category;
			}
		}

		// $room_kit_categories = array();
		// $room_kit_categories_bedrooms_result = $this->Room_kit->get_all_categories()->result();

		// foreach($room_kit_categories_bedrooms_result as $category)
		// {
		// 	$room_kit_categories[] = $category->category;
		// }

		$categories = array_unique($room_categories);
		sort($categories);

		$categories_count = count($categories);
		$config['base_url'] = site_url('reserve/categories');
		$config['total_rows'] = $categories_count;
		$config['per_page'] = 15;
		$this->pagination->initialize($config);

		$categories = array_slice($categories, $offset, $config['per_page']);

		$data = array();
		$data['categories'] = $categories;
		$data['pagination'] = $this->pagination->create_links();

		echo json_encode($data);
	}

	function bedrooms($offset = 0)
	{
		$category = $this->input->post('category');

		$items = array();
		$items_result = $this->Room->get_all_by_category($category, $offset)->result();

		//print_r($items_result);
		foreach($items_result as $item)
		{
			$img_src = "";
			//echo $item->image_id ;
			if ($item->image_id != 'no_image' && trim($item->image_id) != '') {
				$img_src = site_url('app_files/view/'.$item->image_id);
			}

			$items[] = array(
				'id' => $item->room_id,
				'name' => character_limiter($item->name, 58),
				'image_src' => 	$img_src
			);
		}
		$items_count = $this->Room->count_all_by_category($category);

		$config['base_url'] = site_url('reserve/items');
		$config['total_rows'] = $items_count;
		$config['per_page'] = 14;
		$this->pagination->initialize($config);

		//print_r($items);
		$data = array();
		$data['items'] = $items;
		$data['pagination'] = $this->pagination->create_links();

		echo json_encode($data);
	}

	function delete_tax($name)
	{
		$this->check_action_permission('delete_taxes');
		$name = rawurldecode($name);
		$this->reserve_lib->add_deleted_tax($name);
		$this->_reload();
	}
}
?>
