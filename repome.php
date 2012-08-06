<?php
/*
Plugin Name: Repo Me
Plugin URI: http://cargowire.net/projects/repo-me
Description: A plugin that provides an endpoint for posts querying.  Aimed at usage by caching clients that require checking for updates
Version: 1.0
Author: Craig Rowe
Author URI: http://cargowire.net
*/

function apime_rewrite_rules( $wp_rewrite ) {
    $feed_rules = array(
        'repome.php' => 'index.php?repome=1'
    );

    $wp_rewrite->rules = $feed_rules + $wp_rewrite->rules;
    return $wp_rewrite->rules;
}

$datefrom = null;
$dateto = null;

//from, to
function apime_handler( $wp ) {
    if (startsWith($_SERVER["REQUEST_URI"], "/repome.php")) {
        add_filter( 'posts_where', 'apime_filter_where_between' );
        global $datefrom, $dateto, $posttype;
        $datefrom = ($_GET['datefrom']);
        $dateto = ($_GET['dateto']);
        $posttype = ($_GET['posttype']);
        $query = new WP_Query();
        $queryParams = array('posts_per_page' => -1);

		if(isset($posttype) && trim($posttype)!=='') {
			$queryParams['post_type'] = esc_sql($posttype);
        }

		$query->query($queryParams);

        echo ('<?xml version="1.0" encoding="UTF-8" ?>');?>
    <posts>
        <?php
        if( $query->have_posts() ) {
            while ($query->have_posts()) : $query->the_post(); ?>
                <post id="<?php echo get_the_ID(); ?>" url="<?php the_permalink(); ?>" modified="<?php the_modified_date('Y-m-d') ?> <?php the_modified_time('H:i:s') ?>" published="<?php the_time('Y-m-d') ?> <?php the_time('H:i:s') ?>">
                    <title><?php the_title(); ?></title>
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
                    } ?>
                    <categories value="<?php echo $catString; ?>"></categories>
                </post>
        <?php
            endwhile;
        }
        ?>
    </posts>
        <?php
        remove_filter( 'posts_where', 'apime_filter_where_between' );
        exit();
    }

}

function apime_filter_where_between( $where = '' ) {
    global $datefrom, $dateto;
    if(strtotime($datefrom) !== false) {
        $where .= " AND post_modified >= '".$datefrom."'";
    }
    if(strtotime($dateto) !== false) {
        $where .= " AND post_modified < '".$dateto."'";
    }

    return $where;
}

function startsWith($haystack, $needle)
{
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
}

add_filter( 'generate_rewrite_rules','apime_rewrite_rules' );
add_action( 'parse_request', 'apime_handler');