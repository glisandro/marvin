<?php
class Additional_room_numbers extends CI_Model
{
	/*
	Returns all the item numbers for a given item
	*/
	function get_room_numbers($room_id)
	{
		$this->db->from('additional_room_numbers');
		$this->db->where('room_id',$room_id);
		return $this->db->get();
	}
	
	function save($room_id, $additional_room_numbers)
	{
		$this->db->trans_start();

		$this->db->delete('additional_room_numbers', array('room_id' => $room_id));
		
		foreach($additional_room_numbers as $room_number)
		{
			if ($room_number!='')
			{
				$this->db->insert('additional_room_numbers', array('room_id' => $room_id, 'room_number' => $room_number));
			}
		}
		
		$this->db->trans_complete();
		
		return TRUE;
	}
	
	function delete($room_id)
	{
		return $this->db->delete('additional_room_numbers', array('room_id' => $room_id));
	}
	
	function get_room_id($room_number)
	{
		$this->db->from('additional_room_numbers');
		$this->db->where('room_number',$room_number);

		$query = $this->db->get();

		if($query->num_rows() >= 1)
		{
			return $query->row()->room_id;
		}
		
		return FALSE;
	}
}
?>
