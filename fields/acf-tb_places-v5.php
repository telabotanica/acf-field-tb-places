<?php

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


// check if class already exists
if( !class_exists('acf_field_tb_places') ) :


class acf_field_tb_places extends acf_field {


  /*
  *  __construct
  *
  *  This function will setup the field type data
  *
  *  @type  function
  *  @date  5/03/2014
  *  @since  5.0.0
  *
  *  @param  n/a
  *  @return  n/a
  */

  function __construct( $settings ) {

    /*
    *  name (string) Single word, no spaces. Underscores allowed
    */

    $this->name = 'tb_places';


    /*
    *  label (string) Multiple words, can include spaces, visible when selecting a field type
    */

    $this->label = __('Tb Places', 'acf-tb_places');


    /*
    *  category (string) basic | content | choice | relational | jquery | layout | CUSTOM GROUP NAME
    */

    $this->category = 'content';


    /*
    *  defaults (array) Array of default settings which are merged into the field object. These are used later in settings
    */

    $this->defaults = array(
      'tb_type'      => 'all',
      'tb_countries' => ''
    );


    /*
    *  l10n (array) Array of strings that are used in JavaScript. This allows JS strings to be translated in PHP and loaded via:
    *  var message = acf._e('tb_places', 'error');
    */

    $this->l10n = array(
      'error'  => __('Error! Please enter a higher value', 'acf-tb_places'),
    );


    /*
    *  settings (array) Store plugin settings (url, path, version) as a reference for later use with assets
    */

    $this->settings = $settings;


    // do not delete!
      parent::__construct();

  }


  /*
  *  render_field_settings()
  *
  *  Create extra settings for your field. These are visible when editing a field
  *
  *  @type  action
  *  @since  3.6
  *  @date  23/01/13
  *
  *  @param  $field (array) the $field being edited
  *  @return  n/a
  */

  function render_field_settings( $field ) {

    /*
    *  acf_render_field_setting
    *
    *  This function will create a setting for your field. Simply pass the $field parameter and an array of field settings.
    *  The array of settings does not require a `value` or `prefix`; These settings are found from the $field array.
    *
    *  More than one setting can be added by copy/paste the above code.
    *  Please note that you must also have a matching $defaults value for the field name (font_size)
    */

    acf_render_field_setting( $field, array(
      'label'         => __('Type','acf-tb_places'),
      'instructions'  => __('Restrict the search results to a specific type','acf-tb_places'),
      'type'          => 'select',
      'name'          => 'tb_type',
      'choices'       => array(
        'all'          => __('All types','acf-tb_places'),
        'city'         => __('Cities','acf-tb_places'),
        'country'      => __('Countries','acf-tb_places'),
        'address'      => __('Addresses','acf-tb_places'),
        'townhall'     => __('Townhalls','acf-tb_places')
      ),
      'default_value' => array(
        'all'
      ),
      'allow_null' => 0,
      'multiple' => 0
    ));

    acf_render_field_setting( $field, array(
      'label'         => __('Countries', 'acf-tb_places'),
      'instructions'  => __('Change the countries to search in. Add a list of two-letters country codes (ISO 639-1) separated by commas.','acf-tb_places'),
      'type'          => 'text',
      'name'          => 'tb_countries',
      'default_value' => ''
    ));

  }



  /*
  *  render_field()
  *
  *  Create the HTML interface for your field
  *
  *  @param  $field (array) the $field being rendered
  *
  *  @type  action
  *  @since  3.6
  *  @date  23/01/13
  *
  *  @param  $field (array) the $field being edited
  *  @return  n/a
  */

  function render_field( $field ) {


    /*
    *  Review the data of $field.
    *  This will show what data is available
    */

    // echo '<pre>';
    //   print_r( $field );
    // echo '</pre>';

    echo sprintf(
      '<input type="hidden" name="%s" value="%s" data-tb-type="%s" data-tb-countries="%s" />',
      esc_attr( $field['name'] ),
      esc_attr( $field['value'] ),
      esc_attr( $field['tb_type'] !== 'all' ? $field['tb_type'] : '' ),
      esc_attr( $field['tb_countries'] )
    );
    ?>
    <div class="input-search-container">
        <input class="tb-places" type="search" autocomplete="off">
        <div class="tb-places-search-icon"><i class="acf-icon -search"></i></div>
        <button class="tb-places-close tb-hidden"><i class="acf-icon -cancel"></i></button>
    </div>
    <div class="tb-places-results-container tb-hidden">
        <ul class="tb-places-results"></ul>
    </div>
    <?php
  }


