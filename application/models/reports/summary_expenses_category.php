<?php
require_once("report.php");
class Summary_expenses_category extends Report
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
		$location_iden=$this->Employee->get_logged_in_employee_current_location_id();
		
		$this->db->select($expenses.'.category_id,'.$category.'.name as category_name,sum('.$expenses.'.quantity) as total', false);
		$this->db->from($expenses);
		$this->db->join($locations, $expenses.'.location_id = '.$locations.'.location_id');
		$this->db->join($category, $expenses.'.category_id = '.$category.'.category_id');
		$this->db->join($people, $expenses.'.approved_by_employee_id = '.$people.'.person_id');
		
		if ($this->params['category_id']!="-1")
			$this->db->where($expenses.'.category_id = "'.$this->params['category_id'].'"');

		if (isset($this->params['start_date']) && isset($this->params['end_date']))
		{
			$this->db->where($expenses.'.expense_time BETWEEN '.$this->db->escape($this->params['start_date']).' and '.$this->db->escape($this->params['end_date']).' ');
		}


		$this->db->where($expenses.'.deleted', 0);
		$this->db->where($expenses.'.location_id', $location_iden);
		$this->db->group_by($expenses.'.category_id');
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
		$location_iden=$this->Employee->get_logged_in_employee_current_location_id();
			
		$this->db->select('0 as category_id,"Otras" as category_name,sum('.$cash.'.quantity) as total', false);
		$this->db->from($cash);
		$this->db->join($locations, $cash.'.location_id = '.$locations.'.location_id');
		$this->db->join($people, $cash.'.approved_by_employee_id = '.$people.'.person_id');
		
		if ($this->params['category_id']!="-1" && $this->params['category_id']!="0")
			$this->db->where('1', '2', FALSE);

		if (isset($this->params['start_date']) && isset($this->params['end_date']))
		{
			$this->db->where($cash.'.cash_time BETWEEN '.$this->db->escape($this->params['start_date']).' and '.$this->db->escape($this->params['end_date']).' ');
		}
		$this->db->where($cash.'.deleted', 0);
		$this->db->where($cash.'.location_id', $location_iden);
		$this->db->group_by('category_id');
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
		
		if ($this->params['category_id']!="-1")
			$this->db->where($expenses.'.category_id = "'.$this->params['category_id'].'"');
		
		if (isset($this->params['start_date']) && isset($this->params['end_date']))
		{
			$this->db->where($expenses.'.expense_time BETWEEN '.$this->db->escape($this->params['start_date']).' and '.$this->db->escape($this->params['end_date']).' ');
		}
		
		$this->db->where($expenses.'.deleted', 0);
		$this->db->where($expenses.'.location_id', $location_iden);
			
		
		
		$ret = $this->db->get()->row_array();

		if ($this->params['category_id']=="-1" || $this->params['category_id']=="0")
		{
		
			$this->db->select("COUNT(DISTINCT(cash_id)) as expense_count");
			$this->db->from($cash);
			$this->db->join($locations, $cash.'.location_id = '.$locations.'.location_id');
			$this->db->join($people, $cash.'.approved_by_employee_id = '.$people.'.person_id');
			
			
			if (isset($this->params['start_date']) && isset($this->params['end_date']))
			{
				$this->db->where($cash.'.cash_time BETWEEN '.$this->db->escape($this->params['start_date']).' and '.$this->db->escape($this->params['end_date']).' ');
			}
			
			$this->db->where($cash.'.deleted', 0);
			$this->db->where($cash.'.location_id', $location_iden);
			
			
			
			$ret_cash= $this->db->get()->row_array();

			$ret['expense_count']=$ret['expense_count']+$ret_cash['expense_count'];

		}
		return $ret['expense_count'];
	}
	
	
	public function getSummaryData()
	{
		$expenses=$this->db->dbprefix('expenses');
		$cash=$this->db->dbprefix('cash_withdrawal');
		$locations=$this->db->dbprefix('locations');
		$category=$this->db->dbprefix('category');
		$people=$this->db->dbprefix('people');
		$location_iden=$this->Employee->get_logged_in_employee_current_location_id();
		
		$this->db->select('sum('.$expenses.'.quantity) as total', false);
		$this->db->from($expenses);
		$this->db->join($locations, $expenses.'.location_id = '.$locations.'.location_id');
		$this->db->join($category, $expenses.'.category_id = '.$category.'.category_id');
		$this->db->join($people, $expenses.'.approved_by_employee_id = '.$people.'.person_id');
		
		if ($this->params['category_id']!="-1")
			$this->db->where($expenses.'.category_id = "'.$this->params['category_id'].'"');
		
		if (isset($this->params['start_date']) && isset($this->params['end_date']))
		{
			$this->db->where($expenses.'.expense_time BETWEEN '.$this->db->escape($this->params['start_date']).' and '.$this->db->escape($this->params['end_date']).' ');
		}
		
		$this->db->where($expenses.'.deleted', 0);
		$this->db->where($expenses.'.location_id', $location_iden);
			
		
		$return = array(
			'total' => 0,
		);
		
		foreach($this->db->get()->result_array() as $row)
		{
			$return['total'] += to_currency_no_money($row['total'],2);
		}
		
		if ($this->params['category_id']=="-1" || $this->params['category_id']=="0")
		{
			$this->db->select('sum('.$cash.'.quantity) as total', false);
			$this->db->from($cash);
			$this->db->join($locations, $cash.'.location_id = '.$locations.'.location_id');
			$this->db->join($people, $cash.'.approved_by_employee_id = '.$people.'.person_id');
			
			
			if (isset($this->params['start_date']) && isset($this->params['end_date']))
			{
				$this->db->where($cash.'.cash_time BETWEEN '.$this->db->escape($this->params['start_date']).' and '.$this->db->escape($this->params['end_date']).' ');
			}
			
			$this->db->where($cash.'.deleted', 0);
			$this->db->where($cash.'.location_id', $location_iden);
			
			
			
			foreach($this->db->get()->result_array() as $row)
			{
				$return['total'] += to_currency_no_money($row['total'],2);
			}
		}
		
		return $return;
	}

}
?>