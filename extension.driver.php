<?php

	Class extension_production_mode extends Extension {

		public function install() {
			Symphony::Configuration()->set('enabled', 'no', 'production_mode');
			return Symphony::Configuration()->write();
		}

		public function uninstall() {
			Symphony::Configuration()->remove('production_mode');
		}

		public function getSubscribedDelegates(){
			return array(
				array(
					'page' => '/system/preferences/',
					'delegate' => 'AddCustomPreferenceFieldsets',
					'callback' => 'appendPreferences'
				),
				array(
					'page' => '/system/preferences/',
					'delegate' => 'Save',
					'callback' => '__SavePreferences'
				),
				array(
					'page' => '/frontend/',
					'delegate' => 'FrontendParamsResolve',
					'callback' => '__addParam'
				)
			);
		}

		/**
		 * Append production mode preferences
		 *
		 * @param array $context
		 *  delegate context
		 */
		public function appendPreferences($context) {

			// Create preference group
			$group = new XMLElement('fieldset');
			$group->setAttribute('class', 'settings');
			$group->appendChild(new XMLElement('legend', __('Production Mode')));

			// Append settings
			$label = Widget::Label();
			$input = Widget::Input('settings[production_mode][enabled]', 'yes', 'checkbox');
			if(Symphony::Configuration()->get('enabled', 'production_mode') == 'yes') $input->setAttribute('checked', 'checked');
			$label->setValue($input->generate() . ' ' . __('Enable production mode'));
			$group->appendChild($label);

			// Append help
			$group->appendChild(new XMLElement('p', __("Production mode adds a 'production-mode' parameter of 'production' or 'development' to the Parameter Pool"), array('class' => 'help')));

			// Append new preference group
			$context['wrapper']->appendChild($group);
		}

		/**
		 * Save preferences
		 *
		 * @param array $context
		 *  delegate context
		 */
		public function __SavePreferences($context) {

			// Disable production mode by default
			if(!is_array($context['settings'])) {
				$context['settings'] = array('production_mode' => array('enabled' => 'no'));
			}

			// Disable production mode if it has not been set to 'yes'
			elseif(!isset($context['settings']['production_mode'])) {
				$context['settings']['production_mode'] = array('enabled' => 'no');
			}
		}

		/**
		 * Add production mode to parameter pool
		 *
		 * @param array $context
		 *  delegate context
		 */
		public function __addParam($context) {
			$context['params']['production-mode'] = (Symphony::Configuration()->get('enabled', 'production_mode') == 'yes' ? 'production' : 'development');
		}

	}
