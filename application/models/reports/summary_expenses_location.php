<?php
require_once("report.php");
class Summary_expenses_location extends Report
{
	function __construct()
	{
		parent::__construct();
	}

	public function getDataColumns()
	{
		$columns = array();
		
		$columns[] = array('data'=>lang('reports_total'), 'align'=> 'right');
		
		
		return $columns;		
	}
	
	public function getData()
	{	
		$expenses=$this->db->dbprefix('expenses');
		$cash=$this->db->dbprefix('cash_withdrawal');
		$locations=$this->db->dbprefix('locations');
		$category=$this->db->dbprefix('category');
		$people=$this->db->dbprefix('people');
			
		$this->db->select($expenses.'.location_id,'.$locations.'.name as location_name,sum('.$expenses.'.quantity) as total', false);
		$this->db->from($expenses);
		$this->db->join($locations, $expenses.'.location_id = '.$locations.'.location_id');
		$this->db->join($category, $expenses.'.category_id = '.$category.'.category_id');
		$this->db->join($people, $expenses.'.approved_by_employee_id = '.$people.'.person_id');
		
		if ($this->params['location_id']!="-1")
			$this->db->where($expenses.'.location_id = "'.$this->params['location_id'].'"');

		if (isset($this->params['start_date']) && isset($this->params['end_date']))
		{
			$this->db->where($expenses.'.expense_time BETWEEN '.$this->db->escape($this->params['start_date']).' and '.$this->db->escape($this->params['end_date']).' ');
		}
		$this->db->where($expenses.'.deleted', 0);
		//$this->db->where($expenses.'.location_id', $this->Employee->get_logged_in_employee_current_location_id());
		$this->db->group_by($expenses.'.location_id');
		$this->db->order_by($expenses.'.expense_id');
	
		//If we are exporting NOT exporting to excel make sure to use offset and limit
		if (isset($this->params['export_excel']) && !$this->params['export_excel'])
		{
			$this->db->limit($this->report_limit);
			$this->db->offset($this->params['offset']);
		}
		
		return $this->db->get()->result_array();
	}
	
