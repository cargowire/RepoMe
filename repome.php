<?php
/**
 * repome, A mini Api creator that turns wordpress into a queryable repository
 *
 * This class allows for the registration of and endpoint as well as the ability to respond to these requests with xml representing posts within wordpress.
 *
 * @author Craig Rowe <http://cargowire.net>
 * @version 1.0
 * @package repo-me
 */
class repome {

	static $datefrom = null;
	static $dateto = null;
	static $posttype = null;
	
	static function register_rewrite_rules($wp_rewrite){
		$feed_rules = array(
			'repome' => 'index.php?repome=1'
		);

		$wp_rewrite->rules = $feed_rules + $wp_rewrite->rules;
		return $wp_rewrite->rules;
	}
	
	static function register_deleted_post($pid){
		file_put_contents(dirname(__FILE__).'/deleted.txt', $pid . PHP_EOL, FILE_APPEND);
	}
	
	static function handle_request($wp){
		if (repome::startsWith($_SERVER["REQUEST_URI"], "/repome")) {
			add_filter( 'posts_where',  array('repome', 'filter_where_between_modified_dates'));
			
			self::$datefrom = ($_GET['datefrom']);
			self::$dateto = ($_GET['dateto']);
			self::$posttype = ($_GET['posttype']);
			$customfields = ($_GET['customfields']);
			$query = new WP_Query();
			$queryParams = array('posts_per_page' => -1);

			if(isset(self::$posttype) && trim(self::$posttype)!=='') {
				$queryParams['post_type'] = esc_sql(self::$posttype);
			}

			$query->query($queryParams);

			echo ('<?xml version="1.0" encoding="UTF-8" ?>');?>
			<posts>
				<?php
				if( $query->have_posts() ) {
					while ($query->have_posts()) : $query->the_post(); ?>
						<post id="<?php echo get_the_ID(); ?>" url="<?php the_permalink(); ?>" modified="<?php the_modified_date('Y-m-d') ?> <?php the_modified_time('H:i:s') ?>" published="<?php the_time('Y-m-d') ?> <?php the_time('H:i:s') ?>">
							<title><?php the_title(); ?></title>
							<author><?php echo get_the_author(); ?></author>
							<?php $image = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ) ); ?>
							<image src="<?php echo $image[0] ?>" />
							<abstract><![CDATA[<?php echo html_entity_decode(get_the_excerpt(), ENT_QUOTES, 'UTF-8') ?>]]></abstract>
							<body><![CDATA[<?php the_content() ?>]]></body>
							<?php
							$catString = "";
							$categories = get_the_category();
							foreach($categories as $category)
							{
								$catString .= $category->name . " ";
							}
							if(is_array($customfields)){
								echo "<fields>";
								foreach($customfields as $customfield)
								{
									$metaData = get_post_meta(get_the_ID(), $customfield, true);
									if($metaData != ''){
										switch($customfield){
											case "enclosure":
												$powerpressvalues = explode("\n", $metaData);
												$metaValue = $powerpressvalues[0];
												break;
											default:
												$metaValue = $metaData;
												break;
										}
										echo "<" . $customfield . ">" . $metaValue . "</" . $customfield . ">";
									}
								}
								echo "</fields>";
							}
							echo '<categories value="' . $catString . '"></categories>';
						echo '</post>';
					endwhile;
				}
				
				// Deleted Posts
				$deletePosts = file(dirname(__FILE__).'/deleted.txt');
				for($i=0;$i<count($deletePosts);$i++){
					echo '<post id="' . trim($deletePosts[$i]) . '" status="deleted"></post>';
				}
				
				remove_filter( 'posts_where', array('repome', 'filter_where_between_modified_dates'));
				
				// pending, draft, auto-draft, future, private, inherit, trash
				$query->query(array( 'status' => array( 'pending', 'draft', 'future', 'private', 'trash' ) ) );
				if( $query->have_posts() ) {
					while ($query->have_posts()) {
						$query->the_post();
						echo '<post id="' . get_the_ID() . '" status="unpublished"></post>';
					}
				}
				?>
			</posts>
			<?php
			exit();
		}
	}
    
    static function filter_where_between_modified_dates($where)
    {
		if(strtotime(self::$datefrom) !== false) {
			$where .= " AND post_modified >= '".self::$datefrom."'";
		}
		if(strtotime(self::$dateto) !== false) {
			$where .= " AND post_modified < '".self::$dateto."'";
		}

		return $where;
    }
    
    function startsWith($haystack, $needle)
	{
		$length = strlen($needle);
		return (substr($haystack, 0, $length) === $needle);
	}
}