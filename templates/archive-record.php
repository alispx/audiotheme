<?php
/**
 * The template to display list of records.
 *
 * @package AudioTheme
 * @subpackage Template
 * @since 1.2.0
 */

get_header();
?>

<?php do_action( 'audiotheme_before_main_content' ); ?>

<?php get_audiotheme_template_part( 'parts/archive-header', 'record' ); ?>

<?php get_audiotheme_template_part( 'parts/loop-archive', 'record' ); ?>

<?php do_action( 'audiotheme_after_main_content' ); ?>

<?php get_footer(); ?>
