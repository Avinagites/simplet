<?php

	if(isset($_POST['action']) && $_POST['action']=='review') {

		if(!$Member_Auth) {
			$Error = 'You cannot leave a review, you are not logged in.';
		} else {
			if(!isset($_POST['canonical']) || empty($_POST['canonical'])) {
				$Error = 'Could not determine which post you wanted to review on.';
			} else if(!isset($_POST['rating']) || empty($_POST['rating'])) {
				$Error = 'You didn\'t choose a rating.';
			} else if(!isset($_POST['post']) || empty($_POST['post'])) {
				$Error = 'You didn\'t enter a review.';
			} else {

				$Review_Canonical = trim(htmlentities($_POST['canonical'], ENT_QUOTES, 'UTF-8'));
				$Review_Rating = trim(htmlentities($_POST['rating'], ENT_QUOTES, 'UTF-8'));
				$Review_Post = trim(htmlentities($_POST['post'], ENT_QUOTES, 'UTF-8'));

				if(empty($Review_Canonical)) {
					$Error = 'Could not determine which post you wanted to leave a review on.';
				} else if(empty($Review_Post)) {
					$Error = 'You didn\'t enter a comment.';
				} else {

					$Time = time();
					$Review_Status = 'Public';

					$Review_New = mysqli_query($MySQL_Connection, "INSERT INTO `Reviews` (`Member_ID`, `Canonical`, `Status`, `Rating`, `Post`, `Created`, `Modified`) VALUES ('$Member_ID', '$Review_Canonical', '$Review_Status', '$Review_Rating', '$Review_Post', '$Time', '$Time')", MYSQLI_STORE_RESULT);
					if (!$Review_New) exit('Invalid Query (Review_New): '.mysqli_error($MySQL_Connection));

				}

			}
		}
	}

	$Reviews = mysqli_query($MySQL_Connection, "SELECT * FROM `Reviews` WHERE `Canonical`='$Canonical' AND `Status`='Public' ORDER BY `Created` ASC", MYSQLI_STORE_RESULT);
	if(!$Reviews) exit('Invalid Query (Reviews): '.mysqli_error($MySQL_Connection));

	$Reviews_Count = mysqli_num_rows($Reviews);
	if($Reviews_Count==0) {
		echo '
			<hr>
			<h3>No Reviews to Display.</h3>';
	} else {
		if ($Reviews_Count == '1') {
			echo '
			<hr>
			<h3>1 Review</h3>';
		} else {
			$Ratings = mysqli_query($MySQL_Connection, "SELECT SUM(`Rating`) AS `Sum` FROM `Reviews` WHERE `Canonical`='$Canonical' AND `Status`='Public'", MYSQLI_STORE_RESULT);
			if(!$Ratings) exit('Invalid Query (Ratings): '.mysqli_error($MySQL_Connection));
			$Ratings_Fetch = mysqli_fetch_assoc($Ratings);
			$Ratings_Average = round($Ratings_Fetch['Sum']/$Reviews_Count);
			echo '
			<hr>
			<h3>'.$Reviews_Count.' Reviews &nbsp;&mdash;&nbsp; '.$Ratings_Average.' Stars Average</h3>';
		}

		require 'libs/Parsedown.php';

		$Reviews_Members_IDs = array();
		$Reviews_Members_Names = array();
		$Reviews_Members_Avatar = array();

		while($Reviews_Fetch = mysqli_fetch_assoc($Reviews)) {

			$Reviews_Member_ID = $Reviews_Fetch['Member_ID'];
			$Reviews_Rating = $Reviews_Fetch['Rating'];
			$Reviews_Post = Parsedown::instance()->parse(html_entity_decode($Reviews_Fetch['Post'], ENT_QUOTES, 'UTF-8'));
			$Reviews_Created = $Reviews_Fetch['Created'];
			$Reviews_Modified = $Reviews_Fetch['Modified'];

			// TODO
			// The Members in the cache should be changed to Object Oriented code

			if(in_array($Reviews_Member_ID, $Reviews_Members_IDs)) {
				$Reviews_Members_Num = array_search($Reviews_Member_ID, $Reviews_Members_IDs);
				$Reviews_Store_Name = $Reviews_Members_Names[$Reviews_Members_Num];
				$Reviews_Store_Avatar = $Reviews_Members_Avatar[$Reviews_Members_Num];
			} else {
				$Reviews_Member = mysqli_query($MySQL_Connection, "SELECT * FROM `Members` WHERE `ID`='$Reviews_Member_ID' AND `Status`='Active'", MYSQLI_STORE_RESULT);
				if (!$Reviews_Member) exit('Invalid Query (Reviews_Member): '.mysqli_error($MySQL_Connection));
				$Reviews_Member_Count = mysqli_num_rows($Reviews_Member);
				if($Reviews_Member_Count == 0) {
					$Reviews_Store_Name = 'Deactivated';
					$Reviews_Store_Avatar = 'http://www.gravatar.com/avatar/deactivated?s=248&d=mm';
				} else {
					$Reviews_Member_Fetch = mysqli_fetch_assoc($Reviews_Member);
					$Reviews_Store_Name = $Reviews_Member_Fetch['Name'];
					$Reviews_Store_Avatar = 'http://www.gravatar.com/avatar/'.md5($Reviews_Member_Fetch['Mail']).'?s=248&d=identicon';
				}
				$Reviews_Members_ID[] = $Reviews_Member_ID;
				$Reviews_Members_Names[] = $Reviews_Store_Name;
				$Reviews_Members_Avatar[] = $Reviews_Store_Avatar;
			}

			echo '
				<div class="section group darkrow">
					<div class="col span_2_of_12 textcenter';
			if($Reviews_Store_Name === 'Deactivated') echo ' faded';
			echo '"><p>'.$Reviews_Store_Name.'</p></div>
					<div class="col span_10_of_12 textright"><p>';
			if($Reviews_Modified > $Reviews_Created) echo '<span class="faded edited">edited '.date('d M, Y H:i', $Reviews_Modified).' &nbsp;&middot;&nbsp; </span>';
			echo date('d M, Y H:i', $Reviews_Created).'</p></div>
				</div>
				<div class="section group reply">
					<div class="col span_2_of_12"><img class="avatar" src="'.$Reviews_Store_Avatar.'"></div>
					<div class="col span_10_of_12">
						<p>'.$Reviews_Rating.' Stars</p>
						'.$Reviews_Post.'
					</div>
				</div>';
		}

	}

	if($Member_Auth) {
		if($Reviews_Count > 0) echo '
			<hr>';
		echo '
			<div class="clear"></div>
			<form action="" method="post">
				<div class="section group">
					<input type="hidden" name="action" value="review" />
					<input type="hidden" name="canonical" value="'.$Canonical.'" />
					<div class="col span_1_of_12"><br></div>
					<div class="col span_10_of_12">
						<h3>Leave a Review</h3>
						<select name="rating">
							<option value="1">1</option>
							<option value="2">2</option>
							<option value="3">3</option>
							<option value="4">4</option>
							<option value="5">5</option>
						</select>
						<textarea name="post" required></textarea>
					</div>
					<div class="col span_1_of_12"><br></div>
				</div>
				<div class="section group">
					<div class="col span_1_of_12"><br></div>
					<div class="col span_8_of_12">
						<p><small>If you wish, you can use Markdown for formatting.<br>
						Markdown can be used to make [<a href="#">links</a>](http://example.com),<br>
						<strong>**bold text**</strong>, <em>_italics_</em> and <code>`code`</code>.</small></p>
					</div>
					<div class="col span_2_of_12">
						<input type="submit" value="Review" />
					</div>
					<div class="col span_1_of_12"><br></div>
				</div>
			</form>';
	} else {
		echo '
				<h3>You must <a href="'.$Sitewide_Root.'account?login">login</a> to post a review.</h3>';
	}
