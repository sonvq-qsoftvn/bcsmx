<?php
global $inspiry_options;
$number_of_featured_properties = intval( $inspiry_options[ 'inspiry_home_featured_number_1' ] );
if( !$number_of_featured_properties ) {
    $number_of_featured_properties = 4;
}


// Featured properties query arguments
$featured_properties_args = array(
    'post_type' => 'property',
    'posts_per_page' => $number_of_featured_properties,
    'meta_query' => array(
        array(
            'key' => 'REAL_HOMES_featured',
            'value' => 1,
            'compare' => '=',
            'type'  => 'NUMERIC'
        )
    )
);

$featured_properties_query = new WP_Query( $featured_properties_args );

if ( $featured_properties_query->have_posts() ) :
?>
<div class="featured-properties featured-properties-one">

    <div class="container">

        <div class="row zero-horizontal-margin">

            <?php
            /*
             * Columns
             */
            $columns_count = intval( $inspiry_options[ 'inspiry_home_featured_columns' ] );
            if( !$columns_count ) {
                $columns_count = 4;
            }

            $column_class = 'col-md-3';
            if ( 3 == $columns_count ) {
                $column_class = 'col-md-4';
            } else if ( 2 == $columns_count ) {
                $column_class = 'col-md-6';
            }

            $properties_count = 1;

            while ( $featured_properties_query->have_posts() ) :
            $featured_properties_query->the_post();

                $featured_property = new Inspiry_Property( get_the_ID() );

                if ( 0 == ( $properties_count % 2 ) ) {
                    $odd_even_class = 'featured-property-post-even';
                } else {
                    $odd_even_class = 'featured-property-post-odd';
                }

                ?>
                <div class="zero-horizontal-padding col-xs-6 <?php echo esc_attr( $column_class . ' ' . inspiry_col_animation_class( $columns_count, $properties_count ) .' '. inspiry_animation_class() ); ?>">

                    <article class="hentry featured-property-post <?php echo esc_attr( $odd_even_class ); ?>">

                        <div class="property-thumbnail">
                            <?php inspiry_thumbnail(); ?>
                        </div>
                        <!-- .property-thumbnail -->

                        <div class="property-description">
                            <header class="entry-header">
                                <h4 class="entry-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h4>
                                <div class="price-and-status">
                                    <span class="price"><?php echo esc_html( $featured_property->get_price_without_postfix() ); ?></span>
                                    <?php
                                    $first_status_term = $featured_property->get_taxonomy_first_term( 'property-status', 'all' );
                                    if ( $first_status_term ) {
                                        ?>
                                        <a href="<?php echo esc_url( get_term_link( $first_status_term ) ); ?>">
                                            <span class="property-status-tag"><?php echo esc_html( $first_status_term->name ); ?></span>
                                        </a>
                                    <?php
                                    }
                                    ?>
                                </div>
                            </header>
                        </div>
                        <!-- .property-description -->

                    </article>

                </div><!-- .featured-properties-item -->
                <?php

                if ( $number_of_featured_properties > $properties_count ) {
                    if ( 0 == ( $properties_count % $columns_count ) ) {
                        ?></div><div class="row zero-horizontal-margin"><?php
                    }
                }
                $properties_count++;

            endwhile;

            wp_reset_postdata();
            ?>
        </div>
        <!-- .row -->

    </div>
    <!-- .container -->

</div><!-- .featured-properties -->
<?php
endif;
?>