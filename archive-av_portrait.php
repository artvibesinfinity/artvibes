<?php
get_header(); ?>
<div class="av-page-head"><h1 class="av-h1">Chân dung</h1></div>
<section class="av-wrap av-people av-sec">
	<?php while (have_posts()) : the_post(); get_template_part('parts/person', null, ['id' => get_the_ID()]); endwhile; ?>
</section>
<div class="av-wrap av-pages"><?php echo paginate_links(['prev_text' => '←', 'next_text' => '→']); ?></div>
<?php get_footer();
