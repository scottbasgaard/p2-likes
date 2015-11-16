<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class P2_Likes_Widget_Most_Liked extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'p2_likes_most_widget', // Base ID
			__('P2 Most Liked', 'p2-likes'), // Name
			array( 'description' => __( 'Display your most items threads on P2', 'p2-likes' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {

		global $wpdb;

		$title = apply_filters( 'widget_title', $instance['title'] );

		$number = ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 5;
		if ( ! $number )
			$number = 5;

		$days = ( ! empty( $instance['days'] ) ) ? absint( $instance['days'] ) : 7;
			if ( ! $days )
				$days = 7;

		$include_comments = ( ! empty( $instance['include_comments'] ) ) ? true : false;

		echo $args['before_widget'];
		if ( ! empty( $title ) )
			echo $args['before_title'] . $title . $args['after_title'];

		// Define transient for period
		$most_liked_transient_name = 'p2_likes_most_liked_items_transient_' . $days;

		// Cache Results
		if ( false === ( $most_liked_items = get_transient( $most_liked_transient_name ) ) ) {

			$most_liked_items = array();
			$meta_key		= '_p2_likes_total';

			if ( $days != 0 ) {
				// Get P2 Likes Posts
				$posts = $wpdb->get_results("
					SELECT      *
					FROM        $wpdb->postmeta, $wpdb->posts
					WHERE       $wpdb->postmeta.meta_key = '_p2_likes_total'
					AND					$wpdb->posts.ID = $wpdb->postmeta.post_id
					AND DATEDIFF(NOW(), $wpdb->posts.post_date) < " . $days . "
					ORDER BY    meta_value ASC
				");

			} else {
				// Get P2 Likes Posts
				$posts = $wpdb->get_results("
					SELECT      *
					FROM        $wpdb->postmeta as posts
					WHERE       posts.meta_key = '_p2_likes_total'
					ORDER BY    meta_value ASC
				");
			}

			foreach ( $posts as $post ) {
				$most_liked_items[] = array(
					'type' => 'post',
					'id' => $post->post_id,
					'count' => $post->meta_value
				);
			}

			if ( $include_comments ) {
				// Get P2 Likes Comments
				$comments = $wpdb->get_results("
					SELECT      *
					FROM        $wpdb->commentmeta as comments
					WHERE       comments.meta_key = '_p2_likes_total'
					ORDER BY    meta_value ASC
				");

				foreach ( $comments as $comment ) {
					$most_liked_items[] = array(
						'type' => 'comment',
						'id' => $comment->comment_id,
						'count' => $comment->meta_value
					);
				}
			}

			// Sort Posts / Comments by Total Likes
			usort( $most_liked_items, function( $a, $b ) {
			    return $b['count'] - $a['count'];
			});

			// Limit Results
			$most_liked_items = array_slice( $most_liked_items, 0, $number );

			set_transient( $most_liked_transient_name, $most_liked_items, 60*30 );
		}

		?>

		<ul>

			<?php foreach ( $most_liked_items as $item ) {

				if ( $item['type'] == 'post' ) { ?>

					<li>
						<a href="<?php echo get_the_permalink( $item['id'] ); ?>" class="thepermalink" title="<?php esc_attr_e( 'Permalink', 'p2-likes' ); ?>"><?php echo get_the_title( $item['id'] ); ?></a>
						(<?php echo $item['count'] . ' ';
						if ( $item['count'] > 1 ){
							_e( 'likes', 'p2-likes' );
						} else {
							_e( 'like', 'p2-likes' );
						} ?>)
					</li>


				<?php } elseif ( $item['type'] == 'comment' ) {

					$comment = get_comment( $item['id'] ); ?>
					<li>
						Comment on <a class="thepermalink" href="<?php echo esc_url( get_comment_link($comment) ); ?>" title="<?php esc_attr_e( 'Permalink', 'p2-likes' ); ?>"><?php echo get_the_title( $comment->comment_post_ID ); ?></a>
						(<?php echo $item['count'] . ' ';
						if ( $item['count'] > 1 ){
							_e( 'likes', 'p2-likes' );
						} else {
							_e( 'like', 'p2-likes' );
						} ?>)
					</li>

				<?php }

			} ?>

		</ul>

		<?php

		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : __( 'Most Liked', 'p2-likes' );
		$number = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
		$days = isset( $instance['days'] ) ? absint( $instance['days'] ) : 7;
		$include_comments = $instance['include_comments'];
		?>

		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'p2-likes' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>"></p>

		<p><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of items to show:', 'p2-likes' ); ?></label>
		<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>

		<p><label for="<?php echo $this->get_field_id( 'days' ); ?>"><?php _e( 'Number of days to consider (0 for all time):', 'p2-likes' ); ?></label>
		<input id="<?php echo $this->get_field_id( 'days' ); ?>" name="<?php echo $this->get_field_name( 'days' ); ?>" type="text" value="<?php echo $days; ?>" size="3" /></p>

		<p><label for="<?php echo $this->get_field_id( 'include_comments' ); ?>"><?php _e( 'Include comments:', 'p2-likes' ); ?></label>
		<input id="<?php echo $this->get_field_id( 'include_comments' ); ?>" name="<?php echo $this->get_field_name( 'include_comments' ); ?>" class="checkbox" type="checkbox" <?php if ( $include_comments ) : echo 'checked="checked"'; endif; ?> /></p>

		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['number'] = (int) $new_instance['number'];
		$instance['days'] = (int) $new_instance['days'];
		$instance['include_comments'] = (boolean) $new_instance['include_comments'];

		delete_transient( 'p2_likes_most_liked_items' );

		return $instance;
	}

} // class P2_Likes_Widget_Most_Liked

register_widget( 'P2_Likes_Widget_Most_Liked' );
