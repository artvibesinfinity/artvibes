<?php
get_header();
the_post(); ?>
<div class="av-page-head"><h1 class="av-h1"><?php the_title(); ?></h1></div>
<?php if (has_post_thumbnail()) : ?><figure class="av-article__media"><?php the_post_thumbnail('full'); ?></figure><?php endif; ?>
<div class="av-article__body av-prose"><?php the_content(); ?></div>
<?php get_footer();
