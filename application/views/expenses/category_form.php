<?php $this->load->view("partial/header"); ?>
<div id="content-header" class="hidden-print">
	<h1 > <i class="fa fa-pencil"></i>  <?php  if(!$category_info->category_id) { echo lang($controller_name.'_category_new'); } else { echo lang($controller_name.'_category_update'); }    ?>	</h1>
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
				<h5><?php echo lang("expenses_category_basic_information"); ?></h5>
			</div>
			<div class="widget-content">
				<?php echo form_open('expenses/category_save/'.$category_info->category_id,array('id'=>'category_form','class'=>'form-horizontal')); ?>
				
				
				<div class="form-group">	
					<?php echo form_label(lang('category_name').':', 'name',array('class'=>'required wide col-sm-3 col-md-3 col-lg-2 control-label required wide')); ?>
					<div class="col-sm-4 col-md-4 col-lg-4">
						<?php echo form_input(array(
						'name'=>'name',
						'size'=>'8',
						'id'=>'name',
					'class'=>'form-control form-inps',
						'value'=>$category_info->name)
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
	
	
	setTimeout(function(){$(":input:visible:first","#category_form").focus();},100);
		var submitting = false;
		$('#category_form').validate({
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
					gritter(response.success ? <?php echo json_encode(lang('common_success')); ?> +' #' + response.category_id : <?php echo json_encode(lang('common_error')); ?> ,response.message,response.success ? 'gritter-item-success' : 'gritter-item-error',false,false);
					if(response.redirect==2 && response.success)
					{
							window.location.href = '<?php echo site_url('expenses/category'); ?>'
					}
				},
				<?php if(!$category_info->category_id) { ?>
				resetForm:false,
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
				name:
				{
					required:true
				}
	   		},
			messages:
			{
				name:
				{
					required:<?php echo json_encode(lang('category_name_required')); ?>,

				}

	   		
			}
		});
});
</script>
<?php $this->load->view("partial/footer"); ?>
