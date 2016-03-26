<?php
require_once ("secure_area.php");
require_once ("interfaces/idata_controller.php");
class Bedrooms extends Secure_area implements iData_controller
{
	function __construct()
	{
		parent::__construct('bedrooms');
	}

	function index($offset=0)
	{	
		$params = $this->session->userdata('room_search_data') ? $this->session->userdata('room_search_data') : array('offset' => 0, 'order_col' => 'room_id', 'order_dir' => 'asc', 'search' => FALSE, 'category' => FALSE);
		if ($offset!=$params['offset'])
		{
		   redirect('bedrooms/index/'.$params['offset']);
		}

		$this->check_action_permission('search');
		$config['base_url'] = site_url('bedrooms/sorting');
		$config['per_page'] = $this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20; 
		$data['controller_name']=strtolower(get_class());
		$data['per_page'] = $config['per_page'];
		$data['search'] = $params['search'] ? $params['search'] : "";
		$data['category'] = $params['category'] ? $params['category'] : "";
		
		if ($data['search'] || $data['category'])
		{

			$config['total_rows'] = $this->Room->search_count_all($data['search'], $data['category']);
			$table_data = $this->Room->search($data['search'],$data['category'],$data['per_page'],$params['offset'],$params['order_col'],$params['order_dir']);
		}
		else
		{
			$config['total_rows'] = $this->Room->count_all();
			$table_data = $this->Room->get_all($data['per_page'],$params['offset'],$params['order_col'],$params['order_dir']);
		}

		$data['total_rows'] = $config['total_rows'];
		$this->pagination->initialize($config);
		$data['pagination'] = $this->pagination->create_links();
		$data['order_col'] = $params['order_col'];
		$data['order_dir'] = $params['order_dir'];
		
		
		
		$data['manage_table']=get_bedrooms_manage_table($table_data,$this);
		$data['categories'][''] = '--'.lang('bedrooms_select_category_or_all').'--';
		foreach($this->Room->get_all_categories()->result() as $category)
		{
			$category = $category->category;
			$data['categories'][$category] = $category;
		}
		
		$this->load->view('bedrooms/manage',$data);
	}

	function sorting()
	{
		$this->check_action_permission('search');
		$search=$this->input->post('search') ? $this->input->post('search') : "";
		$category = $this->input->post('category');
		
		$per_page=$this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20;
		$offset = $this->input->post('offset') ? $this->input->post('offset') : 0;
		$order_col = $this->input->post('order_col') ? $this->input->post('order_col') : 'name';
		$order_dir = $this->input->post('order_dir') ? $this->input->post('order_dir'): 'asc';

		$room_search_data = array('offset' => $offset, 'order_col' => $order_col, 'order_dir' => $order_dir, 'search' => $search, 'category' => $category);
		$this->session->set_userdata("room_search_data",$room_search_data);
		if ($search || $category)
		{
			$config['total_rows'] = $this->Room->search_count_all($search, $category);
			$table_data = $this->Room->search($search,$category, $per_page,$this->input->post('offset') ? $this->input->post('offset') : 0, $this->input->post('order_col') ? $this->input->post('order_col') : 'name' ,$this->input->post('order_dir') ? $this->input->post('order_dir'): 'asc');
		}
		else
		{
			$config['total_rows'] = $this->Room->count_all();
			$table_data = $this->Room->get_all($per_page,$this->input->post('offset') ? $this->input->post('offset') : 0, $this->input->post('order_col') ? $this->input->post('order_col') : 'name' ,$this->input->post('order_dir') ? $this->input->post('order_dir'): 'asc');
		}
		$config['base_url'] = site_url('bedrooms/sorting');
		$config['per_page'] = $per_page; 
		$this->pagination->initialize($config);
		$data['pagination'] = $this->pagination->create_links();
		$data['manage_table']=get_bedrooms_manage_table_data_rows($table_data,$this);
		echo json_encode(array('manage_table' => $data['manage_table'], 'pagination' => $data['pagination']));	
	}

	
	function find_room_info()
	{
		$room_number=$this->input->post('scan_room_number');
		echo json_encode($this->Room->find_room_info($room_number));
	}
		
