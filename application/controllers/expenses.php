<?php
require_once ("secure_area.php");
class Expenses extends Secure_area
{
	function __construct()
	{
		parent::__construct('expenses');
	}

	function index($offset=0)
	{
		$params = $this->session->userdata('expenses_search_data') ? $this->session->userdata('expenses_search_data') : array('offset' => 0, 'order_col' => 'expense_time', 'order_dir' => 'desc', 'search' => FALSE);
		if ($offset!=$params['offset'])
		{
		   redirect('expenses/index/'.$params['offset']);
		}
		$this->check_action_permission('search');
		$config['base_url'] = site_url('expenses/sorting');
		$config['per_page'] = $this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20; 
		$data['controller_name']=strtolower(get_class());
		$data['per_page'] = $config['per_page'];
		//$data['search'] = $params['search'] ? $params['search'] : "";
		// if ($data['search'])
		// {
		// 	$config['total_rows'] = $this->Expense->search_count_all($data['search']);
		// 	$table_data = $this->Expense->search($data['search'],$data['per_page'],$params['offset'],$params['order_col'],$params['order_dir']);
		// 	$total_quantity=$this->Expense->search_total($data['search'],$data['per_page'],$params['offset'],$params['order_col'],$params['order_dir']);
		// }
		// else
		// {
			$config['total_rows'] = $this->Expense->count_all();
			$table_data = $this->Expense->get_all($data['per_page'],$params['offset'],$params['order_col'],$params['order_dir']);
			$total_quantity = $this->Expense->get_all_total($data['per_page'],$params['offset'],$params['order_col'],$params['order_dir']);
		//}
		$this->pagination->initialize($config);
		$data['pagination'] = $this->pagination->create_links();
		$data['order_col'] = $params['order_col'];
		$data['order_dir'] = $params['order_dir'];
		$data['manage_table']=get_expense_manage_table($table_data,$this);
		$data['total_rows'] = $config['total_rows'];
		$data['total_quantity']=$total_quantity;
		$this->load->view('expenses/manage',$data);
	}

	function clear_state()
	{
		$this->session->unset_userdata('expenses_search_data');
		redirect('expenses');
	}

