<?php
/**
 * The template for displaying a single track.
 *
 * @package AudioTheme\Template
 * @since 1.2.0
 */

get_header();
?>

<?php do_action( 'audiotheme_before_main_content' ); ?>

<?php get_template_part( 'parts/loop-single', 'track' ); ?>

<?php do_action( 'audiotheme_after_main_content' ); ?>

<?php get_footer(); ?>
