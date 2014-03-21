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

		echo $args['before_widget'];
		if ( ! empty( $title ) )
			echo $args['before_title'] . $title . $args['after_title'];
		
		$most_liked_items = array();
		$meta_key		= '_p2_likes_total';
		
		// Get P2 Likes Posts
		$posts = $wpdb->get_results("
			SELECT      *
			FROM        $wpdb->postmeta as posts
			WHERE       posts.meta_key = '_p2_likes_total'
			ORDER BY    meta_value ASC
			LIMIT 10
		");
		
		foreach ( $posts as $post ) {
			$most_liked_items[] = array(
				'type' => 'post',
				'id' => $post->post_id,
				'count' => $post->meta_value
			);
		}
		
		// Get P2 Likes Comments
		$comments = $wpdb->get_results("
			SELECT      *
			FROM        $wpdb->commentmeta as comments
			WHERE       comments.meta_key = '_p2_likes_total'
			ORDER BY    meta_value ASC
			LIMIT 10
		");
		
		foreach ( $comments as $comment ) {
			$most_liked_items[] = array(
				'type' => 'comment',
				'id' => $comment->comment_id,
				'count' => $comment->meta_value
			);
		}
		
		// Sort Posts / Comments by Total Likes
		usort( $most_liked_items, function( $a, $b ) {
		    return $b['count'] - $a['count'];
		});
		
		?>
		
		<ul>
			
			<?php foreach ( $most_liked_items as $item ) {
				
				if ( $item['type'] == 'post' ) { ?>
					
					<li>
						<a href="<?php echo get_the_permalink( $item['id'] ); ?>" class="thepermalink" title="<?php esc_attr_e( 'Permalink', 'p2' ); ?>"><?php echo get_the_title( $item['id'] ); ?></a>
					</li>
					
					
				<?php } elseif ( $item['type'] == 'comment' ) {
					
					$comment = get_comment( $item['id'] ); ?>
					<li>
						<a class="thepermalink" href="<?php echo esc_url( get_comment_link($comment) ); ?>" title="<?php esc_attr_e( 'Permalink', 'p2' ); ?>"><?php echo get_the_title( $comment->comment_post_ID ); ?></a>
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
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'Most Liked', 'p2-likes' );
		}
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
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
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}

} // class P2_Likes_Widget_Most_Liked

register_widget( 'P2_Likes_Widget_Most_Liked' );