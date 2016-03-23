<?php $this->load->view("partial/header"); ?>
<div id="content-header" class="hidden-print">
	<h1 > <i class="fa fa-pencil"></i>  <?php  if(!$cash_info->cash_id) { echo lang($controller_name.'_cash_new'); } else { echo lang($controller_name.'_cash_update'); }    ?>	</h1>
</div>

<div id="breadcrumb" class="hidden-print">
	<?php echo create_breadcrumb(); ?>
</div>

<div class="clear"></div>

<div class="row" id="form">
	<div class="col-md-12">
		<?php echo lang('common_fields_required_message'); ?>
		<div class="widget-box">
			<div class="widget-title">
				<span class="icon">
					<i class="fa fa-align-justify"></i>									
				</span>
				<h5><?php echo lang("expenses_cash_basic_information"); ?></h5>
			</div>
			<div class="widget-content">
				<?php echo form_open('expenses/cash_save/'.$cash_info->cash_id,array('id'=>'cash_form','class'=>'form-horizontal')); ?>
				
				<div class="form-group offset1">
					<?php echo form_label(lang('expenses_date').':', 'start_date',array('class'=>'required wide col-sm-3 col-md-3 col-lg-2 control-label required wide')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
					   
			
				    <div class="input-group date datepicker" data-date="<?php echo $cash_info->cash_time ? date(get_date_format(), strtotime($cash_info->cash_time)) : ''; ?>" data-date-format=<?php echo json_encode(get_js_date_format()); ?>>
  					<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
					<?php echo form_input(array(
				        'name'=>'cash_time',
				        'id'=>'cash_time',
								'class'=>'',
				        'value'=>$cash_info->cash_time ? date(get_date_format(), strtotime($cash_info->cash_time)) : date(get_date_format()))
				    );?> </div>

				    </div>
				</div>

				
				<div class="form-group">	
					<?php echo form_label(lang('expenses_description').':', 'name',array('class'=>' wide col-sm-3 col-md-3 col-lg-2 control-label  wide')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_input(array(
						'name'=>'description',
						'id'=>'description',
					'class'=>'form-control form-inps',
						'value'=>$cash_info->description)
						);?>
					</div>
				</div>

				<div class="form-group">	
					<?php echo form_label(lang('expenses_quantity').':', 'name',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_input(array(
							'name'=>'quantity',
							'id'=>'quantity',
							'class'=>'credit_limit',
							'value'=>$cash_info->quantity ? to_currency_no_money($cash_info->quantity) : '')
							);?>
					</div>
				</div>
				
				
				<div class="form-group">	
					<?php echo form_label(lang('expenses_location').':', 'name',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_dropdown('location_id', $locations, $cash_info->location_id);?>
					</div>
				</div>

				<div class="form-group">	
					<?php echo form_label(lang('expenses_approved').':', 'name',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_dropdown('approved_by_employee_id', $employees, $cash_info->approved_by_employee_id);?>
					</div>
				</div>

				

				<?php echo form_hidden('redirect', $redirect); ?>
				
					<div class="form-actions">
						<?php echo form_submit(array(
						'name'=>'submit',
						'id'=>'submit',
						'value'=>lang('common_submit'),
						'class'=>'btn btn-primary')
						); ?>	
					</div>
				<?php echo form_close(); ?>
			</div>
		</div>
	</div>
</div>
<script type='text/javascript'>
//validation and submit handling
$(document).ready(function()
{
	
	$('.datepicker').datepicker({
		format: <?php echo json_encode(get_js_date_format()); ?>
	});

	setTimeout(function(){$(":input:visible:first","#cash_form").focus();},100);
		var submitting = false;
		$('#cash_form').validate({
			submitHandler:function(form)
			{
				$("#form").mask(<?php echo json_encode(lang('common_wait')); ?>);
				if (submitting) return;
				submitting = true;
				$(form).ajaxSubmit({
				success:function(response)
				{
					$("#form").unmask();
					submitting = false;
					gritter(response.success ? <?php echo json_encode(lang('common_success')); ?> +' #' + response.cash_id : <?php echo json_encode(lang('common_error')); ?> ,response.message,response.success ? 'gritter-item-success' : 'gritter-item-error',false,false);
					if (!confirm(<?php echo json_encode(lang('expenses_cash_confirm_print')); ?>))
					{
						response.redirect=2;
					}
					else
					{
						response.redirect=3;
					}
					if(response.redirect==2 && response.success)
					{
							window.location.href = '<?php echo site_url("expenses"); ?>'
					}
					else if(response.redirect==3 && response.success)
					{
							window.location.href = '<?php echo site_url("expenses/cash_receipt/'+response.cash_id+'"); ?>'
					}
				},
				<?php if(!$cash_info->cash_id) { ?>
				resetForm:true,
				<?php } ?>
				dataType:'json'
			});

			},
			errorClass: "text-danger",
			errorElement: "span",
			highlight:function(element, errorClass, validClass) {
				$(element).parents('.form-group').removeClass('has-success').addClass('has-error');
			},
			unhighlight: function(element, errorClass, validClass) {
				$(element).parents('.form-group').removeClass('has-error').addClass('has-success');
			},
			rules:
			{
				cash_time:
				{
					required:true
				},
				quantity:
				{
					number:true
				}
	   		},
			messages:
			{
				cash_time:
				{
					required:<?php echo json_encode(lang('expenses_time_required')); ?>,

				},
				quantity:
				{
					number:<?php echo json_encode(lang('expenses_quantity_number')); ?>
				}

	   		
			}
		});
});
</script>
<?php $this->load->view("partial/footer"); ?>
