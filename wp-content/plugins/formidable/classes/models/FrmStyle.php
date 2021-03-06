<?php
class FrmStyle {
    public $number = false;	// Unique ID number of the current instance.
	public $id = 0; // the id of the post

	/**
	 * @param int|string $id The id of the stylsheet or 'default'
	 */
	public function __construct( $id = 0 ) {
        $this->id = $id;
    }

    public function get_new() {
		$this->id = 0;

        $max_slug_value = pow(36, 6);
        $min_slug_value = 37; // we want to have at least 2 characters in the slug
        $key = base_convert( rand($min_slug_value, $max_slug_value), 10, 36 );

        $style = array(
            'post_type'     => FrmStylesController::$post_type,
            'ID'            => '',
            'post_title'    => __( 'New Style', 'formidable' ),
            'post_name'     => $key,
            'post_content'  => $this->get_defaults(),
            'menu_order'    => '',
            'post_status'   => 'publish',
        );

        return (object) $style;
    }

	public function save( $settings ) {
		return FrmAppHelper::save_settings( $settings, 'frm_styles' );
    }

	public function duplicate( $id ) {
        // duplicating is a pro feature
    }

    public function update( $id = 'default' ) {
 		$all_instances = $this->get_all();

 		if ( empty($id) ) {
 		     $new_style = (array) $this->get_new();
 		     $all_instances[] = $new_style;
 		}

        $action_ids = array();

 		foreach ( $all_instances as $number => $new_instance ) {
 			$new_instance = stripslashes_deep( (array) $new_instance);
 			$this->id = $new_instance['ID'];
 			if ( $id != $this->id || ! $_POST || ! isset($_POST['frm_style_setting']) ) {
				$all_instances[ $number ] = $new_instance;

 			    if ( $new_instance['menu_order'] && $_POST && empty($_POST['prev_menu_order']) && isset($_POST['frm_style_setting']['menu_order']) ) {
 			        // this style was set to default, so remove default setting on previous default style
 			        $new_instance['menu_order'] = 0;
 			        $action_ids[] = $this->save($new_instance);
 			    }

 			    // don't continue if not saving this style
 			    continue;
 			}

 			$new_instance['post_title'] = sanitize_text_field( $_POST['frm_style_setting']['post_title'] );
 			$new_instance['post_content'] = $_POST['frm_style_setting']['post_content'];
 			$new_instance['post_type']  = FrmStylesController::$post_type;
            $new_instance['post_status']  = 'publish';
			$new_instance['menu_order']  = isset( $_POST['frm_style_setting']['menu_order'] ) ? absint( $_POST['frm_style_setting']['menu_order'] ) : 0;

            if ( empty($id) ) {
                $new_instance['post_name'] = $new_instance['post_title'];
            }

            $default_settings = $this->get_defaults();

            foreach ( $default_settings as $setting => $default ) {
				if ( strpos( $setting, 'color' ) !== false || in_array( $setting, array( 'error_bg', 'error_border', 'error_text' ) ) ) {
                    //if is a color
					$new_instance['post_content'][ $setting ] = str_replace( '#', '', $new_instance['post_content'][ $setting ] );
				} else if ( in_array( $setting, array( 'submit_style', 'important_style', 'auto_width' ) ) && ! isset( $new_instance['post_content'][ $setting ] ) ) {
					$new_instance['post_content'][ $setting ] = 0;
                }
            }

			$all_instances[ $number ] = $new_instance;

            $action_ids[] = $this->save($new_instance);

 		}

 		$this->save_settings($all_instances);

 		return $action_ids;
 	}

