<?php
/**
 * フッター
 *
 * @package Sakura_Clinic
 */

defined( 'ABSPATH' ) || exit;

$sakura_notice = sakura_clinic_get( 'sample_notice' );
?>
</main><!-- #main -->

<?php if ( '' !== $sakura_notice ) : ?>
	<p class="sample-notice"><?php echo esc_html( $sakura_notice ); ?></p>
<?php endif; ?>

<footer class="site-footer">
	<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></p>
	<p class="credit"><?php esc_html_e( '写真: Pexels（Yan Krukov）／イラスト・実装: 制作者', 'sakura-clinic' ); ?></p>
</footer>

<?php wp_footer(); ?>
</body>
</html>
