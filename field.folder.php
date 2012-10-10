<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * PyroStreams File Folder Field Type
 *
 * @package		PyroStreams
 * @author		AI Web Systems, Inc.
 * @copyright	Copyright (c) 2011 - 2012, AI Web Systems, Inc.
 * @license		http://aiwebsystems.com/cms/docs/license
 * @link			http://aiwebsystems.com/cms
 */
class Field_folder
{
	public $field_type_name			= 'Folder';

	public $field_type_slug			= 'folder';
	
	public $db_col_type				= 'int';

	public $version					= '1.0';

	public $author					= array('name'=>'AI Web Systems, Inc.', 'url'=>'http://aiwebsystems.com');
	
	public $custom_parameters		= array('parent_folder', 'naming_format', 'when_deleted');
	
	public $plugin_return			= 'array';
	
	
	// --------------------------------------------------------------------------

	/**
	 * Event
	 *
	 * Called before the form is built.
	 *
	 * @access	public
	 * @return	void
	 */
	public function event()
	{

		// Load up requirements
		$this->CI->config->load('files/files');
		$this->CI->lang->load('files/files');
		$this->CI->load->library('files/files');

		
		// Get extensions allowed
		$allowed_extensions = '';

		foreach (config_item('files:allowed_file_ext') as $type) 
		{
			$allowed_extensions .= implode('|', $type).'|';
		}


		// Add some poop
		$this->CI->template->append_metadata(
			"<script>
				pyro.lang.fetching = '".lang('files:fetching')."';
				pyro.lang.fetch_completed = '".lang('files:fetch_completed')."';
				pyro.lang.start = '".lang('files:start')."';
				pyro.lang.width = '".lang('files:width')."';
				pyro.lang.height = '".lang('files:height')."';
				pyro.lang.ratio = '".lang('files:ratio')."';
				pyro.lang.full_size = '".lang('files:full_size')."';
				pyro.lang.cancel = '".lang('buttons.cancel')."';
				pyro.lang.synchronization_started = '".lang('files:synchronization_started')."';
				pyro.lang.untitled_folder = '".lang('files:untitled_folder')."';
				pyro.lang.exceeds_server_setting = '".lang('files:exceeds_server_setting')."';
				pyro.lang.exceeds_allowed = '".lang('files:exceeds_allowed')."';
				pyro.files = { permissions : ".json_encode(Files::allowed_actions())." };
				pyro.files.max_size_possible = '".Files::$max_size_possible."';
				pyro.files.max_size_allowed = '".Files::$max_size_allowed."';
				pyro.files.valid_extensions = '/".trim($allowed_extensions, '|')."$/i';
				pyro.lang.file_type_not_allowed = '".lang('files:file_type_not_allowed')."';
				pyro.lang.new_folder_name = '".lang('files:new_folder_name')."';
			</script>");


		// Setup the namespace puhlease
		Asset::add_path('files_module', 'system/cms/modules/files/');

		$this->CI->template
			->append_css('files_module::jquery.fileupload-ui.css')
			->append_css('files_module::files.css')
			->append_js('files_module::jquery.fileupload.js')
			->append_js('files_module::jquery.fileupload-ui.js')
			->append_js('files_module::functions.js');

		// Add override file
		$this->CI->type->add_css('folder', 'override.css');
	}

	// --------------------------------------------------------------------------
	
	/**
	 * Output form input
	 *
	 * @param	array
	 * @param	array
	 * @return	string
	 */
	public function form_output( $data )
	{

		// Load up requirements
		$this->CI->config->load('files/files');
		$this->CI->lang->load('files/files');
		$this->CI->load->library('files/files');

		$data['folders'] = $this->CI->file_folders_m->count_by('parent_id', 0);
		$data['locations'] = array_combine(Files::$providers, Files::$providers);
		$data['folder_tree'] = Files::folder_tree();

		$path_check = Files::check_dir(Files::$path);

		if ( ! $path_check['status'])
		{
			$data['messages'] = array('error' => $path_check['message']);
		}

		return $this->CI->type->load_view('folder', 'index', $data);
	}
	
	// --------------------------------------------------------------------------

	/**
	 * Parent folder
	 *
	 * @access	public
	 * @param		[string - value]
	 * @return	string
	 */	
	public function param_parent_folder($value = null)
	{
		// Load the module needed
		$this->CI->load->model('files/file_folders_m');
		
		// Get applicable parent folders
		$applicable_folders = $this->CI->file_folders_m->get_folders();
		
		// Prep for dropdown
		foreach($applicable_folders as $folder):
		
			$folders[$folder->id] = $folder->name;
			
		endforeach;
		
		return form_dropdown('parent_folder', $folders, 'class=""');
	}
	
	/**
	 * Format the folder naming process
	 *
	 * @access	public
	 * @param		[string - value]
	 * @return	string
	 */	
	public function param_naming_format($value = null)
	{
		// Prep value for format selector
		$selected_value = explode(',', $value);
		
		// Load the modules needed
		$this->CI->load->model('fields_m');
		
		// Get fields
		$applicable_fields = $this->CI->db->select('id, field_name')
			->where('field_namespace = "streams"', null, false)
			->get('data_fields')
			->result();


		// Prep for dropdown by adding the ID
		$fields['ID'] = 'Entry ID';

		// Get the others
		foreach($applicable_fields as $field):
		
			$fields[$field->id] = $field->field_name;
			
		endforeach;
		
		$html = $this->CI->type->load_view('folder', 'param_naming_format_js', '', TRUE);
		
		return form_input('naming_format', $value, 'id="naming_format" style="display:none;"').form_multiselect('format_selector', $fields, $selected_value, 'id="format_selector" class=""') . $html;
	}
	
	/**
	 * What happens when the entry is deleted
	 *
	 * @access	public
	 * @param		[string - value]
	 * @return	string
	 */	
	public function param_when_deleted($value = 'delete')
	{	
		return form_dropdown('when_deleted', array('delete'=>'Delete the entry\'s folder and files.', 'keep'=>'Keep folder and files intact.'), $value);
	}
	
	// --------------------------------------------------------------------------

	/**
	 * Before saving... where row_id is present
	 *
	 * @access	public
	 * @param		[string - value]
	 * @return	string
	 */	
	public function pre_save($field_value, $field_params, $stream, $row_id = FALSE, $field_values)
	{
		// Load model needed
		$this->CI->load->model('files/file_folders_m');
		
		
		// Init
		$naming_field_ids 		= array();
		$naming_field_slugs		= array();
		$naming_field_values	= array();
		$naming_field_types		= array();
		
		$folder_name					= '';
		$folder_slug					= '';
		
		// Get the IDs of the fields to use for naming
		$naming_field_ids = explode(',', $field_params->field_data['naming_format']);
		
		// Populate the naming_field arrays
		foreach($naming_field_ids as $id):
			
			// return the $field per id
			if ( $field = $this->CI->fields_m->get_field($id) )
			{
				$naming_field_slugs[] = $field->field_slug;
				$naming_field_types[] = $field->field_type;
				
				if ( isset($field_values[$slug]) && $field_values[$slug] != '' )
				{
					$naming_field_values[] = $field_values[$field->field_slug];
				}
			}
			
		endforeach;
		
		// If none of the fields exist anymore, quit
		if(empty($naming_field_slugs)) return FALSE;
		
		// Format the odd field_types
		foreach($naming_field_values as $index=>$value):
		
			switch($naming_field_types[$index]){
			
				case 'user':
					$user = $this->CI->ion_auth->get_user((int) $value);
					
					if($user)
					{
						$value = $user->display_name;
					}
					
					break;
				case 'choice':
					
					break;
			}
			
			$naming_field_values[$index] = $value;
			
		endforeach;
		
		// Make the folder name and slug
		$folder_name = implode(' ',$naming_field_values);
		
		// _strtoslug that betch
		$folder_slug = self::_strtoslug($folder_name);
		
		if(strlen($folder_name) > 100)
		{
			$folder_name = substr($folder_name,0,95).'...';
			$folder_slug = substr($folder_slug,0,95).'...';
		}
		
		// Do we need to create a folder?
		if( $field_value == '--NONE--')
		{
			// Make the slug unique if it's not
			if(($unique = self::unique_identifier($folder_slug, $field_value)) !== FALSE)
			{
				$folder_slug = $folder_slug . '-' . $unique;
				$folder_name = $folder_name . '-' . $unique;
			}
			
			// Now insert it and get the ID
			$this->CI->file_folders_m->insert(array(
					'name'				=> $folder_name,
					'slug'				=> $folder_slug,
					'parent_id'		=> $field_params->field_data['parent_folder'],
					'date_added'	=> now()
					));
				
				// Grad the folder ID
				$field_value = $this->CI->db->insert_id();
		}
		else
		{
			// Make the slug unique if it's not
			if(($unique = self::unique_identifier($folder_slug, $field_value)) !== FALSE)
			{
				$folder_slug = $folder_slug . '-' . $unique;
				$folder_name = $folder_name . '-' . $unique;
			}
			else
			{
				$folder_slug = $folder_slug;
				$folder_name = $folder_name;
			}
			
			// Update the existing folder! Only update the name...
			$this->CI->file_folders_m->update($field_value, array('name'=>$folder_name, 'slug'=>$folder_slug));
		}
		
		return $field_value;
	}
	
	//
	function unique_identifier($slug = NULL, $folder_id = NULL)
	{		
		// Is this a new folder?
		if($folder_id == '--NONE--')
		{
			// Does it already exist?
			if($this->CI->file_folders_m->exists($slug))
			{
				// Lets loop and try and uniqueify it ( foldername-1 or foldername-2)
				for($c=1;$c<100;$c++)
				{
					if(!$this->CI->file_folders_m->exists($slug . '-' . $c))
					{
						return $c;break;
					}
				}
			}
		}
		else
		{
			
			// Get the current slug
			$current_slug = $this->CI->db->select('slug')->where('id',$folder_id)->limit(1)->get('file_folders')->row(0)->slug;
			
			// Is it different?
			if($slug != $current_slug)
			{
				// Does this different one exist?
				if($this->CI->file_folders_m->exists($slug))
				{	
					// Uniqueify
					for($c=1;$c<100;$c++)
					{
						if(!$this->CI->file_folders_m->exists($slug . '-' . $c))
						{
							return $c;break;
						}
					}
				}
			}
		}
		
		return FALSE;
	}

	// --------------------------------------------------------------------------
	
	/**
	 * Preprocess output
	 *
	 * @param	array
	 * @param	array
	 * @return	string
	 */
	public function pre_output( $input, $params )
	{
		// Load model needed
		$this->CI->load->model('files/file_folders_m');
		
		$input	= ($input==''?'--NONE--':$input);

		$parent_folder_portion 	= '';
		$entry_folder_portion		= '';
		

		// Admin area?
		if ( defined('ADMIN_THEME') )
		{

			/*
			 * Grab the link and out a button
			 */

			// First get the slug for the parent folder if its there
			if(isset($params['parent_folder']) && $params['parent_folder'] != NULL)
			{
				if($this->CI->file_folders_m->exists($params['parent_folder']))
				{
					$slug = $this->CI->db->select('slug')->where('id',$params['parent_folder'])->limit(1)->get('file_folders')->row(0)->slug;
					$parent_folder_portion = $slug . '/';
				}
			}
			
			// Now get the slug for the entry's folder
			if($input != '--NONE--' && $this->CI->file_folders_m->exists($input))
			{
				return $input;
			}
			else
			{
				$options['value']	= '--NONE--';
				
				return '';
			}
		}
		else
		{

			// Otherwise output the plugin contents
			if( ! empty($input) )
			{				
				
				// Get the files model
				$this->CI->load->model('files/file_folders_m');
				
				// Init
				$out = array();
				
				// Get the files
				$files = $this->CI->db
					->order_by('sort', 'ASC')
					->order_by('name', 'ASC')
					->get_where('files', array('folder_id'=>$input))
					->result();
				
				if(empty($files))
				{
					return array();
				}
				
				$c = 0;

				foreach($files as $file)
				{
					
					// Copy
					$out[$c] = (array) $file;
					
					// Add some nice helpers
					$out[$c]['full_path'] 					= 'uploads/' . SITE_REF . '/files/' . $file->filename;
					$out[$c]['absolute_path'] 				= base_url().'uploads/' . SITE_REF . '/files/' . $file->filename;

					$out[$c]['csv_full_path'] 				= ($c==0?'':',').'uploads/' . SITE_REF . '/files/' . $file->filename;
					$out[$c]['csv_absolute_path'] 			= ($c==0?'':',').base_url('uploads/' . SITE_REF . '/files/' . $file->filename);
					
					$out[$c]['thumb_full_path'] 			= 'files/thumb/' . $file->id;
					$out[$c]['thumb_absolute_path'] 		= base_url('files/thumb/' . $file->id);

					$out[$c]['full_tag'] 					= '<img src="uploads/' . SITE_REF . '/files/' . $file->filename . '"/>';
					$out[$c]['absolute_tag'] 				= '<img src="' . base_url('uploads/' . SITE_REF . '/files/' . $file->filename) . '"/>';
					
					$out[$c]['thumb_full_tag'] 				= '<img src="files/thumb/' . $file->id. '/200"/>';
					$out[$c]['thumb_absolute_tag'] 			= '<img src="' . base_url('files/thumb/' . $file->id. '/200') . '"/>';
					
					$out[$c]['sort_value'] 					= 'sort_'.$file->sort;
					$out[$c]['count_value'] 				= 'count_'.$c;
					
					$out[$c]['count']		 				= $c;
					$out[$c]['sort']		 				= $file->sort;

					// Increment
					$c++;
				}
				
				return $out;
			}
			else
			{
				return array();
			}
		}
	}
	
	// --------------------------------------------------------------------------

	/**
	 * Breaks up the items into key/val for template use
	 *
	 * @access	public
	 * @access	public
	 * @param	string
	 * @param	string
	 * @param	array
	 * @return	array
	 */
	public function pre_output_plugin($input, $params)
	{
		// Is there an input?
		if($input != '')
		{				
			
			// Get the files model
			$this->CI->load->model('files/file_folders_m');
			
			// Init
			$out = array();
			
			// Get the files
			$files = $this->CI->db
				->order_by('sort', 'ASC')
				->order_by('name', 'ASC')
				->get_where('files', array('folder_id'=>$input))
				->result();
			
			if(empty($files))
			{
				return array();
			}
			
			$c = 0;
			foreach($files as $file)
			{
				// Copy
				$out[$c] = (array) $file;
				
				// Add some nice helpers
				$out[$c]['full_path'] 					= 'uploads/' . SITE_REF . '/files/' . $file->filename;
				$out[$c]['absolute_path'] 				= base_url().'uploads/' . SITE_REF . '/files/' . $file->filename;

				$out[$c]['csv_full_path'] 				= ($c==0?'':',').'uploads/' . SITE_REF . '/files/' . $file->filename;
				$out[$c]['csv_absolute_path'] 			= ($c==0?'':',').base_url('uploads/' . SITE_REF . '/files/' . $file->filename);
				
				$out[$c]['thumb_full_path'] 			= 'files/thumb/' . $file->id;
				$out[$c]['thumb_absolute_path'] 		= base_url('files/thumb/' . $file->id);

				$out[$c]['full_tag'] 					= '<img src="uploads/' . SITE_REF . '/files/' . $file->filename . '"/>';
				$out[$c]['absolute_tag'] 				= '<img src="' . base_url('uploads/' . SITE_REF . '/files/' . $file->filename) . '"/>';
				
				$out[$c]['thumb_full_tag'] 				= '<img src="files/thumb/' . $file->id. '/200"/>';
				$out[$c]['thumb_absolute_tag'] 			= '<img src="' . base_url('files/thumb/' . $file->id. '/200') . '"/>';
				
				$out[$c]['sort_value'] 					= 'sort_'.$file->sort;
				$out[$c]['count_value'] 				= 'count_'.$c;

				$out[$c]['count']		 				= $c;
				$out[$c]['sort']		 				= $file->sort;

				// Increment
				$c++;
			}
			
			return $out;
		}
		else
		{
			return array();
		}
	}
	
	// --------------------------------------------------------------------------

	/**
	 * Called after entry is deleted
	 *
	 * @access	public
	 * @access	public
	 * @param	obj
	 * @param	obj
	 * @param	obj
	 */
	public function entry_destruct($entry, $field, $stream)
	{
		// Should we delete the folder and files?
		if($field->field_data['when_deleted'] == 'delete')
		{
			
			// Field slug?
			$slug = $field->field_slug;
			
			// ID?
			$folder = $entry->$slug;

			// Clear the folder
			$this->_destroy_folder($folder);
		}
	}

	// --------------------------------------------------------------------------	
	
	/**
	 * Delete folder and contents
	 *
	 * @access	public
	 * @access	public
	 * @param	int
	 */
	private function _destroy_folder($folder)
	{	
		
		// Load the lib
		$this->CI->load->library('files/files');

		$contents = $this->CI->files->folder_contents($folder);

		// Delete folders
		if ( ! empty($contents['data']['folder']) )
		{
			foreach ( $contents['data']['folder'] as $inner ) $this->_destroy_folder($inner->id);
		}

		// Delete files
		if ( ! empty($contents['data']['file']) )
		{
			foreach ( $contents['data']['file'] as $file ) $this->CI->files->delete_file($file->id);
		}

		$this->CI->files->delete_folder($folder);
	}

	// --------------------------------------------------------------------------	
	
	/**
	 * Make slug
	 */

	private function _strtoslug($string)
	{	
		$string = trim($string);
		$string = strtolower($string);
		$string = preg_replace('/[\s-]+/', '-', $string);
		$string = preg_replace("/[^0-9a-zA-Z-]/", '', $string);
		
		return $string;
	}
}

/* End of file field.file_folder.php */