    /**
     * Create static css file
     */
	public function save_settings( $styles ) {
		$filename = FrmAppHelper::plugin_path() . '/css/custom_theme.css.php';
		update_option( 'frm_last_style_update', date('njGi') );

        if ( ! is_file($filename) ) {
            return;
        }

        $defaults = $this->get_defaults();
        $uploads = wp_upload_dir();
		$target_path = $uploads['basedir'] . '/formidable';
		$needed_dirs = array( $target_path, $target_path . '/css' );
        $dirs_exist = true;

        $saving = true;
		$css = '/* ' . __( 'WARNING: Any changes made to this file will be lost when your Formidable settings are updated', 'formidable' ) . ' */' . "\n";

        ob_start();
        $frm_style = $this;
        include($filename);
		$css .= preg_replace( '/\/\*(.|\s)*?\*\//', '', str_replace( array( "\r\n", "\r", "\n", "\t", '    ' ), '', ob_get_contents() ) );
        ob_end_clean();

        $access_type = get_filesystem_method();
        if ( $access_type === 'direct' ) {
			$creds = request_filesystem_credentials( site_url() . '/wp-admin/', '', false, false, array() );
		} else {
			$creds = $this->get_ftp_creds( $access_type );
		}

		if ( ! empty( $creds ) ) {
        	// initialize the API
        	if ( ! WP_Filesystem( $creds ) ) {
        		// any problems and we exit
        		$dirs_exist = false;
			}

            if ( $dirs_exist ) {
	        	global $wp_filesystem;

            	$chmod_dir = defined('FS_CHMOD_DIR') ? FS_CHMOD_DIR : ( fileperms( ABSPATH ) & 0777 | 0755 );
            	$chmod_file = defined('FS_CHMOD_FILE') ? FS_CHMOD_FILE : ( fileperms( ABSPATH . 'index.php' ) & 0777 | 0644 );

                // Create the directories if need be:
            	foreach ( $needed_dirs as $_dir ) {
                    // Only check to see if the Dir exists upon creation failure. Less I/O this way.
            		if ( ! $wp_filesystem->mkdir( $_dir, $chmod_dir ) && ! $wp_filesystem->is_dir( $_dir ) ) {
            			$dirs_exist = false;
                    }
            	}

				$index_path = $target_path . '/index.php';
                $wp_filesystem->put_contents( $index_path, "<?php\n// Silence is golden.\n?>", $chmod_file );

                // only write the file if the folders exist
                if ( $dirs_exist ) {
					$css_file = $target_path . '/css/formidablepro.css';
                    $wp_filesystem->put_contents( $css_file, $css, $chmod_file );
                }
            }
        }

        update_option('frmpro_css', $css);

        delete_transient('frmpro_css');
        set_transient('frmpro_css', $css);
	}

	private function get_ftp_creds( $type ) {
		$credentials = get_option( 'ftp_credentials', array( 'hostname' => '', 'username' => '' ) );

		$credentials['hostname'] = defined('FTP_HOST') ? FTP_HOST : $credentials['hostname'];
		$credentials['username'] = defined('FTP_USER') ? FTP_USER : $credentials['username'];
		$credentials['password'] = defined('FTP_PASS') ? FTP_PASS : '';

		// Check to see if we are setting the public/private keys for ssh
		$credentials['public_key'] = defined('FTP_PUBKEY') ? FTP_PUBKEY : '';
		$credentials['private_key'] = defined('FTP_PRIKEY') ? FTP_PRIKEY : '';

		// Sanitize the hostname, Some people might pass in odd-data:
		$credentials['hostname'] = preg_replace( '|\w+://|', '', $credentials['hostname'] ); //Strip any schemes off

		if ( strpos( $credentials['hostname'], ':' ) ) {
			list( $credentials['hostname'], $credentials['port'] ) = explode( ':', $credentials['hostname'], 2 );
			if ( ! is_numeric( $credentials['port'] ) ) {
				unset( $credentials['port'] );
			}
		} else {
			unset( $credentials['port'] );
		}

		if ( ( defined( 'FTP_SSH' ) && FTP_SSH ) || ( defined( 'FS_METHOD' ) && 'ssh2' == FS_METHOD ) ) {
			$credentials['connection_type'] = 'ssh';
		} else if ( ( defined( 'FTP_SSL' ) && FTP_SSL ) && 'ftpext' == $type ) {
			//Only the FTP Extension understands SSL
			$credentials['connection_type'] = 'ftps';
		} else if ( ! isset( $credentials['connection_type'] ) ) {
			//All else fails (And it's not defaulted to something else saved), Default to FTP
			$credentials['connection_type'] = 'ftp';
		}

		$has_creds = ( ! empty( $credentials['password'] ) && ! empty( $credentials['username'] ) && ! empty( $credentials['hostname'] ) );
		$can_ssh = ( 'ssh' == $credentials['connection_type'] && ! empty( $credentials['public_key'] ) && ! empty( $credentials['private_key'] ) );
		if ( $has_creds || $can_ssh ) {
			$stored_credentials = $credentials;
			if ( ! empty( $stored_credentials['port'] ) ) {
				//save port as part of hostname to simplify above code.
				$stored_credentials['hostname'] .= ':' . $stored_credentials['port'];
			}

			unset( $stored_credentials['password'], $stored_credentials['port'], $stored_credentials['private_key'], $stored_credentials['public_key'] );

			return $credentials;
		}

		return false;
	}

	public function destroy( $id ) {
        return wp_delete_post($id);
    }

