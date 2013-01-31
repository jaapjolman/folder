<script type="text/javascript">var SITE_URL	= "<?php echo site_url(); ?>";</script>
<?php 

	if(!defined('ADMIN_THEME')):
	
		$admin_theme = $this->theme_m->get_admin();
		$this->asset->set_theme($admin_theme->slug);
	
	endif;
	
	if(!defined('ADMIN_THEME')) $this->asset->set_theme($this->theme->slug);

?>

<script type="text/javascript">
	
	$(document).ready(function(){
		
		// Listen for change on chosen selector
		$('#format_selector').chosen().change(function(){
			
			// Init
			var existingids	= $('#naming_format').val();
					existingids = existingids.split(',');
			
			var selectedids	= Array();
			
			var ids					= Array();
			var idstring 		= '';
			
			
			// Get selected
			$('#format_selector > option:selected').each(function(){
				
				selectedids.push($(this).val());
				
			});
			
			// Remove existing IDS from selected that are no longer selected
			$.each(existingids, function(k,v){
				
				if (selectedids.indexOf(v) == -1){ existingids[k]=null; }
					
			});
			
			// Now remove selected ids that are already existing
			$.each(selectedids, function(k,v){
				
				if (existingids.indexOf(v) != -1) selectedids[k]=null;
					
			});
			
			// Join them
			ids = existingids.concat(selectedids);
			
			// Loop and prep string
			var c = 0;
			
			$.each(ids, function(k,v){
				
				// Add to string - mind your comma
				idstring = idstring + (v==null?'':(c==0?'':',') + v);
				
				// If there is something ++ so no ','
				if(idstring!=''){c++;}
			});
			
			// Put the value
			$('#naming_format').val(idstring);
			
		});
		
	});//eof ready
	
</script>