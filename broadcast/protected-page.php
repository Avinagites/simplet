<?php

	$TextTitle = 'Protected Page';
	$WebTitle = 'Protected Page';
	$Canonical = 'protected-page';
	$FeaturedImage = '';
	$Description = '';
	$Keywords = 'protected example page default styles';

	require '../request.php';

if (htmlentities($Request['path'], ENT_QUOTES, 'UTF-8') == '/' . $Canonical) {

	require '../header.php';

	if ($Member_Auth) {

		echo '
	<div class="content">
		<h2>Top Secret</h2>
		<p class="textcenter">You are now authorized to view this super secret page.</p>
		<p class="textcenter">Yes, this is it.</p>
	</div>';

	} else {

		echo '
	<div class="content">
		<h2>Protected Page</h2>
		<p class="textcenter">Sorry, you need to log in to view this super secret content.</p>
	</div>';

	}

	require '../footer.php';

} ?>