  /*
  *  input_admin_enqueue_scripts()
  *
  *  This action is called in the admin_enqueue_scripts action on the edit screen where your field is created.
  *  Use this action to add CSS + JavaScript to assist your render_field() action.
  *
  *  @type  action (admin_enqueue_scripts)
  *  @since  3.6
  *  @date  23/01/13
  *
  *  @param  n/a
  *  @return  n/a
  */

  function input_admin_enqueue_scripts() {

    // vars
    $url = $this->settings['url'];
    $version = $this->settings['version'];


    // register & include JS
    /*wp_register_script( 'acf-input-tb_places-lib', "https://cdn.jsdelivr.net/places.js/1/places.min.js", array('acf-input'), $version );
    wp_enqueue_script('acf-input-tb_places-lib');*/
    wp_register_script( 'acf-input-tb_places-lib', "{$url}assets/js/locality.js", array('acf-input'), $version );
    wp_enqueue_script('acf-input-tb_places-lib');
    wp_register_script( 'acf-input-tb_places', "{$url}assets/js/input.js", array('acf-input'), $version );
    wp_enqueue_script('acf-input-tb_places');


    // register & include CSS
    wp_register_style( 'acf-input-tb_places', "{$url}assets/css/input.css", array('acf-input'), $version );
    wp_enqueue_style('acf-input-tb_places');

  }


  /*
  *  input_admin_head()
  *
  *  This action is called in the admin_head action on the edit screen where your field is created.
  *  Use this action to add CSS and JavaScript to assist your render_field() action.
  *
  *  @type  action (admin_head)
  *  @since  3.6
  *  @date  23/01/13
  *
  *  @param  n/a
  *  @return  n/a
  */

  /*

  function input_admin_head() {



  }

  */


  /*
     *  input_form_data()
     *
     *  This function is called once on the 'input' page between the head and footer
     *  There are 2 situations where ACF did not load during the 'acf/input_admin_enqueue_scripts' and
     *  'acf/input_admin_head' actions because ACF did not know it was going to be used. These situations are
     *  seen on comments / user edit forms on the front end. This function will always be called, and includes
     *  $args that related to the current screen such as $args['post_id']
     *
     *  @type  function
     *  @date  6/03/2014
     *  @since  5.0.0
     *
     *  @param  $args (array)
     *  @return  n/a
     */

  /*

     function input_form_data( $args ) {

     }

  */

  /*
  *  input_admin_footer()
  *
  *  This action is called in the admin_footer action on the edit screen where your field is created.
  *  Use this action to add CSS and JavaScript to assist your render_field() action.
  *
  *  @type  action (admin_footer)
  *  @since  3.6
  *  @date  23/01/13
  *
  *  @param  n/a
  *  @return  n/a
  */

  /*

  function input_admin_footer() {



  }

  */


  /*
  *  field_group_admin_enqueue_scripts()
  *
  *  This action is called in the admin_enqueue_scripts action on the edit screen where your field is edited.
  *  Use this action to add CSS + JavaScript to assist your render_field_options() action.
  *
  *  @type  action (admin_enqueue_scripts)
  *  @since  3.6
  *  @date  23/01/13
  *
  *  @param  n/a
  *  @return  n/a
  */

  /*

  function field_group_admin_enqueue_scripts() {

  }

  */


  /*
  *  field_group_admin_head()
  *
  *  This action is called in the admin_head action on the edit screen where your field is edited.
  *  Use this action to add CSS and JavaScript to assist your render_field_options() action.
  *
  *  @type  action (admin_head)
  *  @since  3.6
  *  @date  23/01/13
  *
  *  @param  n/a
  *  @return  n/a
  */

  /*

  function field_group_admin_head() {

  }

  */


