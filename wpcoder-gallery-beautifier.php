<?php
/*
	Plugin Name: Wordpress Gallery Beautifier
	Plugin URI: http://www.yourwordpresscoder.com/plugin/wordpress-gallery-beautifier/
	Description: This plugin does make a better default wordpress gallery :)
	Version: 1.0
	Author: Your Wordpress Coder
	Author URI: http://www.yourwordpresscoder.com/
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

if (!class_exists('WPCoderGalleryBeautifier')) {
	
    class WPCoderGalleryBeautifier {
        
        function __construct() {
            add_action( 'init', array( &$this, 'plugin_init') );  
        }
        
        function plugin_init() {
            
            wp_enqueue_script( 'jquery' );
            wp_enqueue_script( 'jquery-tools', '/' . PLUGINDIR . '/wpcoder-gallery-beautifier/js/jquery.tools.min.js', array('jquery') );
            wp_enqueue_script( 'colorbox', '/' . PLUGINDIR . '/wpcoder-gallery-beautifier/js/jquery.colorbox.min.js', array('jquery') );
            
            add_action( 'wp_head', array( &$this,'add_js') );
            add_filter( 'gallery_style', array( &$this, 'remove_default_gallery_css') );
            add_filter( 'post_gallery', array( &$this, 'gallery_carousel_filter'), 10, 2);

        }
        
		function remove_default_gallery_css( $css ) { 
			return preg_replace( "#<style type='text/css'>(.*?)</style>#s", '', $css ); 
		}
		
		function gallery_carousel_filter() {
			global $post;
            extract(shortcode_atts(array(
                'order'      => 'ASC',
                'orderby'    => 'menu_order ID',
                'id'         => $post->ID,
                'itemtag'    => 'dl',
                'icontag'    => 'dt',
                'captiontag' => 'dd',
                'columns'    => 3,
                'size'       => 'thumbnail',
            ), $attr));
            
            $post_thumb = get_post_thumbnail_id();
            
            $id = intval($id);
            $attachments = get_children( array('post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
			
            if ( empty($attachments) )
                return '';
                
			if ( is_feed() ) {
                $output = "\n";
                foreach ( $attachments as $id => $attachment )
                    if( $id == $post_thumb) continue;
                    $output .= wp_get_attachment_link($id, $size, true) . "\n";
                    
                return $output;
            }
            
            $output = '
            
			<noscript>
				<div class="no-javascript-error">
					' . __('Please, enable JavaScript to see the gallery', 'wpcoder-gallery-beautifier') . '
				</div>
				<style type="text/css">
					.scrollable, .post-gallery .gallery-navigation, .post-gallery .browse { display: none !important; }
				</style>
			</noscript>
            
            <div class="post-gallery">';
                    $output .= '<div class="scrollable">';
                        $output .= '<div class="items">';   
                        
				            foreach ( $attachments as $id => $attachment ) {
                               if( $id == $post_thumb) continue;
                	           $image = wp_get_attachment_image_src($id, 'large');
					           $output .= '<div><a href="'.$image[0].'" class="large">'.wp_get_attachment_image( $id, 'medium', array( 'alt' => '', 'title' => '') ).'</a><a rel="gallery'.$post->ID.'" href="'.$image[0].'" class="zoom"></a></div>';
				            }
				            
                        $output .= '</div><div class="clear" style="clear: both;"></div>';
                        
                    $output .= '</div>';
                	$output .= '<ul class="gallery-navigation">';
                	
                    foreach ( $attachments as $id => $attachment ) {
                        if( $id == $post_thumb) continue;
                        $output .= '<li class="inline-block"><a href="javascript:;"></a></li>';
                    }
                    
                	$output .= '</ul><div class="clear" style="clear: both;"></div>';
					$output .= '<a class="prev browse left"></a><a class="next browse right"></a>';
            
            	$output .= '</div>';
            
        	return $output;
            
		}
        
        function add_js() {       	
        	
        // If isset custom file with styles, ignore current CSS
        if( file_exists( get_stylesheet_directory() . '/wpcoder-gallery-beautifier.css' )) return "<link rel='stylesheet' href='" . get_bloginfo('template_directory') ."/wpcoder-gallery-beautifier.css' type='text/css' />";
        	
?>
<style type="text/css">
.post-gallery {
	position: relative;
	margin: 0 0 30px 0;
}
.post-gallery .scrollable {
	float: left;
	position: relative;
	overflow: hidden;
	border: 1px solid #8ba9be;
	background: #8ba9be;
}
.post-gallery .scrollable .items {
	width: 20000em;
	position: absolute;
	clear: both;
}
.post-gallery .items div {
	float: left;
	position: relative;
}
.post-gallery .scrollable img {
	float: left;
	border: 0;
}
.post-gallery ul.gallery-navigation {
	clear: left;
	float: left;            
	display: block;
	padding: 0;
	margin: 0;    
	background: #c9dfef;
	list-style-type: none;
	text-align: center;
}
.post-gallery ul.gallery-navigation li {
	width: 9px;
	height: 9px;
	margin: 0 2px;
}
.post-gallery ul.gallery-navigation li a {
	margin-top: 7px;
	display: block;
	width: 9px;
	height: 9px;
	background: url(<?php echo WP_PLUGIN_URL ?>/wpcoder-gallery-beautifier/images/carousel-nav.png) left top no-repeat;
}
.post-gallery ul.gallery-navigation .active a {
	background-position: left bottom;
}
.post-gallery a.browse {
	width: 19px;
	height: 19px;
	position: absolute;
	cursor: pointer;
}
.post-gallery a.browse.left {
	background: url(<?php echo WP_PLUGIN_URL ?>/wpcoder-gallery-beautifier/images/carousel-buttons.png) left top no-repeat;
	left: 3px;
	bottom: 8px;
}
.post-gallery a.browse.right {
	background: url(<?php echo WP_PLUGIN_URL ?>/wpcoder-gallery-beautifier/images/carousel-buttons.png) left bottom no-repeat;
	right: 3px;
	bottom: 8px;
}
a.zoom {
	display: none;
}
.post-gallery a.activeZoom {
	display: block;
	opacity: 0.5;
	background: #000 url(<?php echo WP_PLUGIN_URL ?>/wpcoder-gallery-beautifier/images/zoom.png) center center no-repeat;
	position: absolute;
	left: 0;
	top: 0;
}

#colorbox, #cboxOverlay, #cboxWrapper{position:absolute; top:0; left:0; z-index:9999; overflow:hidden;}
#cboxOverlay{position:fixed; width:100%; height:100%;}
#cboxMiddleLeft, #cboxBottomLeft{clear:left;}
#cboxContent{position:relative;}
#cboxLoadedContent{overflow:auto;}
#cboxLoadedContent iframe{display:block; width:100%; height:100%; border:0;}
#cboxTitle{margin:0;}
#cboxLoadingOverlay, #cboxLoadingGraphic{position:absolute; top:0; left:0; width:100%;}
#cboxPrevious, #cboxNext, #cboxClose, #cboxSlideshow{cursor:pointer;}
#cboxOverlay{background:#000;}
#colorbox{}
    #cboxContent{background:#000; margin-top:20px;}
        #cboxLoadedContent{background:#000; padding:5px;}
        #cboxTitle{position:absolute; top:-20px; left:0; color:#ccc;}
        #cboxCurrent{position:absolute; top:-20px; right:0px; color:#ccc;}
        #cboxSlideshow{position:absolute; top:-20px; right:90px; color:#fff;}
        #cboxPrevious{position:absolute; top:50%; left:5px; margin-top:-32px; background:url(<?php echo WP_PLUGIN_URL ?>/wpcoder-gallery-beautifier/images/controls.png) top left no-repeat; width:28px; height:65px; text-indent:-9999px;}
        #cboxPrevious.hover{background-position:bottom left;}
        #cboxNext{position:absolute; top:50%; right:5px; margin-top:-32px; background:url(<?php echo WP_PLUGIN_URL ?>/wpcoder-gallery-beautifier/images/controls.png) top right no-repeat; width:28px; height:65px; text-indent:-9999px;}
        #cboxNext.hover{background-position:bottom right;}
        #cboxLoadingOverlay{background:#000;}
        #cboxLoadingGraphic{background:url(<?php echo WP_PLUGIN_URL ?>/wpcoder-gallery-beautifier/images/loading.png) center center no-repeat;}
        #cboxClose{position:absolute; top:5px; right:5px; display:block; background:url(<?php echo WP_PLUGIN_URL ?>/wpcoder-gallery-beautifier/images/controls.png) top center no-repeat; width:38px; height:19px; text-indent:-9999px;}
        #cboxClose.hover{background-position:bottom center;}

.inline-block { 
    display: -moz-inline-stack;
    display: inline-block;
    _overflow: hidden;
    zoom: 1;
    *display: inline;
}
.no-javascript-error {
	border: 1px solid #00b4f9;
	background: #d7f4ff;
	padding: 4px 10px;
	text-align: center;
}
</style>
<script type="text/javascript">
jQuery.noConflict()(function(){

	var wp_gallery_img_width 	= jQuery('div.post-gallery .items img:first').width();
	var wp_gallery_img_height 	= jQuery('div.post-gallery .items img:first').height();
	
	jQuery('div.post-gallery').css('width', (wp_gallery_img_width + 2) + 'px');
	jQuery('div.post-gallery').css('height', (wp_gallery_img_height + 38) + 'px');

	jQuery('.post-gallery .items div, .post-gallery a.zoom').css('width', wp_gallery_img_width + 'px');
	jQuery('.post-gallery .items div, .post-gallery a.zoom').css('height', wp_gallery_img_height + 'px');

	jQuery('div.post-gallery .scrollable').css('width', (wp_gallery_img_width) + 'px');
	jQuery('div.post-gallery .scrollable').css('height', wp_gallery_img_height + 'px');

	jQuery('.post-gallery ul.gallery-navigation').css('width', (wp_gallery_img_width + 2) + 'px');
	jQuery('.post-gallery ul.gallery-navigation').css('height', 38 + 'px');
	
    jQuery(".post-gallery .scrollable").scrollable({circular: true}).navigator({
			navi:'ul.gallery-navigation'
	}).autoscroll({ 
			autoplay: true, interval: 4000
	});

	jQuery("ul.gallery-navigation li:first").addClass('active');
     
	jQuery('.post-gallery a.large img').hover( function() {
	    jQuery( this).parent().next('a.zoom').addClass('activeZoom').show();
	}, function() {
	         
	});
	     
	jQuery('.post-gallery a.zoom').hover( function() {
	         
	}, function() {
	    jQuery(this).removeClass('activeZoom').hide();
	});

    jQuery('.post-gallery .items div:not(.cloned) a[rel]').colorbox({
        'slideshowStart' 	: '',
        'slideshowStop' 	: '',
        'current' 			: '',
        'previous' 			: '',
        'next' 				: '',
        'close' 			: ''
    });
    

});
</script>

<?php
        }
    }
        
    global $WPCoderGalleryBeautifier;
    $WPCoderGalleryBeautifier = new WPCoderGalleryBeautifier();

}