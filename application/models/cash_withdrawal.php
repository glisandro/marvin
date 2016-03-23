<?php
class Cash_withdrawal extends CI_Model
{

	function get_info($cash_id)
	{
		$this->db->from('cash_withdrawal');	
		$this->db->where('cash_withdrawal.cash_id',$cash_id);
		$query = $this->db->get();
		
		if($query->num_rows()==1)
		{
			return $query->row();
		}
		else
		{
			//Get empty base parent object, as $category_id is NOT an expense
			$cash_obj=$this;
			
			//Get all the fields from customer table
			$fields = $this->db->list_fields('cash_withdrawal');
			
			//append those fields to base parent object, we we have a complete empty object
			foreach ($fields as $field)
			{
				$cash_obj->$field='';
			}
			
			return $cash_obj;
		}
	}

	function save(&$cash_data,$cash_id=false)
	{
		if (!$cash_id or !$this->exists($cash_id))
		{
			if($this->db->insert('cash_withdrawal',$cash_data))
			{
				$cash_data['cash_id']=$this->db->insert_id();
				return true;
			}
			return false;
		}

		$this->db->where('cash_id', $cash_id);
		return $this->db->update('cash_withdrawal',$cash_data);
	}

	function exists( $cash_id )
	{
		$this->db->from('cash_withdrawal');
		$this->db->where('cash_id',$cash_id);
		$this->db->where('deleted',0);
		$query = $this->db->get();

		return ($query->num_rows()==1);
	}
	
	
}
?>