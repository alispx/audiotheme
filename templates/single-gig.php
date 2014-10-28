<?php
/**
 * The template for displaying a single gig.
 *
 * @package AudioTheme\Template
 * @since 1.2.0
 */

get_header();
?>

<?php do_action( 'audiotheme_before_main_content' ); ?>

<?php get_audiotheme_template_part( 'loop-single', 'gig' ); ?>

<?php do_action( 'audiotheme_after_main_content' ); ?>

<?php get_footer(); ?>