	function room_number_exists()
	{
		if($this->Room->account_number_exists($this->input->post('room_number')))
		echo 'false';
		else
		echo 'true';
		
	}

	
	function check_duplicate()
	{
		echo json_encode(array('duplicate'=>$this->Room->check_duplicate($this->input->post('term'))));
	}

	function search()
	{
		$this->check_action_permission('search');
		$search=$this->input->post('search');
		$category = $this->input->post('category');
		$offset = $this->input->post('offset') ? $this->input->post('offset') : 0;
		$order_col = $this->input->post('order_col') ? $this->input->post('order_col') : 'name';
		$order_dir = $this->input->post('order_dir') ? $this->input->post('order_dir'): 'asc';
		
		$room_search_data = array('offset' => $offset, 'order_col' => $order_col, 'order_dir' => $order_dir, 'search' => $search,  'category' => $category);
		$this->session->set_userdata("room_search_data",$room_search_data);
		$per_page=$this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20;
		$search_data=$this->Room->search($search, $category, $per_page,$this->input->post('offset') ? $this->input->post('offset') : 0, $this->input->post('order_col') ? $this->input->post('order_col') : 'name' ,$this->input->post('order_dir') ? $this->input->post('order_dir'): 'asc');
		$config['base_url'] = site_url('bedrooms/search');
		$config['total_rows'] = $this->Room->search_count_all($search, $category);
		$config['per_page'] = $per_page ;
		$this->pagination->initialize($config);				
		$data['pagination'] = $this->pagination->create_links();
		$data['manage_table']=get_bedrooms_manage_table_data_rows($search_data,$this);
		echo json_encode(array('manage_table' => $data['manage_table'], 'pagination' => $data['pagination']));
	}

	function suggest()
	{
		$suggestions = $this->Room->get_search_suggestions($this->input->get('term'),100);
		echo json_encode($suggestions);
	}

	function room_search()
	{
		$suggestions = $this->Room->get_room_search_suggestions($this->input->get('term'),100);
		echo json_encode($suggestions);
	}

	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest_category()
	{
		$suggestions = $this->Room->get_category_suggestions($this->input->get('term'));
		echo json_encode($suggestions);
	}

	function view($room_id=-1,$redirect=0, $sale_or_receiving = 'reserve')
	{
		$this->check_action_permission('add_update');
      $this->load->helper('report');
		$data = array();
		$data['controller_name']=strtolower(get_class());

		$data['room_info']=$this->Room->get_info($room_id);
		$data['room_tax_info']=$this->Room_taxes->get_info($room_id);
		$data['tiers']=$this->Tier->get_all()->result();
		$data['locations'] = array();
		$data['location_tier_prices'] = array();
		$data['additional_room_numbers'] = $this->Additional_room_numbers->get_room_numbers($room_id);
		
		if ($room_id != -1)
		{
			$data['next_room_id'] = $this->Room->get_next_id($room_id);
			$data['prev_room_id'] = $this->Room->get_prev_id($room_id);;
		}
			
		foreach($this->Location->get_all()->result() as $location)
		{
			if($this->Employee->is_location_authenticated($location->location_id))
			{				
				$data['locations'][] = $location;
				$data['location_bedrooms'][$location->location_id] = $this->Room_location->get_info($room_id,$location->location_id);
				$data['location_taxes'][$location->location_id] = $this->Room_location_taxes->get_info($room_id, $location->location_id);
								
				foreach($data['tiers'] as $tier)
				{					
					$tier_prices = $this->Room_location->get_tier_price_row($tier->id,$data['room_info']->room_id, $location->location_id);
					if (!empty($tier_prices))
					{
						$data['location_tier_prices'][$location->location_id][$tier->id] = $tier_prices;
					}
					else
					{
						$data['location_tier_prices'][$location->location_id][$tier->id] = FALSE;			
					}
				}
			}
			
		}
				
		$data['redirect']=$redirect;
		$data['sale_or_receiving']=$sale_or_receiving;
		
		$data['tier_prices'] = array();
		$data['tier_type_options'] = array('unit_price' => lang('bedrooms_fixed_price'), 'percent_off' => lang('bedrooms_percent_off'));
		foreach($data['tiers'] as $tier)
		{
			$tier_prices = $this->Room->get_tier_price_row($tier->id,$data['room_info']->room_id);
			if (!empty($tier_prices))
			{
				$data['tier_prices'][$tier->id] = $tier_prices;
			}
			else
			{
				$data['tier_prices'][$tier->id] = FALSE;			
			}
		}

		$this->load->view("bedrooms/form",$data);
	}

