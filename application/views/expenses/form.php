<?php $this->load->view("partial/header"); ?>
<div id="content-header" class="hidden-print">
	<h1 > <i class="fa fa-pencil"></i>  <?php  if(!$expense_info->expense_id) { echo lang($controller_name.'_new'); } else { echo lang($controller_name.'_update'); }    ?>	</h1>
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
				<h5><?php echo lang("expenses_basic_information"); ?></h5>
			</div>
			<div class="widget-content">
				<?php echo form_open('expenses/save/'.$expense_info->expense_id,array('id'=>'expense_form','class'=>'form-horizontal')); ?>
				
				<div class="form-group offset1">
					<?php echo form_label(lang('expenses_date').':', 'start_date',array('class'=>'required wide col-sm-3 col-md-3 col-lg-2 control-label required wide')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
					   
			
				    <div class="input-group date datepicker" data-date="<?php echo $expense_info->expense_time ? date(get_date_format(), strtotime($expense_info->expense_time)) : ''; ?>" data-date-format=<?php echo json_encode(get_js_date_format()); ?>>
  					<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
					<?php echo form_input(array(
				        'name'=>'expense_time',
				        'id'=>'expense_time',
								'class'=>'',
				        'value'=>$expense_info->expense_time ? date(get_date_format(), strtotime($expense_info->expense_time)) : date(get_date_format()))
				    );?> </div>

				    </div>
				</div>

				<div class="form-group">	
					<?php echo form_label(lang('expenses_number').':', 'name',array('class'=>'required wide col-sm-3 col-md-3 col-lg-2 control-label required wide')); ?>
					<div class="col-sm-4 col-md-4 col-lg-4">
						<?php echo form_input(array(
						'name'=>'nro_receipt',
						'size'=>'8',
						'id'=>'nro_receipt',
					'class'=>'form-control form-inps',
						'value'=>$expense_info->nro_receipt)
						);?>
					</div>
				</div>

				<div class="form-group">	
					<?php echo form_label(lang('expenses_description').':', 'name',array('class'=>' wide col-sm-3 col-md-3 col-lg-2 control-label  wide')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_input(array(
						'name'=>'description',
						'id'=>'description',
					'class'=>'form-control form-inps',
						'value'=>$expense_info->description)
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
							'value'=>$expense_info->quantity ? to_currency_no_money($expense_info->quantity) : '')
							);?>
					</div>
				</div>
				
				<div class="form-group">	
					<?php echo form_label(lang('expenses_category').':', 'name',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_dropdown('category_id', $categorys, $expense_info->category_id);?>
					</div>
				</div>

				<div class="form-group">	
					<?php echo form_label(lang('expenses_location').':', 'name',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_dropdown('location_id', $locations, $expense_info->location_id);?>
					</div>
				</div>

				<div class="form-group">	
					<?php echo form_label(lang('expenses_approved').':', 'name',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_dropdown('approved_by_employee_id', $employees, $expense_info->approved_by_employee_id);?>
					</div>
				</div>

				<div class="form-group">	
					<?php echo form_label(lang('expenses_note').':', 'name',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_textarea(array(
							'name'=>'note',
							'id'=>'note',
							'value'=>$expense_info->note,
							'class'=>'form-control  form-textarea',
							'rows'=>'5',
							'cols'=>'17')		
						);?>
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

	setTimeout(function(){$(":input:visible:first","#expense_form").focus();},100);
		var submitting = false;
		$('#expense_form').validate({
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
					gritter(response.success ? <?php echo json_encode(lang('common_success')); ?> +' #' + response.expense_id : <?php echo json_encode(lang('common_error')); ?> ,response.message,response.success ? 'gritter-item-success' : 'gritter-item-error',false,false);
					if (!confirm(<?php echo json_encode(lang('expenses_confirm_print')); ?>))
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
							window.location.href = '<?php echo site_url("expenses/receipt/'+response.expense_id+'"); ?>'
					}
				},
				<?php if(!$expense_info->expense_id) { ?>
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
				expense_time:
				{
					required:true
				},
				nro_receipt:
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
				expense_time:
				{
					required:<?php echo json_encode(lang('expenses_time_required')); ?>,

				},
				nro_receipt:
				{
					required:<?php echo json_encode(lang('nro_receipt_required')); ?>,
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
