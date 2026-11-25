<?php
/**
 * Template Name: About (Giới thiệu – Địa chỉ – Liên hệ)
 * Page content = "Về chúng tôi"; featured image = banner. Offices, images and form text: Customize → Artvibes → About & Liên hệ.
 */
get_header();
the_post();
$offices = array_filter(preg_split('/\R\s*\R/', trim(av_mod('av_offices'))));
?>
<div class="av-page-head" id="top">
	<h1 class="av-h1"><?php the_title(); ?></h1>
	<?php av_chips([['Tất cả', '#top', true], ['Về chúng tôi', '#ve-chung-toi', false], ['Địa chỉ', '#dia-chi', false], ['Liên hệ', '#lien-he', false]]); ?>
</div>

<section class="av-wrap av-sec av-split" id="ve-chung-toi">
	<h2 class="av-h3">Về chúng tôi</h2>
	<?php if (has_post_thumbnail()) : ?><figure class="av-about__banner"><?php the_post_thumbnail('full'); ?></figure><?php endif; ?>
	<div class="av-prose av-about__text"><?php the_content(); ?></div>
</section>

<?php if ($offices) : ?>
<section class="av-wrap av-sec av-split" id="dia-chi">
	<div class="av-split__cols av-split__cols--even">
		<div>
			<h2 class="av-h3">Địa chỉ</h2>
			<?php foreach ($offices as $o) {
				$lines = preg_split('/\R/', trim($o));
				echo '<div class="av-office"><h3>' . esc_html(array_shift($lines)) . '</h3><p>' . implode('<br>', array_map('esc_html', $lines)) . '</p></div>';
			} ?>
		</div>
		<?php if ($img = av_mod('av_addr_img')) echo '<img src="' . esc_url($img) . '" alt="" loading="lazy">'; ?>
	</div>
</section>
<?php endif; ?>

<section class="av-wrap av-sec av-split" id="lien-he">
	<div class="av-split__cols av-split__cols--even">
		<?php if ($img = av_mod('av_contact_img')) echo '<img src="' . esc_url($img) . '" alt="" loading="lazy">'; ?>
		<div class="av-contact-box">
			<h2 class="av-h3">Liên hệ</h2>
			<?php if ($t = av_mod('av_contact_intro')) echo '<div class="av-prose">' . wpautop(esc_html($t)) . '</div>'; ?>
			<?php av_contact_form(); ?>
		</div>
	</div>
</section>
<div class="av-wrap av-loadmore"><a href="#top">Trở lại đầu trang ↑</a></div>
<?php get_footer();
