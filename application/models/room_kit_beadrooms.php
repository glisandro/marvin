<?php
class Item_kit_beadrooms extends CI_Model
{
	/*
	Gets room kit beadrooms for a particular room kit
	*/
	function get_info($room_kit_id)
	{
		$this->db->from('room_kit_beadrooms');
		$this->db->where('room_kit_id',$room_kit_id);
		//return an array of room kit beadrooms for an room
		return $this->db->get()->result();
	}

	/*
	Inserts or updates an room kit's beadrooms
	*/
	function save(&$room_kit_beadrooms_data, $room_kit_id)
	{
		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();

		$this->delete($room_kit_id);

		foreach ($room_kit_beadrooms_data as $row)
		{
			$row['room_kit_id'] = $room_kit_id;
			$this->db->insert('room_kit_beadrooms',$row);
		}

		$this->db->trans_complete();
		return true;
	}

	/*
	Deletes room kit beadrooms given an room kit
	*/
	function delete($room_kit_id)
	{
		return $this->db->delete('room_kit_beadrooms', array('room_kit_id' => $room_kit_id));
	}

	/**
	 * Get kits with room
	 * @param type $ite_id
	 * @return type
	 */
	function get_kits_have_room($room_id)
	{
	    $this->db->from('room_kit_beadrooms');
	    $this->db->where('room_id',$room_id);
	    return $this->db->get()->result_array();
	}
}
?>
