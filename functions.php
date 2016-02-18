<?php
require_once 'db.php';
require_once 'class.InstagramHarvest.php';
// query the API based on the tag passed
function get_tagged_instagram_response($tag)
{	
	global $access_token;
	
	global $dbh;

	$sql = "SELECT `min_tag_id`
        	FROM `config`
       		WHERE `hashtag` = '".$tag."'
        	LIMIT 1";

    $rows = $dbh->query($sql);
    $results = array();

    if(count($rows) > 0){
		while ( $row = $rows->fetch_assoc() )
		{
			$results[] = $row;
		}
	}else{
		$results[0]['min_tag_id'] = 0;
	}

	$min_tag_id = $results[0]['min_tag_id'];

	/*
	 * instantiate class and run fetch
	 */
	$instagram = new \Miguelpelota\InstagramHarvest($tag, $access_token, $min_tag_id);

	$data = $instagram->fetchImages();

	// $result = fetch_data('https://api.instagram.com/v1/tags/'.$tag.'/media/recent/?count=100&access_token='.$instagramToken);
	// $result = json_decode($result, true);

	$min_tag_id_new = $instagram->getNewMinTagId();

	$sql = "UPDATE `config`
	        SET `min_tag_id` = '".$min_tag_id_new."'
	        WHERE `hashtag` = '".$tag."'";

	$dbh->query($sql);

	return $data;
}

// curl method to recieve data from API
function fetch_data($url)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 20);
	$result = curl_exec($ch);
	curl_close($ch); 

	return $result;
}


function get_confirmed_posts_id()
{
	global $dbh;

	// get all entries in confirmed table
	$sql = 'SELECT * FROM `confirmed`';
	$rows = $dbh->query($sql);
	$results = array();
	while ( $row = $rows->fetch_assoc() )
	{
		$results[] = $row;
	}
	return $results;
}


// get all content from `all_posts` if ig_id is present in the `confirmed` table
function get_confirmed_posts()
{
	global $dbh;

	// get all entries in confirmed table
	$sql = 'SELECT * FROM `confirmed`';
	$rows = $dbh->query($sql);
	$results = array();
	while ( $row = $rows->fetch_assoc() )
	{
		$results[] = array_values($row);
	}

	$i = 0;
	$sql  = 'SELECT * FROM `all_posts` ';
	$sql .= 'WHERE `ig_id` IN (';
	foreach ($results as $result)
	{
		$sql .= "'" . $result[0] . "'";
		if($i < (count($results) - 1) ){
			$sql .= ',';
		}
		$i++;
	}
	$sql .= ')';

	$rows = $dbh->query($sql);
	$results = array();

	if($rows && count($rows) > 0){
		while ( $row = $rows->fetch_assoc() )
		{
			$results[] = $row;
		}
	}

	return $results;
}

// get all posts in `all_posts` table
function get_stored_posts()
{
	global $dbh;

	$sql = 'SELECT * FROM `all_posts`';

	$rows = $dbh->query($sql);
	$results = array();
	while ( $row = $rows->fetch_assoc() )
	{
		$results[] = $row;
	}

	return $results;
}

// count how many posts we have stored in all_posts table (can be used to check if we're up to date with the API)
function count_stored_posts()
{
	global $dbh;

	$sql = 'SELECT * FROM `all_posts`';

	return mysqli_num_rows($dbh->query($sql));
}

// count how many posts we have stored in confirmed table (can be used to check if we're up to date with the API)
function count_confirmed()
{
	global $dbh;

	$sql = 'SELECT * FROM `confirmed`';

	return mysqli_num_rows($dbh->query($sql));
}


// checks for date of last database entry, then only adds new ones from the API
function get_new_posts_from_last_hash()
{
	global $dbh;

	$posts = get_needed_ig_fields();

	if(count($posts) > 0)
	{
		$t = 0;

		$sql  = "INSERT INTO all_posts";
		$sql .= " (`ig_id`, `user_name`, `user_profile`, `created_time`, `link`, `type`, `likes`, `image`, `caption`, `comments`)";
		$sql .= " VALUES ";
		foreach($posts as $post)
		{
			// runs the $dbh->real_escape_string on all the $post items, then explodes into a comma seperated string
			// for mysqli to import			
			$values = array_map(
				function($value){
					global $dbh;
					return "'" . $dbh->real_escape_string($value) . "'";
				}, $post);
			$values = implode(',', $values);

			$sql .= "(" . $values . ")";
			if($t < (count($posts) - 1) ){
				$sql .= ',';
			}
			$t++;
		}

		if ($dbh->query($sql) === TRUE) {
		    // return "New record created successfully";
		} else {
		    return "Error: " . $sql . "<br>" . $dbh->error;
		}
	}
	return $posts;
}

// get only the fields we need from the API
function get_needed_ig_fields()
{
	$data = array();
	$posts = get_tagged_instagram_response('530medialab');
	$posts = json_encode($posts);
	$posts = json_decode($posts, true);
	$i = 0;
	foreach ($posts as $post)
	{
		$data[$i]['ig_id'] = $post['id'];
		$data[$i]['user_name'] = $post['user']['username'];
		$data[$i]['user_profile'] = $post['user']['profile_picture'];
		$data[$i]['created_time'] = $post['created_time'];
		$data[$i]['link'] = $post['link'];
		$data[$i]['type'] = $post['type'];
		$data[$i]['likes'] = $post['likes']['count'];
		$data[$i]['image'] = $post['images']['standard_resolution']['url'];
		$data[$i]['caption'] = $post['caption']['text'];
		$data[$i]['comments'] = $post['comments']['count'];

		$i++;
	}

	return $data;
}



// DONT USE OFTEN truncate all_posts table and repopulate completely from API
function reload_all_posts($tag)
{
	$i = 0;

	global $dbh;

	$sql = "UPDATE `config`
	        SET `min_tag_id` = '0'
	        WHERE `hashtag` = '".$tag."'";
	$dbh->query($sql);

	$sql = "TRUNCATE TABLE all_posts";
	$dbh->query($sql);

	$posts = get_needed_ig_fields();

	

	$sql  = "INSERT INTO all_posts";
	$sql .= " (`ig_id`, `user_name`, `user_profile`, `created_time`, `link`, `type`, `likes`, `image`, `caption`, `comments`)";
	$sql .= " VALUES ";
	foreach($posts as $post)
	{
		// runs the $dbh->real_escape_string on all the $post items, then explodes into a comma seperated string
		// for mysqli to import
		
		$values = array_map(
			function($value){
				global $dbh;
				return "'" . $dbh->real_escape_string($value) . "'";
			}, $post);
		$values = implode(',', $values);

		$sql .= "(" . $values . ")";
		if($i < (count($posts) - 1) ){
			$sql .= ',';
		}
		$i++;
	}

	if ($dbh->query($sql) === TRUE) {
	    // return "New record created successfully";
	} else {
	    return "Error: " . $sql . "<br>" . $dbh->error;
	}
}


// add item to the confirmed table
function add_to_confirmed($item)
{
	global $dbh;
	$sql  = "INSERT INTO confirmed";
	$sql .= " (`ig_id`) VALUE ('" . $item . "')";
	$rows = $dbh->query($sql);


	return $sql;
}

// remove item from the confirmed table
function remove_from_confirmed($item)
{
	global $dbh;
	$sql  = "DELETE FROM confirmed";
	$sql .= " WHERE `ig_id`='" . $item . "'";
	$rows = $dbh->query($sql);


	return $sql;
}



?>