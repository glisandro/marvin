<?php
class Room_location extends CI_Model
{

	function exists($room_id,$location=false)
	{
		if(!$location)
		{
			$location= $this->Employee->get_logged_in_employee_current_location_id();
		}
		$this->db->from('location_bedrooms');
		$this->db->where('room_id',$room_id);
		$this->db->where('location_id',$location);
		$query = $this->db->get();

		return ($query->num_rows()==1);
	}
	
	
	function save($room_location_data,$room_id=-1,$location_id=false)
	{
		if(!$location_id)
		{
			$location_id= $this->Employee->get_logged_in_employee_current_location_id();
		}
		
		if (!$this->exists($room_id,$location_id))
		{
			$room_location_data['room_id'] = $room_id;
			$room_location_data['location_id'] = $location_id;
			return $this->db->insert('location_bedrooms',$room_location_data);
		}

		$this->db->where('room_id',$room_id);
		$this->db->where('location_id',$location_id);
		return $this->db->update('location_bedrooms',$room_location_data);
		
	}
	
	function save_quantity($quantity, $room_id, $location_id=false)
	{
		if(!$location_id)
		{
			$location_id= $this->Employee->get_logged_in_employee_current_location_id();
		}
		
		$sql = 'INSERT INTO '.$this->db->dbprefix('location_bedrooms'). ' (quantity, room_id, location_id)'
		    . ' VALUES (?, ?, ?)'
		    . ' ON DUPLICATE KEY UPDATE quantity = ?'; 
		
		return $this->db->query($sql, array($quantity, $room_id, $location_id,$quantity));		
	}
	
	/*
	Updates multiple item locations at once
	*/
	function update_multiple($room_location_data, $room_ids,$select_inventory=0, $location_id = false)
	{
		if(!$location_id)
		{
			$location_id= $this->Employee->get_logged_in_employee_current_location_id();
		}

		if(!$select_inventory)
		{
			$this->db->where_in('room_id',$room_ids);
		}

		
		$this->db->where('location_id', $location_id);
		return $this->db->update('location_bedrooms',$room_location_data);
	}
	
	
	function get_info($room_id,$location=false)
	{
		if(!$location)
		{
			$location= $this->Employee->get_logged_in_employee_current_location_id();
		}
		
		$this->db->from('location_bedrooms');
		$this->db->where('room_id',$room_id);
		$this->db->where('location_id',$location);
		$query = $this->db->get();

		if($query->num_rows()==1)
		{
			$row = $query->row();
			
			//Store a boolean indicating if the price has been overwritten
			$row->is_overwritten = ($row->cost_price !== NULL ||
			$row->unit_price !== NULL ||
			$row->promo_price !== NULL || 
			$this->is_tier_overwritten($room_id, $location));
			return $row;
		
		}
		else
		{
			//Get empty base parent object, as $room_id is NOT an item_location
			$item_location_obj=new stdClass();

			//Get all the fields from item_locations table
			$fields = $this->db->list_fields('location_bedrooms');

			foreach ($fields as $field)
			{
				$item_location_obj->$field='';
			}
			
			$item_location_obj->is_overwritten = FALSE;

			return $item_location_obj;
		}

	}
	
	function get_location_quantity($room_id,$location=false)
	{
		if(!$location)
		{
			$location= $this->Employee->get_logged_in_employee_current_location_id();
		}
		
		$this->db->from('location_bedrooms');
		$this->db->where('room_id',$room_id);
		$this->db->where('location_id',$location);
		$query = $this->db->get();

		if($query->num_rows()==1)
		{
			$row=$query->row();
			return $row->quantity;
		}

		return NULL;
	}
	
	function get_tier_price_row($tier_id,$room_id, $location_id)
	{
		$this->db->from('location_bedrooms_tier_prices');
		$this->db->where('tier_id',$tier_id);
		$this->db->where('room_id ',$room_id);
		$this->db->where('location_id ',$location_id);
		return $this->db->get()->row();
	}
		
	function delete_tier_price($tier_id, $room_id, $location_id)
	{
		$this->db->where('tier_id', $tier_id);
		$this->db->where('room_id', $room_id);
		$this->db->where('location_id', $location_id);
		$this->db->delete('location_bedrooms_tier_prices');
	}
	
	function tier_exists($tier_id, $room_id, $location_id)
	{
		$this->db->from('location_bedrooms_tier_prices');
		$this->db->where('tier_id',$tier_id);
		$this->db->where('room_id',$room_id);
		$this->db->where('location_id',$location_id);
		$query = $this->db->get();

		return ($query->num_rows()>=1);
		
	}
	
	function save_item_tiers($tier_data,$room_id, $location_id)
	{	
		if($this->tier_exists($tier_data['tier_id'],$room_id,$location_id))
		{
			$this->db->where('tier_id', $tier_data['tier_id']);
			$this->db->where('room_id', $room_id);
			$this->db->where('location_id', $location_id);

			return $this->db->update('location_bedrooms_tier_prices',$tier_data);
			
		}

		return $this->db->insert('location_bedrooms_tier_prices',$tier_data);	
	}

	function is_tier_overwritten($room_id, $location_id)
	{
		$this->db->from('location_bedrooms_tier_prices');
		$this->db->where('room_id',$room_id);
		$this->db->where('location_id',$location_id);
		$query = $this->db->get();

		return ($query->num_rows()>=1);
	}
}
?>
