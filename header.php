<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="screen-reader-text" href="#main"><?php echo esc_html(av__('Bỏ qua tới nội dung')); ?></a>
<header class="av-header">
	<div class="av-header__top">
		<div class="av-header__left">
			<button class="av-btn-sub" type="button" data-open="av-sub">Subscribe</button>
			<button class="av-icon-btn" type="button" data-toggle="av-search" aria-label="<?php echo esc_attr(av__('Tìm kiếm')); ?>" aria-expanded="false">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m15.5 15.5 5 5"/></svg>
			</button>
			<form id="av-search" class="av-search" role="search" action="<?php echo esc_url(av_home()); ?>" hidden>
				<input type="search" name="s" placeholder="<?php echo esc_attr(av__('Tìm kiếm…')); ?>" aria-label="<?php echo esc_attr(av__('Tìm kiếm')); ?>" value="<?php echo esc_attr(get_search_query()); ?>">
			</form>
		</div>
		<?php get_template_part('parts/logo'); ?>
		<div class="av-header__right">
			<?php echo av_lang_switcher() . av_socials(); ?>
			<button class="av-burger" type="button" data-toggle="av-nav" aria-label="Menu" aria-expanded="false"><span></span></button>
		</div>
	</div>
	<nav id="av-nav" class="av-nav" aria-label="<?php echo esc_attr(av__('Menu chính')); ?>">
		<?php wp_nav_menu(['theme_location' => 'primary', 'container' => false, 'menu_class' => 'av-nav__list', 'depth' => 2, 'fallback_cb' => 'av_primary_menu_fallback']); ?>
	</nav>
</header>
<main id="main">
