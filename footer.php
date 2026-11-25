</main>
<footer class="av-footer">
	<?php get_template_part('parts/logo', null, ['light' => true]); ?>
	<nav aria-label="<?php echo esc_attr(av__('Chuyên mục')); ?>">
		<?php wp_nav_menu(['theme_location' => 'footer', 'container' => false, 'menu_class' => 'av-fcols', 'depth' => 2, 'fallback_cb' => 'av_footer_menu_fallback']); ?>
	</nav>
	<p class="av-footer__desc"><?php echo esc_html(av__(av_mod('av_footer_desc'))); ?></p>
	<p class="av-footer__copy"><?php echo esc_html(av__(av_mod('av_copyright'))); ?></p>
</footer>

<dialog id="av-sub" class="av-dialog" aria-label="<?php echo esc_attr(av__('Đăng ký nhận bản tin')); ?>">
	<button class="av-dialog__x" type="button" data-close aria-label="<?php echo esc_attr(av__('Đóng')); ?>">×</button>
	<p class="av-dialog__t"><?php echo esc_html(av__('Đăng ký nhận bản tin')); ?></p>
	<p><?php echo esc_html(av__('Nhận những câu chuyện kiến trúc, nội thất và thiết kế hay nhất qua email.')); ?></p>
	<?php av_subscribe_form(); ?>
</dialog>

<?php
$sent = sanitize_key($_GET['av_sent'] ?? '');
$msg = ['sub' => 'Cảm ơn bạn đã đăng ký nhận tin!', 'contact' => 'Đã gửi liên hệ. Ban biên tập sẽ phản hồi sớm.', 'err' => 'Chưa gửi được — vui lòng kiểm tra lại email.'][$sent] ?? '';
$msg = $msg ? av__($msg) : '';
if ($msg) echo '<div class="av-toast' . ($sent === 'err' ? ' is-err' : '') . '" role="status">' . esc_html($msg) . '</div>';
wp_footer();
?>
</body>
</html>
