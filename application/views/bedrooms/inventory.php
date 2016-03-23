<?php $this->load->view("partial/header"); ?>
<?php echo form_open('bedrooms/save_inventory/'.$room_info->room_id,array('id'=>'room_form')); ?>
<div id="content-header" class="hidden-print">
	<h1 > <i class="fa fa-bar-chart"> </i><?php echo lang("bedrooms_inventory_tracking"); ?>	</h1>
</div>

<div id="breadcrumb" class="hidden-print">
	<?php echo create_breadcrumb(); ?>
</div>
<div class="clear"></div>
	<div class="container-fluid">
		<div class="row">
			<div class="row">
				<div class="span6 offset3">
					<div class="widget-box">
						<div class="widget-title"><span class="icon"><i class="fa fa-file"></i></span><h5><?php echo lang("bedrooms_basic_information"); ?></h5></div>
							<div class="widget-content nopadding">
								<table class="table table-bordered table-hover">
									<tr>
										<td>	
											<?php echo form_label(lang('bedrooms_room_number').':', 'name',array('class'=>'wide')); ?>
										</td>
										<td>
											<?php 
												$inumber = array (
												'name'=>'room_number',
												'id'=>'room_number',
												'value'=>$room_info->room_number,
												'style'       => 'border:none',
												'readonly' => 'readonly'
												);
												echo form_input($inumber)
											?>
										</td>
									</tr>
									<tr>
										<td>	
											<?php echo form_label(lang('bedrooms_name').':', 'name',array('class'=>'wide')); ?>
										</td>
										<td>	
											<?php $iname = array (
											'name'=>'name',
											'id'=>'name',
											'value'=>$room_info->name,
											'style'       => 'border:none',
											'readonly' => 'readonly'
											);
											echo form_input($iname);
											?>
										</td>
									</tr>
									<tr>
										<td>	
											<?php echo form_label(lang('bedrooms_category').':', 'category',array('class'=>'wide')); ?>
										</td>
										<td>	
											<?php 
												$cat = array (
												'name'=>'category',
												'id'=>'category',
												'value'=>$room_info->category,
												'style'       => 'border:none',
												'readonly' => 'readonly'
												);
												echo form_input($cat);
											?>
										</td>
									</tr>
									<tr>
										<td>
											<?php echo form_label(lang('bedrooms_current_quantity').':', 'quantity',array('class'=>'wide')); ?>
										</td>
										<td>
											<?php 
												$qty = array (
												'name'=>'quantity',
												'id'=>'quantity',
												'value'=>to_quantity($room_location_info->quantity),
												'style'       => 'border:none',
												'readonly' => 'readonly'
												);
												echo form_input($qty);
											?>
										</td>
									</tr>
									<tr>
										<td><?php echo form_label(lang('bedrooms_add_minus').':', 'quantity',array('class'=>'required wide')); ?></td>
										<td><?php echo form_input(array(
											'name'=>'newquantity',
											'id'=>'newquantity'
												)
											);?>
										</td>
									</tr>
									<tr>
										<td>	<?php echo form_label(lang('bedrooms_inventory_comments').':', 'description',array('class'=>'wide')); ?></td>
									<td><?php echo form_textarea(array(
										'name'=>'trans_comment',
										'id'=>'trans_comment',
										'rows'=>'3',
										'cols'=>'17')		
										);?>
									</td>
								</tr>
								<tr>
									<td colspan="2" align="center" >
										<?php
										echo form_submit(array(
										'name'=>'submit',
										'id'=>'submit',
										'value'=>lang('common_submit'),
										'class'=>'btn btn-primary')
										);
										?>
									</td>
								</tr>
							</table>
							
							<table class="table table-bordered table-striped table-hover data-table">
								<thead><tr align="center" style="font-weight:bold"><td width="15%"><?php echo lang("bedrooms_inventory_tracking"); ?></td><td width="25%"><?php echo lang("employees_employee"); ?></td><td width="15%"><?php echo lang("bedrooms_in_out_qty"); ?></td><td width="45%"><?php echo lang("bedrooms_remarks"); ?></td></tr></thead>
								<tbody>
									<?php foreach($this->Bedrooms_inventory->get_inventory_data_for_room($room_info->room_id)->result_array() as $row) { ?>
										<tr  align="center">
											<td><?php echo date(get_date_format(). ' '.get_time_format(), strtotime($row['trans_date']))?></td>
											<td>
												<?php
													$person_id = $row['trans_user'];
													$employee = $this->Employee->get_info($person_id);
													echo $employee->first_name." ".$employee->last_name;
												?>
											</td>
											<td align="right"><?php echo to_quantity($row['trans_inventory']);?></td>
											
											<?php
											$row['trans_comment'] = preg_replace('/'.$this->config->item('sale_prefix').' ([0-9]+)/', anchor('sales/receipt/$1', $row['trans_comment']), $row['trans_comment']);
																							$row['trans_comment'] = preg_replace('/RECV ([0-9]+)/', anchor('receivings/receipt/$1', $row['trans_comment']), $row['trans_comment']);
											?>
											<td><?php echo $row['trans_comment'];?></td>
										</tr>
									<?php } ?>
								</tbody>
							</table>
							
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php  echo form_close(); ?>
			
<script type='text/javascript'>

//validation and submit handling
$(document).ready(function()
{	
	var submitting = false;
	$('#room_form').validate({
		submitHandler:function(form)
		{
			if (submitting) return;
			submitting = true;
			$(form).ajaxSubmit({
			success:function(response)
			{
					if(!response.success)
						{ 
							gritter(<?php echo json_encode(lang('common_error')); ?>,response.message,'gritter-room-error',false,true);
							
						}
						else
						{
							gritter(<?php echo json_encode(lang('common_success')); ?>,response.message,'gritter-room-success',false,true);
							setTimeout(function()
							{
								window.location.reload(true);								
							}, 1200);
						}
					submitting = false;
			},
			dataType:'json'
		});

		},
			errorClass: "help-inline",
			errorElement: "span",
			highlight:function(element, errorClass, validClass) {
				$(element).parents('.control-group').addClass('error');
			},
			unhighlight: function(element, errorClass, validClass) {
				$(element).parents('.control-group').removeClass('error');
				$(element).parents('.control-group').addClass('success');
			},
		rules: 
		{
			newquantity:
			{
				required:true,
				number:true
			}
   		},
		messages: 
		{
			
			newquantity:
			{
				required:<?php echo json_encode(lang('bedrooms_quantity_required')); ?>,
				number:<?php echo json_encode(lang('bedrooms_quantity_number')); ?>
			}
		}
	});
});
</script>
<?php $this->load->view('partial/footer'); ?>
