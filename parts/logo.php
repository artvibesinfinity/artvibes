<?php
/** Site logo: uploaded custom logo, or the two-line "THE / ARTVIBES" wordmark from the site title. */
$name = get_bloginfo('name');
$small = '';
if (preg_match('/^(the)\s+(.+)$/i', $name, $m)) [, $small, $name] = $m;
?>
<a class="av-logo" href="<?php echo esc_url(home_url('/')); ?>" rel="home">
	<?php if (has_custom_logo() && empty($args['light'])) :
		echo wp_get_attachment_image(get_theme_mod('custom_logo'), 'medium', false, ['alt' => esc_attr(get_bloginfo('name'))]);
	else : ?>
		<?php if ($small) : ?><span class="av-logo__the"><?php echo esc_html($small); ?></span><?php endif; ?>
		<span class="av-logo__name"><?php echo esc_html($name); ?></span>
	<?php endif; ?>
</a>
