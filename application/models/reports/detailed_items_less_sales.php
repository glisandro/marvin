<?php
require_once("report.php");
class Detailed_items_less_sales extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{		
		$columns = array();
		
		$columns[] = array('data'=>lang('reports_item_name'), 'align'=> 'left');
		// $columns[] = array('data'=>lang('reports_item_number'), 'align'=> 'left');
		// $columns[] = array('data'=>lang('items_product_id'), 'align'=> 'left');
		// $columns[] = array('data'=>lang('reports_category'), 'align'=> 'left');
		$columns[] = array('data'=>lang('reports_quantity_purchased_filter'), 'align'=> 'left');
		// $columns[] = array('data'=>lang('reports_subtotal'), 'align'=> 'right');
		// $columns[] = array('data'=>lang('reports_total'), 'align'=> 'right');
		// $columns[] = array('data'=>lang('reports_tax'), 'align'=> 'right');

		if($this->Employee->has_module_action_permission('reports','show_profit',$this->Employee->get_logged_in_employee_info()->person_id))
		{
		//	$columns[] = array('data'=>lang('reports_profit'), 'align'=> 'right');
		}
		
		return $columns;		
	}
	
	
	public function getData()
	{
		$logged_in_location_id = $this->Employee->get_logged_in_employee_current_location_id();
		
		$this->db->select('items.name, items.item_number, items.product_id, items.category , IFNULL(sum(quantity_purchased),0) as quantity_purchased, sum(subtotal) as subtotal, sum(total) as total, sum(tax) as tax, sum(profit) as profit', false);
		$this->db->from('items');
		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->join('sales_items_temp', 'sales_items_temp.item_id = items.item_id AND '.$this->db->dbprefix('sales_items_temp').'.deleted=0 AND '.$this->db->dbprefix('sales_items_temp').'.quantity_purchased>0','left');
		//	$this->db->where('quantity_purchased > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->join('sales_items_temp', 'sales_items_temp.item_id = items.item_id AND '.$this->db->dbprefix('sales_items_temp').'.deleted=0 AND '.$this->db->dbprefix('sales_items_temp').'.quantity_purchased<0','left');
		//	$this->db->where('quantity_purchased < 0');
		}
		else
		{
			$this->db->join('sales_items_temp', 'sales_items_temp.item_id = items.item_id AND '.$this->db->dbprefix('sales_items_temp').'.deleted=0 ','left');
		
		}
		$this->db->join('location_items', 'sales_items_temp.item_id = location_items.item_id AND '.$this->db->dbprefix('location_items').'.location_id='.$logged_in_location_id,'left');
		if ($this->params['item']!="-1")
			$this->db->where('items.item_id', $this->params['item']);
		
		if ($this->params['sale_type'] == 'sales')
		{
		//	$this->db->where('quantity_purchased > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
		//	$this->db->where('quantity_purchased < 0');
		}
		//$this->db->where($this->db->dbprefix('sales_items_temp').'.deleted', 0);
		//$this->db->where('location_items.location_id', $logged_in_location_id);
		$this->db->group_by('items.item_id');
		$this->db->order_by('quantity_purchased','ASC');

		//If we are exporting NOT exporting to excel make sure to use offset and limit
		if (isset($this->params['export_excel']) && !$this->params['export_excel'])
		{
			$this->db->limit($this->report_limit);
			$this->db->offset($this->params['offset']);
		}

		return $this->db->get()->result_array();		
	}
	
	function getTotalRows()
	{
		$this->db->select('COUNT(DISTINCT('.$this->db->dbprefix('items').'.item_id)) as item_count');
		$this->db->from('items');		
		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->join('sales_items_temp', 'sales_items_temp.item_id = items.item_id AND '.$this->db->dbprefix('sales_items_temp').'.deleted=0 AND '.$this->db->dbprefix('sales_items_temp').'.quantity_purchased>0','left');
		//	$this->db->where('quantity_purchased > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->join('sales_items_temp', 'sales_items_temp.item_id = items.item_id AND '.$this->db->dbprefix('sales_items_temp').'.deleted=0 AND '.$this->db->dbprefix('sales_items_temp').'.quantity_purchased<0','left');
		//	$this->db->where('quantity_purchased < 0');
		}
		else
		{
			$this->db->join('sales_items_temp', 'sales_items_temp.item_id = items.item_id AND '.$this->db->dbprefix('sales_items_temp').'.deleted=0 ','left');
		
		}
		if ($this->params['item']!="-1")
			$this->db->where('items.item_id', $this->params['item']);
		
		//$this->db->where($this->db->dbprefix('sales_items_temp').'.deleted', 0);
		
		$ret = $this->db->get()->row_array();
		return $ret['item_count'];
	}
	
	public function getSummaryData()
	{
		$this->db->select('sum(subtotal) as subtotal, sum(total) as total, sum(tax) as tax, sum(profit) as profit', false);
		$this->db->from('items');
		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->join('sales_items_temp', 'sales_items_temp.item_id = items.item_id AND '.$this->db->dbprefix('sales_items_temp').'.deleted=0 AND '.$this->db->dbprefix('sales_items_temp').'.quantity_purchased>0','left');
		//	$this->db->where('quantity_purchased > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->join('sales_items_temp', 'sales_items_temp.item_id = items.item_id AND '.$this->db->dbprefix('sales_items_temp').'.deleted=0 AND '.$this->db->dbprefix('sales_items_temp').'.quantity_purchased<0','left');
		//	$this->db->where('quantity_purchased < 0');
		}
		else
		{
			$this->db->join('sales_items_temp', 'sales_items_temp.item_id = items.item_id AND '.$this->db->dbprefix('sales_items_temp').'.deleted=0 ','left');
		
		}
		if ($this->params['item']!="-1")
			$this->db->where('items.item_id', $this->params['item']);
		
		if ($this->config->item('hide_store_account_payments_from_report_totals'))
		{
			$this->db->where('store_account_payment', 0);
		}
		
		//$this->db->group_by('sale_id');
		
		$return = array(
			'subtotal' => 0,
			'total' => 0,
			'tax' => 0,
			'profit' => 0,
		);
		
		foreach($this->db->get()->result_array() as $row)
		{
			$return['subtotal'] += to_currency_no_money($row['subtotal'],2);
			$return['total'] += to_currency_no_money($row['total'],2);
			$return['tax'] += to_currency_no_money($row['tax'],2);
			$return['profit'] += to_currency_no_money($row['profit'],2);
		}
		if(!$this->Employee->has_module_action_permission('reports','show_profit',$this->Employee->get_logged_in_employee_info()->person_id))
		{
			unset($return['profit']);
		}
		return $return;
	}
}
?>