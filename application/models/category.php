<?php
class Category extends CI_Model
{
	function get_all($limit=10000, $offset=0,$col='name',$order='asc')
	{

		$categorys=$this->db->dbprefix('category');
		$data=$this->db->query("SELECT *
						FROM ".$categorys." 
						WHERE deleted =0  ORDER BY ".$col." ". $order." 
						LIMIT  ".$offset.",".$limit);		
						
		return $data;
	}

	function count_all()
	{
		$this->db->from('category');
		$this->db->where('deleted',0);
		return $this->db->count_all_results();
	}

	function get_info($category_id)
	{
		$this->db->from('category');	
		$this->db->where('category.category_id',$category_id);
		$query = $this->db->get();
		
		if($query->num_rows()==1)
		{
			return $query->row();
		}
		else
		{
			//Get empty base parent object, as $category_id is NOT an expense
			$expense_obj=$this;
			
			//Get all the fields from customer table
			$fields = $this->db->list_fields('category');
			
			//append those fields to base parent object, we we have a complete empty object
			foreach ($fields as $field)
			{
				$expense_obj->$field='';
			}
			
			return $expense_obj;
		}
	}

	function save(&$category_data,$category_id=false)
	{
		if (!$category_id or !$this->exists($category_id))
		{
			if($this->db->insert('category',$category_data))
			{
				$category_data['category_id']=$this->db->insert_id();
				return true;
			}
			return false;
		}

		$this->db->where('category_id', $category_id);
		return $this->db->update('category',$category_data);
	}

	function exists( $category_id )
	{
		$this->db->from('category');
		$this->db->where('category_id',$category_id);
		$this->db->where('deleted',0);
		$query = $this->db->get();

		return ($query->num_rows()==1);
	}
	
	function delete_list($category_ids)
	{
		
		$this->db->where_in('category_id',$category_ids);
		return $this->db->update('category', array('deleted' => 1));
 	}

 	function search($search, $limit=20,$offset=0,$column='name',$orderby='asc')
	{
		$this->db->from('category');
		$this->db->where("(name LIKE '%".$this->db->escape_like_str($search)."%') and deleted=0");		
		$this->db->order_by($column,$orderby);
		$this->db->limit($limit);
		$this->db->offset($offset);
		return $this->db->get();
	}

	function search_count_all($search, $limit=10000)
	{
			$this->db->from('category');
			$this->db->where("(name LIKE '%".$this->db->escape_like_str($search)."%') and deleted=0");		
			$this->db->limit($limit);
			$result=$this->db->get();				
			return $result->num_rows();
	}

	function get_search_suggestions($search,$limit=25)
	{
		$suggestions = array();
		
		$this->db->from('category');
		
		$this->db->where("(name LIKE '%".$this->db->escape_like_str($search)."%') and deleted=0");
		
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
		
		
		
		//only return $limit suggestions
		if(count($suggestions > $limit))
		{
			$suggestions = array_slice($suggestions, 0,$limit);
		}
		return $suggestions;
	
	}
	function get_category_search_suggestions($search,$limit=25)
	{
		$suggestions = array();
		
		$this->db->from('category');
		$this->db->where('deleted', 0);
		$this->db->like("name",$search);
		$this->db->limit($limit);	
		$by_company_name = $this->db->get();
		
		$temp_suggestions = array();
		
		foreach($by_company_name->result() as $row)
		{
			$temp_suggestions[$row->category_id] = $row->name;
		}
		
		asort($temp_suggestions);
		
		foreach($temp_suggestions as $key => $value)
		{
			$suggestions[]=array('value'=> $key, 'label' => $value);		
		}

		$suggestions = array_values($suggestions);
		
		//only return $limit suggestions
		if(count($suggestions > $limit))
		{
			$suggestions = array_slice($suggestions, 0,$limit);
		}
		return $suggestions;

	}
}
?>