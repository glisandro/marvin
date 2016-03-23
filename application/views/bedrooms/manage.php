<?php $this->load->view("partial/header"); ?>
<script type="text/javascript">
$(document).ready(function()
{
	<?php
	$has_cost_price_permission = $this->Employee->has_module_action_permission('bedrooms','see_cost_price', $this->Employee->get_logged_in_employee_info()->person_id);
	if ($has_cost_price_permission)
	{
	?>
		var table_columns = ["","room_id","room_number",'name','category','bed', 'cost_price','unit_price','quantity','','','','',''];
	<?php	
	}
	else
	{
	?>
		var table_columns = ["","room_id","room_number",'name','category','bed','unit_price','quantity','','','','',''];	
	<?php	
	}
	?>
	enable_sorting("<?php echo site_url("$controller_name/sorting"); ?>",table_columns, <?php echo $per_page; ?>, <?php echo json_encode($order_col);?>, <?php echo json_encode($order_dir);?>);
    enable_select_all();
    enable_checkboxes();
    enable_row_selection();
    enable_search('<?php echo site_url("$controller_name/suggest");?>',<?php echo json_encode(lang("common_confirm_search"));?>);
    enable_delete(<?php echo json_encode(lang($controller_name."_confirm_delete"));?>,<?php echo json_encode(lang($controller_name."_none_selected"));?>);
    enable_cleanup(<?php echo json_encode(lang("bedrooms_confirm_cleanup"));?>);
	
    

	
	 
	 <?php if ($this->session->flashdata('manage_success_message')) { ?>
		gritter(<?php echo json_encode(lang('common_success')); ?>, <?php echo json_encode($this->session->flashdata('manage_success_message')); ?>,'gritter-item-success',false,false);
	 <?php } ?>
});

function post_bulk_form_submit(response)
{
	window.location.reload();
}

function select_inv()
{	
	if (confirm(<?php echo json_encode(lang('bedrooms_select_all_message')); ?>))
	{
		$('#select_inventory').val(1);
		$('#selectall').css('display','none');
		$('#selectnone').css('display','block');
		$.post('<?php echo site_url("items/select_inventory");?>', {select_inventory: $('#select_inventory').val()});
	}
		
}
function select_inv_none()
{
	$('#select_inventory').val(0);
	$('#selectnone').css('display','none');
	$('#selectall').css('display','block');
	$.post('<?php echo site_url("items/clear_select_inventory");?>', {select_inventory: $('#select_inventory').val()});	
}
select_inv_none();
</script>
<div id="content-header" class="hidden-print">
	<h1 ><i class="icon fa fa-table"></i> <?php echo lang('module_'.$controller_name); ?></h1>
</div>

<div id="breadcrumb" class="hidden-print">
		<?php echo create_breadcrumb(); ?>
</div>
<div class="clear"></div>

<?php if($pagination) {  ?>
	<div class="pagination hidden-print alternate text-center fg-toolbar ui-toolbar" id="pagination_top" >
		<?php echo $pagination;?>
	</div>
<?php }  ?>
<div class="pull-right">
		<div class="row">
				<div class="col-md-12 center" style="text-align: center;">					
				<div class="btn-group  ">
				<?php if ($this->Employee->has_module_action_permission($controller_name, 'add_update', $this->Employee->get_logged_in_employee_info()->person_id)) {?>				
					
						<?php echo 
						anchor("$controller_name/view/-1/",
						'<i class="fa fa-pencil   hidden-lg fa fa-2x tip-bottom" data-original-title="'.lang($controller_name.'_new').'"></i> <span class="visible-lg">'.lang($controller_name.'_new').'</span>',
						array('class'=>'btn btn-medium btn-primary', 
							'title'=>lang($controller_name.'_new')));
						?>

					
										
				<?php } ?>

				<?php if ($this->Employee->has_module_action_permission($controller_name, 'delete', $this->Employee->get_logged_in_employee_info()->person_id)) {?>				

					<?php echo 
						anchor("$controller_name/delete",
						'<i class="fa fa-trash-o hidden-lg fa fa-2x tip-bottom" data-original-title="'.lang('common_delete').'"></i><span class="visible-lg">'.lang("common_delete").'</span>',
						array('id'=>'delete', 
							'class'=>'btn btn-danger disabled','title'=>lang("common_delete"))); 
					?>
					<?php echo 
						anchor("$controller_name/cleanup",
						'<i class="fa fa-undo hidden-lg fa fa-2x tip-bottom" data-original-title="'.lang('bedrooms_cleanup_old_bedrooms').'"></i><span class="visible-lg">'.lang("items_cleanup_old_items").'</span>',
						array('id'=>'cleanup', 
							'class'=>'btn btn-warning','title'=>lang("bedrooms_cleanup_old_bedrooms"))); 
					?>
				<?php } ?>
			</div>
		 </div>
		</div>
	</div>
	<div class="row ">
		<?php echo form_open("$controller_name/search",array('id'=>'search_form', 'autocomplete'=> 'off')); ?>
			<input type="text" name ='search' id='search' value="<?php echo H($search); ?>"  placeholder="<?php echo lang('common_search'); ?> <?php echo lang('module_'.$controller_name); ?>"/>
			<?php echo form_dropdown('category', $categories, $category, 'id="category"'); ?>
			<?php echo form_submit('submitf', lang('common_search'),'class="btn btn-primary btn-sm"'); ?>
		</form>
	</div>
	<?php if($total_rows > $per_page) { ?>
		<div id="selectall" class="selectall" onclick="select_inv()" style="text-align: center;display:none;cursor:pointer">
			<?php echo lang('bedrooms_all').' <b>'.$per_page.'</b> '.lang('bedrooms_select_inventory').' <b style="text-decoration:underline">'.$total_rows.'</b> '.lang('bedrooms_select_inventory_total'); ?></div>
			<div id="selectnone" class="selectnone" onclick="select_inv_none()" style="text-align: center;display:none; cursor:pointer">
			<?php echo '<b>'.$total_rows.'</b> '.lang('bedrooms_selected_inventory_total').' '.lang('bedrooms_select_inventory_none'); ?>
		</div>
		<?php 
		}
		echo form_input(array(
		'name'=>'select_inventory',
		'id'=>'select_inventory',
		'style'=>'display:none',
		)); 
	?>
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="widget-box">
					<div class="widget-title">
						<span class="icon">
							<i class="fa fa-th"></i>
						</span>
						<h5 ><?php echo lang('common_list_of').' '.lang('module_'.$controller_name); ?></h5>
						<span title="<?php echo $total_rows; ?> total <?php echo $controller_name?>" class="label label-info tip-left"><?php echo $total_rows; ?></span>
						<a href="<?php echo site_url($controller_name.'/clear_state'); ?>" class="btn btn-info btn-sm clear-state pull-right"><?php echo lang('common_clear_search'); ?></a>
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
	</div>
	<?php $this->load->view("partial/footer"); ?>