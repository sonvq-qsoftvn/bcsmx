<?php
/*
 * Template Name: Edit Profile
 */

get_header();

get_template_part( 'partials/header/banner' );
?>
    <div id="content-wrapper" class="site-content-wrapper site-pages">

        <div id="content" class="site-content layout-boxed">

            <div class="container">

                <div class="row">

                    <div class="col-xs-12 site-main-content">

                        <main id="main" class="site-main">

                            <div class="white-box user-profile-wrapper">

                                <?php
                                /*
                                 * Display page contents if any
                                 */
                                if ( have_posts() ):
                                    while ( have_posts() ):
                                        the_post();
                                        $content = get_the_content();
                                        if ( !empty( $content ) ) {
                                            ?>
                                            <article id="post-<?php the_ID(); ?>" <?php post_class('clearfix'); ?> >
                                                <div class="entry-content clearfix">
                                                    <?php the_content(); ?>
                                                </div>
                                            </article>
                                            <?php
                                        }
                                    endwhile;
                                endif;

                                if( is_user_logged_in() ) {

                                    // get user information
                                    global $current_user;
                                    get_currentuserinfo();
                                    $current_user_meta = get_user_meta( $current_user->ID );

                                    ?>
                                    <form id="inspiry-edit-user" class="submit-form" enctype="multipart/form-data" method="post">

                                        <div class="row">

                                            <div class="col-md-6">
                                                <div class="form-option user-profile-img-wrapper clearfix">

                                                    <div id="user-profile-img">
                                                        <div class="profile-thumb">
                                                            <?php
                                                            if( isset( $current_user_meta['profile_image_id'] ) ) {
                                                                $profile_image_id = intval( $current_user_meta['profile_image_id'][0] );
                                                                if ( $profile_image_id ) {
                                                                    echo wp_get_attachment_image( $profile_image_id, 'inspiry-agent-thumbnail', false, array( 'class' => 'img-responsive' ) );
                                                                    echo '<input type="hidden" class="profile-image-id" name="profile-image-id" value="' . $profile_image_id .'"/>';
                                                                }
                                                            }
                                                            ?>
                                                        </div>
                                                    </div>
                                                    <!-- #user-profile-img -->

                                                    <div class="profile-img-controls">

                                                        <ul class="field-description list-unstyled">
                                                            <li><span>*</span><?php _e( 'Profile image should have minimum width and height of 220px.', 'inspiry' ); ?></li>
                                                            <li><span>*</span><?php _e( 'Make sure to save changes after uploading the new image.', 'inspiry' ); ?></li>
                                                        </ul>

                                                        <a id="select-profile-image" class="btn-default" href="javascript:;">
                                                            <i class="fa fa-picture-o"></i><?php _e( 'Change', 'inspiry' ); ?>
                                                        </a>

                                                        <a id="remove-profile-image" class="btn-default btn-orange" href="#remove-profile-image">
                                                            <i class="fa fa-trash-o"></i><?php _e( 'Remove', 'inspiry' ); ?>
                                                        </a>

                                                        <div id="errors-log"></div>
                                                        <div id="plupload-container"></div>

                                                    </div>
                                                    <!-- .profile-img-controls -->

                                                </div>
                                                <!-- .user-profile-img-wrapper -->
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-option">
                                                    <label for="description"><?php _e('Biographical Information', 'inspiry') ?></label>
                                                    <textarea name="description" id="description" rows="5" cols="30"><?php if( isset( $current_user_meta['description'] ) ) { echo esc_textarea( $current_user_meta['description'][0] ); } ?></textarea>
                                                </div>
                                            </div>

                                        </div>
                                        <!-- .row -->

                                        <div class="row">

                                            <div class="col-md-4">
                                                <div class="form-option">
                                                    <label for="first-name"><?php _e('First Name', 'inspiry'); ?></label>
                                                    <input class="valid required" name="first-name" type="text" id="first-name"
                                                           value="<?php if( isset( $current_user_meta['first_name'] ) ) { echo esc_attr( $current_user_meta['first_name'][0] ); } ?>"
                                                           title="<?php _e('* Provide First Name!', 'inspiry'); ?>"
                                                           autofocus />
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-option">
                                                    <label for="last-name"><?php _e('Last Name', 'inspiry'); ?></label>
                                                    <input class="required" name="last-name" type="text" id="last-name"
                                                           value="<?php if( isset( $current_user_meta['last_name'] ) ) {  echo esc_attr( $current_user_meta['last_name'][0] ); } ?>"
                                                           title="<?php _e('* Provide Last Name!', 'inspiry'); ?>" />
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-option">
                                                    <label for="display-name"><?php _e('Display Name', 'inspiry'); ?> *</label>
                                                    <input class="required" name="display-name" type="text" id="display-name"
                                                           value="<?php echo esc_attr( $current_user->display_name ); ?>"
                                                           title="<?php _e('* Provide Display Name!', 'inspiry'); ?>"
                                                           required />
                                                </div>
                                            </div>

                                        </div>
                                        <!-- .row -->

                                        <div class="row">

                                            <div class="col-md-4">
                                                <div class="form-option">
                                                    <label for="email"><?php _e('Email', 'inspiry'); ?> *</label>
                                                    <input class="email required" name="email" type="email" id="email"
                                                           value="<?php echo esc_attr( $current_user->user_email ); ?>"
                                                           title="<?php _e('* Provide Valid Email Address!', 'inspiry'); ?>"
                                                           required/>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-option">
                                                    <label for="mobile-number"><?php _e('Mobile Number', 'inspiry'); ?></label>
                                                    <input class="digits" name="mobile-number" type="text" id="mobile-number"
                                                           value="<?php if( isset( $current_user_meta['mobile_number'] ) ) { echo esc_attr( $current_user_meta['mobile_number'][0] ); } ?>"
                                                           title="<?php _e('* Only Digits are allowed!', 'inspiry'); ?>" />
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-option">
                                                    <label for="office-number"><?php _e('Office Number', 'inspiry'); ?></label>
                                                    <input class="digits" name="office-number" type="text" id="office-number"
                                                           value="<?php if( isset( $current_user_meta['office_number'] ) ) { echo esc_attr( $current_user_meta['office_number'][0] ); } ?>"
                                                           title="<?php _e('* Only Digits are allowed!', 'inspiry'); ?>" />
                                                </div>
                                            </div>


                                        </div>
                                        <!-- .row -->

                                        <div class="row">

                                            <div class="col-md-4">
                                                <div class="form-option">
                                                    <label for="fax-number"><?php _e('Fax Number', 'inspiry'); ?></label>
                                                    <input class="digits" name="fax-number" type="text" id="fax-number"
                                                           value="<?php if( isset( $current_user_meta['fax_number'] ) ) { echo esc_attr( $current_user_meta['fax_number'][0] ); } ?>"
                                                           title="<?php _e('* Only Digits are allowed!', 'inspiry'); ?>" />
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-option">
                                                    <label for="facebook-url"><?php _e('Facebook URL', 'inspiry'); ?></label>
                                                    <input class="url" name="facebook-url" type="text" id="facebook-url"
                                                           value="<?php if( isset( $current_user_meta['facebook_url'] ) ) { echo esc_url( $current_user_meta['facebook_url'][0] ); } ?>"
                                                           title="<?php _e('* Provide Valid URL!', 'inspiry'); ?>" />
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-option">
                                                    <label for="twitter-url"><?php _e('Twitter URL', 'inspiry'); ?></label>
                                                    <input class="url" name="twitter-url" type="text" id="twitter-url"
                                                           value="<?php if( isset( $current_user_meta['twitter_url'] ) ) { echo esc_url( $current_user_meta['twitter_url'][0] ); } ?>"
                                                           title="<?php _e('* Provide Valid URL!', 'inspiry'); ?>" />
                                                </div>
                                            </div>


                                        </div>
                                        <!-- .row -->

                                        <div class="row">

                                            <div class="col-md-4">
                                                <div class="form-option">
                                                    <label for="google-plus-url"><?php _e('Google Plus URL', 'inspiry'); ?></label>
                                                    <input class="url" name="google-plus-url" type="text" id="google-plus-url"
                                                           value="<?php if( isset( $current_user_meta['google_plus_url'] ) ) { echo esc_url( $current_user_meta['google_plus_url'][0] ); } ?>"
                                                           title="<?php _e('* Provide Valid URL!', 'inspiry'); ?>" />
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-option">
                                                    <label for="linkedin-url"><?php _e('LinkedIn URL', 'inspiry'); ?></label>
                                                    <input class="url" name="linkedin-url" type="text" id="linkedin-url"
                                                           value="<?php if( isset( $current_user_meta['linkedin_url'] ) ) { echo esc_url( $current_user_meta['linkedin_url'][0] ); } ?>"
                                                           title="<?php _e('* Provide Valid URL!', 'inspiry'); ?>" />
                                                </div>
                                            </div>


                                        </div>
                                        <!-- .row -->

                                        <!-- todo: add instagram and pinterest -->

                                        <div class="row">

                                            <div class="col-md-4">
                                                <div class="form-option">
                                                    <label for="pass1"><?php _e( 'Password', 'inspiry' ); ?>
                                                        <small><?php _e( '( Fill it only when you want to change )', 'inspiry' ); ?></small>
                                                    </label>
                                                    <input name="pass1" type="password" id="pass1">
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-option">
                                                    <label for="pass2"><?php _e('Confirm Password', 'inspiry'); ?></label>
                                                    <input name="pass2" type="password" id="pass2" />
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-option">
                                                    <?php
                                                    //action hook for plugin and extra fields
                                                    do_action( 'edit_user_profile', $current_user );

                                                    // WordPress Nonce for Security Check
                                                    wp_nonce_field( 'update_user', 'user_profile_nonce' );
                                                    ?>
                                                    <input type="hidden" name="action" value="inspiry_update_profile" />
                                                </div>
                                            </div>

                                        </div>
                                        <!-- .row -->

                                        <div class="form-option">
                                            <input type="submit" id="update-user" name="update-user" class="btn-small btn-orange" value="<?php _e( 'Save Changes', 'inspiry' ); ?>">
                                            <img src="<?php echo get_template_directory_uri(); ?>/images/ajax-loader-2.gif" id="ajax-loader" alt="Loading...">
                                        </div>

                                        <p id="form-message"></p>
                                        <ul id="form-errors"></ul>

                                    </form>
                                    <!-- #inspiry-edit-user -->
                                    <?php

                                } else {
                                    inspiry_message( __( 'Login Required', 'inspiry' ), __( 'You need to login to edit your profile!', 'inspiry' ) );
                                }
                                ?>

                            </div>
                            <!-- .user-profile-wrapper -->


                        </main>
                        <!-- .site-main -->

                    </div>
                    <!-- .site-main-content -->

                </div>
                <!-- .row -->

            </div>
            <!-- .container -->

        </div>
        <!-- .site-content -->

    </div><!-- .site-content-wrapper -->

<?php
/*
 * Footer
 */
get_footer();