    public function get_one() {
        if ( 'default' == $this->id ) {
            $style = $this->get_default_style();
            if ( $style ) {
                $this->id = $style->ID;
            } else {
                $this->id = 0;
            }
            return $style;
        }

        $style = get_post($this->id);

        if ( ! $style ) {
            return $style;
        }

        $style->post_content = FrmAppHelper::maybe_json_decode($style->post_content);

        $default_values = $this->get_defaults();

        // fill default values
        $style->post_content = $this->override_defaults($style->post_content);
        $style->post_content = wp_parse_args( $style->post_content, $default_values);

        return $style;
    }

    public function get_all( $orderby = 'title', $order = 'ASC', $limit = 99 ) {
        $post_atts = array(
			'post_type'   => FrmStylesController::$post_type,
			'post_status' => 'publish',
			'numberposts' => $limit,
			'orderby'     => $orderby,
			'order'       => $order,
        );

        $temp_styles = FrmAppHelper::check_cache(serialize($post_atts), 'frm_styles', $post_atts, 'get_posts');

        if ( empty($temp_styles) ) {
            global $wpdb;
            // make sure there wasn't a conflict with the query
			$query = $wpdb->prepare( 'SELECT * FROM ' . $wpdb->posts . ' WHERE post_type=%s AND post_status=%s ORDER BY post_title ASC LIMIT 99', FrmStylesController::$post_type, 'publish' );
            $temp_styles = FrmAppHelper::check_cache('frm_backup_style_check', 'frm_styles', $query, 'get_results');

            if ( empty($temp_styles) ) {
                // create a new style if there are none
         		$new = $this->get_new();
         		$new->post_title = $new->post_name = __( 'Formidable Style', 'formidable' );
         		$new->menu_order = 1;
         		$new = $this->save( (array) $new);
         		$this->update('default');

                $post_atts['include'] = $new;

                $temp_styles = get_posts( $post_atts );
            }
        }

        $default_values = $this->get_defaults();
        $default_style = false;

        $styles = array();
        foreach ( $temp_styles as $style ) {
            $this->id = $style->ID;
            if ( $style->menu_order ) {
                if ( $default_style ) {
                    // only return one default
                    $style->menu_order = 0;
                } else {
                    // check for a default style
                    $default_style = $style->ID;
                }
            }

            $style->post_content = FrmAppHelper::maybe_json_decode($style->post_content);

            // fill default values
            $style->post_content = $this->override_defaults($style->post_content);
            $style->post_content = wp_parse_args( $style->post_content, $default_values);

			$styles[ $style->ID ] = $style;
        }

        if ( ! $default_style ) {
            $default_style = reset($styles);
			$styles[ $default_style->ID ]->menu_order = 1;
        }

        return $styles;
    }

	public function get_default_style( $styles = null ) {
        if ( ! isset($styles) ) {
			$styles = $this->get_all( 'menu_order', 'DESC', 1 );
        }

        foreach ( $styles as $style ) {
            if ( $style->menu_order ) {
                return $style;
            }
        }
    }

	public function override_defaults( $settings ) {
	    if ( ! is_array($settings) ) {
	        return $settings;
	    }

	    $settings['line_height'] = ( ! isset($settings['field_height']) || $settings['field_height'] == '' || $settings['field_height'] == 'auto') ? 'normal' : $settings['field_height'];

	    if ( ! isset($settings['form_desc_size']) && isset($settings['description_font_size']) ) {
	        $settings['form_desc_size'] = $settings['description_font_size'];
	        $settings['form_desc_color'] = $settings['description_color'];
	        $settings['title_color'] = $settings['label_color'];
	    }

	    if ( ! isset($settings['section_color']) && isset($settings['label_color']) ) {
	        $settings['section_color'] = $settings['label_color'];
	        $settings['section_border_color'] = $settings['border_color'];
	    }

	    if ( ! isset($settings['submit_hover_bg_color']) && isset($settings['submit_bg_color']) ) {
	        $settings['submit_hover_bg_color'] = $settings['submit_bg_color'];
	        $settings['submit_hover_color'] = $settings['submit_text_color'];
	        $settings['submit_hover_border_color'] = $settings['submit_border_color'];

	        $settings['submit_active_bg_color'] = $settings['submit_bg_color'];
	        $settings['submit_active_color'] = $settings['submit_text_color'];
            $settings['submit_active_border_color'] = $settings['submit_border_color'];
	    }

	    return $settings;
	}

