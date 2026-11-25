<?php
/** Portrait card: full-bleed photo, name, short bio, "View Profile". */
$id = $args['id'];
?>
<article class="av-person">
	<?php echo av_img($id, 'large'); ?>
	<div class="av-person__text">
		<h3><?php echo esc_html(get_the_title($id)); ?></h3>
		<p><?php echo esc_html(av_excerpt($id, 30)); ?></p>
		<a class="av-btn-ghost" href="<?php echo esc_url(get_permalink($id)); ?>">View Profile</a>
	</div>
</article>
