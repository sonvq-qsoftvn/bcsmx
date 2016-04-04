<?php
/*
 * How it Works Styles Options
 */
global $opt_name;

Redux::setSection( $opt_name, array(
    'title' => __( 'How it Works', 'inspiry'),
    'id'    => 'how-it-works-styles',
    'desc'  => __('This section contains how it works section styles options.', 'inspiry' ),
    'subsection' => true,
    'fields'=> array (

        array(
            'id'        => 'inspiry_hiw_background_color',
            'type'      => 'color',
            'title'     => __( 'Background Color', 'inspiry' ),
            'desc'      => 'default: #202020',
            'default'   => '#202020',
            'validate'  => 'color',
            'transparent' => false,
        ),

        array(
            'id'        => 'inspiry_hiw_overlay_background_color',
            'type'      => 'color_rgba',
            'mode'      => 'background-color',
            'output'    => array( '.submit-property-one:before' ),
            'title'     => __( 'Overlay Background Color', 'inspiry' ),
            'desc'      => 'default: rgba(0, 0, 0, 0.7)',
            'default'   => array(
                'color'     => '#000000',
                'alpha'     => 0.7
            ),
            'required' => array( 'inspiry_hiw_section_bg', '=', 1 ),
        ),

        array(
            'id'        => 'inspiry_hiw_section_title_color',
            'type'      => 'color',
            'output'    => array(
                '.submit-property .title',
                '.submit-property .sub-title',
            ),
            'title'     => __( 'Section Title Color', 'inspiry' ),
            'desc'      => 'default: #ffffff',
            'default'   => '#ffffff',
            'validate'  => 'color',
            'transparent' => false,
        ),

        array(
            'id'        => 'inspiry_hiw_text_color',
            'type'      => 'color',
            'output'    => array( '.submit-property' ),
            'title'     => __( 'Overall Text Color', 'inspiry' ),
            'desc'      => 'default: #b3b6bb',
            'default'   => '#b3b6bb',
            'validate'  => 'color',
            'transparent' => false,
        ),

        array(
            'id'        => 'inspiry_hiw_title_color',
            'type'      => 'color',
            'output'    => array( '.submit-property .submit-property-title' ),
            'title'     => __( 'Title Color in Columns', 'inspiry' ),
            'desc'      => 'default: #50b848',
            'default'   => '#50b848',
            'validate'  => 'color',
            'transparent' => false,
        ),

        array(
            'id'        => 'inspiry_hiw_button_color',
            'type'      => 'link_color',
            'output'    => array( '.submit-property .btn-green' ),
            'title'     => __( 'Button Text Color', 'inspiry' ),
            'default'   => array(
                'regular' => '#ffffff',
                'hover'   => '#ffffff',
                'active'  => '#ffffff',
            ),
            'transparent' => false,
        ),

        array(
            'id'        => 'inspiry_hiw_btn_background_color',
            'type'      => 'color',
            'mode'      => 'background-color',
            'output'    => array( '.submit-property .btn-green' ),
            'title'     => __( 'Button Background Color', 'inspiry' ),
            'desc'      => 'default: #50b848',
            'default'   => '#50b848',
            'validate'  => 'color',
            'transparent' => false,
        ),

        array(
            'id'        => 'inspiry_hiw_btn_hover_background_color',
            'type'      => 'color',
            'mode'      => 'background-color',
            'output'    => array(
                '.submit-property .btn-green:hover',
                '.submit-property .btn-green:focus'
            ),
            'title'     => __( 'Button Background Color on Hover', 'inspiry' ),
            'desc'      => 'default: #4bad43',
            'default'   => '#4bad43',
            'validate'  => 'color',
            'transparent' => false,
        ),


    ) ) );