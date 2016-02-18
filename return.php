<?php
require_once 'functions.php';
if(isset($_GET['confirmed']))
{
	$storedPosts = get_confirmed_posts();
	if($storedPosts){
		foreach($storedPosts as $storedPost)
		{
			$ig_id = $storedPost['ig_id'];
			$user_name = $storedPost['user_name'];
			$user_profile = $storedPost['user_profile'];
			$created_time = $storedPost['created_time'];
			$created_time = date('m-d-Y',$created_time);
			$link = $storedPost['link'];
			$type = $storedPost['type'];				
			$likes = $storedPost['likes'];
			$image = $storedPost['image'];
			$caption = $storedPost['caption'];
			$comments = $storedPost['comments'];
			?>
			<div class="ig__item" data-ig-id="<?php echo $ig_id; ?>">
				<div class="ig__item--user-meta">
					<div class="user-meta__profile">
						<a href="https://www.instagram.com/<?php echo $user_name; ?>" target="_blank"><img src="<?php echo $user_profile; ?>"/></a>
					</div>
					<div class="user-meta__name">
						<a href="https://www.instagram.com/<?php echo $user_name; ?>" target="_blank"><?php echo $user_name; ?>
					</div>
				</div>
				<div class="ig__item--image">
					<a href="<?php echo $link; ?>" target="_blank">
						<img src="<?php echo $image; ?>"/>
					</a>
				</div>
				<div class="ig__item--meta">
					<div class="meta__likes">
						<?php echo $likes; ?>
					</div>
					<div class="meta__comments">
						<?php echo $comments; ?>
					</div>
					<div class="meta__date">
						<?php echo $created_time;?>
					</div>
				</div>
				<div class="ig__item--caption">
					<?php echo $caption; ?>
				</div>	
				<div class="ig__remove--item">
					remove from confirmed
				</div>				
			</div>
			<?php
		}
	}else{
		?>
		<div class="notice no-posts">
			You haven't confirmed any posts to be shown
		</div>
		<?php
	}
}


if(isset($_GET['all_posts']))
{
	$storedPosts = get_stored_posts();

	$confirmedPosts = get_confirmed_posts_id();
	$postIDs = array();
	$i = 0;
	foreach($confirmedPosts as $post){
		$postIDs[$i] = $post['ig_id'];
		$i++;
	}

	foreach($storedPosts as $storedPost)
	{
		$ig_id = $storedPost['ig_id'];
		$user_name = $storedPost['user_name'];
		$user_profile = $storedPost['user_profile'];
		$created_time = $storedPost['created_time'];
		$created_time = date('m-d-Y',$created_time);
		$link = $storedPost['link'];
		$type = $storedPost['type'];				
		$likes = $storedPost['likes'];
		$image = $storedPost['image'];
		$caption = $storedPost['caption'];
		$comments = $storedPost['comments'];
		?>
		<div class="ig__item" data-ig-id="<?php echo $ig_id; ?>">
			<div class="ig__item--user-meta">
				<div class="user-meta__profile">
					<a href="https://www.instagram.com/<?php echo $user_name; ?>" target="_blank"><img src="<?php echo $user_profile; ?>"/></a>
				</div>
				<div class="user-meta__name">
					<a href="https://www.instagram.com/<?php echo $user_name; ?>" target="_blank"><?php echo $user_name; ?>
				</div>
			</div>
			<div class="ig__item--image">
				<a href="<?php echo $link; ?>" target="_blank">
					<img src="<?php echo $image; ?>"/>
				</a>
			</div>
			<div class="ig__item--meta">
				<div class="meta__likes">
					<?php echo $likes; ?>
				</div>
				<div class="meta__comments">
					<?php echo $comments; ?>
				</div>
				<div class="meta__date">
					<?php echo $created_time;?>
				</div>
			</div>
			<div class="ig__item--caption">
				<?php echo $caption; ?>
			</div>	
			<?php if(!in_array($ig_id, $postIDs)){ ?>
				<div class="ig__add--item">
					add to confirmed
				</div>	
			<?php }else{ ?>
				<div class="ig__null--item">
					in confirmed
				</div>
			<?php } ?>			
		</div>
		<?php
	}
}


if(isset($_GET['update']))
{
	get_new_posts_from_last_hash();
}


if(isset($_GET['reload_all']))
{
	reload_all_posts('530medialab');
}

if(isset($_GET['add']))
{
	$value = $_GET['add'];
	add_to_confirmed($value);
}

if(isset($_GET['remove']))
{
	$value = $_GET['remove'];
	echo remove_from_confirmed($value);
}

?>