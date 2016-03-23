<?php
class Room_taxes extends CI_Model
{
	/*
	Gets tax info for a particular item
	*/
	function get_info($room_id)
	{
		$this->db->from('bedrooms_taxes');
		$this->db->where('room_id',$room_id);
		$this->db->order_by('cumulative');
		$this->db->order_by('id');
		//return an array of taxes for an item
		return $this->db->get()->result_array();
	}
	
	/*
	Inserts or updates an item's taxes
	*/
	function save(&$bedrooms_taxes_data, $room_id)
	{
		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();
		$current_taxes = $this->get_info($room_id);
		
		//Delete and add
		if (count($current_taxes) != count($bedrooms_taxes_data))
		{
			$this->delete($room_id);
		
			foreach ($bedrooms_taxes_data as $row)
			{
				$row['room_id'] = $room_id;
				$this->db->insert('bedrooms_taxes',$row);		
			}
		}
		else //Update
		{
			for($k=0;$k<count($current_taxes);$k++)
			{
				$current_tax = $current_taxes[$k];
				$new_tax = $bedrooms_taxes_data[$k];
				
				$this->db->where('id', $current_tax['id']);
				$this->db->update('bedrooms_taxes', $new_tax);
			}
			
		}
		$this->db->trans_complete();
		return true;
	}
	
	function save_multiple(&$bedrooms_taxes_data, $room_ids,$select_inventory=false)
	{
		if($select_inventory)
		{
			$room_data=$this->Item->get_all($this->Room->count_all());
			
			foreach($room_data->result() as $room)
			{
				$this->save($bedrooms_taxes_data, $room->room_id);

			}
		}
		else
		{	
			foreach($room_ids as $room_id)
			{
				$this->save($bedrooms_taxes_data, $room_id);
			}
		}
	}
	/*
	Deletes taxes given an item
	*/
	function delete($room_id)
	{
		return $this->db->delete('bedrooms_taxes', array('room_id' => $room_id)); 
	}
}
?>