	function clone_room($room_id)
	{
		$this->check_action_permission('add_update');
      $this->load->helper('report');
		$data = array();
		$data['controller_name']=strtolower(get_class());
		$data['redirect']=2;
		$data['room_info']=$this->Room->get_info($room_id);
		
		//Unset unique identifiers
		$data['room_info']->room_number = '';
		
		$data['room_tax_info']=$this->Room_taxes->get_info($room_id);
		$data['tiers']=$this->Tier->get_all()->result();
		$data['locations'] = array();
		$data['location_tier_prices'] = array();
		$data['additional_room_numbers'] = FALSE;
		
		foreach($this->Location->get_all()->result() as $location)
		{
			if($this->Employee->is_location_authenticated($location->location_id))
			{				
				$data['locations'][] = $location;
				$data['location_bedrooms'][$location->location_id] = $this->Room_location->get_info($room_id,$location->location_id);
				$data['location_taxes'][$location->location_id] = $this->Room_location_taxes->get_info($room_id, $location->location_id);
								
				foreach($data['tiers'] as $tier)
				{					
					$tier_prices = $this->Room_location->get_tier_price_row($tier->id,$data['room_info']->room_id, $location->location_id);
					if (!empty($tier_prices))
					{
						$data['location_tier_prices'][$location->location_id][$tier->id] = $tier_prices;
					}
					else
					{
						$data['location_tier_prices'][$location->location_id][$tier->id] = FALSE;			
					}
				}
			}
			
		}
				
		$data['tier_prices'] = array();
		$data['tier_type_options'] = array('unit_price' => lang('items_fixed_price'), 'percent_off' => lang('items_percent_off'));
		foreach($data['tiers'] as $tier)
		{
			$tier_prices = $this->Item->get_tier_price_row($tier->id,$data['room_info']->room_id);
			
			if (!empty($tier_prices))
			{
				$data['tier_prices'][$tier->id] = $tier_prices;
			}
			else
			{
				$data['tier_prices'][$tier->id] = FALSE;			
			}
		}

		$data['selected_supplier'] = $this->Item->get_info($room_id)->supplier_id;
		$data['is_clone'] = TRUE;
		$this->load->view("bedrooms/form",$data);
	}

	function inventory($room_id=-1)
	{
		$this->check_action_permission('add_update');
		$data['room_info']=$this->Room->get_info($room_id);
		$data['room_location_info']=$this->Room_location->get_info($room_id);
		$this->load->view("bedrooms/inventory",$data);
	}

