<?php
class Expense extends CI_Model
{
	function get_all($limit=10000, $offset=0,$col='g.expense_id',$order='desc')
	{

		$expenses=$this->db->dbprefix('expenses');
		$cash=$this->db->dbprefix('cash_withdrawal');
		$locations=$this->db->dbprefix('locations');
		$employees=$this->db->dbprefix('employees');
		$category=$this->db->dbprefix('category');
		$peoples=$this->db->dbprefix('people');
		$location_id=$this->Employee->get_logged_in_employee_current_location_id();
		$data=$this->db->query("SELECT g.expense_id,g.expense_time,g.nro_receipt,g.description,g.quantity,CONCAT(p.first_name,' ',p.last_name) as approved,l.name as location,c.name as category
						FROM ".$expenses." as g
						INNER JOIN ".$locations." as l ON l.location_id=g.location_id
						INNER JOIN ".$employees." as e ON e.id=g.approved_by_employee_id
						INNER JOIN ".$peoples." as p ON p.person_id=e.person_id
						INNER JOIN ".$category." as c ON c.category_id=g.category_id
						WHERE g.deleted =0 AND g.expense_time='".date('Y-m-d')."'
						AND g.location_id='".$location_id."' 
						UNION
						SELECT g.cash_id as expense_id,g.cash_time as expense_time,'' as nro_receipt,g.description,g.quantity,CONCAT(p.first_name,' ',p.last_name) as approved,l.name as location,'' as category
						FROM ".$cash." as g
						INNER JOIN ".$locations." as l ON l.location_id=g.location_id
						INNER JOIN ".$employees." as e ON e.id=g.approved_by_employee_id
						INNER JOIN ".$peoples." as p ON p.person_id=e.person_id
						WHERE g.deleted =0 AND g.cash_time='".date('Y-m-d')."' 
						AND g.location_id='".$location_id."' 
						ORDER BY ".$col." ". $order." 
						LIMIT  ".$offset.",".$limit);
							
		return $data;
	}

	/*
	Preform a search on expenses
	*/
	
	function search($search, $limit=20,$offset=0,$column='expense_id',$orderby='desc')
	{
		$expenses=$this->db->dbprefix('expenses');
		$this->db->select('expenses.*,first_name,last_name,name');
		$this->db->from('expenses');
		$this->db->join('locations','locations.location_id=expenses.location_id');		
		$this->db->join('employees','expenses.approved_by_employee_id=employees.id');		
		$this->db->join('people','employees.person_id=people.person_id');		
		$this->db->where("(first_name LIKE '%".$this->db->escape_like_str($search)."%' or 
		last_name LIKE '%".$this->db->escape_like_str($search)."%' or 
		phone_number LIKE '%".$this->db->escape_like_str($search)."%' or 
		CONCAT(`first_name`,' ',`last_name`) LIKE '%".$this->db->escape_like_str($search)."%' or 
		CONCAT(`last_name`,', ',`first_name`) LIKE '%".$this->db->escape_like_str($search)."%') and ".$expenses.".deleted=0");		
		$this->db->order_by($column,$orderby);
		$this->db->limit($limit);
		$this->db->offset($offset);
		return $this->db->get();
	}

	function search_count_all($search, $limit=10000)
	{
			$expenses=$this->db->dbprefix('expenses');
			$this->db->from('expenses');
			$this->db->join('locations','locations.location_id=expenses.location_id');		
			$this->db->join('employees','expenses.approved_by_employee_id=employees.id');		
			$this->db->join('people','employees.person_id=people.person_id');		
			$this->db->where("(first_name LIKE '%".$this->db->escape_like_str($search)."%' or 
			last_name LIKE '%".$this->db->escape_like_str($search)."%' or 
			phone_number LIKE '%".$this->db->escape_like_str($search)."%' or 
			CONCAT(`first_name`,' ',`last_name`) LIKE '%".$this->db->escape_like_str($search)."%' or 
			CONCAT(`last_name`,', ',`first_name`) LIKE '%".$this->db->escape_like_str($search)."%') and ".$expenses.".deleted=0");		
			$this->db->limit($limit);
			$result=$this->db->get();				
			return $result->num_rows();
	}

	function search_total($search,$limit=10000, $offset=0,$col='g.expense_id',$order='desc')
	{
		$expenses=$this->db->dbprefix('expenses');
		$locations=$this->db->dbprefix('locations');
		$employees=$this->db->dbprefix('employees');
		$peoples=$this->db->dbprefix('people');
		
		$data=$this->db->query("SELECT IFNULL(SUM(quantity),0) as total 
						FROM ".$expenses." as g
						INNER JOIN ".$locations." as l ON l.location_id=g.location_id
						INNER JOIN ".$employees." as e ON e.id=g.approved_by_employee_id
						INNER JOIN ".$peoples." as p ON p.person_id=e.person_id
						WHERE (first_name LIKE '%".$this->db->escape_like_str($search)."%' or 
			            last_name LIKE '%".$this->db->escape_like_str($search)."%' or 
			phone_number LIKE '%".$this->db->escape_like_str($search)."%' or 
			CONCAT(`first_name`,' ',`last_name`) LIKE '%".$this->db->escape_like_str($search)."%' or 
			CONCAT(`last_name`,', ',`first_name`) LIKE '%".$this->db->escape_like_str($search)."%') and g.deleted=0 
		               ORDER BY ".$col." ". $order." 
						LIMIT  ".$offset.",".$limit);		
		
		if ($data->num_rows()==1)
		{
			foreach($data->result() as $row)
			{
				return  $row->total;
			}
		}				
		return 0;
	}

	function get_all_total($limit=10000, $offset=0,$col='g.expense_id',$order='desc')
	{
		$expenses=$this->db->dbprefix('expenses');
		$cash=$this->db->dbprefix('cash_withdrawal');
		$locations=$this->db->dbprefix('locations');
		$employees=$this->db->dbprefix('employees');
		$category=$this->db->dbprefix('category');
		$peoples=$this->db->dbprefix('people');
		$location_id=$this->Employee->get_logged_in_employee_current_location_id();
		
		$data=$this->db->query("SELECT IFNULL(SUM(t.quantity),0) as total FROM ( 
						SELECT g.quantity,g.expense_time
						FROM ".$expenses." as g
						INNER JOIN ".$locations." as l ON l.location_id=g.location_id
						INNER JOIN ".$employees." as e ON e.id=g.approved_by_employee_id
						INNER JOIN ".$peoples." as p ON p.person_id=e.person_id
						INNER JOIN ".$category." as c ON c.category_id=g.category_id
						WHERE g.deleted =0 AND g.expense_time='".date('Y-m-d')."'
						AND g.location_id=".$location_id."
						) as t");		
		
		$total=0;
		if ($data->num_rows()==1)
		{
			foreach($data->result() as $row)
			{
				$total+=$row->total;
			}
		}	

		$data=$this->db->query("SELECT IFNULL(SUM(t.quantity),0) as total FROM ( 
						SELECT g.quantity,g.cash_time as expense_time
						FROM ".$cash." as g
						INNER JOIN ".$locations." as l ON l.location_id=g.location_id
						INNER JOIN ".$employees." as e ON e.id=g.approved_by_employee_id
						INNER JOIN ".$peoples." as p ON p.person_id=e.person_id
						WHERE g.deleted =0 AND g.cash_time='".date('Y-m-d')."'
		   			    AND g.location_id=".$location_id."
						) as t");		
		
		if ($data->num_rows()==1)
		{
			foreach($data->result() as $row)
			{
				$total+=$row->total;
			}
		}	

		return $total;
	}

	function count_all()
	{
		$expenses=$this->db->dbprefix('expenses');
		$cash=$this->db->dbprefix('cash_withdrawal');
		$locations=$this->db->dbprefix('locations');
		$employees=$this->db->dbprefix('employees');
		$category=$this->db->dbprefix('category');
		$peoples=$this->db->dbprefix('people');
		$location_id=$this->Employee->get_logged_in_employee_current_location_id();
		
		$data=$this->db->query("SELECT g.expense_id,g.expense_time,g.nro_receipt,g.description,g.quantity,CONCAT(p.first_name,' ',p.last_name) as approved,l.name as location,c.name as category
						FROM ".$expenses." as g
						INNER JOIN ".$locations." as l ON l.location_id=g.location_id
						INNER JOIN ".$employees." as e ON e.id=g.approved_by_employee_id
						INNER JOIN ".$peoples." as p ON p.person_id=e.person_id
						INNER JOIN ".$category." as c ON c.category_id=g.category_id
						WHERE g.deleted =0 AND g.expense_time='".date('Y-m-d')."' 
						AND g.location_id=".$location_id."
						UNION
						SELECT g.cash_id as expense_id,g.cash_time as expense_time,'' as nro_receipt,g.description,g.quantity,CONCAT(p.first_name,' ',p.last_name) as approved,l.name as location,'' as category
						FROM ".$cash." as g
						INNER JOIN ".$locations." as l ON l.location_id=g.location_id
						INNER JOIN ".$employees." as e ON e.id=g.approved_by_employee_id
						INNER JOIN ".$peoples." as p ON p.person_id=e.person_id
						WHERE g.deleted =0 AND g.cash_time='".date('Y-m-d')."' 
						AND g.location_id=".$location_id."
						"
						);
		
		return $data->num_rows();
	}

	/*
	Gets information about a particular expense
	*/
	function get_info($expense_id)
	{
		$this->db->from('expenses');	
		$this->db->where('expenses.expense_id',$expense_id);
		$query = $this->db->get();
		
		if($query->num_rows()==1)
		{
			return $query->row();
		}
		else
		{
			//Get empty base parent object, as $expense_id is NOT an expense
			$expense_obj=$this;
			
			//Get all the fields from customer table
			$fields = $this->db->list_fields('expenses');
			
			//append those fields to base parent object, we we have a complete empty object
			foreach ($fields as $field)
			{
				$expense_obj->$field='';
			}
			
			return $expense_obj;
		}
	}

	/*
	Inserts or updates a expenses
	*/
	function save(&$expense_data,$expense_id=false)
	{
		if (!$expense_id or !$this->exists($expense_id))
		{
			if($this->db->insert('expenses',$expense_data))
			{
				$expense_data['expense_id']=$this->db->insert_id();
				return true;
			}
			return false;
		}
		$expense_data['expense_id']=$expense_id;
		$this->db->where('expense_id', $expense_id);
		return $this->db->update('expenses',$expense_data);
	}

	/*
	Determines if a given expense_id is an expense
	*/
	function exists( $expense_id )
	{
		$this->db->from('expenses');
		$this->db->where('expense_id',$expense_id);
		$this->db->where('deleted',0);
		$query = $this->db->get();

		return ($query->num_rows()==1);
	}

	/*
	Get search suggestions to find expenses
	*/
	function get_search_suggestions($search,$limit=25)
	{
		$suggestions = array();
		
		$expenses=$this->db->dbprefix('expenses');
		$this->db->from('expenses');
		$this->db->join('locations','expenses.location_id=locations.location_id');		
		$this->db->join('employees','expenses.approved_by_employee_id=employees.id');		
		$this->db->join('people','employees.person_id=people.person_id');
		
		$this->db->where("(first_name LIKE '%".$this->db->escape_like_str($search)."%' or 
		last_name LIKE '%".$this->db->escape_like_str($search)."%' or 
		CONCAT(`first_name`,' ',`last_name`) LIKE '%".$this->db->escape_like_str($search)."%' or 
		CONCAT(`last_name`,', ',`first_name`) LIKE '%".$this->db->escape_like_str($search)."%') and ".$expenses.".deleted=0");
		
		$this->db->limit($limit);	
		$by_name = $this->db->get();
		
		$temp_suggestions = array();
		foreach($by_name->result() as $row)
		{
			$temp_suggestions[] = $row->last_name.', '.$row->first_name;
		}
		
		sort($temp_suggestions);
		foreach($temp_suggestions as $temp_suggestion)
		{
			$suggestions[]=array('label'=> $temp_suggestion);		
		}
		
		
		$this->db->from('expenses');
		$this->db->join('locations','expenses.location_id=locations.location_id');		
		$this->db->join('employees','expenses.approved_by_employee_id=employees.id');		
		$this->db->join('people','employees.person_id=people.person_id');	
		$this->db->where($expenses.'.deleted',0);		
		$this->db->like("phone_number",$search);
		$this->db->limit($limit);	
		$by_phone = $this->db->get();
		
		$temp_suggestions = array();
		foreach($by_phone->result() as $row)
		{
			$temp_suggestions[]=$row->phone_number;		
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

	/*
	Deletes a list of expenses
	*/
	function delete_list($expenses)
	{
		$expense_ids=array();
		$cash_ids=array();
		foreach($expenses as $value)
		{
			if ($value['type']=="expense")
			{
				$expense_ids[]=$value['id'];
			}
			else
			{
				$cash_ids[]=$value['id'];
			}
		}
		$res=true;
		if (count($expense_ids)>0)
		{
		$this->db->where_in('expense_id',$expense_ids);
		$res=$this->db->update('expenses', array('deleted' => 1));
 		}
 		$res2=true;
		if (count($cash_ids)>0)
		{
		$this->db->where_in('cash_id',$cash_ids);
		$res2=$this->db->update('cash_withdrawal', array('deleted' => 1));
 		}
 		return ($res && $res2);	
 	}
	
	
}
?>