	public function get_defaults() {
        return array(
            'theme_css'         => 'ui-lightness',
            'theme_name'        => 'UI Lightness',

			'center_form'		=> '',
            'form_width'        => '100%',
            'form_align'        => 'left',
            'direction'         => is_rtl() ? 'rtl' : 'ltr',
            'fieldset'          => '0px',
            'fieldset_color'    => '000000',
            'fieldset_padding'  => '0 0 15px 0',
            'fieldset_bg_color' => '',

            'title_size'        => '20px',
            'title_color'       => '444444',
			'title_margin_top'  => '10px',
			'title_margin_bottom' => '10px',
            'form_desc_size'    => '14px',
            'form_desc_color'   => '666666',
			'form_desc_margin_top' => '10px',
			'form_desc_margin_bottom' => '25px',

            'font'              => '"Lucida Grande","Lucida Sans Unicode",Tahoma,sans-serif',
            'font_size'         => '14px',
            'label_color'       => '444444',
            'weight'            => 'bold',
            'position'          => 'none',
            'align'             => 'left',
            'width'             => '150px',
            'required_color'    => 'B94A48',
            'required_weight'   => 'bold',
            'label_padding'     => '0 0 3px 0',

            'description_font_size' => '12px',
            'description_color' => '666666',
            'description_weight' => 'normal',
            'description_style' => 'normal',
            'description_align' => 'left',

            'field_font_size'   => '14px',
            'field_height' 		=> '32px',
            'line_height'		=> 'normal',
            'field_width'       => '100%',
            'auto_width'        => false,
            'field_pad'         => '6px 10px',
            'field_margin'      => '20px',
			'field_weight' => 'normal',
            'text_color'        => '555555',
            //'border_color_hv'   => 'cccccc',
            'border_color'      => 'cccccc',
            'field_border_width' => '1px',
            'field_border_style' => 'solid',

            'bg_color'          => 'ffffff',
            //'bg_color_hv'       => 'ffffff',
			'remove_box_shadow' => '',
            'bg_color_active'   => 'ffffff',
			'border_color_active' => '66afe9',
			'remove_box_shadow_active' => '',
            'text_color_error'  => '444444',
            'bg_color_error'    => 'ffffff',
			'border_color_error' => 'B94A48',
			'border_width_error' => '1px',
			'border_style_error' => 'solid',
            'bg_color_disabled' => 'ffffff',
            'border_color_disabled' => 'E5E5E5',
            'text_color_disabled' => 'A1A1A1',

            'radio_align'       => 'block',
            'check_align'       => 'block',
            'check_font_size'   => '13px',
            'check_label_color' => '444444',
            'check_weight'      => 'normal',

            'section_font_size' => '18px',
            'section_color'     => '444444',
            'section_weight'    => 'bold',
            'section_pad'       => '15px 0 3px 0',
            'section_mar_top'   => '15px',
			'section_mar_bottom' => '12px',
            'section_bg_color'  => '',
            'section_border_color' => 'e8e8e8',
            'section_border_width' => '2px',
            'section_border_style' => 'solid',
            'section_border_loc' => '-top',
            'collapse_icon'     => '6',
            'collapse_pos'      => 'after',
            'repeat_icon'       => '1',

            'submit_style'      => false,
            'submit_font_size'  => '14px',
            'submit_width'      => 'auto',
            'submit_height'     => 'auto',
            'submit_bg_color'   => 'ffffff',
            'submit_border_color' => 'cccccc',
            'submit_border_width' => '1px',
            'submit_text_color' => '444444',
            'submit_weight'     => 'normal',
            'submit_border_radius' => '4px',
            'submit_bg_img'     => '',
            'submit_margin'     => '10px',
            'submit_padding'    => '6px 11px',
            'submit_shadow_color' => 'eeeeee',
            'submit_hover_bg_color' => 'efefef',
            'submit_hover_color' => '444444',
            'submit_hover_border_color' => 'cccccc',
            'submit_active_bg_color' => 'efefef',
            'submit_active_color' => '444444',
            'submit_active_border_color' => 'cccccc',

            'border_radius'     => '4px',
            'error_bg'          => 'F2DEDE',
            'error_border'      => 'EBCCD1',
            'error_text'        => 'B94A48',
            'error_font_size'   => '14px',

            'success_bg_color'  => 'DFF0D8',
            'success_border_color' => 'D6E9C6',
            'success_text_color' => '468847',
            'success_font_size' => '14px',

            'important_style'   => false,

            'custom_css'        => '',
        );
    }

	public function get_field_name( $field_name, $post_field = 'post_content' ) {
		return 'frm_style_setting' . ( empty( $post_field ) ? '' : '[' . $post_field . ']' ) . '[' . $field_name . ']';
	}

	public static function get_bold_options() {
		return array(
			100 => 100, 200 => 200, 300 => 300,
			'normal' => __( 'normal', 'formidable' ),
			500 => 500, 600 => 600,
			'bold' => __( 'bold', 'formidable' ),
			800 => 800, 900 => 900,
		);
	}
}
