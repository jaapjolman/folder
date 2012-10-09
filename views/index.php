<?php if ( ! empty($value) ): ?>

<section class="item" id="files-window">
	
	<section class="center" id="center-pane">
			<?php if ( ! $folders) : ?>
				<div class="no_data"><?php echo lang('files:no_folders'); ?></div>
			<?php endif; ?>

			<ul class="folders-center pane"></ul>

			<ul class="context-menu-source">
				<li 							data-applies-to="folder" 			data-menu="open"><?php echo lang('files:open'); ?></li>
				<li data-role="create_folder"	data-applies-to="pane root-pane"	data-menu="new-folder"><?php echo lang('files:new_folder'); ?></li>
				<li data-role="upload" 			data-applies-to="folder pane" 		data-menu="upload"><?php echo lang('files:upload'); ?></li>
				<li data-role="edit_file"		data-applies-to="file" 				data-menu="rename"><?php echo lang('files:rename'); ?></li>
				<li data-role="edit_folder"		data-applies-to="folder" 			data-menu="rename"><?php echo lang('files:rename'); ?></li>
				<!--<li 						data-applies-to="file" 				data-menu="edit"><?php echo lang('files:edit'); ?></li>-->
				<li data-role="download_file"	data-applies-to="file" 				data-menu="download"><?php echo lang('files:download'); ?></li>
				<li data-role="synchronize"		data-applies-to="folder"			data-menu="synchronize"><?php echo lang('files:synchronize'); ?></li>
				<li data-role="delete_file"		data-applies-to="file" 				data-menu="delete"><?php echo lang('files:delete'); ?></li>
				<li data-role="delete_folder"	data-applies-to="folder" 			data-menu="delete"><?php echo lang('files:delete'); ?></li>
				<li 							data-applies-to="folder file pane"	data-menu="details"><?php echo lang('files:details'); ?></li>
			</ul>
	</section>

	<section class="side sidebar-right" id="right-pane">
		<div id="console-area">
			<span class="subsection-title"><?php echo lang('files:activity'); ?></span>
			<ul id="console"></ul>
		</div>

	</section>

	<div class="hidden">
		
		<div id="item-details">
			<h4><?php echo lang('files:details'); ?></h4>
			<ul>
				<li><label><?php echo lang('files:id'); ?>:</label> 
					<span class="id"></span>
				</li>
				<li><label><?php echo lang('files:name'); ?>:</label> 
					<span class="name"></span>
				</li>
				<li><label><?php echo lang('files:slug'); ?>:</label> 
					<span class="slug"></span>
				</li>
				<li><label><?php echo lang('files:path'); ?>:</label> 
					<input readonly="readonly" type="text" class="path"/>
				</li>
				<li><label><?php echo lang('files:added'); ?>:</label> 
					<span class="added"></span>
				</li>
				<li><label><?php echo lang('files:width'); ?>:</label> 
					<span class="width"></span>
				</li>
				<li><label><?php echo lang('files:height'); ?>:</label> 
					<span class="height"></span>
				</li>
				<li><label><?php echo lang('files:filename'); ?>:</label> 
					<span class="filename"></span>
				</li>
				<li><label><?php echo lang('files:filesize'); ?>:</label> 
					<span class="filesize"></span>
				</li>
				<li><label><?php echo lang('files:download_count'); ?>:</label> 
					<span class="download_count"></span>
				</li>
				<li><label><?php echo lang('files:location'); ?>:</label> 
					<span class="location-static"></span>
				</li>
				<li><label><?php echo lang('files:container'); ?>:</label> 
					<span class="container-static"></span>
				</li>
				<li><label><?php echo lang('files:location'); ?>:</label> 
					<?php echo form_dropdown('location', $locations, '', 'class="location"'); ?>
				</li>
				<li><label><?php echo lang('files:bucket'); ?>:</label> 
					<?php echo form_input('bucket', '', 'class="container amazon-s3"'); ?>
					<a class="container-button button"><?php echo lang('files:check_container'); ?></a>
				</li>
				<li><label><?php echo lang('files:container'); ?>:</label> 
					<?php echo form_input('container', '', 'class="container rackspace-cf"'); ?>
					<a class="container-button button"><?php echo lang('files:check_container'); ?></a>
				</li>
				<li>
					<span class="container-info"></span>
				</li>
				<li><label><?php echo lang('files:description'); ?>:</label> <br />
					<textarea class="description"></textarea>
				</li>
			</ul>
			<div class="buttons">
				<?php $this->load->view('admin/partials/buttons', array('buttons' => array('save', 'cancel') )); ?>
			</div>
		</div>

		<ul>
			<li class="new-folder" data-id="" data-name=""><span class="name-text"><?php echo lang('files:new_folder_name'); ?></span></li>
		</ul>
	</div>

	<div class="hidden"><?php echo form_input($form_slug, $value); ?></div>

</section>

<script type="text/javascript">
	$(document).ready(function(){

		// Load the flash data ID
		setTimeout(function(){pyro.files.folder_contents(<?php echo $value; ?>);}, '1000');

		$('#files-window').parents('div.input').css('width', '100%');

		$('#files-window').parents('section.item').append('<div class="hidden"><div id="files-uploader"><div class="files-uploader-browser"><form action="admin/files/upload" method="post" enctype="multipart/form-data" class="file_upload"><label for="file" class="upload"><?php echo lang('files:uploader'); ?></label><input type="file" name="file" multiple="multiple" /></form><ul id="files-uploader-queue" class="ui-corner-all"></ul></div><div class="buttons align-right padding-top"><a href="#" title="" class="button start-upload"><?php echo lang('files:upload'); ?></a><a href="#" title="" class="button cancel-upload"><?php echo lang('buttons.cancel'); ?></a></div></div></div>');

		pyro.init_upload($('#files-uploader'));
	});
</script>
<?php else: ?>
<?php echo form_hidden($form_slug, '--NONE--'); ?>
<?php echo lang('streams.folder.save_first'); ?>
<?php endif; ?>