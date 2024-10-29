<?php

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


// check if class already exists
if( !class_exists('acf_field_rest') ) :

    /**
     * Class acf_field_rest
     */
    class acf_field_rest extends acf_field {

        /*
        *  __construct
        *
        *  This function will setup the field type data
        *
        *  @type	function
        *  @date	5/03/2014
        *  @since	5.0.0
        *
        *  @param	$settings
        *  @return	n/a
        */
        function __construct( $settings ) {

            /*
            *  name (string) Single word, no spaces. Underscores allowed
            */
            $this->name = 'rest';

            /*
            *  label (string) Multiple words, can include spaces, visible when selecting a field type
            */
            $this->label = __('Rest', 'acf-rest');

            /*
            *  category (string) basic | content | choice | relational | jquery | layout | CUSTOM GROUP NAME
            */
            $this->category = 'choice';

            /*
            *  defaults (array) Array of default settings which are merged into the field object. These are used later in settings
            */
            $this->defaults = array(
                'endpoint'	=> '',
            );

            /*
            *  l10n (array) Array of strings that are used in JavaScript. This allows JS strings to be translated in PHP and loaded via:
            *  var message = acf._e('rest', 'error');
            */
            $this->l10n = array(
                'error'	=> __('Error! Please enter a higher value', 'acf-rest'),
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
        *  @type	action
        *  @since	3.6
        *  @date	23/01/13
        *
        *  @param	$field (array) the $field being edited
        *  @return	n/a
        */
        function render_field_settings( $field ) {
            /*
            *  acf_render_field_setting
            *
            *  This function will create a setting for your field. Simply pass the $field parameter and an array of field settings.
            *  The array of settings does not require a `value` or `prefix`; These settings are found from the $field array.
            *
            *  More than one setting can be added by copy/paste the above code.
            *  Please note that you must also have a matching $defaults value for the field name
            */

            $this->render_select_endoint_type_field($field);
            $this->render_enpoint_field($field);
            $this->render_checkbox_field($field);
            $this->render_username_field($field);
            $this->render_password_field($field);
            $this->render_select_default_field($field);
        }

        /*
        *  render_field()
        *
        *  Create the HTML interface for your field
        *
        *  @param	$field (array) the $field being rendered
        *
        *  @type	action
        *  @since	3.6
        *  @date	23/01/13
        *
        *  @return	n/a
        */
        function render_field( $field ) {
            $url = $field['endpoint'];

            //Check if endpoint requires authentication
            if($field['checkbox-endpoint'] != '0') {
                //Endpoint requires authentication
                $endpoint_type = $field['endpoint-type'];
                $endpoint_username = $field['endpoint-username'];
                $endpoint_password = $field['endpoint-password'];

                //Check if selected endpoint is a magento endpoint
                switch ($endpoint_type) {
                    case 'magento':
                        $response = $this->AuthenticateMagentoApi($endpoint_username,$endpoint_password);
                        $response_code = wp_remote_retrieve_response_code($response);

                        if($response_code != '200') {
                            echo '<span style="color:red">'. json_decode(wp_remote_retrieve_body($response), true)['message'] . ' Please change your settings.</span>';
                        }

                        $token = json_decode(wp_remote_retrieve_body($response), true);

                        $categories = $this->getMagentoCatagories($token,$url);

                        ?>

                        <select name="<?= esc_attr($field['name']) ?>">
                            <option value="0">Uncategorized</option>
                            <?php foreach ($categories as $option): ?>
                                <option <?= ((int)$field['value'] === $option['value']) ? 'selected' : '' ?>  value="<?= $option['value'] ?>"><?= $option['identifier'] ?></option>
                                <?php foreach ($option['subs'] as $subs): ?>
                                    <option <?= ((int)$field['value'] === $subs['value']) ? 'selected' : '' ?>  value="<?= $subs['value'] ?>">-- <?= $subs['identifier'] ?></option>
                                <?php endforeach; ?>
                            <?php endforeach; ?>

                        </select>

                        <?php

                        break;
                    default:
                        //TODO: Add authtication for other api's
                        $response = wp_remote_get($url);
                }

            } else {
                $response = wp_remote_get($url);

                $response_code = wp_remote_retrieve_response_code($response);

                if ($this->responseSuccess($response_code)) {
                    $options = json_decode(wp_remote_retrieve_body($response), true);
                }
                ?>

                <select name="<?= esc_attr($field['name']) ?>">
                    <option value="0">- Select -</option>
                    <?php foreach ($options as $option): ?>
                        <option <?= ((int)$field['value'] === $option['value']) ? 'selected' : '' ?>  value="<?= $option['value'] ?>"><?php if(isset($option['identifier'])) { echo $option['identifier']; } else { echo $option['label']; } ?></option>
                    <?php endforeach; ?>
                </select>
                <?php
            }
        }

        /**
         * Renders the field where the default value of the select can be chosen
         *
         * @param $field
         * @throws Exception
         */
        private function render_select_endoint_type_field($field)
        {
            $fieldSetting = array(
                'label' => __('Endpoint type', 'acf-rest'),
                'instructions' => __('Choose one of the enpoint options', 'acf-rest'),
                'type' => 'select',
                'name' => 'endpoint-type'
            );

            //Set default options for the available endpoints that this plugin supports.
            $selectOptions= [
                'magento' => 'Magento',
                'other' => 'Other'
            ];

            $fieldSetting['choices'] = $selectOptions;

            acf_render_field_setting($field, $fieldSetting);
        }

        /**
         * Renders the field that accepts the endpoint from which the options of the select will be retrieved
         *
         * @param $field
         */
        private function render_enpoint_field($field)
        {
            acf_render_field_setting($field, array(
                'label' => __('Endpoint', 'acf-rest'),
                'instructions' => __('Choose the endpoint from which the choices for the rest dropdown will be retrieved', 'acf-rest'),
                'type' => 'text',
                'name' => 'endpoint'
            ));
        }

        /**
         * Renders the field that accepts the endpoint from which the options of the select will be retrieved
         *
         * @param $field
         */
        private function render_checkbox_field($field)
        {
            acf_render_field_setting($field, array(
                'label' => __('Required', 'acf-rest'),
                'instructions' => __('Is for this endpoint authentication required', 'acf-rest'),
                'type' => 'true_false',
                'name' => 'checkbox-endpoint'
            ));
        }

        /**
         * Renders the field that adds a username field to the endpoint from which the options of the select will be retrieved
         *
         * @param $field
         */
        private function render_username_field($field)
        {
            acf_render_field_setting($field, array(
                'label' => __('Username', 'acf-rest'),
                'instructions' => __('If it is needed for the endpoint you can add a username in here', 'acf-rest'),
                'type' => 'text',
                'name' => 'endpoint-username'
            ));
        }

        /**
         * Renders the field that adds a password field to the endpoint from which the options of the select will be retrieved
         *
         * @param $field
         */
        private function render_password_field($field)
        {
            acf_render_field_setting($field, array(
                'label' => __('Password', 'acf-rest'),
                'instructions' => __('If it is needed for the endpoint you can add a password in here', 'acf-rest'),
                'type' => 'password',
                'name' => 'endpoint-password'
            ));
        }

        /**
         * Renders the field where the default value of the select can be chosen
         *
         * @param $field
         * @throws Exception
         */
        private function render_select_default_field($field)
        {
            $fieldSetting = array(
                'label' => __('Default Value', 'acf-rest'),
                'instructions' => __('Choose the default value of the select', 'acf-rest'),
                'type' => 'select',
                'name' => 'default'
            );

            $fieldSetting = $this->setSelectOptions($field, $fieldSetting);

            acf_render_field_setting($field, $fieldSetting);
        }

        /**
         * Builds and returns an array with key value pairs that are later used to populate the select
         *
         * @param $endpoint
         * @param $fieldSetting
         * @return mixed
         * @throws Exception
         */
        private function getSelectOptionsArray($endpoint, $fieldSetting)
        {
            $selectOptions = array();
            try {
                $endpointData = json_decode(wp_remote_retrieve_body(wp_remote_get($endpoint)), true);
                $selectOptions['0'] = '- Select -';
                foreach ($endpointData as $option) {
                    if (isset($option['identifier'])) {
                        $selectOptions[$option['value']] = $option['identifier'];
                    } else {
                        $selectOptions[$option['value']] = $option['label'];
                    }
                }
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
            $fieldSetting['choices'] = $selectOptions;
            return $fieldSetting;
        }

        /**
         * Sets the select options when rendering an existing field that already has its endpoint set
         *
         * @param $field
         * @param $fieldSetting
         * @return mixed
         */
        private function setSelectOptions($field, $fieldSetting)
        {
            $fieldData = _acf_get_field_by_key($field['key']);
            $endpoint = $fieldData['endpoint'];

            if($field['checkbox-endpoint'] != '0') {

                if (!is_null($endpoint) && !$endpoint == '') {
                    $endpoint_type = $field['endpoint-type'];
                    $endpoint_username = $field['endpoint-username'];
                    $endpoint_password = $field['endpoint-password'];

                    //Check if selected endpoint is a magento endpoint
                    switch ($endpoint_type) {
                        case 'magento':
                            $response = $this->AuthenticateMagentoApi($endpoint_username,$endpoint_password, $endpoint);
                            $response_code = wp_remote_retrieve_response_code($response);

                            if($response_code != '200') {
                                echo '<span style="color:red">Something went wrong, Please change your settings or contact the administrator. <br /> Extra information: '. json_decode(wp_remote_retrieve_body($response), true)['message'] . '</span>';
                                exit;
                            }

                            $token = json_decode(wp_remote_retrieve_body($response), true);

                            $categories = $this->getMagentoCatagories($token,$endpoint);
                            $selectOptions = array();
                            $selectOptions['0'] = 'Uncategorized';
                            foreach ($categories as $option) {
                                $selectOptions[$option['value']] = $option['identifier'];
                            }
                            $fieldSetting['choices'] = $selectOptions;

                            break;
                        default:
                            //TODO: Add authtication for other api's
                            $response = wp_remote_get($endpoint);
                    }
                }

            } else {

                if (!is_null($endpoint) && !$endpoint == '') {
                    $fieldSetting = $this->getSelectOptionsArray($endpoint, $fieldSetting);
                }
            }


            return $fieldSetting;
        }

        /**
         * Authenticated the Magento API for the call
         *
         * @param $username
         * @param $password
         * @return mixed
         * @throws Exception
         */
        private function AuthenticateMagentoApi($username, $password, $endpoint)
        {
            $base_url = explode('/', $endpoint);
            $auth_url = $base_url['0'] .'//' . $base_url['2'] . '/rest/V1/integration/admin/token';

            $auth_params = [
                'username' => $username,
                'password' => $password
            ];

            $args = [
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode($auth_params)
            ];

            $response =  wp_remote_post($auth_url, $args);

            return $response;

        }

        /**
         * Authenticated the Magento API for the call
         *
         * @param $token
         * @return mixed
         * @throws Exception
         */
        private function getMagentoCatagories($token,$url)
        {
            $args = [
                'headers' => ['Authorization' => 'Bearer ' . $token]
            ];

            $response = json_decode(wp_remote_retrieve_body(wp_remote_get($url, $args)), true);

            $categories = [];
            if (isset($response['children_data'])) {
                foreach ($response['children_data'] as $category) {

                    if (!empty($category['children_data'])) {
                        $subcategories = [];
                        foreach ($category['children_data'] as $subs) {

                            if ($subs['parent_id'] === $category['id']) {
                                $subcategories[] = [
                                    'value' => $subs['id'],
                                    'identifier' => $subs['name']
                                ];
                            }
                        }
                    }

                    $categories[] = [
                        'value' => $category['id'],
                        'identifier' => $category['name'],
                        'subs' => $subcategories
                    ];

                }
            } else {
                echo $response['message'];
            }

            return $categories;

        }

        /**
         * @param $response_code
         * @return bool
         */
        private function responseSuccess($response_code)
        {
            return $response_code === 200;
        }
    }

// initialize
    new acf_field_rest( $this->settings );

// class_exists check
endif;

?>