  /*
  *  load_value()
  *
  *  This filter is applied to the $value after it is loaded from the db
  *
  *  @type  filter
  *  @since  3.6
  *  @date  23/01/13
  *
  *  @param  $value (mixed) the value found in the database
  *  @param  $post_id (mixed) the $post_id from which the value was loaded
  *  @param  $field (array) the field array holding all the field options
  *  @return  $value
  */

  function load_value( $value, $post_id, $field ) {

    // Compatibility with Google Maps
    if ( is_array( $value ) && array_key_exists( 'address', $value ) ) {

      $value['value'] = $value['address'];
      unset($value['address']);
      $value = json_encode($value);

    }

    return $value;

  }


  /*
  *  update_value()
  *
  *  This filter is applied to the $value before it is saved in the db
  *
  *  @type  filter
  *  @since  3.6
  *  @date  23/01/13
  *
  *  @param  $value (mixed) the value found in the database
  *  @param  $post_id (mixed) the $post_id from which the value was loaded
  *  @param  $field (array) the field array holding all the field options
  *  @return  $value
  */

  /*

  function update_value( $value, $post_id, $field ) {

    return $value;

  }

  */


  /*
  *  format_value()
  *
  *  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
  *
  *  @type  filter
  *  @since  3.6
  *  @date  23/01/13
  *
  *  @param  $value (mixed) the value which was loaded from the database
  *  @param  $post_id (mixed) the $post_id from which the value was loaded
  *  @param  $field (array) the field array holding all the field options
  *
  *  @return  $value (mixed) the modified value
  */

  function format_value( $value, $post_id, $field ) {

    // bail early if no value
    if( empty($value) ) {

      return $value;

    }

    $value = json_decode($value);

    // return
    return $value;
  }


  /*
  *  validate_value()
  *
  *  This filter is used to perform validation on the value prior to saving.
  *  All values are validated regardless of the field's required setting. This allows you to validate and return
  *  messages to the user if the value is not correct
  *
  *  @type  filter
  *  @date  11/02/2014
  *  @since  5.0.0
  *
  *  @param  $valid (boolean) validation status based on the value and the field's required setting
  *  @param  $value (mixed) the $_POST value
  *  @param  $field (array) the field array holding all the field options
  *  @param  $input (string) the corresponding input name for $_POST value
  *  @return  $valid
  */

  function validate_value( $valid, $value, $field, $input ){

    // Basic usage
    // if( $value < $field['custom_minimum_setting'] )
    // {
    //   $valid = false;
    // }

    // Advanced usage
    // if( $value < $field['custom_minimum_setting'] )
    // {
    //   $valid = __('The value is too little!','acf-tb_places'),
    // }


    // return
    return $valid;

  }


  /*
  *  delete_value()
  *
  *  This action is fired after a value has been deleted from the db.
  *  Please note that saving a blank value is treated as an update, not a delete
  *
  *  @type  action
  *  @date  6/03/2014
  *  @since  5.0.0
  *
  *  @param  $post_id (mixed) the $post_id from which the value was deleted
  *  @param  $key (string) the $meta_key which the value was deleted
  *  @return  n/a
  */

  /*

  function delete_value( $post_id, $key ) {



  }

  */


  /*
  *  load_field()
  *
  *  This filter is applied to the $field after it is loaded from the database
  *
  *  @type  filter
  *  @date  23/01/2013
  *  @since  3.6.0
  *
  *  @param  $field (array) the field array holding all the field options
  *  @return  $field
  */

  /*

  function load_field( $field ) {

    return $field;

  }

  */


  /*
  *  update_field()
  *
  *  This filter is applied to the $field before it is saved to the database
  *
  *  @type  filter
  *  @date  23/01/2013
  *  @since  3.6.0
  *
  *  @param  $field (array) the field array holding all the field options
  *  @return  $field
  */

  /*

  function update_field( $field ) {

    return $field;

  }

  */


  /*
  *  delete_field()
  *
  *  This action is fired after a field is deleted from the database
  *
  *  @type  action
  *  @date  11/02/2014
  *  @since  5.0.0
  *
  *  @param  $field (array) the field array holding all the field options
  *  @return  n/a
  */

  /*

  function delete_field( $field ) {



  }

  */


}


// initialize
new acf_field_tb_places( $this->settings );


// class_exists check
endif;

?>
