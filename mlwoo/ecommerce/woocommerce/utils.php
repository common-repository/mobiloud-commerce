<?php
/**
 * Contains utility functions that can be used plugin-wide.
 */

namespace MLWoo\Ecommerce\WooCommerce\Utils;

/**
 * Returns URL for the default product image for a placeholder.
 */
function get_placeholder_image_url() {
	return apply_filters( 'mlwoo_placeholder_image_url', MLWOO_URL . 'dist/images/woocommerce-placeholder.png' );
}

/**
 * Returns array of products by default.
 */
function get_posts( $args = array() ) {
	$posts        = array();
	$default_args = array(
		'post_type'      => 'product',
		'posts_per_page' => 10,
	);

	$default_args = array_merge( $default_args, $args );
	$query        = new \WP_Query( $default_args );

	while ( $query->have_posts() ) {
		$query->the_post();

		$post_id = get_the_ID();

		$posts[] = apply_filters( 'mlwoo_get_posts', array(
			'id'    => $post_id,
			'title' => get_the_title(),
		) );
	}

	\wp_reset_postdata();

	return array(
		'count' => $query->found_posts,
		'posts' => $posts,
	);
}

/**
 * Returns array of product categories by default.
 */
function get_taxonomy_terms( $args = array() ) {
	$categories   = array();
	$default_args = array(
		'taxonomy' => 'product_cat',
		'number'   => 10,
		'fields'   => 'all',
		'count'    => true,
	);

	$default_args = array_merge( $default_args, $args );
	$terms        = get_terms( $default_args );

	foreach ( $terms as $term ) {
		$categories[] = apply_filters( 'mlwoo_get_taxonomy_terms', $term );
	}

	return $categories;
}

/**
 * Return the product catogory by product ID.
 */
function get_product_category( $product_id ) {
	$terms = get_the_terms( $product_id, 'product_cat' );
	foreach ( $terms as $term ) {
		$product_cat_id = $term->term_id;
		break;
	}

	$term = get_term_by( 'id', $product_cat_id, 'product_cat' );

	if ( is_a( $term, 'WP_Term' ) ) {
		return $term->name;
	}

	return false;
}

/**
 * Filter hook function to add image URL, product category
 * and product price to the product.
 */
function add_to_products_response( $post ) {
	$product           = wc_get_product( $post['id'] );
	$post['image_url'] = get_the_post_thumbnail_url( $post['id'], 'medium' );
	$post['category']  = \MLWoo\Ecommerce\WooCommerce\Utils\get_product_category( $post['id'] );
	$post['price']     = $product->get_price_html();

	return $post;
}

/**
 * Ajax handler to load more products.
 */
function get_products() {
	$search = filter_input( INPUT_POST, 'search', FILTER_SANITIZE_STRING );
	$page   = (int)filter_input( INPUT_POST, 'page', FILTER_SANITIZE_NUMBER_INT );

	add_filter( 'mlwoo_get_posts', '\MLWoo\Ecommerce\WooCommerce\Utils\add_to_products_response' );
	$products = get_posts( array(
		's'      => $search,
		'offset' => ( $page - 1 ) * 10,
	) );
	remove_filter( 'mlwoo_get_posts', '\MLWoo\Ecommerce\WooCommerce\Utils\add_to_products_response' );

	ob_start();

	?>
	<?php if ( apply_filters( 'mlwoo_get_products_for_loop', true ) ) : ?>
		<?php foreach ( $products['posts'] as $post ) : ?>
			<a onclick="nativeFunctions.handleLink( '<?php echo esc_url( sprintf( MLWOO_ENDPOINT . '?product_id=%s', $post['id'] ) ); ?>', '<?php echo $post['title']; ?>', 'native' )" class="mlwoo__grid-item--normal">
				<div class="mlwoo__grid-item__wrapper">
					<div class="mlwoo__grid-item__wrapper-inner">
						<img class="mlwoo__grid-item-image" src="<?php echo esc_url( $post['image_url'] ); ?>" />
						<div class="mlwoo__grid-item-meta">
							<div class="mlwoo__grid-item__category"><?php echo esc_html( $post['category'] ); ?></div>
							<div class="mlwoo__grid-item__title"><?php echo esc_html( $post['title'] ); ?></div>
							<div class="mlwoo__grid-item__price"><?php echo wp_kses_post( $post['price'] ); ?></div>
						</div>
					</div>
				</div>
			</a>
		<?php endforeach; ?>
	<?php endif; ?>

	<?php do_action( 'mlwoo_get_products_after_for_loop', $products ); ?>

	<?php
	$buffered_content = \ob_get_clean();
	$content = array(
		'htmlString' => $buffered_content,
		'maxPosts'   => $products['count'],
	);
	wp_send_json_success( $content );
}
add_action( 'wp_ajax_mlwoo_get_products', '\MLWoo\Ecommerce\WooCommerce\Utils\get_products' );
add_action( 'wp_ajax_nopriv_mlwoo_get_products', '\MLWoo\Ecommerce\WooCommerce\Utils\get_products' );

