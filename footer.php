</main>
<footer class="av-footer">
	<?php get_template_part('parts/logo', null, ['light' => true]); ?>
	<nav aria-label="Chuyên mục">
		<?php wp_nav_menu(['theme_location' => 'footer', 'container' => false, 'menu_class' => 'av-fcols', 'depth' => 2, 'fallback_cb' => 'av_footer_menu_fallback']); ?>
	</nav>
	<p class="av-footer__desc"><?php echo esc_html(av_mod('av_footer_desc')); ?></p>
	<p class="av-footer__copy"><?php echo esc_html(av_mod('av_copyright')); ?></p>
</footer>

<dialog id="av-sub" class="av-dialog" aria-label="Đăng ký nhận tin">
	<button class="av-dialog__x" type="button" data-close aria-label="Đóng">×</button>
	<p class="av-dialog__t">Đăng ký nhận bản tin</p>
	<p>Nhận những câu chuyện kiến trúc, nội thất và thiết kế hay nhất qua email.</p>
	<?php av_subscribe_form(); ?>
</dialog>

<?php
$sent = sanitize_key($_GET['av_sent'] ?? '');
$msg = ['sub' => 'Cảm ơn bạn đã đăng ký nhận tin!', 'contact' => 'Đã gửi liên hệ. Ban biên tập sẽ phản hồi sớm.', 'err' => 'Chưa gửi được — vui lòng kiểm tra lại email.'][$sent] ?? '';
if ($msg) echo '<div class="av-toast' . ($sent === 'err' ? ' is-err' : '') . '" role="status">' . esc_html($msg) . '</div>';
wp_footer();
?>
</body>
</html>
