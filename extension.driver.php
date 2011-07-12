<?php

	Class extension_Textupload extends Extension {

		public function about() {
			return array(
				'name' => 'Field: Text Upload',
				'version' => '0.3',
				'release-date' => '2011-07-12',
				'author' => array(
					'name' => 'Craig Zheng',
					'email' => 'craig@symphony-cms.com'
				),
				'description'	=> 'Upload and parse text.'
			);
		}
		
		public function getSubscribedDelegates() {
			return array(
				array(
					'page'		=> '/backend/',
					'delegate'	=> 'InitaliseAdminPageHead',
					'callback'	=> 'loadFormAssets'
				),
			);
		}

		public function uninstall() {
			if(parent::uninstall() == true) {
				Symphony::Database()->query("DROP TABLE `tbl_fields_textupload`");
				return true;
			}

			return false;
		}

		public function install() {

			try{
				Symphony::Database()->query("CREATE TABLE IF NOT EXISTS `tbl_fields_textupload` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`field_id` int(11) unsigned NOT NULL,
					`file_destination` VARCHAR(255) DEFAULT NULL,
					`file_validator` VARCHAR(255) DEFAULT NULL,
					`text_formatter` VARCHAR(255) DEFAULT NULL,
					`text_cdata` ENUM('yes', 'no') DEFAULT 'no',
					`text_size` ENUM('small', 'medium', 'large', 'huge') DEFAULT 'medium',
					`text_mode` ENUM('editable', 'disabled', 'hidden') DEFAULT 'editable',
				PRIMARY KEY  (`id`),
				KEY `field_id` (`field_id`)
				)");
			}
			catch(Exception $e) {
				return false;
			}

			return true;
		}
		
		public function loadFormAssets($context) {	
			$page = $context['parent']->Page;
			
			$callback = Administration::instance()->getPageCallback();
			
			if ($page instanceof contentPublish and in_array($page->_context['page'], array('new', 'edit'))) {
				
				$page->addStylesheetToHead(
					URL . '/extensions/textupload/assets/textupload.publish.css',
					'screen',
					991
				);
				$page->addScriptToHead(
					URL . '/extensions/textupload/assets/textupload.publish.js',
					992
				);
			}
			
		}	

	}