	function save($room_id=-1)
	{
		$this->check_action_permission('add_update');		
		$room_data = array(
		'name'=>$this->input->post('name'),
		'description'=>$this->input->post('description'),
		'category'=>$this->input->post('category'),
		'beds'=>$this->input->post('beds'),
		'room_number'=>$this->input->post('room_number')=='' ? null:$this->input->post('room_number'),
		'cost_price'=>$this->input->post('cost_price'),
		'unit_price'=>$this->input->post('unit_price'),
		'promo_price'=>$this->input->post('promo_price') ? $this->input->post('promo_price') : NULL,
		'start_date'=>$this->input->post('start_date') ? date('Y-m-d', strtotime($this->input->post('start_date'))) : NULL,
		'end_date'=>$this->input->post('end_date') ?date('Y-m-d', strtotime($this->input->post('end_date'))) : NULL,
		'is_service'=>$this->input->post('is_service') ? $this->input->post('is_service') : 0 ,
		'override_default_tax'=> $this->input->post('override_default_tax') ? $this->input->post('override_default_tax') : 0,
		);
		
		if ($this->input->post('override_default_commission'))
		{
			if ($this->input->post('commission_type') == 'fixed')
			{
				$room_data['commission_fixed'] = (float)$this->input->post('commission_value');
				$room_data['commission_percent'] = NULL;
			}
			else
			{
				$room_data['commission_percent'] = (float)$this->input->post('commission_value');
				$room_data['commission_fixed'] = NULL;
			}
		}
		else
		{
			$room_data['commission_percent'] = NULL;
			$room_data['commission_fixed'] = NULL;
		}
		
		$employee_id=$this->Employee->get_logged_in_employee_info()->person_id;
		$cur_room_info = $this->Room->get_info($room_id);

		$redirect=$this->input->post('redirect');
		$sale_or_receiving=$this->input->post('sale_or_receiving');

		if($this->Room->save($room_data,$room_id))
		{
			
			$success_message = '';
			
			//New room
			if($room_id==-1)
			{	
				$success_message = lang('bedrooms_successful_adding').' '.$room_data['name'];
				$this->session->set_flashdata('manage_success_message', $success_message);
				echo json_encode(array('success'=>true,'message'=>$success_message,'room_id'=>$room_data['room_id'],'redirect' => $redirect, 'sale_or_receiving'=>$sale_or_receiving));
				$room_id = $room_data['room_id'];
			}
			else //previous room
			{
				$success_message = lang('bedrooms_successful_updating').' '.$room_data['name'];
				$this->session->set_flashdata('manage_success_message', $success_message);
				echo json_encode(array('success'=>true,'message'=>$success_message,'room_id'=>$room_id,'redirect' => $redirect, 'sale_or_receiving'=>$sale_or_receiving));
			}
			
			if ($this->input->post('additional_room_numbers') && is_array($this->input->post('additional_room_numbers')))
			{
				$this->Additional_room_numbers->save($room_id, $this->input->post('additional_room_numbers'));
			}
			else
			{
				$this->Additional_room_numbers->delete($room_id);
			}
			
			if ($this->input->post('locations'))
			{
				foreach($this->input->post('locations') as $location_id => $room_location_data)
				{		        
					$override_prices = isset($room_location_data['override_prices']) && $room_location_data['override_prices'];
				
					$room_location_before_save = $this->Room_location->get_info($room_id,$location_id);
					$data = array(
						'location_id' => $location_id,
						'room_id' => $room_id,
						'location' => $room_location_data['location'],
						'cost_price' => $override_prices && $room_location_data['cost_price'] != '' ? $room_location_data['cost_price'] : NULL,
						'unit_price' => $override_prices && $room_location_data['unit_price'] != '' ? $room_location_data['unit_price'] : NULL,
						'promo_price' => $override_prices && $room_location_data['promo_price'] != '' ? $room_location_data['promo_price'] : NULL,
						'start_date' => $override_prices && $room_location_data['promo_price']!='' && $room_location_data['start_date'] != '' ? date('Y-m-d', strtotime($room_location_data['start_date'])) : NULL,
						'end_date' => $override_prices && $room_location_data['promo_price'] != '' && $room_location_data['end_date'] != '' ? date('Y-m-d', strtotime($room_location_data['end_date'])) : NULL,
						'quantity' => $room_location_data['quantity'] != '' && !$this->input->post('is_service')  ? $room_location_data['quantity'] : NULL,
						'reorder_level' => isset($room_location_data['reorder_level']) && $room_location_data['reorder_level'] != '' && $room_location_data['reorder_level']!=$this->input->post('reorder_level') ? $room_location_data['reorder_level'] : NULL,
						'override_default_tax'=> isset($room_location_data['override_default_tax'] ) && $room_location_data['override_default_tax'] != '' ? $room_location_data['override_default_tax'] : 0,
					);
					$this->Room_location->save($data, $room_id,$location_id);
					

					if (isset($room_location_data['room_tier']))
					{
						$tier_type = $room_location_data['tier_type'];

						foreach($room_location_data['room_tier'] as $tier_id => $price_or_percent)
						{
							//If we are overriding prices and we have a price/percent, add..otherwise delete
							if ($override_prices && $price_or_percent)
							{				
								$tier_data=array('tier_id'=>$tier_id);
								$tier_data['room_id'] = isset($room_data['room_id']) ? $room_data['room_id'] : $room_id;
								$tier_data['location_id'] = $location_id;
							
								if ($tier_type[$tier_id] == 'unit_price')
								{
									$tier_data['unit_price'] = $price_or_percent;
									$tier_data['percent_off'] = NULL;
								}
								else
								{
									$tier_data['percent_off'] = (int)$price_or_percent;
									$tier_data['unit_price'] = NULL;
								}

								$this->Room_location->save_room_tiers($tier_data,$room_id, $location_id);
							}
							else
							{
								$this->Room_location->delete_tier_price($tier_id, $room_id, $location_id);
							}

						}
					}
									
				
					if (isset($room_location_data['tax_names']))
					{
						$location_bedrooms_taxes_data = array();
						$tax_names = $room_location_data['tax_names'];
						$tax_percents = $room_location_data['tax_percents'];
						$tax_cumulatives = $room_location_data['tax_cumulatives'];
						for($k=0;$k<count($tax_percents);$k++)
						{
							if (is_numeric($tax_percents[$k]))
							{
								$location_bedrooms_taxes_data[] = array('name'=>$tax_names[$k], 'percent'=>$tax_percents[$k], 'cumulative' => isset($tax_cumulatives[$k]) ? $tax_cumulatives[$k] : '0' );
							}
						}
						$this->Room_location_taxes->save($location_bedrooms_taxes_data, $room_id, $location_id);
					}
					
					// if ($room_location_data['quantity'] != '' && !$this->input->post('is_service') && $room_location_data['quantity'] != $room_location_before_save->quantity)
					// {
					// 	$inv_data = array
					// 		(
					// 		'trans_date'=>date('Y-m-d H:i:s'),
					// 		'trans_room'=>$room_id,
					// 		'trans_user'=>$employee_id,
					// 		'trans_comment'=>lang('bedrooms_manually_editing_of_quantity'),
					// 		'trans_inventory'=>$room_location_data['quantity'] - $room_location_before_save->quantity,
					// 		'location_id' => $location_id,
					// 	);
					// 	$this->Bedrooms_inventory->insert($inv_data);
					// }
				}
			}
			$bedrooms_taxes_data = array();
			$tax_names = $this->input->post('tax_names');
			$tax_percents = $this->input->post('tax_percents');
			$tax_cumulatives = $this->input->post('tax_cumulatives');
			for($k=0;$k<count($tax_percents);$k++)
			{
				if (is_numeric($tax_percents[$k]))
				{
					$bedrooms_taxes_data[] = array('name'=>$tax_names[$k], 'percent'=>$tax_percents[$k], 'cumulative' => isset($tax_cumulatives[$k]) ? $tax_cumulatives[$k] : '0' );
				}
			}
			$this->Room_taxes->save($bedrooms_taxes_data, $room_id);
			
			
			//Delete Image
			if($this->input->post('del_image') && $room_id != -1)
			{
			    if($cur_room_info->image_id != null)
			    {
					$this->Room->update_image(NULL,$room_id);
					$this->Appfile->delete($cur_room_info->image_id);
			    }
			}

			//Save Image File
			if(!empty($_FILES["image_id"]) && $_FILES["image_id"]["error"] == UPLOAD_ERR_OK)
			{			    
			    $allowed_extensions = array('png', 'jpg', 'jpeg', 'gif');
				$extension = strtolower(pathinfo($_FILES["image_id"]["name"], PATHINFO_EXTENSION));

			    if (in_array($extension, $allowed_extensions))
			    {
				    $config['image_library'] = 'gd2';
				    $config['source_image']	= $_FILES["image_id"]["tmp_name"];
				    $config['create_thumb'] = FALSE;
				    $config['maintain_ratio'] = TRUE;
				    $config['width']	 = 400;
				    $config['height']	= 300;
				    $this->load->library('image_lib', $config); 
				    $this->image_lib->resize();
				    $image_file_id = $this->Appfile->save($_FILES["image_id"]["name"], file_get_contents($_FILES["image_id"]["tmp_name"]));
			    }

			    $this->Room->update_image($image_file_id,$room_id);
			}
		}
		else //failure
		{
			echo json_encode(array('success'=>false,'message'=>lang('bedrooms_error_adding_updating').' '.
			$room_data['name'],'room_id'=>-1));
		}

	}

