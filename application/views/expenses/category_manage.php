<?php $this->load->view("partial/header"); ?>
<script type="text/javascript">
	$(document).ready(function() 
	{ 
		var table_columns = ['','name'];
		
		enable_sorting("<?php echo site_url("$controller_name/sorting"); ?>",table_columns, <?php echo $per_page; ?>, <?php echo json_encode($order_col);?>, <?php echo json_encode($order_dir); ?>);
	    enable_select_all();
	    enable_checkboxes();
	    enable_row_selection();
	    enable_search('<?php echo site_url("$controller_name/suggest");?>',<?php echo json_encode(lang("common_confirm_search"));?>);
	    enable_delete(<?php echo json_encode(lang($controller_name."_category_confirm_delete"));?>,<?php echo json_encode(lang($controller_name."_none_selected"));?>);
		 
		 <?php if ($this->session->flashdata('manage_success_message')) { ?>
 			gritter(<?php echo json_encode(lang('common_success')); ?>, <?php echo json_encode($this->session->flashdata('manage_success_message')); ?>,'gritter-item-success',false,false);
		 <?php } ?>
			
	}); 
</script>
<div id="content-header" class="hidden-print">
	<h1> <i class="icon fa fa-th-large"></i>
		<?php echo lang($controller_name.'_category'); ?></h1>


	</div>


	<div id="breadcrumb" class="hidden-print">
		<?php echo create_breadcrumb(); ?>

	</div>
	<div class="clear"></div>
	<?php if($pagination) {  ?>
	<div class="pagination hidden-print alternate text-center fg-toolbar ui-toolbar" id="pagination_top">
		<?php echo $pagination;?>
	</div>
	 <?php }  ?>
	
		
	<div class=" pull-right">
		<div class="row">
			<div class="col-md-12 center" style="text-align: center;">					
				<div class="btn-group  ">

				<?php if ($this->Employee->has_module_action_permission($controller_name, 'add_update', $this->Employee->get_logged_in_employee_info()->person_id)) {?>
					<?php echo anchor("$controller_name/category_view/-1/2",
						'<i title="'.lang($controller_name.'_new').'" class="fa fa-pencil tip-bottom hidden-lg fa fa-2x"></i><span class="visible-lg">'.lang($controller_name.'_category_new').'</span>',
						array('id' => 'new-person-btn', 'class'=>'btn btn-primary', 'title'=>$this->lang->line($controller_name.'_category_new')));
					}	
				?>
				
	
					<?php if ($this->Employee->has_module_action_permission($controller_name, 'delete', $this->Employee->get_logged_in_employee_info()->person_id)) {?>
					<?php echo anchor("$controller_name/category_delete",
						'<i title="'.lang('common_delete').'" class="fa fa-trash-o tip-bottom hidden-lg fa fa-2x"></i><span class="visible-lg">'.lang('common_delete').'</span>'
						,array('id'=>'delete', 'class'=>'btn btn-danger disabled delete_inactive ','title'=>$this->lang->line("common_delete"))); ?>
						<?php } ?>
						

							</div>
						</div>
					</div>
			</div>
				<div class="row ">
					<?php echo form_open("$controller_name/category_search",array('id'=>'search_form', 'autocomplete'=> 'off')); ?>
					<input type="text" name ='search' id='search' value="<?php echo H($search);  ?>"   placeholder="<?php echo lang('common_search'); ?> <?php echo lang($controller_name.'_category'); ?>"/>
				</form>
			</div>
			
			
			<div class="row">
				<div class="col-md-12">
					<div class="widget-box">
						<div class="widget-title">
							<span class="icon">
								<i class="fa fa-th"></i>
							</span>
							<h5 ><?php echo lang('common_list_of').' '.lang($controller_name.'_category'); ?></h5>
							<span title="<?php echo $total_rows; ?> total <?php echo lang($controller_name.'_category')?>" class="label label-info tip-left"><?php echo $total_rows; ?></span>
							<a href="<?php echo site_url($controller_name.'/category_clear_state'); ?>" class="btn btn-info btn-sm clear-state pull-right"><?php echo lang('common_clear_search'); ?></a>
						</div>
					<div class="widget-content nopadding table_holder table-responsive" >
							<?php echo $manage_table; ?>			
						</div>		
						<?php if($pagination) {  ?>

						<div class="pagination hidden-print alternate text-center fg-toolbar ui-toolbar" id="pagination_bottom" >
							<?php echo $pagination;?>
						</div>
						<?php } ?>
					</div>
				</div>
			</div>
			<?php $this->load->view("partial/footer"); ?>