<?php
/**
 * The template to display a list of videos.
 *
 * @package AudioTheme\Core\Template
 * @since 1.2.0
 */

get_header();
?>

<?php do_action( 'audiotheme_before_main_content' ); ?>

<?php get_audiotheme_template_part( 'parts/archive-header', 'video' ); ?>

<?php get_audiotheme_template_part( 'video/loop', 'archive' ); ?>

<?php do_action( 'audiotheme_after_main_content' ); ?>

<?php get_footer(); ?>
