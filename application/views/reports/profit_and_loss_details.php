<?php $this->load->view("partial/header"); ?>
<div id="content-header">
	<h1 > <i class="fa fa-bar-chart"> </i><?php echo lang('reports_reports'); ?> - <?php echo lang('reports_profit_and_loss') ?>	</h1>
</div>

<div id="breadcrumb" class="hidden-print">
	<?php echo create_breadcrumb(); ?>
</div>
<div class="clear"></div>
	<div class="row">
		<div class="col-md-12">
			<div class="widget-box">
				<div class="widget-title">
					<h5><?php echo $subtitle ?></h5>
				</div>
				<div class="widget-content">

<h2><?php echo lang('reports_sales'); ?></h2>
<div class="table-responsive">
<table style="width: 40%;" class="table table-bordered table-striped table-hover  tablesorter">
	<?php $total_sales=0;
		foreach($details_data['sales_by_payments'] as $sale_payment) { 
			$total_sales+=$sale_payment['payment_amount'];
		?>
		<tr>
			<td><?php echo $sale_payment['payment_type']; ?></td>
			<td style="text-align: right;"><?php echo to_currency($sale_payment['payment_amount']); ?></td>
		</tr>
		
	<?php } ?>
</table>
</div>
<br />
<h2><?php echo lang('reports_expenses'); ?></h2>
<div class="table-responsive">
<table style="width: 40%;" class="table table-bordered table-striped table-hover  tablesorter">
	<tr>
		<td><?php echo lang('reports_expenses'); ?></td>
		<td style="text-align: right;"><?php echo to_currency($details_data['total_expenses']); ?></td>
	</tr>
</table>
</div>
<br />

<h2><?php echo lang('reports_returns'); ?></h2>
<div class="table-responsive">
<table style="width: 40%;" class="table table-bordered table-striped table-hover  tablesorter">
	<?php foreach($details_data['returns_by_category'] as $category) { ?>
		<tr>
			<td><?php echo $category['category']; ?></td>
			<td style="text-align: right;"><?php echo to_currency($category['total']); ?></td>
		</tr>
		
	<?php } ?>
</table>
</div>
<br />
<h2><?php echo lang('reports_receivings'); ?></h2>
<div class="table-responsive">
<table style="width: 40%;" class="table table-bordered table-striped table-hover  tablesorter">
	<?php foreach($details_data['receivings_by_category'] as $category) { ?>
		<tr>
			<td><?php echo $category['category']; ?></td>
			<td style="text-align: right;"><?php echo to_currency($category['total']); ?></td>
		</tr>
		
	<?php } ?>
</table>
</div>
<br />
<h2><?php echo lang('reports_discounts'); ?></h2>
<div class="table-responsive">
<table style="width: 40%;" class="table table-bordered table-striped table-hover  tablesorter">
	<tr>
		<td><?php echo lang('reports_discount'); ?></td>
		<td style="text-align: right;"><?php echo to_currency($details_data['discount_total']['discount']); ?></td>
	</tr>
</table>
</div>
<br />
<h2><?php echo lang('reports_taxes'); ?></h2>
<div class="table-responsive">
<table style="width: 40%;" class="table table-bordered table-striped table-hover  tablesorter">
	<tr>
		<td><?php echo lang('reports_taxes'); ?></td>
		<td style="text-align: right;"><?php echo to_currency($details_data['taxes']['tax']); ?></td>
	</tr>
</table>
</div>
<br />

<h2><?php echo lang('reports_total'); ?></h2>
<div class="table-responsive">
<table style="width: 40%;" class="table table-bordered table-striped table-hover  tablesorter">
	<tr>
		<td><?php echo lang('reports_total'); ?></td>
		<td style="text-align: right;"><?php echo to_currency($details_data['total']); ?></td>
	</tr>
</table>
</div>
<br>
<h2><?php echo lang('reports_gross_profit'); ?></h2>
<div class="table-responsive">
<table style="width: 40%;" class="table table-bordered table-striped table-hover  tablesorter">
	<tr>
		<td><?php echo lang('reports_gross_profit'); ?></td>
		<td style="text-align: right;"><?php echo to_currency($total_sales-$details_data['total_expenses']); ?></td>
	</tr>
</table>
</div>
<?php
if($this->Employee->has_module_action_permission('reports','show_profit',$this->Employee->get_logged_in_employee_info()->person_id))
{
?>
	<br />
	<h2><?php echo lang('reports_profit'); ?></h2>
	<div class="table-responsive">
	<table style="width: 40%;" class="table table-bordered table-striped table-hover  tablesorter">
		<tr>
			<td><?php echo lang('reports_total'); ?></td>
			<td style="text-align: right;"><?php echo to_currency($details_data['profit']); ?></td>
		</tr>
	</table>
	</div>
	<br />
<?php } ?>
</div>
</div>
</div>
</div>

<?php $this->load->view("partial/footer"); ?>