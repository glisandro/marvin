<?php $this->load->view("partial/header"); ?>
<script type="text/javascript">
	$(document).ready(function() 
	{ 
		 <?php if ($this->session->flashdata('manage_success_message')) { ?>
 			gritter(<?php echo json_encode(lang('common_success')); ?>, <?php echo json_encode($this->session->flashdata('manage_success_message')); ?>,'gritter-item-success',false,false);
		 <?php } ?>
			
	}); 
</script>
<?php
if (isset($error_message))
{
	echo '<h1 style="text-align: center;">'.$error_message.'</h1>';
	exit;
}
?>
<div id="receipt_wrapper" class="receipt_<?php echo $this->config->item('receipt_text_size');?>">
	<div id="receipt_header">
		<?php if($this->config->item('company_logo')) {?>
		<div id="company_logo"><?php echo img(array('src' => $this->Appconfig->get_logo_image())); ?></div>
		<?php } ?>
		<div id="company_address"><?php echo nl2br($this->Location->get_info_for_key('address')); ?></div>
		<div id="company_phone"><?php echo $this->Location->get_info_for_key('phone'); ?></div>
		<?php if($this->config->item('website')) { ?>
			<div id="website"><?php echo $this->config->item('website'); ?></div>
		<?php } ?>
		<div id="sale_time"><?php echo $expense_time ?></div>
		<div class="pull-right"><button class="btn btn-primary text-white hidden-print" id="new_sale_button_1" onclick="window.location='<?php echo site_url('expenses/view/-1/3'); ?>'" > <?php echo lang('expenses_new'); ?> </button></div>
	</div>
	<div id="receipt_general_info">
		<div ><?php echo lang('expenses_number').": ".$nro_receipt; ?></div>
		<div ><?php echo lang('expenses_description').": ".$description; ?></div>
		<div ><?php echo lang('expenses_quantity').": <b>".to_currency($quantity)."</b>"; ?></div>
		<div ><?php echo lang('expenses_category').": ".$category_name; ?></div>
		<div ><?php echo lang('expenses_location').": ".$location_name; ?></div>
		<div ><?php echo lang('expenses_approved').": ".$person_name; ?></div>
		<div ><?php echo lang('expenses_note').": ".$note; ?></div>
			
	</div>
	<?php 
	if ($this->Employee->has_module_action_permission('expenses', 'add_update', $this->Employee->get_logged_in_employee_info()->person_id)){

	echo form_open("expenses/view/".$expense_id."/3",array('id'=>'expenses_change_form')); ?>
	<button class="btn btn-primary text-white hidden-print" id="edit_expense" onclick="submit()" > <?php echo lang('common_edit'); ?> </button>

	<?php }	?>
	</form>
	<button class="btn btn-primary text-white hidden-print" id="print_button" onclick="print_receipt()" > <?php echo lang('sales_print'); ?> </button>
	<?php
	if ($this->Employee->has_module_action_permission('expenses', 'search', $this->Employee->get_logged_in_employee_info()->person_id)){

	echo form_open("expenses",array('id'=>'expenses_change_form')); ?>
	<button class="btn btn-primary text-white hidden-print" id="search_expense" onclick="submit()" > <?php echo lang('expenses'); ?> </button>

	<?php }	?>
	</form>
	

</div>
	
<?php $this->load->view("partial/footer"); ?>

<?php if ($this->config->item('print_after_sale'))
{
?>
<script type="text/javascript">
$(window).bind("load", function() {
	window.print();
});
</script>
<?php }  ?>

<script type="text/javascript">
function print_receipt()
 {
 	window.print();
 }
</script>
