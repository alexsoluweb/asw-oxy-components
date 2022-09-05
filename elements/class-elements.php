<?php

namespace asw\oxygen\elements;

use OxyEl;

// Base setup for custom elements
class Elements extends OxyEl {

	public function __construct() {

		// Init our global settings
		$this->lws_oxy_el_settings = array(
			'url'         => plugin_dir_url( __FILE__ ),
			'assets_url'  => plugin_dir_url( __FILE__ ) . 'assets',
			'path'        => plugin_dir_path( __FILE__ ),
			'assets_path' => plugin_dir_path( __FILE__ ) . 'assets',
		);

		parent::__construct();
	}

	public function init() {
		//
	}

	public function afterInit() {
		//
	}

}
