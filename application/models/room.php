<?php
class Room extends CI_Model
{
	/*
	Determines if a given room_id is an room
	*/
	function exists($room_id)
	{
		$this->db->from('bedrooms');
		$this->db->where('room_id',$room_id);
		$query = $this->db->get();

		return ($query->num_rows()==1);
	}

	/*
	Returns all the bedrooms
	*/
	function get_all($limit=10000, $offset=0,$col='room_id',$order='desc')
	{
		$current_location=$this->Employee->get_logged_in_employee_current_location_id();
		$this->db->select('bedrooms.*,
		location_bedrooms.quantity as quantity, 
		location_bedrooms.cost_price as location_cost_price,
		location_bedrooms.unit_price as location_unit_price');
		
		$this->db->from('bedrooms');
		$this->db->join('location_bedrooms', 'location_bedrooms.room_id = bedrooms.room_id and location_id = '.$current_location, 'left');
		$this->db->where('bedrooms.deleted',0);
		$this->db->order_by($col, $order);
		$this->db->limit($limit);
		$this->db->offset($offset);
		return $this->db->get();
	}
	
	function get_all_by_category($category, $offset=0, $limit = 14)
	{
		$bedrooms_table = $this->db->dbprefix('bedrooms');
		$room_kits_table = $this->db->dbprefix('room_kits');
		
		$result = $this->db->query("(SELECT room_id, name, image_id FROM $bedrooms_table 
		WHERE deleted = 0 and category = ".$this->db->escape($category). " ORDER BY name)  ORDER BY name LIMIT $offset, $limit");
		return $result;
	}
	
	function count_all_by_category($category)
	{
		$this->db->from('bedrooms');
		$this->db->where('deleted',0);
		$this->db->where('category',$category);
		$bedrooms_count = $this->db->count_all_results();

		
		return $bedrooms_count ;

	}
	
	function get_all_categories()
	{
		$this->db->select('category');
		$this->db->from('bedrooms');
		$this->db->where('deleted',0);
		$this->db->distinct();
		$this->db->order_by("category", "asc");
		return $this->db->get();
	}
	
	function get_next_id($room_id)
	{
		$bedrooms_table = $this->db->dbprefix('bedrooms');
		$result = $this->db->query("SELECT room_id FROM $bedrooms_table WHERE room_id = (select min(room_id) from $bedrooms_table where deleted = 0 and room_id > ".$this->db->escape($room_id).")");
		
		if($result->num_rows() > 0)
		{
			$row = $result->result();
			return $row[0]->room_id;
		}
		
		return FALSE;
	}
	
	function get_prev_id($room_id)
	{
		$bedrooms_table = $this->db->dbprefix('bedrooms');
		$result = $this->db->query("SELECT room_id FROM $bedrooms_table WHERE room_id = (select max(room_id) from $bedrooms_table where deleted = 0 and room_id <".$this->db->escape($room_id).")");
		
		if($result->num_rows() > 0)
		{
			$row = $result->result();
			return $row[0]->room_id;
		}
		
		return FALSE;
	}
	
	function get_tier_price_row($tier_id,$room_id)
	{
		$this->db->from('bedrooms_tier_prices');
		$this->db->where('tier_id',$tier_id);
		$this->db->where('room_id ',$room_id);
		return $this->db->get()->row();
	}
		
	function delete_tier_price($tier_id, $room_id)
	{
		
		$this->db->where('tier_id', $tier_id);
		$this->db->where('room_id', $room_id);
		$this->db->delete('bedrooms_tier_prices');
	}
	
	function tier_exists($tier_id, $room_id)
	{
		$this->db->from('bedrooms_tier_prices');
		$this->db->where('tier_id',$tier_id);
		$this->db->where('room_id',$room_id);
		$query = $this->db->get();

		return ($query->num_rows()>=1);
		
	}
	
	function save_room_tiers($tier_data,$room_id)
	{
		if($this->tier_exists($tier_data['tier_id'],$room_id))
		{
			$this->db->where('tier_id', $tier_data['tier_id']);
			$this->db->where('room_id', $room_id);

			return $this->db->update('bedrooms_tier_prices',$tier_data);
			
		}

		return $this->db->insert('bedrooms_tier_prices',$tier_data);	
	}


	function account_number_exists($room_number)
	{
		$this->db->from('bedrooms');	
		$this->db->where('room_number',$room_number);
		$query = $this->db->get();
		
		return ($query->num_rows()==1);
	}

	
	function count_all()
	{
		$this->db->from('bedrooms');
		$this->db->where('deleted',0);
		return $this->db->count_all_results();
	}
	
	/*
	Gets information about a particular room
	*/
	function get_info($room_id)
	{
		$this->db->from('bedrooms');
		$this->db->where('room_id',$room_id);
		
		$query = $this->db->get();

		if($query->num_rows()==1)
		{
			return $query->row();
		}
		else
		{
			//Get empty base parent object, as $room_id is NOT an room
			$room_obj=new stdClass();

			//Get all the fields from bedrooms table
			$fields = $this->db->list_fields('bedrooms');

			foreach ($fields as $field)
			{
				$room_obj->$field='';
			}

			return $room_obj;
		}
	}

	/*
	Get an room id given an room number or product_id or additional room number
	*/
	function get_room_id($room_number)
	{
		$this->db->from('bedrooms');
		$this->db->where('room_number',$room_number);
		//$this->db->or_where('product_id', $room_number); 

		$query = $this->db->get();

		if($query->num_rows() >= 1)
		{
			return $query->row()->room_id;
		}
		
		if ($additional_room_id = $this->Additional_room_numbers->get_room_id($room_number))
		{
			return $additional_room_id;
		}

		return false;
	}

	/*
	Gets information about multiple bedrooms
	*/
	function get_multiple_info($room_ids)
	{
		$this->db->from('bedrooms');
		$this->db->where_in('room_id',$room_ids);
		$this->db->order_by("room_id", "asc");
		return $this->db->get();
	}

	/*
	Inserts or updates a room
	*/
	function save(&$room_data,$room_id=false)
	{
		if (!$room_id or !$this->exists($room_id))
		{
			if($this->db->insert('bedrooms',$room_data))
			{
				$room_data['room_id']=$this->db->insert_id();
				return true;
			}
			return false;
		}

		$this->db->where('room_id', $room_id);
		return $this->db->update('bedrooms',$room_data);
	}

	/*
	Updates multiple bedrooms at once
	*/
	function update_multiple($room_data,$room_ids,$select_inventory=0)
	{
		if(!$select_inventory){
		$this->db->where_in('room_id',$room_ids);
		}
		return $this->db->update('bedrooms',$room_data);
	}

	
	/*
	Deletes one room
	*/
	function delete($room_id)
	{
		$room_info = $this->Room->get_info($room_id);
	
		if ($room_info->image_id !== NULL)
		{
			$this->Room->update_image(NULL,$room_id);
			$this->Appfile->delete($room_info->image_id);			
		}			
		
		$this->db->where('room_id', $room_id);
		return $this->db->update('bedrooms', array('deleted' => 1));
	}

	/*
	Deletes a list of bedrooms
	*/
	function delete_list($room_ids,$select_inventory)
	{
		foreach($room_ids as $room_id)
		{
			$room_info = $this->Room->get_info($room_id);
		
			if ($room_info->image_id !== NULL)
			{
				$this->Room->update_image(NULL,$room_id);
				$this->Appfile->delete($room_info->image_id);			
			}			
		}
		
		if(!$select_inventory){
		$this->db->where_in('room_id',$room_ids);
		}
		return $this->db->update('bedrooms', array('deleted' => 1));
 	}

 	/*
	Get search suggestions to find bedrooms
	*/
	function get_search_suggestions($search,$limit=25)
	{
		$suggestions = array();

		$this->db->from('bedrooms');
		$this->db->like('name', $search);
		$this->db->where('deleted',0);
		$this->db->limit($limit);
		$by_name = $this->db->get();
		$temp_suggestions = array();
		foreach($by_name->result() as $row)
		{
			$temp_suggestions[] = $row->name;
		}
		
		sort($temp_suggestions);
		foreach($temp_suggestions as $temp_suggestion)
		{
			$suggestions[]=array('label'=> $temp_suggestion);		
		}
		
		
		$this->db->select('category');
		$this->db->from('bedrooms');
		$this->db->where('deleted',0);
		$this->db->distinct();
		$this->db->like('category', $search);
		$this->db->limit($limit);
		$by_category = $this->db->get();
		
		$temp_suggestions = array();
		foreach($by_category->result() as $row)
		{
			$temp_suggestions[] = $row->category;
		}
		
		sort($temp_suggestions);
		foreach($temp_suggestions as $temp_suggestion)
		{
			$suggestions[]=array('label'=> $temp_suggestion);		
		}
		

		$this->db->from('bedrooms');
		$this->db->like('room_number', $search);
		$this->db->where('deleted',0);
		$this->db->limit($limit);
		$by_room_number = $this->db->get();
		
		$temp_suggestions = array();
		foreach($by_room_number->result() as $row)
		{
			$temp_suggestions[] = $row->room_number;
		}
		
		sort($temp_suggestions);
		foreach($temp_suggestions as $temp_suggestion)
		{
			$suggestions[]=array('label'=> $temp_suggestion);		
		}
		
		$this->db->from('bedrooms');
		$this->db->where('deleted',0);
		$this->db->limit($limit);
		
		$this->db->from('bedrooms');
		$this->db->where('room_id', $search);
		$this->db->where('deleted',0);
		$this->db->limit($limit);
		$by_room_id = $this->db->get();
		$temp_suggestions = array();
		foreach($by_room_id->result() as $row)
		{
			$temp_suggestions[] = $row->room_id;
		}
		
		sort($temp_suggestions);
		foreach($temp_suggestions as $temp_suggestion)
		{
			$suggestions[]=array('label'=> $temp_suggestion);		
		}
		
		//only return $limit suggestions
		if(count($suggestions > $limit))
		{
			$suggestions = array_slice($suggestions, 0,$limit);
		}
		return $suggestions;

	}

	function check_duplicate($term)
	{
		$this->db->from('bedrooms');
		$this->db->where('deleted',0);		
		$query = $this->db->where("name = ".$this->db->escape($term));
		$query=$this->db->get();
		
		if($query->num_rows()>0)
		{
			return true;
		}
		
		
	}
	
	function get_room_search_suggestions($search,$limit=25,$optionTotal="")
	{
		$suggestions = array();

		$this->db->from('bedrooms');
		$this->db->where('deleted',0);
		$this->db->like('name', $search);
		$this->db->limit($limit);
		$by_name = $this->db->get();
		
		$temp_suggestions = array();
		
		foreach($by_name->result() as $row)
		{
			if ($row->category && $row->size)
			{
				$temp_suggestions[$row->room_id] =  $row->name . ' ('.$row->category.', '.$row->size.')';
			}
			elseif ($row->category)
			{
				$temp_suggestions[$row->room_id] =  $row->name . ' ('.$row->category.')';
			}
			elseif ($row->size)
			{
				$temp_suggestions[$row->room_id] =  $row->name . ' ('.$row->size.')';
			}
			else
			{
				$temp_suggestions[$row->room_id] = $row->name;				
			}
			
		}
		
		asort($temp_suggestions);
		
		if ($optionTotal!="")
		{
			$suggestions[]=array('value'=> '', 'label' => "Todos");		
			
		}

		foreach($temp_suggestions as $key => $value)
		{
			$suggestions[]=array('value'=> $key, 'label' => $value);		
		}
		
		$this->db->from('bedrooms');
		$this->db->where('deleted',0);
		$this->db->like('room_number', $search);
		$this->db->limit($limit);
		$by_room_number = $this->db->get();
		
		$temp_suggestions = array();
		
		foreach($by_room_number->result() as $row)
		{
			$temp_suggestions[$row->room_id] = $row->room_number;
		}
		
		asort($temp_suggestions);
		
		foreach($temp_suggestions as $key => $value)
		{
			$suggestions[]=array('value'=> $key, 'label' => $value);		
		}
				
		$this->db->from('bedrooms');
		$this->db->where('deleted',0);
		$this->db->limit($limit);
		
		for($k=count($suggestions)-1;$k>=0;$k--)
		{
			if (!$suggestions[$k]['label'])
			{
				unset($suggestions[$k]);
			}
		}
		
		$suggestions = array_values($suggestions);
		
		//only return $limit suggestions
		if(count($suggestions > $limit))
		{
			$suggestions = array_slice($suggestions, 0,$limit);
		}
		return $suggestions;

	}

	function get_category_suggestions($search)
	{
		$suggestions = array();
		$this->db->distinct();
		$this->db->select('category');
		$this->db->from('bedrooms');
		$this->db->like('category', $search);
		$this->db->where('deleted', 0);
		$this->db->limit(25);
		$by_category = $this->db->get();
		foreach($by_category->result() as $row)
		{
			$suggestions[]=array('label' => $row->category);
		}

		return $suggestions;
	}

	/*
	Preform a search on bedrooms
	*/
	
	function search($search, $category = false, $limit=20,$offset=0,$column='name',$orderby='asc')
	{
		$current_location=$this->Employee->get_logged_in_employee_current_location_id();
		
			$search_terms_array=explode(" ", $this->db->escape_like_str($search));
	
			//to keep track of which search term of the array we're looking at now	
			$search_name_criteria_counter=0;
			$sql_search_name_criteria = '';
			//loop through array of search terms
			foreach ($search_terms_array as $x)
			{
				$sql_search_name_criteria.=
				($search_name_criteria_counter > 0 ? " AND " : "").
				"name LIKE '%".$this->db->escape_like_str($x)."%'";
				$search_name_criteria_counter++;
			}
				
			
			$this->db->select('bedrooms.*,
			location_bedrooms.quantity as quantity, 
			location_bedrooms.cost_price as location_cost_price,
			location_bedrooms.unit_price as location_unit_price');
			$this->db->from('bedrooms');
			$this->db->join('location_bedrooms', 'location_bedrooms.room_id = bedrooms.room_id and location_id = '.$current_location, 'left');
			$this->db->where("((".
			$sql_search_name_criteria. ") or 
			room_number LIKE '%".$this->db->escape_like_str($search)."%' or ".
			$this->db->dbprefix('bedrooms').".room_id LIKE '%".$this->db->escape_like_str($search)."%' or 
			category LIKE '%".$this->db->escape_like_str($search)."%') and deleted=0");
			
			if ($category)
			{
				$this->db->where('bedrooms.category', $category);
			}
				
			$this->db->order_by($column, $orderby);
			$this->db->limit($limit);
			$this->db->offset($offset);
			return $this->db->get();
	}

	function search_count_all($search, $category = FALSE, $limit=10000)
	{
			$this->db->from('bedrooms');
			$this->db->where("(name LIKE '%".$this->db->escape_like_str($search)."%' or 
			room_number LIKE '%".$this->db->escape_like_str($search)."%' or 
			category LIKE '%".$this->db->escape_like_str($search)."%') and deleted=0");
			
			if ($category)
			{
				$this->db->where('bedrooms.category', $category);
			}
			
			$this->db->limit($limit);
			$result=$this->db->get();				
			return $result->num_rows();
	}

	
	function get_categories()
	{
		$this->db->select('category');
		$this->db->from('bedrooms');
		$this->db->where('deleted',0);
		$this->db->distinct();
		$this->db->order_by("category", "asc");

		return $this->db->get();
	}
	
	function cleanup()
	{
		$room_data = array('room_number' => null);
		$this->db->where('deleted', 1);
		return $this->db->update('bedrooms',$room_data);
	}
	
	function update_image($file_id,$room_id)
	{
		$this->db->set('image_id',$file_id);
	    $this->db->where('room_id',$room_id);
	    
		return $this->db->update('bedrooms');
	}
	
	function create_or_update_store_account_room()
	{
		$room_id = FALSE;
		
		$this->db->from('bedrooms');
		$this->db->where('name', lang('sales_store_account_payment'));
		$this->db->where('deleted', 0);

		$result=$this->db->get();				
		if ($result->num_rows() > 0)
		{
			$query_result = $result->result();
			$room_id = $query_result[0]->room_id;
		}
		
		$room_data = array(
			'name'			=>	lang('sales_store_account_payment'),
			'description'	=>	'',
			'room_number'	=> NULL,
			'category'		=>	lang('sales_store_account_payment'),
			'beds'			=> '',
			'cost_price'	=>	0,
			'unit_price'	=>	0,
			'is_service'=> 1
		);
		
		$this->save($room_data, $room_id);
			
		if ($room_id)
		{
			return $room_id;
		}
		else
		{
			return $room_data['room_id'];
		}
	}
	
	function get_store_account_room_id()
	{
		$this->db->from('bedrooms');
		$this->db->where('name', lang('sales_store_account_payment'));
		$this->db->where('deleted', 0);

		$result=$this->db->get();				
		if ($result->num_rows() > 0)
		{
			$query_result = $result->result();
			return $query_result[0]->room_id;
		}
		
		return FALSE;
	}
}
?>
