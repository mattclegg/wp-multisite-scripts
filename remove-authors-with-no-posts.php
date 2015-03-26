<?php
/*  Copyright 2015  mattclegg  (email : cleggmatt@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
require_once( dirname( __FILE__ ) . '/wp-load.php' );

global $wpdb;
print("<pre>");

$users = array_column( $wpdb->get_results( 'SELECT ID FROM wp_users', ARRAY_A ), 'ID' );
echo "\nUsers found in DB:" . count($users);

$results = array_column( $wpdb->get_results( 'SELECT blog_id FROM wp_blogs WHERE blog_id != 1', ARRAY_A ), 'blog_id' );
echo "\nBlogs found in DB:" . count($results);

foreach($results as $result) {
	foreach($users as $user_id) {
		$no_of_posts = array_column( $wpdb->get_results( "SELECT COUNT(*) AS Total FROM wp_{$result}_posts WHERE post_author='{$user_id}'", ARRAY_A ), 'Total' )[0];

		if($no_of_posts > 0) {
			echo "\nUser {$user_id} wrote {$no_of_posts} posts.";
			$users = array_diff($users, array($user_id));
		}
	}
}

echo "\nUsers who wrote NO posts:" . count($users);

$disposable_users = $wpdb->get_results( "SELECT ID, CONCAT(user_login,' (',user_nicename,' - ',user_email,')') AS name FROM wp_users WHERE ID IN (" . implode(",", $users) . ")", OBJECT );

foreach($disposable_users as $disposable_user) {
	echo "\nDELETING:" . $disposable_user->name;
	$wpdb->delete( "wp_users", array("ID" => $disposable_user->ID) );
}

echo "\n.";