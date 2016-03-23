<?php $this->load->view("partial/header"); ?>

<div id="content-header" class="hidden-print">
	<h1 > <i class="fa fa-pencil"></i>  <?php  if(!$room_info->room_id || (isset($is_clone) && $is_clone)) { echo lang($controller_name.'_new'); } else { echo lang($controller_name.'_update'); }    ?>	</h1>
</div>

<div id="breadcrumb" class="hidden-print">
	<?php echo create_breadcrumb(); ?>
</div>
<div class="clear"></div>
<div class="room_navigation clearfix">
	<?php
	if (isset($prev_room_id) && $prev_room_id)
	{
		echo '<div class="previous_room">';
			echo anchor('bedrooms/view/'.$prev_room_id, '&laquo; '.lang('bedrooms_prev_room'));
		echo '</div>';
	}
	?>

	<?php
	if (isset($next_room_id) && $next_room_id)
	{
		echo '<div class="next_room">';
			echo anchor('bedrooms/view/'.$next_room_id,lang('bedrooms_next_room').' &raquo;');
		echo '</div>';
	}
	?>
</div>

<?php echo form_open_multipart('bedrooms/save/'.(!isset($is_clone) ? $room_info->room_id : ''),array('id'=>'room_form','class'=>'form-horizontal')); ?>
	<div class="row" id="form">
		<div class="col-md-12">
			<?php echo lang('common_fields_required_message'); ?>
			
			<div class="widget-box">
				<div class="widget-title">
					<span class="icon">
						<i class="fa fa-align-justify"></i>									
					</span>
					<h5><?php echo lang("bedrooms_basic_information"); ?></h5>
				</div>
				<div class="widget-content nopadding">
					<div class="row">
					<div class="span7 ">
					<div class="form-group">
						<?php echo form_label(lang('bedrooms_room_number').':', 'room_number',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_input(array(
								'name'=>'room_number',
								'id'=>'room_number',
								'class'=>'form-control form-inps',
								'value'=>$room_info->room_number)
							);?>
						</div>
					</div>

					<div class="form-group">
					<?php echo form_label(lang('bedrooms_name').':', 'name',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label required wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_input(array(
							'name'=>'name',
							'id'=>'name',
							'class'=>'form-control form-inps',
							'value'=>$room_info->name)
						);?>
						</div>
					</div>


					<div class="form-group">
					<?php echo form_label(lang('bedrooms_category').':', 'category',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label required wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
						<?php  echo form_input(
								array(	'name'=>'category',
										'id'=>'category',
										'class'=>'form-control form-inps',
										'value'=>$room_info->category)
							);
						?>
						</div>
					</div>

					<div class="form-group">
					<?php echo form_label(lang('bedrooms_beds').':', 'beds',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_input(array(
							'name'=>'beds',
							'id'=>'beds',
							'class'=>'form-control form-inps',
							'value'=>$room_info->beds)
						);?>
						</div>
					</div>

					<div class="form-group">
					<?php echo form_label(lang('bedrooms_description').':', 'description',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_textarea(array(
							'name'=>'description',
							'id'=>'description',
							'value'=>$room_info->description,
								'class'=>'form-control  form-textarea',
							'rows'=>'5',
							'cols'=>'17')
						);?>
						</div>
					</div>

					
					<div class="form-group">
					<?php echo form_label(lang('bedrooms_is_service').':', 'is_service',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_checkbox(array(
							'name'=>'is_service',
							'id'=>'is_service',
								'class'=>'delete-checkbox',
							'value'=>1,
							'checked'=>($room_info->is_service) ? 1 : 0)
						);?>
						</div>
					</div>
					
					
				</div>
				<div class="form-group">
                <div class="col-sm-3 col-md-3 col-lg-2 control-label wide">&nbsp;</div>
				<div class="col-sm-9 col-md-9 col-lg-10">
							<div id="avatar">
				      		<?php echo $room_info->image_id ? img(array('src' => site_url('app_files/view/'.$room_info->image_id),'class'=>'img-polaroid img-polaroid-s')) : img(array('src' => base_url().'/img/avatar.png','class'=>'','id'=>'image_empty')); ?>
							</div>
                            <div class="image-upload">
							 <?php echo form_upload(array(
                                'name'=>'image_id',
                                'id'=>'image_id',
                                'value'=>$room_info->image_id)
                              );?>   
                          </div>  
                </div> 
				</div>
				<?php if($room_info->image_id) {  ?>
				<div class="form-group">
				<?php echo form_label(lang('bedrooms_del_image').':', 'del_image',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
					<?php echo form_checkbox(array(
						'name'=>'del_image',
						'id'=>'del_image',
						'class'=>'delete-checkbox',
						'value'=>1
					));?>
					</div>
				</div>
				<?php } ?>

			</div>	


			<div class="widget-title widget-title1 pricing-widget">
				<span class="icon">
					<i class="fa fa-align-justify"></i>									
				</span>
				<h5><?php echo lang("bedrooms_pricing_and_inventory"); ?></h5>
			</div>
					<?php if ($this->Employee->has_module_action_permission('bedrooms','see_cost_price', $this->Employee->get_logged_in_employee_info()->person_id) or $room_info->name=="") { ?>
						<div class="form-group">
							<?php echo form_label(lang('bedrooms_cost_price').' ('.lang('bedrooms_without_tax').')'.':', 'cost_price',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label required wide')); ?>
								<div class="col-sm-9 col-md-9 col-lg-10">
									<?php echo form_input(array(
										'name'=>'cost_price',
										'size'=>'8',
										'id'=>'cost_price',
										'class'=>'form-control form-inps',
										'value'=>$room_info->cost_price ? to_currency_no_money($room_info->cost_price,10) : '')
									);?>
								</div>
						</div>
					<?php 
					}
					else
					{
						echo form_hidden('cost_price', $room_info->cost_price);
					}
					?>

				<div class="form-group">
				<?php echo form_label(lang('bedrooms_unit_price').':', 'unit_price',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label required wide')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
					<?php echo form_input(array(
						'name'=>'unit_price',
						'size'=>'8',
						'id'=>'unit_price',
								'class'=>'form-control form-inps',
						'value'=>$room_info->unit_price ? to_currency_no_money($room_info->unit_price, 10) : '')
					);?>
					</div>
				</div>

				
				<div class="form-group">
				<?php echo form_label(lang('bedrooms_promo_price').':', 'promo_price',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
				    <div class="col-sm-9 col-md-9 col-lg-10">
				    <?php echo form_input(array(
				        'name'=>'promo_price',
				        'size'=>'8',
								'class'=>'form-control',
				        'id'=>'promo_price',
						'class'=>'form-inps',
				        'value'=> $room_info->promo_price ? to_currency_no_money($room_info->promo_price,10) : '')
				    );?>
				    </div>
				</div>

					<div class="form-group offset1">
					<?php echo form_label(lang('bedrooms_promo_start_date').':', 'start_date',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label text-info wide')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
					   
			
				    <div class="input-group date datepicker" data-date="<?php echo $room_info->start_date ? date(get_date_format(), strtotime($room_info->start_date)) : ''; ?>" data-date-format=<?php echo json_encode(get_js_date_format()); ?>>
  					<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
					<?php echo form_input(array(
				        'name'=>'start_date',
				        'id'=>'start_date',
								'class'=>'form-control form-inps',
				        'value'=>$room_info->start_date ? date(get_date_format(), strtotime($room_info->start_date)) : '')
				    );?> </div>

				    </div>
				</div>


					<div class="form-group offset1">
					<?php echo form_label(lang('bedrooms_promo_end_date').':', 'end_date',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label text-info wide')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
					   
			
				    <div class="input-group date datepicker" data-date="<?php echo $room_info->end_date ? date(get_date_format(), strtotime($room_info->end_date)) : ''; ?>" data-date-format=<?php echo json_encode(get_js_date_format()); ?>>
  					<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
					<?php echo form_input(array(
				        'name'=>'end_date',
				        'id'=>'end_date',
								'class'=>'form-control form-inps',
				        'value'=>$room_info->end_date ? date(get_date_format(), strtotime($room_info->end_date)) : '')
				    );?> </div>

				    </div>
				</div>

				<div class="form-group override-commission-container">
					<?php echo form_label(lang('common_override_default_commission').':', '',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
					
					<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_checkbox(array(
							'name'=>'override_default_commission',
							'class' => 'override_default_commission delete-checkbox',
							'value'=>1,
							'checked'=>(boolean)(($room_info->commission_percent > 0) || ($room_info->commission_fixed > 0))));
						?>
					</div>
				</div>
				<div class="commission-container <?php if (!($room_info->commission_percent > 0) && !($room_info->commission_fixed > 0)){echo 'hidden';} ?>">
					<p style="margin-top: 10px;"><?php echo lang('common_commission_help');?></p>
						<div class="form-group">
						<?php echo form_label(lang('reports_commission'), 'commission_value',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
						<div class='col-sm-9 col-md-9 col-lg-10'>
						<?php echo form_input(array(
							'name'=>'commission_value',
							'size'=>'8',
							'class'=>'form-control margin10 form-inps', 
							'value'=> $room_info->commission_fixed > 0 ? to_quantity($room_info->commission_fixed, FALSE) : to_quantity($room_info->commission_percent, FALSE))
						);?>

						<?php echo form_dropdown('commission_type', array('percent' => lang('common_percentage'), 'fixed' => lang('common_fixed_amount')), $room_info->commission_fixed > 0 ? 'fixed' : 'percent');?>
						</div>
					</div>

				</div>
				
				<div class="form-group override-taxes-container">
					<?php echo form_label(lang('bedrooms_override_default_tax').':', '',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
					
					<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_checkbox(array(
							'name'=>'override_default_tax',
							'class' => 'override_default_tax_checkbox delete-checkbox',
							'value'=>1,
							'checked'=>(boolean)$room_info->override_default_tax));
						?>
					</div>
				</div>
				<div class="tax-container <?php if (!$room_info->override_default_tax){echo 'hidden';} ?>">	
					<div class="form-group">
					<?php echo form_label(lang('bedrooms_tax_1').':', 'tax_percent_1',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_input(array(
							'name'=>'tax_names[]',
							'id'=>'tax_name_1 noreset',
							'size'=>'8',
							'class'=>'form-control margin10 form-inps',
							'placeholder' => lang('common_tax_name'),
							'value'=> isset($room_tax_info[0]['name']) ? $room_tax_info[0]['name'] : ($this->Location->get_info_for_key('default_tax_1_name') ? $this->Location->get_info_for_key('default_tax_1_name') : $this->config->item('default_tax_1_name')))
						);?>
						</div>
                        <label class="col-sm-3 col-md-3 col-lg-2 control-label wide" for="tax_percent_1">&nbsp;</label>
						<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_input(array(
							'name'=>'tax_percents[]',
							'id'=>'tax_percent_name_1',
							'size'=>'3',
							'class'=>'form-control form-inps-tax',
							'placeholder' => lang('bedrooms_tax_percent'),
							'value'=> isset($room_tax_info[0]['percent']) ? $room_tax_info[0]['percent'] : '')
						);?>
						<div class="tax-percent-icon">%</div>
						<div class="clear"></div>
						<?php echo form_hidden('tax_cumulatives[]', '0'); ?>
						</div>
					</div>

					<div class="form-group">
					<?php echo form_label(lang('bedrooms_tax_2').':', 'tax_percent_2',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_input(array(
							'name'=>'tax_names[]',
							'id'=>'tax_name_2',
							'size'=>'8',
							'class'=>'form-control form-inps margin10',
							'placeholder' => lang('common_tax_name'),
							'value'=> isset($room_tax_info[1]['name']) ? $room_tax_info[1]['name'] : ($this->Location->get_info_for_key('default_tax_2_name') ? $this->Location->get_info_for_key('default_tax_2_name') : $this->config->item('default_tax_2_name')))
						);?>
						</div>
                        <label class="col-sm-3 col-md-3 col-lg-2 control-label text-info wide">&nbsp;</label>
						<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_input(array(
							'name'=>'tax_percents[]',
							'id'=>'tax_percent_name_2',
							'size'=>'3',
							'class'=>'form-control form-inps-tax',
							'placeholder' => lang('bedrooms_tax_percent'),
							'value'=> isset($room_tax_info[1]['percent']) ? $room_tax_info[1]['percent'] : '')
						);?>
						<div class="tax-percent-icon">%</div>
						<div class="clear"></div>
						<?php echo form_checkbox('tax_cumulatives[]', '1', (isset($room_tax_info[1]['cumulative']) && $room_tax_info[1]['cumulative']) ? (boolean)$room_tax_info[1]['cumulative'] : (boolean)$this->config->item('default_tax_2_cumulative'), 'class="cumulative_checkbox"'); ?>
					    <span class="cumulative_label">
						<?php echo lang('common_cumulative'); ?>
					    </span>
						</div>
					</div>
                     
					<div class="col-sm-9 col-sm-offset-3 col-md-9 col-md-offset-3 col-lg-9 col-lg-offset-3"  style="visibility: <?php echo isset($room_tax_info[2]['name']) ? 'hidden' : 'visible';?>">
						<a href="javascript:void(0);" class="show_more_taxes"><?php echo lang('common_show_more');?> &raquo;</a>
					</div>
					<div class="more_taxes_container" style="display: <?php echo isset($room_tax_info[2]['name']) ? 'block' : 'none';?>">
						<div class="form-group">
						<?php echo form_label(lang('bedrooms_tax_3').':', 'tax_percent_3',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_input(array(
								'name'=>'tax_names[]',
								'id'=>'tax_name_3 noreset',
								'size'=>'8',
								'class'=>'form-control form-inps margin10',
								'placeholder' => lang('common_tax_name'),
								'value'=> isset($room_tax_info[2]['name']) ? $room_tax_info[2]['name'] : ($this->Location->get_info_for_key('default_tax_3_name') ? $this->Location->get_info_for_key('default_tax_3_name') : $this->config->item('default_tax_3_name')))
							);?>
							</div>
                            <label class="col-sm-3 col-md-3 col-lg-2 control-label wide">&nbsp;</label>
							<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_input(array(
								'name'=>'tax_percents[]',
								'id'=>'tax_percent_name_3',
								'size'=>'3',
								'class'=>'form-control form-inps-tax margin10',
								'placeholder' => lang('bedrooms_tax_percent'),
								'value'=> isset($room_tax_info[2]['percent']) ? $room_tax_info[2]['percent'] : '')
							);?>
							<div class="tax-percent-icon">%</div>
							<div class="clear"></div>
							<?php echo form_hidden('tax_cumulatives[]', '0'); ?>
							</div>
						</div>

						<div class="form-group">
						<?php echo form_label(lang('bedrooms_tax_4').':', 'tax_percent_4',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_input(array(
								'name'=>'tax_names[]',
								'id'=>'tax_name_4 noreset',
								'size'=>'8',
								'class'=>'form-control  form-inps margin10',
								'placeholder' => lang('common_tax_name'),
								'value'=> isset($room_tax_info[3]['name']) ? $room_tax_info[3]['name'] : ($this->Location->get_info_for_key('default_tax_4_name') ? $this->Location->get_info_for_key('default_tax_4_name') : $this->config->item('default_tax_4_name')))
							);?>
							</div>
                            <label class="col-sm-3 col-md-3 col-lg-2 control-label wide">&nbsp;</label>
							<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_input(array(
								'name'=>'tax_percents[]',
								'id'=>'tax_percent_name_4',
								'size'=>'3',
								'class'=>'form-control form-inps-tax', 
								'placeholder' => lang('bedrooms_tax_percent'),
								'value'=> isset($room_tax_info[3]['percent']) ? $room_tax_info[3]['percent'] : '')
							);?>
							<div class="tax-percent-icon">%</div>
							<div class="clear"></div>
							<?php echo form_hidden('tax_cumulatives[]', '0'); ?>
							</div>
						</div>
						
						<div class="form-group">
						<?php echo form_label(lang('bedrooms_tax_5').':', 'tax_percent_5',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_input(array(
								'name'=>'tax_names[]',
								'id'=>'tax_name_5 noreset',
								'size'=>'8',
								'class'=>'form-control  form-inps margin10',
								'placeholder' => lang('common_tax_name'),
								'value'=> isset($room_tax_info[4]['name']) ? $room_tax_info[4]['name'] : ($this->Location->get_info_for_key('default_tax_5_name') ? $this->Location->get_info_for_key('default_tax_5_name') : $this->config->item('default_tax_5_name')))
							);?>
							</div>
                            <label class="col-sm-3 col-md-3 col-lg-2 control-label wide">&nbsp;</label>
							<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_input(array(
								'name'=>'tax_percents[]',
								'id'=>'tax_percent_name_5',
								'size'=>'3',
								'class'=>'form-control form-inps-tax margin10',
								'placeholder' => lang('bedrooms_tax_percent'),
								'value'=> isset($room_tax_info[4]['percent']) ? $room_tax_info[4]['percent'] : '')
							);?>
							<div class="tax-percent-icon">%</div>
							<div class="clear"></div>
							<?php echo form_hidden('tax_cumulatives[]', '0'); ?>
							</div>
						</div>
					</div> <!--End more Taxes Container-->
                    <div class="clear"></div>
				</div>
				<?php foreach($locations as $location) { ?>
					<div class="widget-title widget-title1">
						<span class="icon">
							<i class="fa fa-align-justify"></i>									
						</span>
						<h5><?php echo $location->name; ?></h5>
					</div>

					<div class="form-group quantity-input <?php if ($room_info->is_service){echo 'hidden';} ?>">
					<?php echo form_label(lang('bedrooms_quantity').':', '', array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_input(array(
							'name'=>'locations['.$location->location_id.'][quantity]',
							'value'=> $location_bedrooms[$location->location_id]->room_id !== '' && $location_bedrooms[$location->location_id]->quantity !== NULL ? to_quantity($location_bedrooms[$location->location_id]->quantity): '',
								'class'=>'form-control form-inps',
						));?>
						</div>
					</div>		
					
					<?php if ($this->Location->count_all() > 1) {?>
						<div class="form-group reorder-input <?php if ($room_info->is_service){echo 'hidden';} ?>">
						<?php echo form_label(lang('bedrooms_reorder_level').':', '', array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_input(array(
								'name'=>'locations['.$location->location_id.'][reorder_level]',
								'value'=> $location_bedrooms[$location->location_id]->room_id !== '' &&  $location_bedrooms[$location->location_id]->reorder_level !== NULL ? to_quantity($location_bedrooms[$location->location_id]->reorder_level): '',
									'class'=>'form-control form-inps',
							));?>
							</div>
						</div>
					<?php } ?>
					<div class="form-group">
					<?php echo form_label(lang('bedrooms_location_at_store').':', '', array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_input(array(
							'name'=>'locations['.$location->location_id.'][location]',
							'class'=>'form-control form-inps',
							'value'=> $location_bedrooms[$location->location_id]->room_id !== '' ? $location_bedrooms[$location->location_id]->location: ''
						));?>
						</div>
					</div>
					
					<?php if ($this->Location->count_all() > 1) {?>
							<div class="form-group override-prices-container">
							<?php echo form_label(lang('bedrooms_override_prices').':', '',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_checkbox(array(
									'name'=>'locations['.$location->location_id.'][override_prices]',
									'class' => 'override_prices_checkbox delete-checkbox',
									'value'=>1,
									'checked'=>(boolean)isset($location_bedrooms[$location->location_id]) && is_object($location_bedrooms[$location->location_id]) && $location_bedrooms[$location->location_id]->is_overwritten));
								?>
							</div>
						</div>
						<div class="room-location-price-container <?php if ($location_bedrooms[$location->location_id] === FALSE || !$location_bedrooms[$location->location_id]->is_overwritten){echo 'hidden';} ?>">	
							<?php if ($this->Employee->has_module_action_permission('bedrooms','see_cost_price', $this->Employee->get_logged_in_employee_info()->person_id) or $room_info->name=="") { ?>
									<div class="form-group">
										<?php echo form_label(lang('bedrooms_cost_price').' ('.lang('bedrooms_without_tax').'):', '',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
											<div class="col-sm-9 col-md-9 col-lg-10">
												<?php echo form_input(array(
													'name'=>'locations['.$location->location_id.'][cost_price]',
													'size'=>'8',
													'class'=>'form-control form-inps',
													'value'=> $location_bedrooms[$location->location_id]->room_id !== '' && $location_bedrooms[$location->location_id]->cost_price ? to_currency_no_money($location_bedrooms[$location->location_id]->cost_price, 10): ''
												)
												);?>
										</div>
									</div>
								<?php 
								}
								else
								{
									echo form_hidden('locations['.$location->location_id.'][cost_price]', $location_bedrooms[$location->location_id]->room_id !== '' ? $location_bedrooms[$location->location_id]->cost_price: '');
								}
								?>

							<div class="form-group">
							<?php echo form_label(lang('bedrooms_unit_price').':', '',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
								<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_input(array(
									'name'=>'locations['.$location->location_id.'][unit_price]',
									'size'=>'8',
								'class'=>'form-control form-inps',
									'value'=>$location_bedrooms[$location->location_id]->room_id !== '' && $location_bedrooms[$location->location_id]->unit_price ? to_currency_no_money($location_bedrooms[$location->location_id]->unit_price, 10) : ''
									)
								);?>
								</div>
							</div>

							

							<div class="form-group">
							<?php echo form_label(lang('bedrooms_promo_price').':', '',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
							    <div class="col-sm-9 col-md-9 col-lg-10">
							    <?php echo form_input(array(
									'name'=>'locations['.$location->location_id.'][promo_price]',
							        'size'=>'8',
								'class'=>'form-control form-inps',
									'value'=> $location_bedrooms[$location->location_id]->room_id !== '' && $location_bedrooms[$location->location_id]->promo_price ? to_currency_no_money($location_bedrooms[$location->location_id]->promo_price, 10): ''
								)
							    );?>
							    </div>
							</div>

								<div class="form-group offset1">
								<?php echo form_label(lang('bedrooms_promo_start_date').':', '',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label text-info wide')); ?>
								<div class="col-sm-9 col-md-9 col-lg-10">
						
								<div class="input-group date datepicker" data-date="<?php echo $location_bedrooms[$location->location_id]->room_id !== '' &&  $location_bedrooms[$location->location_id]->start_date ? date(get_date_format(), strtotime($location_bedrooms[$location->location_id]->start_date)): ''; ?>" data-date-format=<?php echo json_encode(get_js_date_format()); ?>>
		  							<span class="input-group-addon"><i class="fa fa-calendar"></i></span>

								<?php echo form_input(array(
									'name'=>'locations['.$location->location_id.'][start_date]',
							        'size'=>'8',
								'class'=>'form-control form-inps',
									 'value'=> $location_bedrooms[$location->location_id]->room_id !== '' && $location_bedrooms[$location->location_id]->start_date ? date(get_date_format(), strtotime($location_bedrooms[$location->location_id]->start_date)): ''
									)
								);?>       
							    </div>
							</div>
							</div>
								<div class="form-group offset1">
								<?php echo form_label(lang('bedrooms_promo_end_date').':', '',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label text-info wide')); ?>
							    <div class="col-sm-9 col-md-9 col-lg-10">
							    	<div class="input-group date datepicker" data-date="<?php echo $location_bedrooms[$location->location_id]->room_id !== '' && $location_bedrooms[$location->location_id]->end_date ? date(get_date_format(), strtotime($location_bedrooms[$location->location_id]->end_date)): ''; ?>" data-date-format=<?php echo json_encode(get_js_date_format()); ?>>
		  								<span class="input-group-addon"><i class="fa fa-calendar"></i></span>

										    <?php echo form_input(array(
												'name'=>'locations['.$location->location_id.'][end_date]',
										        'size'=>'8',
								'class'=>'form-control form-inps',
												 'value'=> $location_bedrooms[$location->location_id]->room_id !== '' && $location_bedrooms[$location->location_id]->end_date ? date(get_date_format(), strtotime($location_bedrooms[$location->location_id]->end_date)): ''
										    	));
											?> 
								    </div>
								</div>
							</div>
						</div>
						<div class="form-group override-taxes-container">
							<?php echo form_label(lang('bedrooms_override_default_tax').':', '',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>

							<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_checkbox(array(
									'name'=>'locations['.$location->location_id.'][override_default_tax]',
									'class' => 'override_default_tax_checkbox  delete-checkbox',
									'value'=>1,
									'checked'=> $location_bedrooms[$location->location_id]->room_id !== '' ? (boolean)$location_bedrooms[$location->location_id]->override_default_tax: FALSE
									));
								?>
							</div>
						</div>

						<div class="tax-container <?php if ($location_bedrooms[$location->location_id] === FALSE || !$location_bedrooms[$location->location_id]->override_default_tax){echo 'hidden';} ?>">	
							<div class="form-group">
							<?php echo form_label(lang('bedrooms_tax_1').':', '',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
								<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_input(array(
									'name'=>'locations['.$location->location_id.'][tax_names][]',
									'size'=>'8',
									'class'=>'form-control form-inps margin10',
									'placeholder' => lang('common_tax_name'),
									'value' => isset($location_taxes[$location->location_id][0]['name']) ? $location_taxes[$location->location_id][0]['name'] : ($this->Location->get_info_for_key('default_tax_1_name') ? $this->Location->get_info_for_key('default_tax_1_name') : $this->config->item('default_tax_1_name'))
								));?>
								</div>
                                <label class="col-sm-3 col-md-3 col-lg-2 control-label wide">&nbsp;</label>
								<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_input(array(
									'name'=>'locations['.$location->location_id.'][tax_percents][]',
									'size'=>'3',
									'class'=>'form-control form-inps-tax margin10',
									'placeholder' => lang('bedrooms_tax_percent'),
									'value' => isset($location_taxes[$location->location_id][0]['percent']) ? $location_taxes[$location->location_id][0]['percent'] : ''
								));?>
								<div class="tax-percent-icon">%</div>
								<div class="clear"></div>
								<?php echo form_hidden('locations['.$location->location_id.'][tax_cumulatives][]', '0'); ?>
								</div>
							</div>
						<div class="form-group">
						<?php echo form_label(lang('bedrooms_tax_2').':', '',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_input(array(
								'name'=>'locations['.$location->location_id.'][tax_names][]',
								'size'=>'8',
								'class'=>'form-control form-inps margin10',
								'placeholder' => lang('common_tax_name'),
								'value' => isset($location_taxes[$location->location_id][1]['name']) ? $location_taxes[$location->location_id][1]['name'] : ($this->Location->get_info_for_key('default_tax_1_name') ? $this->Location->get_info_for_key('default_tax_1_name') : $this->config->item('default_tax_1_name'))
								)
							);?>
							</div>
                            <label class="col-sm-3 col-md-3 col-lg-2 control-label wide">&nbsp;</label>
							<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_input(array(
								'name'=>'locations['.$location->location_id.'][tax_percents][]',
								'size'=>'3',
								'class'=>'form-control form-inps-tax',
								'placeholder' => lang('bedrooms_tax_percent'),
								'value' => isset($location_taxes[$location->location_id][1]['percent']) ? $location_taxes[$location->location_id][1]['percent'] : ''
								)
							);?>
							<div class="tax-percent-icon">%</div>
							<div class="clear"></div>
							<?php echo form_checkbox('locations['.$location->location_id.'][tax_cumulatives][]', '1', isset($location_taxes[$location->location_id][1]['cumulative']) ? (boolean)$location_taxes[$location->location_id][1]['cumulative'] : ($this->Location->get_info_for_key('default_tax_2_cumulative') ? (boolean)$this->Location->get_info_for_key('default_tax_2_cumulative') : (boolean)$this->config->item('default_tax_2_cumulative')), 'class="cumulative_checkbox"'); ?>
						    <span class="cumulative_label">
							<?php echo lang('common_cumulative'); ?>
						    </span>
							</div> <!-- end col-sm-9...-->
						</div><!--End form-group-->
						
						<div class="col-sm-9 col-sm-offset-3 col-md-9 col-md-offset-3 col-lg-9 col-lg-offset-3" style="visibility: <?php echo isset($location_taxes[$location->location_id][2]['name']) ? 'hidden' : 'visible';?>">
							<a href="javascript:void(0);" class="show_more_taxes"><?php echo lang('common_show_more');?> &raquo;</a>
						</div>
						
						<div class="more_taxes_container"  style="display: <?php echo isset($location_taxes[$location->location_id][2]['name']) ? 'block' : 'none';?>">
							<div class="form-group">
							<?php echo form_label(lang('bedrooms_tax_3').':', '',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
								<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_input(array(
									'name'=>'locations['.$location->location_id.'][tax_names][]',
									'size'=>'8',
									'class'=>'form-control form-inps margin10',
									'placeholder' => lang('common_tax_name'),
									'value' => isset($location_taxes[$location->location_id][2]['name']) ? $location_taxes[$location->location_id][2]['name'] : ($this->Location->get_info_for_key('default_tax_3_name') ? $this->Location->get_info_for_key('default_tax_3_name') : $this->config->item('default_tax_3_name'))
								));?>
								</div>
                                <label class="col-sm-3 col-md-3 col-lg-2 control-label wide">&nbsp;</label>
								<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_input(array(
									'name'=>'locations['.$location->location_id.'][tax_percents][]',
									'size'=>'3',
									'class'=>'form-control form-inps-tax',
									'placeholder' => lang('bedrooms_tax_percent'),
									'value' => isset($location_taxes[$location->location_id][2]['percent']) ? $location_taxes[$location->location_id][2]['percent'] : ''
								));?>
								<div class="tax-percent-icon">%</div>
								<div class="clear"></div>
								<?php echo form_hidden('locations['.$location->location_id.'][tax_cumulatives][]', '0'); ?>
								</div>
							</div>
							
							
							<div class="form-group">
							<?php echo form_label(lang('bedrooms_tax_4').':', '',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
								<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_input(array(
									'name'=>'locations['.$location->location_id.'][tax_names][]',
									'size'=>'8',
									'class'=>'form-control form-inps margin10',
									'placeholder' => lang('common_tax_name'),
									'value' => isset($location_taxes[$location->location_id][3]['name']) ? $location_taxes[$location->location_id][3]['name'] : ($this->Location->get_info_for_key('default_tax_4_name') ? $this->Location->get_info_for_key('default_tax_4_name') : $this->config->item('default_tax_4_name'))
								));?>
								</div>
                                <label class="col-sm-3 col-md-3 col-lg-2 control-label wide">&nbsp;</label>
								<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_input(array(
									'name'=>'locations['.$location->location_id.'][tax_percents][]',
									'size'=>'3',
									'class'=>'form-control form-inps-tax',
									'placeholder' => lang('bedrooms_tax_percent'),
									'value' => isset($location_taxes[$location->location_id][3]['percent']) ? $location_taxes[$location->location_id][3]['percent'] : ''
								));?>
								<div class="tax-percent-icon">%</div>
								<div class="clear"></div>
								<?php echo form_hidden('locations['.$location->location_id.'][tax_cumulatives][]', '0'); ?>
								</div>
							</div>
							
							
							
							<div class="form-group">
							<?php echo form_label(lang('bedrooms_tax_5').':', '',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
								<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_input(array(
									'name'=>'locations['.$location->location_id.'][tax_names][]',
									'size'=>'8',
									'class'=>'form-control form-inps margin10',
									'placeholder' => lang('common_tax_name'),
									'value' => isset($location_taxes[$location->location_id][4]['name']) ? $location_taxes[$location->location_id][4]['name'] : ($this->Location->get_info_for_key('default_tax_5_name') ? $this->Location->get_info_for_key('default_tax_5_name') : $this->config->item('default_tax_5_name'))
								));?>
								</div>
                                <label class="col-sm-3 col-md-3 col-lg-2 control-label wide">&nbsp;</label>
								<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_input(array(
									'name'=>'locations['.$location->location_id.'][tax_percents][]',
									'size'=>'3',
									'class'=>'form-control form-inps-tax',
									'placeholder' => lang('bedrooms_tax_percent'),
									'value' => isset($location_taxes[$location->location_id][4]['percent']) ? $location_taxes[$location->location_id][4]['percent'] : ''
								));?>
								<div class="tax-percent-icon">%</div>
								<div class="clear"></div>
								<?php echo form_hidden('locations['.$location->location_id.'][tax_cumulatives][]', '0'); ?>
								</div>
							</div>
						</div><!-- End more taxes container-->
                        <div class="clear"></div>
					</div> <!-- End tax-container-->
				<?php } /*End if for multi locations*/ ?>
			<?php } /*End foreach for locations*/ ?>	
			
				<?php echo form_hidden('redirect', isset($redirect) ? $redirect : ''); ?>
				<?php echo form_hidden('sale_or_receiving', isset($sale_or_receiving) ? $sale_or_receiving : ''); ?>
				
					<div class="form-actions">
						<?php
						if (isset($redirect) && $redirect == 1)
						{
							echo form_button(array(
						    'name' => 'cancel',
						    'id' => 'cancel',
							 'class' => 'btn btn-danger',
						    'value' => 'true',
						    'content' => lang('common_cancel')
							));
						
						}
						?>
						
				<?php
				echo form_submit(array(
					'name'=>'submitf',
					'id'=>'submitf',
					'value'=>lang('common_submit'),
					'class'=>'submit_button btn btn-primary')
				);
				?>
				</div>
			<?php echo form_close(); ?>
			
			<div class="item_navigation">
				<?php
				if (isset($prev_room_id) && $prev_room_id)
				{
					echo '<div class="previous_room">';
						echo anchor('bedrooms/view/'.$prev_room_id, '&laquo; '.lang('bedrooms_prev_room'));
					echo '</div>';
				}
				?>

				<?php
				if (isset($next_room_id) && $next_room_id)
				{
					echo '<div class="next_room">';
						echo anchor('bedrooms/view/'.$next_room_id,lang('bedrooms_next_room').' &raquo;');
					echo '</div>';
				}
				?>
			</div>
			
			</div>
		</div>
	</div>
</div>
		

<script type='text/javascript'>
//validation and submit handling
$(document).ready(function()
{
	
	$(".delete_room_number").click(function()
	{
		$(this).parent().parent().remove();
	});
	
	$("#add_addtional_room_number").click(function()
	{
		$("#additional_room_numbers tbody").append('<tr><td><input type="text" class="form-control form-inps" size="40" name="additional_room_numbers[]" value="" /></td><td>&nbsp;</td></tr>');
	});
	
	$("#cancel").click(cancelRoomAddingFromSaleOrRecv);
	
    setTimeout(function(){$(":input:visible:first","#room_form").focus();},100);
    $('#image_id').imagePreview({ selector : '#avatar' }); // Custom preview container
	
	$('.datepicker').datepicker({
		format: <?php echo json_encode(get_js_date_format()); ?>
	});
   	
	$(".override_default_tax_checkbox, .override_prices_checkbox, .override_default_commission").change(function()
	{
		$(this).parent().parent().next().toggleClass('hidden')
	});
	
	$("#is_service").change(function()
	{
		if ($(this).prop('checked'))
		{
			$(".quantity-input").addClass('hidden');			
			$(".reorder-input").addClass('hidden');			
		}
		else
		{
			$(".quantity-input").removeClass('hidden');
			$(".reorder-input").removeClass('hidden');
		}
	});

	$( "#category" ).autocomplete({
		source: "<?php echo site_url('bedrooms/suggest_category');?>",
		delay: 300,
		autoFocus: false,
		minLength: 0
	});

	$('#room_form').validate({
		submitHandler:function(form)
		{
			$.post('<?php echo site_url("bedrooms/check_duplicate");?>', {term: $('#name').val()},function(data) {
			<?php if(!$room_info->room_id) {  ?>
			if(data.duplicate)
				{
					
					if(confirm(<?php echo json_encode(lang('bedrooms_duplicate_exists'));?>))
					{
						doRoomSubmit(form);
					}
					else 
					{
						return false;
					}
				}
			<?php }  else ?>
			 {
				doRoomSubmit(form);
			 }} , "json")
		     .error(function() {
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
		<?php if(!$room_info->room_id) {  ?>
			room_number:
			{
				remote: 
				    { 
					url: "<?php echo site_url('bedrooms/room_number_exists');?>", 
					type: "post"
					
				    } 
			},
			
		<?php } ?>
		
		
		<?php foreach($locations as $location) { ?>
			"<?php echo 'locations['.$location->location_id.'][quantity]'; ?>":
			{
				number: true
			},
			"<?php echo 'locations['.$location->location_id.'][reorder_level]'; ?>":
			{
				number: true
			},
			"<?php echo 'locations['.$location->location_id.'][cost_price]'; ?>":
			{
				number: true
			},
			"<?php echo 'locations['.$location->location_id.'][unit_price]'; ?>":
			{
				number: true
			},			
			"<?php echo 'locations['.$location->location_id.'][promo_price]'; ?>":
			{
				number: true
			},			
			
		<?php } ?>
		
			name:"required",
			category:"required",
			cost_price:
			{
				required:true,
				number:true
			},

			unit_price:
			{
				required:true,
				number:true
			},
			promo_price:
			{
				number: true
			},
			reorder_level:
			{
				number:true
			},
   		},
		messages:
		{			
			<?php if(!$room_info->room_id) {  ?>
			room_number:
			{
				remote: <?php echo json_encode(lang('bedrooms_room_number_exists')); ?>
				   
			},
			<?php } ?>
			
			
			<?php foreach($locations as $location) { ?>
				"<?php echo 'locations['.$location->location_id.'][quantity]'; ?>":
				{
					number: <?php echo json_encode(lang('common_this_field_must_be_a_number')); ?>
				},
				"<?php echo 'locations['.$location->location_id.'][reorder_level]'; ?>":
				{
					number: <?php echo json_encode(lang('common_this_field_must_be_a_number')); ?>
				},
				"<?php echo 'locations['.$location->location_id.'][cost_price]'; ?>":
				{
					number: <?php echo json_encode(lang('common_this_field_must_be_a_number')); ?>
				},
				"<?php echo 'locations['.$location->location_id.'][unit_price]'; ?>":
				{
					number: <?php echo json_encode(lang('common_this_field_must_be_a_number')); ?>
				},			
				"<?php echo 'locations['.$location->location_id.'][promo_price]'; ?>":
				{
					number: <?php echo json_encode(lang('common_this_field_must_be_a_number')); ?>
				},			
				
			<?php } ?>
			
			name:<?php echo json_encode(lang('bedrooms_name_required')); ?>,
			category:<?php echo json_encode(lang('bedrooms_category_required')); ?>,
			cost_price:
			{
				required:<?php echo json_encode(lang('bedrooms_cost_price_required')); ?>,
				number:<?php echo json_encode(lang('bedrooms_cost_price_number')); ?>
			},
			unit_price:
			{
				required:<?php echo json_encode(lang('bedrooms_unit_price_required')); ?>,
				number:<?php echo json_encode(lang('bedrooms_unit_price_number')); ?>
			},
			promo_price:
			{
				number: <?php echo json_encode(lang('common_this_field_must_be_a_number')); ?>
			}
		}
	});
});

var submitting = false;

function doRoomSubmit(form)
{
	if (submitting) return;
	submitting = true;
	$("#form").mask(<?php echo json_encode(lang('common_wait')); ?>);
	$(form).ajaxSubmit({
	success:function(response)
	{
		$("#form").unmask();
		submitting = false;
		gritter(response.success ? <?php echo json_encode(lang('common_success')); ?> +' #' + response.room_id : <?php echo json_encode(lang('common_error')); ?> ,response.message,response.success ? 'gritter-room-success' : 'gritter-room-error',false,false);

		if(response.redirect==1 && response.success)
		{ 
			if (response.sale_or_receiving == 'sale')
			{
				$.post('<?php echo site_url("sales/add");?>', {room: response.room_id}, function()
				{
					window.location.href = '<?php echo site_url('sales'); ?>'
				});
			}
			else
			{
				$.post('<?php echo site_url("receivings/add");?>', {room: response.room_id}, function()
				{
					window.location.href = '<?php echo site_url('receivings'); ?>'
				});
			}
		}
		else if(response.redirect==2 && response.success)
		{
			window.location.href = '<?php echo site_url('bedrooms'); ?>'
		}

		
		<?php if(!$room_info->room_id) { ?>
		//If we have a new item, make sure we hide the tax containers to "reset"
		$(".tax-container").addClass('hidden');
		$(".room-location-price-container").addClass('hidden');
		$('.commission-container').addClass('hidden');
		
		//Make the quantity inputs show up again in case they were hidden
		$(".quantity-input").removeClass('hidden');
		$(".reorder-input").removeClass('hidden');
		
		<?php } ?>
	},
	<?php if(!$room_info->room_id) { ?>
	resetForm: true,
	<?php } ?>
	dataType:'json'
	});
}

function cancelRoomAddingFromSaleOrRecv()
{
	if (confirm(<?php echo json_encode(lang('bedrooms_are_you_sure_cancel')); ?>))
	{
		<?php if (isset($sale_or_receiving) && $sale_or_receiving == 'sale') {?>
			window.location = <?php echo json_encode(site_url('sales')); ?>;
		<?php } else { ?>
			window.location = <?php echo json_encode(site_url('receivings')); ?>;
		<?php } ?>
	}
}

</script>
<?php $this->load->view('partial/footer'); ?>