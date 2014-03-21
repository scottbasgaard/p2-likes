<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Update Posts
$posts_args = array(
	'post_type' => 'post',
	'meta_key' => '_p2_likes'
);
$posts_query = new WP_Query( $posts_args );

if ( $posts_query->have_posts() ) {
	
	// Add New 'Likes Total' Meta
	while ( $posts_query->have_posts() ) { $posts_query->the_post();
		if ( $likes = get_post_meta( $post->ID, '_p2_likes', true ) ) {
			update_post_meta( $id, '_p2_likes_total', count($likes) );
		}
	}
	
}
wp_reset_postdata();

// Update Comments
$args = array(
	'meta_query' => array(
		array(
			'key'   => '_p2_likes'
		)
	)
);
$comments_query = new WP_Comment_Query;
$comments = $comments_query->query( $args );
 
if( $comments ) {
	
	// Add New 'Likes Total' Meta
	foreach( $comments as $comment ) {
		if ( $likes = get_comment_meta( $comment->comment_ID, '_p2_likes', true ) ) {
			update_comment_meta( $id, '_p2_likes_total', count($likes) );
		}
	}
	
}