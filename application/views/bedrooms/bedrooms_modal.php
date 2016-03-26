<div class="modal-dialog">
	<div class="modal-content">
		<div class="modal-header">
			<button data-dismiss="modal" class="close" type="button">×</button>
			<h3><?php echo lang("bedrooms_basic_information"); ?></h3>
		</div>
		<div class="modal-body nopadding">
			<?php echo $room_info->image_id ? img(array('src' => site_url('app_files/view/'.$room_info->image_id),'class'=>' img-polaroid')) : img(array('src' => base_url().'/img/avatar.png','class'=>' img-polaroid','id'=>'image_empty')); ?>
			<table class="table table-bordered table-hover table-striped" width="1200px">
				<tr> <td><?php echo lang('bedrooms_room_number'); ?></td> <td> <?php echo $room_info->room_number; ?></td></tr>
				<tr> <td><h4><?php echo lang('bedrooms_name'); ?></h4></td> <td> <h4><?php echo $room_info->name; ?></h4></td></tr>
				<tr> <td><?php echo lang('bedrooms_category'); ?></td> <td> <?php echo $room_info->category; ?></td></tr>
				<tr> <td><?php echo lang('bedrooms_beds'); ?></td> <td> <?php echo $room_info->beds; ?></td></tr>
				<?php if ($this->Employee->has_module_action_permission('bedrooms','see_cost_price', $this->Employee->get_logged_in_employee_info()->person_id) or $room_info->name=="")	{ ?>
				<tr> <td><?php echo lang('bedrooms_cost_price'); ?></td> <td> <?php echo to_currency($room_info->cost_price, 10); ?></td></tr>
				<?php } ?>
				<tr> <td><?php echo lang('bedrooms_unit_price'); ?></td> <td> <?php echo to_currency($room_info->unit_price, 10); ?></td></tr>
				<tr> <td><?php echo lang('bedrooms_promo_price'); ?></td> <td> <?php echo to_currency($room_info->promo_price, 10); ?></td></tr>
				<tr> <td><?php echo lang('bedrooms_quantity'); ?></td> <td> <?php echo to_quantity($room_location_info->quantity); ?></td></tr>
				<tr> <td><?php echo lang('bedrooms_reorder_level'); ?></td> <td> <?php echo to_quantity($reorder_level); ?></td></tr>
				<tr> <td><?php echo lang('bedrooms_location'); ?></td> <td> <?php echo $room_location_info->location; ?></td></tr>
				<tr> <td><?php echo lang('bedrooms_description'); ?></td> <td> <?php echo $room_info->description; ?></td></tr>
			</table>
			
			
		</div>
	</div>
</div>



