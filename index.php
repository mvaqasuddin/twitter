<?php
/**
 * @package Twitter
 * @version 1
 */
/*
Plugin Name: Vaqas Twitter With Cache
Plugin URI: http://www.re.vu/mvaqasuddin
Description: This is custom twitter app which is based on cache with 10 min delay time it will save your https request @ twitter all the time any one come to your page
Author: Vaqas Uddin
Version: 1
Author Email : mvaqasuddin@gmail.com
*/
add_shortcode('twitter',function($atts,$content){
	$atts = shortcode_atts(array(
		'username' => 'vaqasuddin',
		'content'  => !empty($content) ? $content : "Follow Me On Twitter",
		'show_tweets' => false,
		'num_tweets' => 5,
		'tweet_reset_time' => 10
	),$atts);
	
	extract( $atts );
	
	if ( $show_tweets )
	{
		$tweets = fetch_tweets($username,$num_tweets,$tweet_reset_time);
	}
		
	return "$tweets <p><a href='http://www.twitter.com/$username'>$content</a></p>";
	
});

function fetch_tweets($username,$num_tweets,$tweet_reset_time)
{
	global $id;
	$recent_tweets = get_post_meta($id, 'va_twitter');
	//delete_post_meta($id, 'va_twitter'); die();
	reset_data($recent_tweets,$tweet_reset_time);
	if ( empty($recent_tweets) ) {
				$tweets = curl("https://api.twitter.com/1/statuses/user_timeline/$username.json");
				$data = array();
				if ( $tweets ) {
					foreach ( $tweets as $tweet )
					{
						if ( $num_tweets-- === 0 ) break;
						$data[] = $tweet->text;
					}	
				
				$recent_tweets = array( (int) date('i',time()) );
				$recent_tweets[] = '<ul id="twitter"><li>'. implode('</li><li>',$data).'</li></ul>';
				cache($recent_tweets);
			}
		}
	return isset($recent_tweets[0][1]) ? $recent_tweets[0][1] : $recent_tweets[1];
	
}

function curl($url)
{
	$ch = curl_init($url);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch,CURLOPT_CONNECTTIMEOUT, 3);
	curl_setopt($ch,CURLOPT_TIMEOUT, 5);
	return json_decode( curl_exec($ch) );
}


function cache($recent_tweets)
{
	// [0] current min
	// [1] current html
	global $id;
	add_post_meta($id,'va_twitter',$recent_tweets,true);
}

function reset_data($recent_tweets,$tweet_reset_time)
{
	global $id;
	if ( isset($recent_tweets[0][0]) ) {
		$delay = $recent_tweets[0][0] + (int)$tweet_reset_time;
		if( $delay >= 60 ) $delay -= 60;
		if ( $delay <= (int)date('i',time() )) {
			delete_post_meta($id,'va_twitter');
		}
	}
}

//https://api.twitter.com/1/statuses/user_timeline/$username.json