	/*
	This deletes expenses from the expenses table
	*/
	function delete()
	{
		$this->check_action_permission('delete');
		$expenses_to_ids=$this->input->post('ids');
		$expenses_to_types=$this->input->post('types');
		$expenses_to_delete=array();
		for($i=0;$i<count($expenses_to_ids);$i++)
		{
			$expenses_to_delete[]=array('id'=>$expenses_to_ids[$i],'type'=>$expenses_to_types[$i]);
		}


		if($this->Expense->delete_list($expenses_to_delete))
		{
			$success_message=lang('expenses_successful_deleted').' '.
			count($expenses_to_delete).' '.lang('expenses_one_or_multiple');
			$this->session->set_flashdata('manage_success_message', $success_message);
			echo json_encode(array('success'=>true,'message'=>$success_message,'redirect' => true));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>lang('expenses_cannot_be_deleted')));
		}
	}
	

	/*
	Loads the customer edit form
	*/
	function view($expense_id=-1,$redirect=0)
	{
		$this->check_action_permission('add_update');
		
		
		
		$locations = array();
		$locations_result = $this->Location->get_all()->result_array();
		
		if (count($locations_result) > 0)
		{
			//$locations[0] = lang('locations_none');
			foreach($locations_result as $location)
			{
				$locations[$location['location_id']]=$location['name'];
			}	
		}
		$data['locations']=$locations;

		$data['employees'] = array();
		foreach ($this->Employee->get_all()->result() as $employee)
		{
			$data['employees'][$employee->person_id] = $employee->first_name . ' '. $employee->last_name;
		}

		$data['categorys'] = array();
		foreach ($this->Category->get_all()->result() as $category)
		{
			$data['categorys'][$category->category_id] = $category->name;
		}

		$data['controller_name']=strtolower(get_class());
		$data['expense_info']=$this->Expense->get_info($expense_id);
		$data['redirect']= $redirect;
		$this->load->view("expenses/form",$data);
	}

	function save($expense_id=-1)
	{
		$expense_data = array(
		'expense_time'=>date('Y-m-d', strtotime($this->input->post('expense_time'))),
		'nro_receipt'=>$this->input->post('nro_receipt'),
		'description'=>$this->input->post('description'),
		'quantity'=>$this->input->post('quantity'),
		'category_id'=>$this->input->post('category_id'),
		'location_id'=>$this->input->post('location_id'),
		'approved_by_employee_id'=>$this->input->post('approved_by_employee_id'),
		'note'=>$this->input->post('note')
		);

		$redirect=$this->input->post('redirect');

		if( $this->Expense->save( $expense_data, $expense_id ) )
		{
			$success_message = '';
			
			//New giftcard
			if($expense_id==-1)
			{
				$success_message = lang('expenses_successful_adding');
				echo json_encode(array('success'=>true,'message'=>$success_message,'expense_id'=>$expense_data['expense_id'],'redirect' => $redirect,'expense_id' => $expense_data['expense_id']));
				$expense_id = $expense_data['expense_id'];
			}
			else //previous giftcard
			{
				$success_message = lang('expenses_successful_updating');
				$this->session->set_flashdata('manage_success_message', $success_message);
				echo json_encode(array('success'=>true,'message'=>$success_message,'expense_id'=>$expense_id,'redirect' => $redirect,'expense_id' => $expense_data['expense_id']));
			}
			
		}
		else//failure
		{
			echo json_encode(array('success'=>false,'message'=>lang('expenses_error_adding_updating'),'expense_id'=>-1));
		}

	}

	function receipt($expense_id)
	{
		$expense_info = $this->Expense->get_info($expense_id);
		
		$category_id = $expense_info->category_id;
		$category_info = $this->Category->get_info($category_id);
		$data['category_name'] = $category_info->name;
		
		$location_id = $expense_info->location_id;
		$location_info = $this->Location->get_info($location_id);
		$data['location_name'] = $location_info->name;

		$person_id = $expense_info->approved_by_employee_id;
		$person_info = $this->Person->get_info($person_id);
		$data['person_name'] = $person_info->first_name." ".$person_info->last_name;;
		
		$data['expense_time']= date(get_date_format(), strtotime($expense_info->expense_time));
		
		$data['expense_id'] = $expense_id;
		$data['nro_receipt'] = $expense_info->nro_receipt;
		$data['description'] = $expense_info->description;
		$data['quantity'] = $expense_info->quantity;
		$data['note'] = $expense_info->note;
		
		
		$this->load->view("expenses/receipt",$data);
		
	}

	function cash_receipt($cash_id)
	{
		$cash_info = $this->Cash_withdrawal->get_info($cash_id);
		
		$location_id = $cash_info->location_id;
		$location_info = $this->Location->get_info($location_id);
		$data['location_name'] = $location_info->name;

		$person_id = $cash_info->approved_by_employee_id;
		$person_info = $this->Person->get_info($person_id);
		$data['person_name'] = $person_info->first_name." ".$person_info->last_name;;
		
		$data['cash_time']= date(get_date_format(), strtotime($cash_info->cash_time));
		
		$data['cash_id'] = $cash_id;
		$data['description'] = $cash_info->description;
		$data['quantity'] = $cash_info->quantity;
		
		
		$this->load->view("expenses/cash_receipt",$data);
		
	}

	function sorting()
	{
		$this->check_action_permission('search');
		$search=$this->input->post('search') ? $this->input->post('search') : "";
		$per_page=$this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20;
		
		$offset = $this->input->post('offset') ? $this->input->post('offset') : 0;
		$order_col = $this->input->post('order_col') ? $this->input->post('order_col') : 'expense_id';
		$order_dir = $this->input->post('order_dir') ? $this->input->post('order_dir'): 'desc';

		$expenses_search_data = array('offset' => $offset, 'order_col' => $order_col, 'order_dir' => $order_dir, 'search' => $search);
		$this->session->set_userdata("expenses_search_data",$expenses_search_data);
		
		if ($search)
		{
			$config['total_rows'] = $this->Expense->search_count_all($search);
			$table_data = $this->Expense->search($search,$per_page,$this->input->post('offset') ? $this->input->post('offset') : 0, $this->input->post('order_col') ? $this->input->post('order_col') : 'expense_id' ,$this->input->post('order_dir') ? $this->input->post('order_dir'): 'desc');
		}
		else
		{
			$config['total_rows'] = $this->Expense->count_all();
			$table_data = $this->Expense->get_all($per_page,$this->input->post('offset') ? $this->input->post('offset') : 0, $this->input->post('order_col') ? $this->input->post('order_col') : 'expense_id' ,$this->input->post('order_dir') ? $this->input->post('order_dir'): 'desc');
		}
		$config['base_url'] = site_url('expenses/sorting');
		$config['per_page'] = $per_page; 
		$this->pagination->initialize($config);
		$data['pagination'] = $this->pagination->create_links();
		$data['manage_table']=get_people_manage_table_data_rows($table_data,$this);
		echo json_encode(array('manage_table' => $data['manage_table'], 'pagination' => $data['pagination']));	
		
	}
	
	/*
	Returns customer table data rows. This will be called with AJAX.
	*/
	function search()
	{
		$this->check_action_permission('search');
		$search=$this->input->post('search');
		$offset = $this->input->post('offset') ? $this->input->post('offset') : 0;
		$order_col = $this->input->post('order_col') ? $this->input->post('order_col') : 'expense_id';
		$order_dir = $this->input->post('order_dir') ? $this->input->post('order_dir'): 'desc';

		$expenses_search_data = array('offset' => $offset, 'order_col' => $order_col, 'order_dir' => $order_dir, 'search' => $search);
		$this->session->set_userdata("expenses_search_data",$expenses_search_data);
		$per_page=$this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20;
		$search_data=$this->Expense->search($search,$per_page,$offset, $order_col ,$order_dir);
		$config['base_url'] = site_url('expenses/search');
		$config['total_rows'] = $this->Expense->search_count_all($search);
		$config['per_page'] = $per_page ;
		$this->pagination->initialize($config);				
		$data['pagination'] = $this->pagination->create_links();
		$data['total_rows'] = $this->Expense->search_count_all($search);
		$data['manage_table']=get_people_manage_table_data_rows($search_data,$this);
		echo json_encode(array('manage_table' => $data['manage_table'], 'pagination' => $data['pagination']));
	}
	
	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{
		$suggestions = $this->Expense->get_search_suggestions($this->input->get('term'),100);
		echo json_encode($suggestions);
	}




	function category($offset=0)
	{
		$params = $this->session->userdata('category_search_data') ? $this->session->userdata('category_search_data') : array('offset' => 0, 'order_col' => 'name', 'order_dir' => 'asc', 'search' => FALSE);
		if ($offset!=$params['offset'])
		{
		   redirect('expenses/category/'.$params['offset']);
		}
		$this->check_action_permission('search');
		$config['base_url'] = site_url('expenses/category_sorting');
		$config['per_page'] = $this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20; 
		$data['controller_name']=strtolower(get_class());
		$data['per_page'] = $config['per_page'];
		$data['search'] = $params['search'] ? $params['search'] : "";
		if ($data['search'])
		{
			$config['total_rows'] = $this->Category->search_count_all($data['search']);
			$table_data = $this->Category->search($data['search'],$data['per_page'],$params['offset'],$params['order_col'],$params['order_dir']);
		}
		else
		{
			$config['total_rows'] = $this->Category->count_all();
			$table_data = $this->Category->get_all($data['per_page'],$params['offset'],$params['order_col'],$params['order_dir']);
		}
		$this->pagination->initialize($config);
		$data['pagination'] = $this->pagination->create_links();
		$data['order_col'] = $params['order_col'];
		$data['order_dir'] = $params['order_dir'];
		$data['manage_table']=get_category_manage_table($table_data,$this);
		$data['total_rows'] = $config['total_rows'];
		$this->load->view('expenses/category_manage',$data);
	}

	function category_view($category_id=-1,$redirect=0)
	{
		$this->check_action_permission('add_update');
		
		
		$data['controller_name']=strtolower(get_class());
		$data['category_info']=$this->Category->get_info($category_id);
		$data['redirect']= $redirect;
		$this->load->view("expenses/category_form",$data);
	}

	function category_save($category_id=-1)
	{
		$category_data = array(
		'name'=>$this->input->post('name')
		);

		$redirect=$this->input->post('redirect');

		if( $this->Category->save( $category_data, $category_id ) )
		{
			$success_message = '';
			
			//New giftcard
			if($category_id==-1)
			{
				$success_message = lang('category_successful_adding');
				$this->session->set_flashdata('manage_success_message', $success_message);
				echo json_encode(array('success'=>true,'message'=>$success_message,'category_id'=>$category_data['category_id'],'redirect' => $redirect));
				$category_id = $category_data['category_id'];
			}
			else //previous giftcard
			{
				$success_message = lang('category_successful_updating');
				$this->session->set_flashdata('manage_success_message', $success_message);
				echo json_encode(array('success'=>true,'message'=>$success_message,'category_id'=>$category_id,'redirect' => $redirect));
			}
			
		}
		else//failure
		{
			echo json_encode(array('success'=>false,'message'=>lang('category_error_adding_updating'),'category_id'=>-1));
		}

	}

	function category_delete()
	{
		$this->check_action_permission('delete');
		$category_to_delete=$this->input->post('ids');
		
		if($this->Category->delete_list($category_to_delete))
		{
			$success_message = lang('category_successful_deleted').' '.
			count($category_to_delete).' '.lang('category_one_or_multiple');
			$this->session->set_flashdata('manage_success_message', $success_message);
			echo json_encode(array('success'=>true,'message'=>$success_message,'redirect' => true));
		}
		else
		{
			$success_message = lang('category_cannot_be_deleted');
			$this->session->set_flashdata('manage_success_message', $success_message);
			echo json_encode(array('success'=>false,'message'=>$success_message));
		}
	}

	function category_search()
	{
		$this->check_action_permission('search');
		$search=$this->input->post('search');
		$offset = $this->input->post('offset') ? $this->input->post('offset') : 0;
		$order_col = $this->input->post('order_col') ? $this->input->post('order_col') : 'name';
		$order_dir = $this->input->post('order_dir') ? $this->input->post('order_dir'): 'asc';

		$category_search_data = array('offset' => $offset, 'order_col' => $order_col, 'order_dir' => $order_dir, 'search' => $search);
		$this->session->set_userdata("category_search_data",$category_search_data);
		$per_page=$this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20;
		$search_data=$this->Category->search($search,$per_page,$offset, $order_col ,$order_dir);
		$config['base_url'] = site_url('expenses/category_search');
		$config['total_rows'] = $this->Category->search_count_all($search);
		$config['per_page'] = $per_page ;
		$this->pagination->initialize($config);				
		$data['pagination'] = $this->pagination->create_links();
		$data['total_rows'] = $this->Category->search_count_all($search);
		$data['manage_table']=get_category_manage_table_data_rows($search_data,$this);
		echo json_encode(array('manage_table' => $data['manage_table'], 'pagination' => $data['pagination']));
	}

	function category_clear_state()
	{
		$this->session->unset_userdata('category_search_data');
		redirect('expenses/category');
	}
	
	/*
	Gives search suggestions based on what is being searched for
	*/
	function category_suggest()
	{
		$suggestions = $this->Category->get_search_suggestions($this->input->get('term'),100);
		echo json_encode($suggestions);
	}

	function cash($cash_id=-1,$redirect=0)
	{
		$this->check_action_permission('add_update');
		
		
		
		$locations = array();
		$locations_result = $this->Location->get_all()->result_array();
		
		if (count($locations_result) > 0)
		{
			//$locations[0] = lang('locations_none');
			foreach($locations_result as $location)
			{
				$locations[$location['location_id']]=$location['name'];
			}	
		}
		$data['locations']=$locations;

		$data['employees'] = array();
		foreach ($this->Employee->get_all()->result() as $employee)
		{
			$data['employees'][$employee->person_id] = $employee->first_name . ' '. $employee->last_name;
		}

		
		$data['controller_name']=strtolower(get_class());
		$data['cash_info']=$this->Cash_withdrawal->get_info($cash_id);
		$data['redirect']= $redirect;
		$this->load->view("expenses/cash_form",$data);
	}


	function cash_save($cash_id=-1)
	{
		$cash_data = array(
		'cash_time'=>date('Y-m-d', strtotime($this->input->post('cash_time'))),
		'description'=>$this->input->post('description'),
		'quantity'=>$this->input->post('quantity'),
		'location_id'=>$this->input->post('location_id'),
		'approved_by_employee_id'=>$this->input->post('approved_by_employee_id')
		);

		$redirect=$this->input->post('redirect');

		if( $this->Cash_withdrawal->save( $cash_data, $cash_id ) )
		{
			$success_message = '';
			
			//New giftcard
			if($cash_id==-1)
			{
				$success_message = lang('expenses_cash_successful_adding');
				$this->session->set_flashdata('manage_success_message', $success_message);
				echo json_encode(array('success'=>true,'message'=>$success_message,'cash_id'=>$cash_data['cash_id'],'redirect' => $redirect));
				$cash_id = $cash_data['cash_id'];
			}
			else //previous giftcard
			{
				$success_message = lang('expenses_cash_successful_updating');
				$this->session->set_flashdata('manage_success_message', $success_message);
				echo json_encode(array('success'=>true,'message'=>$success_message,'cash_id'=>$cash_id,'redirect' => $redirect));
			}
			
		}
		else//failure
		{
			echo json_encode(array('success'=>false,'message'=>lang('expenses_cash_error_adding_updating'),'cash_id'=>-1));
		}

	}
	
}
?>