	function save_inventory($room_id=-1)
	{
		$this->check_action_permission('add_update');		
		$employee_id=$this->Employee->get_logged_in_employee_info()->person_id;
		$cur_room_info = $this->Room->get_info($room_id);
		$cur_room_location_info = $this->Room_location->get_info($room_id);
		
		$inv_data = array
		(
			'trans_date'=>date('Y-m-d H:i:s'),
			'trans_room'=>$room_id,
			'trans_user'=>$employee_id,
			'trans_comment'=>$this->input->post('trans_comment'),
			'trans_inventory'=>$this->input->post('newquantity'),
			'location_id'=>$this->Employee->get_logged_in_employee_current_location_id()
		);
		$this->Bedrooms_inventory->insert($inv_data);

		//Update stock quantity
		if($this->Room_location->save_quantity($cur_room_location_info->quantity + $this->input->post('newquantity'),$room_id))
		{
			echo json_encode(array('success'=>true,'message'=>lang('bedrooms_successful_updating').' '.
			$cur_room_info->name,'room_id'=>$room_id));
		}
		else//failure
		{
			echo json_encode(array('success'=>false,'message'=>lang('bedrooms_error_adding_updating').' '.
			$cur_room_info->name,'room_id'=>-1));
		}

	}

	function delete()
	{
		$this->check_action_permission('delete');		
		$bedrooms_to_delete=$this->input->post('ids');
		$select_inventory=$this->get_select_inventory();
		$total_rows= $select_inventory ? $this->Room->count_all() : count($bedrooms_to_delete);
		//clears the total inventory selection
		$this->clear_select_inventory();
		if($this->Room->delete_list($bedrooms_to_delete,$select_inventory))
		{
			
			echo json_encode(array('success'=>true,'message'=>lang('bedrooms_successful_deleted').' '.
			$total_rows.' '.lang('bedrooms_one_or_multiple')));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>lang('bedrooms_cannot_be_deleted')));
		}
	}

	function cleanup()
	{
		$this->Room->cleanup();
		echo json_encode(array('success'=>true,'message'=>lang('bedrooms_cleanup_sucessful')));
	}

	function get_select_inventory() 
	{
		return $this->session->userdata('select_inventory') ? $this->session->userdata('select_inventory') : 0;
	}

	function clear_select_inventory() 	
	{
		$this->session->unset_userdata('select_inventory');
		
	}
}
?>