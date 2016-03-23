<?php
class Bedrooms_inventory extends CI_Model 
{	
	function insert($inventory_data)
	{
		if(is_numeric($inventory_data['trans_inventory']))
		{
			return $this->db->insert('bedrooms_inventory',$inventory_data);
		}
		
		return TRUE;
	}
	
	

	function get_inventory_data_for_room($room_id, $location_id = false)
	{
		if (!$location_id)
		{
			$location_id=$this->Employee->get_logged_in_employee_current_location_id();
		}
		$this->db->from('bedrooms_inventory');
		$this->db->where('trans_room',$room_id);
		$this->db->where('location_id',$location_id);
		$this->db->order_by("trans_date", "desc");
		return $this->db->get();		
	}
}

?>