/**
 * Filter hook function to add image URL to the
 * term object.
 */
function add_to_categories_response( $term ) {
	$thumbnail_id = get_term_meta( $term->term_id, 'thumbnail_id', true );
	$image_url    = wp_get_attachment_url( $thumbnail_id );

	if ( false === $image_url ) {
		$image_url = \MLWoo\Ecommerce\WooCommerce\Utils\get_placeholder_image_url();
	}

	$term->image_url = $image_url;

	return $term;
}

/**
 * Ajax handler to load more categories on the products categories page.
 */
function get_categories() {
	$page = (int)filter_input( INPUT_POST, 'page', FILTER_SANITIZE_NUMBER_INT );

	add_filter( 'mlwoo_get_taxonomy_terms', '\MLWoo\Ecommerce\WooCommerce\Utils\add_to_categories_response' );
	$categories = \MLWoo\Ecommerce\WooCommerce\Utils\get_taxonomy_terms( array(
		'offset' => ( $page - 1 ) * 10,
	) );
	remove_filter( 'mlwoo_get_taxonomy_terms', '\MLWoo\Ecommerce\WooCommerce\Utils\add_to_categories_response' );

	$max_found = wp_count_terms(
		array(
			'hide_empty' => true,
			'taxonomy'   => 'product_cat',
			'number'     => 10,
		)
	);

	ob_start(); ?>

	<?php if ( apply_filters( 'mlwoo_get_categories_for_loop', true ) ) : ?>
		<?php foreach ( $categories as $category ) : ?>
			<a href="" onclick="nativeFunctions.handleLink( '<?php echo esc_url( sprintf( MLWOO_ENDPOINT . '/product-category/%s', $category->term_id ) ); ?>', '<?php echo $category->name; ?>', 'native' )" class="mlwoo__grid-item--square">
				<div class="mlwoo__grid-item__wrapper">
					<div class="mlwoo__grid-item__wrapper-inner" style="background-image: url( <?php echo esc_url( $category->image_url ); ?> )">
						<div class="mlwoo__grid-item-title mlwoo__grid-item-title--category">
							<?php echo esc_html( $category->name ); ?>
						</div>
					</div>
				</div>
			</a>
		<?php endforeach; ?>
	<?php endif;?>

	<?php do_action( 'mlwoo_get_categories_after_for_loop', $categories ) ?>

	<?php
	$buffered_content = \ob_get_clean();
	$content = array(
		'htmlString' => $buffered_content,
		'maxPosts'   => $max_found,
	);
	wp_send_json_success( $content );
}
add_action( 'wp_ajax_mlwoo_get_categories', '\MLWoo\Ecommerce\WooCommerce\Utils\get_categories' );
add_action( 'wp_ajax_nopriv_mlwoo_get_categories', '\MLWoo\Ecommerce\WooCommerce\Utils\get_categories' );

/**
 * Returns an array of horizontal menu items with
 * MobiLoud endpoints for product categories.
 *
 * @param array $item Current horizontal menu item.
 *
 * @return array
 */
function filter_endpoint_url( $item ) {
	if ( 'category' !== $item['type'] ) {
		return $item;
	}

	$term_id = (int)$item['id'];
	$term    = get_term( $term_id );

	if ( is_wp_error( $term ) ) {
		return $item;
	}

	if ( 'product_cat' !== $term->taxonomy ) {
		return $item;
	}
	
	$item['endpoint_url'] = MLWOO_ENDPOINT . '/product-category/' . $term_id;
	return $item;
}
add_filter( 'ml_get_menu_config_item', '\MLWoo\Ecommerce\WooCommerce\Utils\filter_endpoint_url' );