	public function getDataCash()
	{	
		$expenses=$this->db->dbprefix('expenses');
		$cash=$this->db->dbprefix('cash_withdrawal');
		$locations=$this->db->dbprefix('locations');
		$category=$this->db->dbprefix('category');
		$people=$this->db->dbprefix('people');
			
		$this->db->select($cash.'.location_id,'.$locations.'.name as location_name,sum('.$cash.'.quantity) as total', false);
		$this->db->from($cash);
		$this->db->join($locations, $cash.'.location_id = '.$locations.'.location_id');
		$this->db->join($people, $cash.'.approved_by_employee_id = '.$people.'.person_id');
		
		if ($this->params['location_id']!="-1")
			$this->db->where($expenses.'.location_id = "'.$this->params['location_id'].'"');

		if (isset($this->params['start_date']) && isset($this->params['end_date']))
		{
			$this->db->where($cash.'.cash_time BETWEEN '.$this->db->escape($this->params['start_date']).' and '.$this->db->escape($this->params['end_date']).' ');
		}
		$this->db->where($cash.'.deleted', 0);
		//$this->db->where($cash.'.location_id', $this->Employee->get_logged_in_employee_current_location_id());
		$this->db->group_by('location_id');
		$this->db->order_by('cash_id');
	
		//If we are exporting NOT exporting to excel make sure to use offset and limit
		if (isset($this->params['export_excel']) && !$this->params['export_excel'])
		{
			$this->db->limit($this->report_limit);
			$this->db->offset($this->params['offset']);
		}
		
		return $this->db->get()->result_array();
	}
		
	
	public function getTotalRows()
	{
		$expenses=$this->db->dbprefix('expenses');
		$cash=$this->db->dbprefix('cash_withdrawal');
		$locations=$this->db->dbprefix('locations');
		$category=$this->db->dbprefix('category');
		$people=$this->db->dbprefix('people');
		
		$this->db->select("COUNT(DISTINCT(expense_id)) as expense_count");
		$this->db->from($expenses);
		$this->db->join($locations, $expenses.'.location_id = '.$locations.'.location_id');
		$this->db->join($category, $expenses.'.category_id = '.$category.'.category_id');
		$this->db->join($people, $expenses.'.approved_by_employee_id = '.$people.'.person_id');
		
		if ($this->params['location_id']!="-1")
			$this->db->where($expenses.'.location_id = "'.$this->params['location_id'].'"');
		
		if (isset($this->params['start_date']) && isset($this->params['end_date']))
		{
			$this->db->where($expenses.'.expense_time BETWEEN '.$this->db->escape($this->params['start_date']).' and '.$this->db->escape($this->params['end_date']).' ');
		}
		
		$this->db->where($expenses.'.deleted', 0);
		//$this->db->where($expenses.'.location_id', $this->Employee->get_logged_in_employee_current_location_id());
			
		
		
		$ret = $this->db->get()->row_array();
		
		$this->db->select("COUNT(DISTINCT(cash_id)) as expense_count");
		$this->db->from($cash);
		$this->db->join($locations, $cash.'.location_id = '.$locations.'.location_id');
		$this->db->join($people, $cash.'.approved_by_employee_id = '.$people.'.person_id');
		
		if ($this->params['location_id']!="-1")
			$this->db->where($cash.'.location_id = "'.$this->params['location_id'].'"');
		
		if (isset($this->params['start_date']) && isset($this->params['end_date']))
		{
			$this->db->where($cash.'.cash_time BETWEEN '.$this->db->escape($this->params['start_date']).' and '.$this->db->escape($this->params['end_date']).' ');
		}
		
		$this->db->where($cash.'.deleted', 0);
		//$this->db->where($cash.'.location_id', $this->Employee->get_logged_in_employee_current_location_id());
			
	
		
		$ret_cash = $this->db->get()->row_array();

		$ret['expense_count']=$ret['expense_count']+$ret_cash['expense_count'];
		return $ret['expense_count'];

	}
	public function getSummaryData()
	{
		$expenses=$this->db->dbprefix('expenses');
		$cash=$this->db->dbprefix('cash_withdrawal');
		$locations=$this->db->dbprefix('locations');
		$category=$this->db->dbprefix('category');
		$people=$this->db->dbprefix('people');
		
		$this->db->select('sum('.$expenses.'.quantity) as total', false);
		$this->db->from($expenses);
		$this->db->join($locations, $expenses.'.location_id = '.$locations.'.location_id');
		$this->db->join($category, $expenses.'.category_id = '.$category.'.category_id');
		$this->db->join($people, $expenses.'.approved_by_employee_id = '.$people.'.person_id');
		
		if ($this->params['location_id']!="-1")
			$this->db->where($expenses.'.location_id = "'.$this->params['location_id'].'"');
		
		if (isset($this->params['start_date']) && isset($this->params['end_date']))
		{
			$this->db->where($expenses.'.expense_time BETWEEN '.$this->db->escape($this->params['start_date']).' and '.$this->db->escape($this->params['end_date']).' ');
		}
		
		$this->db->where($expenses.'.deleted', 0);
		//$this->db->where($expenses.'.location_id', $this->Employee->get_logged_in_employee_current_location_id());
			
		
		$return = array(
			'total' => 0,
		);
		
		foreach($this->db->get()->result_array() as $row)
		{
			$return['total'] += to_currency_no_money($row['total'],2);
		}

		$this->db->select('sum('.$cash.'.quantity) as total', false);
		$this->db->from($cash);
		$this->db->join($locations, $cash.'.location_id = '.$locations.'.location_id');
		$this->db->join($people, $cash.'.approved_by_employee_id = '.$people.'.person_id');
		
		if ($this->params['location_id']!="-1")
			$this->db->where($cash.'.location_id = "'.$this->params['location_id'].'"');
		
		if (isset($this->params['start_date']) && isset($this->params['end_date']))
		{
			$this->db->where($cash.'.cash_time BETWEEN '.$this->db->escape($this->params['start_date']).' and '.$this->db->escape($this->params['end_date']).' ');
		}
		
		$this->db->where($cash.'.deleted', 0);
		//$this->db->where($cash.'.location_id', $this->Employee->get_logged_in_employee_current_location_id());
			
		foreach($this->db->get()->result_array() as $row)
		{
			$return['total'] += to_currency_no_money($row['total'],2);
		}
		
		return $return;
	}

}
?>