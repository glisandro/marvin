<?php
class Room_taxes_finder extends CI_Model
{
	/*
	Gets tax info for a particular room
	*/
	function get_info($room_id)
	{
		$room_location_info = $this->Room_location->get_info($room_id);
		if($room_location_info->override_default_tax)
		{
			return $this->Room_location_taxes->get_info($room_id);
		}
		
		$room_info = $this->Room->get_info($room_id);

		if($room_info->override_default_tax)
		{
			return $this->Room_taxes->get_info($room_id);
		}

		$return = array();
		
		//Location Config
		$default_tax_1_rate = $this->Location->get_info_for_key('default_tax_1_rate');
		$default_tax_1_name = $this->Location->get_info_for_key('default_tax_1_name');
				
		$default_tax_2_rate = $this->Location->get_info_for_key('default_tax_2_rate');
		$default_tax_2_name = $this->Location->get_info_for_key('default_tax_2_name');
		$default_tax_2_cumulative = $this->Location->get_info_for_key('default_tax_2_cumulative') ? $this->Location->get_info_for_key('default_tax_2_cumulative') : 0;
		
		$default_tax_3_rate = $this->Location->get_info_for_key('default_tax_3_rate');
		$default_tax_3_name = $this->Location->get_info_for_key('default_tax_3_name');
		
		$default_tax_4_rate = $this->Location->get_info_for_key('default_tax_4_rate');
		$default_tax_4_name = $this->Location->get_info_for_key('default_tax_4_name');
		
		$default_tax_5_rate = $this->Location->get_info_for_key('default_tax_5_rate');
		$default_tax_5_name = $this->Location->get_info_for_key('default_tax_5_name');
		
		if ($default_tax_1_rate)
		{
			$return[] = array(
				'id' => -1,
				'room_id' => $room_id,
				'name' => $default_tax_1_name,
				'percent' => $default_tax_1_rate,
				'cumulative' => 0
			);
		}
		
		if ($default_tax_2_rate)
		{
			$return[] = array(
				'id' => -1,
				'room_id' => $room_id,
				'name' => $default_tax_2_name,
				'percent' => $default_tax_2_rate,
				'cumulative' => $default_tax_2_cumulative
			);
		}

		if ($default_tax_3_rate)
		{
			$return[] = array(
				'id' => -1,
				'room_id' => $room_id,
				'name' => $default_tax_3_name,
				'percent' => $default_tax_3_rate,
				'cumulative' => 0
			);
		}


		if ($default_tax_4_rate)
		{
			$return[] = array(
				'id' => -1,
				'room_id' => $room_id,
				'name' => $default_tax_4_name,
				'percent' => $default_tax_4_rate,
				'cumulative' => 0
			);
		}


		if ($default_tax_5_rate)
		{
			$return[] = array(
				'id' => -1,
				'room_id' => $room_id,
				'name' => $default_tax_5_name,
				'percent' => $default_tax_5_rate,
				'cumulative' => 0
			);
		}
		
		if (!empty($return))
		{
			return $return;
		}
		
		//Global Store Config
		$default_tax_1_rate = $this->config->item('default_tax_1_rate');
		$default_tax_1_name = $this->config->item('default_tax_1_name');
				
		$default_tax_2_rate = $this->config->item('default_tax_2_rate');
		$default_tax_2_name = $this->config->item('default_tax_2_name');
		$default_tax_2_cumulative = $this->config->item('default_tax_2_cumulative') ? $this->config->item('default_tax_2_cumulative') : 0;
		
		$default_tax_3_rate = $this->config->item('default_tax_3_rate');
		$default_tax_3_name = $this->config->item('default_tax_3_name');
		
		$default_tax_4_rate = $this->config->item('default_tax_4_rate');
		$default_tax_4_name = $this->config->item('default_tax_4_name');
		
		$default_tax_5_rate = $this->config->item('default_tax_5_rate');
		$default_tax_5_name = $this->config->item('default_tax_5_name');
		
		$return = array();
		
		if ($default_tax_1_rate)
		{
			$return[] = array(
				'id' => -1,
				'room_id' => $room_id,
				'name' => $default_tax_1_name,
				'percent' => $default_tax_1_rate,
				'cumulative' => 0
			);
		}
		
		if ($default_tax_2_rate)
		{
			$return[] = array(
				'id' => -1,
				'room_id' => $room_id,
				'name' => $default_tax_2_name,
				'percent' => $default_tax_2_rate,
				'cumulative' => $default_tax_2_cumulative
			);
		}

		if ($default_tax_3_rate)
		{
			$return[] = array(
				'id' => -1,
				'room_id' => $room_id,
				'name' => $default_tax_3_name,
				'percent' => $default_tax_3_rate,
				'cumulative' => 0
			);
		}

		if ($default_tax_4_rate)
		{
			$return[] = array(
				'id' => -1,
				'room_id' => $room_id,
				'name' => $default_tax_4_name,
				'percent' => $default_tax_4_rate,
				'cumulative' => 0
			);
		}

		if ($default_tax_5_rate)
		{
			$return[] = array(
				'id' => -1,
				'room_id' => $room_id,
				'name' => $default_tax_5_name,
				'percent' => $default_tax_5_rate,
				'cumulative' => 0
			);
		}
		
				
		return $return;
	}
}
?>
