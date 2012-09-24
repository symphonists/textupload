<?php
	
	require_once(TOOLKIT . '/fields/field.upload.php');
	
	Class fieldTextUpload extends fieldUpload {
	
		protected $_sizes = array();

	/*-------------------------------------------------------------------------
		Definition
	-------------------------------------------------------------------------*/

		public function __construct(){
			parent::__construct();
			
			$this->_name = 'Text Upload';
			
			$this->set('show_column', 'no');
			$this->set('text_size', 'medium');
			$this->set('location', 'main');
			
			$this->_sizes = array(
				array('small', false, __('Small Box')),
				array('medium', false, __('Medium Box')),
				array('large', false, __('Large Box')),
				array('huge', false, __('Huge Box'))
			);
		}

	/*-------------------------------------------------------------------------
		Setup
	-------------------------------------------------------------------------*/

		public function createTable(){
			$field_id = $this->get('id');
			
			return Symphony::Database()->query(
				"CREATE TABLE IF NOT EXISTS `tbl_entries_data_{$field_id}` (
					`id` INT(11) unsigned NOT NULL auto_increment,
					`entry_id` INT(11) unsigned NOT NULL,
					`value` MEDIUMTEXT DEFAULT NULL,
					`value_formatted` MEDIUMTEXT DEFAULT NULL,
					`word_count` INT(11) UNSIGNED DEFAULT NULL,
					`file` varchar(255) default NULL,
					`size` int(11) unsigned NULL,
					`mimetype` varchar(50) default NULL,
					`meta` varchar(255) default NULL,
					`timestamp` int(11) UNSIGNED DEFAULT NULL,
					PRIMARY KEY  (`id`),
					KEY `entry_id` (`entry_id`),
					KEY `file` (`file`),
					FULLTEXT KEY `value` (`value`),
					FULLTEXT KEY `value_formatted` (`value_formatted`)
				) ENGINE=MyISAM;"
			);
		}

	/*-------------------------------------------------------------------------
		Settings
	-------------------------------------------------------------------------*/

		public function displaySettingsPanel(&$wrapper, $errors=NULL){
			Field::displaySettingsPanel($wrapper, $errors);
			
			/*---------------------------------------------------------------
				Upload Options
			---------------------------------------------------------------*/
			
			// Build fieldset and legend
			$fieldset = new XMLElement('fieldset');
			$legend = new XMLElement('legend', __('Upload Options'));
			$fieldset->appendChild($legend);
			
			// Build group div
			$group = new XMLElement('div');
			$group->setAttribute('class', 'group');

			// Get upload destinations
			$ignore = array(
				'/workspace/events',
				'/workspace/data-sources',
				'/workspace/text-formatters',
				'/workspace/pages',
				'/workspace/utilities'
			);
			$directories = General::listDirStructure(WORKSPACE, null, true, DOCROOT, $ignore);
			
			// Populate destination options array
			$options = array();
			$options[] = array('/workspace', false, '/workspace');
			if(!empty($directories) && is_array($directories)){
				foreach($directories as $d) {
					$d = '/' . trim($d, '/');
					if(!in_array($d, $ignore)) {
						$options[] = array(
							$d,
							($this->get('file_destination') == $d),
							$d
						);
					}
				}
			}
			
			// Build destinations label
			$label = Widget::Label(__('Destination Directory'));
			
			// Build destination select
			$label->appendChild(
				Widget::Select(
					'fields[' . $this->get('sortorder') . '][file_destination]',
					$options
				)
			);
			
			if(isset($errors['file_destination'])) {
				$group->appendChild(
					Widget::wrapFormElementWithError(
						$label,
						$errors['file_destination']
					)
				);
			}
			else {
				$group->appendChild($label);
			}

			$div = new XMLElement('div');
			// Set custom validators
			$rules = array(
				'HTML'	=> '/\.(?:html|htm)$/i',
				'CSS'	=> '/\.(?:css)$/i',
				'JS'	=> '/\.(?:js)$/i'
			);
			
			// Build container and label
			$label = Widget::Label(__('Validation Rule'));
			$label->appendChild(new XMLElement('i', __('Optional')));
			
			// Build and append input
			$label->appendChild(
				Widget::Input(
					'fields[' . $this->get('sortorder') . '][file_validator]',
					$this->get('file_validator')
				)
			);
			$div->appendChild($label);

			// Build and append validators taglist
			$ul = new XMLElement(
				'ul',
				NULL,
				array('class' => 'tags singular')
			);
			foreach($rules as $name => $rule) {
				$ul->appendChild(new XMLElement(
					'li',
					$name,
					array('class' => $rule)
				));
			}
			$div->appendChild($ul);
			$group->appendChild($div);
			$fieldset->appendChild($group);
			$wrapper->appendChild($fieldset);
			
			/*---------------------------------------------------------------
				Text Options
			---------------------------------------------------------------*/
			
			// Build fieldset and legend
			$fieldset = new XMLElement('fieldset');
			$legend = new XMLElement('legend', __('Text Options'));
			$fieldset->appendChild($legend);
			
			// Build group div
			$group = new XMLElement('div');
			$group->setAttribute('class', 'group');
			
			// Build Formatter select
			$group->appendChild($this->buildFormatterSelect(
				$this->get('text_formatter'),
				'fields[' . $this->get('sortorder') . '][text_formatter]',
				'Text Formatter'
			));
			
			
			// Build mode select
			$label = Widget::Label(__('Mode'));
			
			$options = array(
				array(
					'editable',
					($this->get('text_mode') == 'editable' ? TRUE : FALSE),
					__('Editable')
				),
				array(
					'disabled',
					($this->get('text_mode') == 'disabled' ? TRUE : FALSE),
					__('Disabled')
				),
				array(
					'hidden',
					($this->get('text_mode') == 'hidden' ? TRUE : FALSE),
					__('Hidden')
				)
			);
			
			$select = Widget::Select(
				'fields[' . $this->get('sortorder') . '][text_mode]',
				$options
			);
			$label->appendChild($select);
			
			$group->appendChild($label);
			$fieldset->appendChild($group);
			
			// Build group div
			$group = new XMLElement('div');
			$group->setAttribute('class', 'group');
			
			// Build CDATA checkbox
			$label = Widget::Label();
			$input = Widget::Input(
				'fields[' . $this->get('sortorder') . '][text_cdata]',
				'yes',
				'checkbox'
			);
			if($this->get('text_cdata') == 'yes') {
				$input->setAttribute('checked', 'checked');
			}     
			$label->setValue(
				__('%s Output as CDATA',
					array(
						$input->generate()
					)
				)
			);
			$group->appendChild($label);
			
			// Textarea Size
			$values = $this->_sizes;

			foreach ($values as &$value) {
				$value[1] = $value[0] == $this->get('text_size');
			}

			$label = Widget::Label('Size');
			$label->appendChild(Widget::Select(
				'fields[' . $this->get('sortorder') . '][text_size]', $values
			));

			$group->appendChild($label);
			
			$fieldset->appendChild($group);
			
			$wrapper->appendChild($fieldset);
			
			/*---------------------------------------------------------------
				Standard Options
			---------------------------------------------------------------*/
			$fieldset = new XMLElement('fieldset');
			$div = new XMLElement('div', NULL, array('class' => 'compact'));
			$this->appendRequiredCheckbox($div);
			$this->appendShowColumnCheckbox($div);
			$fieldset->appendChild($div);
			$wrapper->appendChild($fieldset);
		}
		
		public function checkFields(&$errors, $checkForDuplicates=true){
			if(!is_dir(DOCROOT . $this->get('file_destination') . '/')){
				$errors['file_destination'] = __('Directory <code>%s</code> does not exist.', array($this->get('destination')));
			}

			elseif(!is_writable(DOCROOT . $this->get('file_destination') . '/')){
				$errors['file_destination'] = __('Destination folder, <code>%s</code>, is not writable. Please check permissions.', array($this->get('file_destination')));
			}

			Field::checkFields($errors, $checkForDuplicates);
		}

		public function commit(){
			if(!Field::commit()) return false;

			$id = $this->get('id');

			if($id === false) return false;

			$fields = array(
				'field_id' 			=> $id,
				'file_destination'	=> $this->get('file_destination'),
				'file_validator'	=> $this->get('file_validator'),
				'text_formatter'	=> $this->get('text_formatter'),
				'text_cdata'		=> ($this->get('text_cdata') ? $this->get('text_cdata') : 'no'),
				'text_size'			=> $this->get('text_size'),
				'text_mode'			=> ($this->get('text_mode') ? $this->get('text_mode') : 'editable'),
			);
			
			return FieldManager::saveSettings($id, $fields);
		}

	/*-------------------------------------------------------------------------
		Publish:
	-------------------------------------------------------------------------*/

		public function displayPublishPanel(XMLElement &$wrapper, $data = null, $error = null, $prefix = null, $postfix = null, $entry_id = null) {

			// Make sure directory exists
			if(!is_dir(DOCROOT . $this->get('file_destination') . '/')) {
				$error = __(
					'The destination directory, <code>%s</code>, does not exist.',
					array($this->get('file_destination'))
				);
			}
			
			// Make sure directory is writeable
			elseif(!$error && !is_writable(DOCROOT . $this->get('file_destination') . '/')) {
				$error = __(
					'Destination folder, <code>%s</code>, is not writable. Please check permissions.',
					array($this->get('file_destination'))
				);
			}
			
			$mode = $this->get('text_mode');

			// Build label
			$label = Widget::Label($this->get('label'));
			$class = 'file';
			$label->setAttribute('class', $class);
			if($this->get('required') != 'yes') {
				$label->appendChild(new XMLElement('i', __('Optional')));
			}
			$wrapper->appendChild($label);
			
			// Create a frame div
			$div = new XMLElement('span', NULL, array('class' => 'frame'));
			$div->setAttribute(
				'id',
				'fields' . $fieldnamePrefix . '[' . $this->get('element_name') . ']' . $fieldnamePostfix
			);
			
			// Create a span for the file info
			$span = new XMLElement('span', NULL, array('class' => 'file'));
			
			// If file exists
			if($data['file']) {
				
				// If the file's been updated in the meantime
				if(filemtime(WORKSPACE . $data['file']) > $data['timestamp']) {
					$this->updateFromFile($data);
				}
				
				$span->appendChild(
					Widget::Anchor(
						'/workspace' . $data['file'],
						URL . '/workspace' . $data['file']
					)
				);
				$div->appendChild($span);
				
				// If the text is not hidden, display textarea
				if($mode !== 'hidden') {
					$textarea = Widget::Textarea(
						'fields' . $fieldnamePrefix . '[' . $this->get('element_name') . ']' . $fieldnamePostfix,
						20,
						50,
						$data['value_formatted']
					);
					$textarea->setAttribute('class', 'code ' . $this->get('text_size'));
					if($mode == 'disabled') {
						$textarea->setAttribute('disabled', 'disabled');
					}
					$div->appendChild($textarea);
				}
			}
			else {
				// Build input
				$span->appendChild(
					Widget::Input(
						'fields' . $fieldnamePrefix . '[' . $this->get('element_name') . ']' . $fieldnamePostfix,
						$data['file'],
						($data['file'] ? 'hidden' : 'file')
					)
				);
				$div->appendChild($span);
			}

			if($error != NULL) {
				$wrapper->appendChild(
					Widget::wrapFormElementWithError($div, $error)
				);
			}
			else {
				$wrapper->appendChild($div);
			}
		}
		
	/*-------------------------------------------------------------------------
		Input
	-------------------------------------------------------------------------*/
		
		function checkPostFieldData($data, &$message, $entry_id=NULL){

			$message = NULL;

			if(empty($data) || $data['error'] == UPLOAD_ERR_NO_FILE) {

				if($this->get('required') == 'yes'){
					$message = __("'%s' is a required field.", array($this->get('label')));
					return self::__MISSING_FIELDS__;
				}

				return self::__OK__;
			}

			// If it's an array, we're dealing with a file
			if(is_array($data)){
				if(!is_dir(DOCROOT . $this->get('file_destination') . '/')){
					$message = __('The destination directory, <code>%s</code>, does not exist.', array($this->get('file_destination')));
					return self::__ERROR__;
				}

				elseif(!is_writable(DOCROOT . $this->get('file_destination') . '/')){
					$message = __('Destination folder, <code>%s</code>, is not writable. Please check permissions.', array($this->get('file_destination')));
					return self::__ERROR__;
				}

				if($data['error'] != UPLOAD_ERR_NO_FILE && $data['error'] != UPLOAD_ERR_OK){

					switch($data['error']){

						case UPLOAD_ERR_INI_SIZE:
							$message = __('File chosen in "%1$s" exceeds the maximum allowed upload size of %2$s specified by your host.', array($this->get('label'), (is_numeric(ini_get('upload_max_filesize')) ? General::formatFilesize(ini_get('upload_max_filesize')) : ini_get('upload_max_filesize'))));
							break;

						case UPLOAD_ERR_FORM_SIZE:
							$message = __('File chosen in "%1$s" exceeds the maximum allowed upload size of %2$s, specified by Symphony.', array($this->get('label'), General::formatFilesize(Symphony::Configuration()->get('max_upload_size', 'admin'))));
							break;

						case UPLOAD_ERR_PARTIAL:
							$message = __("File chosen in '%s' was only partially uploaded due to an error.", array($this->get('label')));
							break;

						case UPLOAD_ERR_NO_TMP_DIR:
							$message = __("File chosen in '%s' was only partially uploaded due to an error.", array($this->get('label')));
							break;

						case UPLOAD_ERR_CANT_WRITE:
							$message = __("Uploading '%s' failed. Could not write temporary file to disk.", array($this->get('label')));
							break;

						case UPLOAD_ERR_EXTENSION:
							$message = __("Uploading '%s' failed. File upload stopped by extension.", array($this->get('label')));
							break;

					}

					return self::__ERROR_CUSTOM__;
				}
				
				## Sanitize the filename
				$data['name'] = Lang::createFilename($data['name']);

				if($this->get('file_validator') != NULL){
					$rule = $this->get('file_validator');

					if(!General::validateString($data['name'], $rule)){
						$message = __("File chosen in '%s' does not match allowable file types for that field.", array($this->get('label')));
						return self::__INVALID_FIELDS__;
					}

				}

				$abs_path = DOCROOT . '/' . trim($this->get('file_destination'), '/');
				$new_file = $abs_path . '/' . $data['name'];
				$existing_file = NULL;

				if($entry_id){
					$row = Symphony::Database()->fetchRow(0, "SELECT * FROM `tbl_entries_data_".$this->get('id')."` WHERE `entry_id` = '$entry_id' LIMIT 1");
					$existing_file = $abs_path . '/' . basename($row['file'], '/');
				}

				if((strtolower($existing_file) != strtolower($new_file)) && file_exists($new_file)){
					$message = __('A file with the name %1$s already exists in %2$s. Please rename the file first, or choose another.', array($data['name'], $this->get('file_destination')));
					return self::__INVALID_FIELDS__;
				}	

			}
			
			return self::__OK__;

		}

		public function processRawFieldData($data, &$status, &$message=null, $simulate = false, $entry_id = null) {

			$status = self::__OK__;

			// fixes bug where files are deleted, but their database entries are not.
			if($data === NULL) {
				return array(
					'value'				=> NULL,
					'value_formatted'	=> NULL,
					'word_count'		=> NULL,
					'file'				=> NULL,
					'size'				=> NULL,
					'mimetype'			=> NULL,
					'meta'				=> NULL,
					'timestamp'			=> NULL,
				);
			}

			// It's not an array, so we're updating via the textarea
			if(!is_array($data)) {
				$status = self::__OK__;

				// Grab the existing entry data to preserve the other fields
				if(isset($entry_id) && !is_null($entry_id)) {
					$row = Symphony::Database()->fetchRow(0, sprintf(
						"SELECT * FROM `tbl_entries_data_%d` WHERE `entry_id` = %d",
						$this->get('id'),
						$entry_id
					));
					if(!empty($row)) {
						$result = $row;
					}
				}
				
				
				// Set the value based on the textarea content
				$result['value'] = $data;
				$result['value_formatted'] = $this->applyFormatting($data);
				
				// Update the file
				$this->updateFile(WORKSPACE . $result['file'], $data);

				return $result;
			}
			if($simulate) return;

			// Upload the new file
			$abs_path = DOCROOT . '/' . trim($this->get('file_destination'), '/');
			$rel_path = str_replace('/workspace', '', $this->get('file_destination'));
			$existing_file = NULL;

			if(!is_null($entry_id)) {
				$row = Symphony::Database()->fetchRow(0, sprintf(
					"SELECT * FROM `tbl_entries_data_%s` WHERE `entry_id` = %d LIMIT 1",
					$this->get('id'),
					$entry_id
				));

				$existing_file = rtrim($rel_path, '/') . '/' . trim(basename($row['file']), '/');

				// File was removed
				if($data['error'] == UPLOAD_ERR_NO_FILE && !is_null($existing_file) && is_file(WORKSPACE . $existing_file)) {
					General::deleteFile(WORKSPACE . $existing_file);
				}
			}

			if($data['error'] == UPLOAD_ERR_NO_FILE || $data['error'] != UPLOAD_ERR_OK){
				return;
			}

			// Sanitize the filename
			$data['name'] = Lang::createFilename($data['name']);

			if(!General::uploadFile($abs_path, $data['name'], $data['tmp_name'], Symphony::Configuration()->get('write_mode', 'file'))) {

				$message = __('There was an error while trying to upload the file <code>%1$s</code> to the target directory <code>%2$s</code>.', array($data['name'], 'workspace/'.ltrim($rel_path, '/')));
				$status = self::__ERROR_CUSTOM__;
				return;
			}

			$status = self::__OK__;

			$file = rtrim($rel_path, '/') . '/' . trim($data['name'], '/');

			// File has been replaced
			if(!is_null($existing_file) && (strtolower($existing_file) != strtolower($file)) && is_file(WORKSPACE . $existing_file)) {
				General::deleteFile(WORKSPACE . $existing_file);
			}

			// If browser doesn't send MIME type (e.g. .flv in Safari)
			if (strlen(trim($data['type'])) == 0) {
				$data['type'] = 'unknown';
			}
			
			// Get text contents
			$contents = file_get_contents($abs_path . '/' . $data['name']);

			return array(
				'value'				=> $contents,
				'value_formatted'	=> $this->applyFormatting($contents),
				'word_count'		=> General::countWords($contents),
				'file'				=> $file,
				'size'				=> $data['size'],
				'mimetype'			=> $data['type'],
				'meta'				=> serialize(self::getMetaInfo(WORKSPACE . $file, $data['type'])),
				'timestamp'			=> time()
			);

		}

	/*-------------------------------------------------------------------------
		Output:
	-------------------------------------------------------------------------*/
		
		public function fetchIncludableElements() { 
			return array(
				$this->get('element_name') . ': formatted',
				$this->get('element_name') . ': unformatted',
				$this->get('element_name') . ': file-only'
			);
		}

		public function appendFormattedElement(&$wrapper, $data, $encode = false, $mode = null) {
		
			// It is possible an array of NULL data will be passed in. Check for this.
			if(!is_array($data) || !isset($data['file']) || is_null($data['file'])) {
				return;
			}
			
			// If the file's been modified more recently than the entry, update the field data
			if(filemtime(WORKSPACE . $data['file']) > $data['timestamp']) {
				$this->updateFromFile($data);
			}

			// Check content options
			if($mode == 'unformatted') {
				$value = trim($data['value']);
			}
			else {
				$value = trim($data['value_formatted']);
			}

			if($this->get('text_cdata') == 'yes') {
				$value = '<![CDATA[' . $value . ']]>';
			}
			
			// Main XML element
			$item = new XMLElement($this->get('element_name'));

			if($mode != 'file-only') {
			
				// Textual content
				$attributes = array(
					'mode'			=> $mode,
					'word-count'	=> $data['word_count']
				);
			
				$item->appendChild(new XMLElement(
					'content', $value, $attributes
				));
			}
			
			// File information
			$file_element = new XMLElement('file');
			$file = WORKSPACE . $data['file'];
			$file_element->setAttributeArray(array(
				'size' => (file_exists($file) && is_readable($file) ? General::formatFilesize(filesize($file)) : 'unknown'),
			 	'path' => str_replace(WORKSPACE, NULL, dirname(WORKSPACE . $data['file'])),
				'type' => $data['mimetype']
			));

			$file_element->appendChild(new XMLElement('filename', General::sanitize(basename($data['file']))));

			$m = unserialize($data['meta']);

			if(is_array($m) && !empty($m)){
				$file_element->appendChild(new XMLElement('meta', NULL, $m));
			}
			
			$item->appendChild($file_element);

			$wrapper->appendChild($item);
		}


	/*-------------------------------------------------------------------------
		Events:
	-------------------------------------------------------------------------*/
		
		public function applyFormatting($data) {
			if ($this->get('text_formatter') != 'none') {
				
				$tfm = new TextformatterManager($this->_engine);

				$formatter = $tfm->create($this->get('text_formatter'));
				$formatted = $formatter->run($data);
			 	$formatted = preg_replace('/&(?![a-z]{0,4}\w{2,3};|#[x0-9a-f]{2,6};)/i', '&amp;', $formatted);

			 	return $formatted;
			}

			return General::sanitize($data);
		}

		public function updateFromFile($data) {
			$contents = file_get_contents(WORKSPACE . $data['file']);
			$file = $data['file'];
			$data['value'] = $contents;
			$data['value_formatted'] = $this->applyFormatting($contents);
			$data['timestamp'] = time();
		
			Symphony::Database()->update(
				$data,
				'tbl_entries_data_' . $this->get('id'),
				"`file` = '{$file}'"
			);
		}
		
		public function updateFile($path, $content) {
			// TODO Error checking
			General::deleteFile($path);
			General::writeFile($path, $content, Symphony::Configuration()->get('write_mode', 'file'));
		}
	}
