<?php

namespace asw\oxygen\elements;

use asw\oxygen\Oxygen;

/**
 * Elements_I18N_Text
 *
 * Internationalisation text component
 */
class I18N_Text extends Elements {

	public function init() {
		//
	}

	public function afterInit() {
		parent::afterInit();
		$this->removeApplyParamsButton();
	}

	public function name() {
		return 'I18N Text';
	}

	public function slug() {
		return 'i18n-text';
	}


	// Render the output
	public function render( $options, $defaults, $content ) {
		$tag               = isset( $options['i18n_tag'] ) ? esc_attr( $options['i18n_tag'] ) : 'span';
		$text              = isset( $options['i18n_text'] ) ? $options['i18n_text'] : 'Some text';
		$i18n_replace_pipe = isset( $options['i18n_replace_pipe'] ) ? $options['i18n_replace_pipe'] : 'false';

		if ( wp_is_mobile() && $i18n_replace_pipe === 'true' ) {
			$text = str_replace( '|', '<br>', $text );
		}

		// Do not translate inside the builder
		if ( ! Oxygen::rendering_component_inside_builder() ) {
			echo "<${tag} class='oxy-i18n-text'>" . __( $text, Oxygen::get_plugin_textdomain() ) . "</${tag}>";
		} else {
			echo "<${tag} class='oxy-i18n-text'>" . $text . "</${tag}>";
		}
	}

	public function controls() {

		// Text field
		$this->addOptionControl(
			array(
				'type'    => 'textarea',
				'name'    => 'Translatable text',
				'slug'    => 'i18n_text',
				'default' => 'Some text',
			)
		)->rebuildElementOnChange();

		// Copyright
		$this->addOptionControl(
			array(
				'type'  => 'checkbox',
				'name'  => 'Replace \'|\' with new line on mobile',
				'slug'  => 'i18n_replace_pipe',
				'value' => 'false',
			)
		)->rebuildElementOnChange();

		// Tag choice
		$this->addOptionControl(
			array(
				'type'    => 'dropdown',
				'name'    => 'Tag',
				'slug'    => 'i18n_tag',
				'default' => 'span',
			)
		)->setValue(
			array(
				'h1'   => 'h1',
				'h2'   => 'h2',
				'h3'   => 'h3',
				'h4'   => 'h4',
				'h5'   => 'h5',
				'h6'   => 'h6',
				'span' => 'span',
				'div'  => 'div',
				'p'    => 'p',
			)
		)->setDefaultValue( 'span' )->rebuildElementOnChange();

	}

}
