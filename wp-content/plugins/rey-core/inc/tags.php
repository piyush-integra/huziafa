<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if(!function_exists('reycore__texts')):
	/**
	 * Text strings
	 *
	 * @since 1.6.6
	 **/
	function reycore__texts( $text = '' )
	{
		$texts = apply_filters('reycore/texts', [
			'qty' => esc_attr_x( 'Qty', 'Product quantity input tooltip', 'rey-core' ),
			'cannot_update_cart' => esc_html__('Couldn\'t update cart!', 'rey-core'),
			'added_to_cart_text' => esc_html__('ADDED TO CART', 'rey-core'),
		]);

		if( !empty($text) && isset($texts[$text]) ){
			return $texts[$text];
		}
	}
endif;

if(!function_exists('reycore__get_svg_icon')):
	/**
	 * Wrapper for Rey Theme's rey__get_svg_icon()
	 *
	 * @since 1.0.0
	 */
	function reycore__get_svg_icon( $args = [] ) {
		if( function_exists('rey__get_svg_icon') ){
			return rey__get_svg_icon( $args );
		}
		return false;
	}
endif;

if(!function_exists('reycore__social_icons_sprite_path')):
	/**
	 * Retrieve social icon sprite path
	 *
	 * @since 1.3.7
	 **/
	function reycore__social_icons_sprite_path()
	{
		return REY_CORE_URI . 'assets/images/social-icons-sprite.svg';
	}
endif;


if(!function_exists('reycore__get_svg_social_icon')):
	/**
	 * Wrapper for Rey Theme's rey__get_svg_icon()
	 * with the addition of the social icon sprite.
	 *
	 * @since 1.0.0
	 */
	function reycore__get_svg_social_icon( $args = [] ) {
		if( function_exists('rey__get_svg_icon') ){
			$args['sprite_path'] = reycore__social_icons_sprite_path();
			return rey__get_svg_icon( $args );
		}
		return false;
	}
endif;


if(!function_exists('reycore__icons_sprite_path')):
	/**
	 * Retrieve icon sprite path
	 *
	 * @since 1.3.7
	 **/
	function reycore__icons_sprite_path()
	{
		return REY_CORE_URI . 'assets/images/icon-sprite.svg';
	}
endif;


if(!function_exists('reycore__get_svg_icon__core')):
	/**
	 * Wrapper for Rey Theme's rey__get_svg_icon()
	 * with the addition of the social icon sprite.
	 *
	 * @since 1.0.0
	 */
	function reycore__get_svg_icon__core( $args = [] ) {
		if( function_exists('rey__get_svg_icon') ){
			$args['sprite_path'] = reycore__icons_sprite_path();
			$args['version'] = REY_CORE_VERSION;
			return rey__get_svg_icon( $args );
		}
		return false;
	}
endif;


if(!function_exists('reycore__social_sharing_icons_list')):
	/**
	 * Social Icons List
	 *
	 * @helper https://gist.github.com/HoldOffHunger/1998b92acb80bc83547baeaff68aaaf4
	 *
	 * @since 1.3.0
	 **/
	function reycore__social_sharing_icons_list()
	{
		return apply_filters('reycore/social_sharing', [
			'digg' => [
				'title' => esc_html__('Digg', 'rey-core'),
				'url' => 'http://digg.com/submit?url={url}',
				'icon' => 'digg',
				'color' => '005be2'
			],
			'mail' => [
				'title' => esc_html__('Mail', 'rey-core'),
				'url' => 'mailto:?body={url}',
				'icon' => 'envelope',
				// 'color' => ''
			],
			'facebook' => [
				'title' => esc_html__('FaceBook', 'rey-core'),
				'url' => 'https://www.facebook.com/sharer/sharer.php?u={url}',
				'icon' => 'facebook',
				'url_attributes' => 'onclick="window.open(this.href, \'facebook-share\',\'width=580,height=296\');return false;" ',
				'color' => '#1877f2'
			],
			'facebook-f' => [
				'title' => esc_html__('Facebook', 'rey-core'),
				'url' => 'https://www.facebook.com/sharer/sharer.php?u={url}',
				'icon' => 'facebook-f',
				'url_attributes' => 'onclick="window.open(this.href, \'facebook-share\',\'width=580,height=296\');return false;" ',
				'color' => '#1877f2'
			],
			'linkedin' => [
				'title' => esc_html__('LinkedIn', 'rey-core'),
				'url' => 'http://www.linkedin.com/shareArticle?mini=true&url={url}&title={title}',
				'icon' => 'linkedin',
				'url_attributes' => 'onclick="window.open(this.href, \'linkedin-share\', \'width=930,height=720\');return false;"',
				'color' => '#007bb5'
			],
			'pinterest' => [
				'title' => esc_html__('Pinterest', 'rey-core'),
				'url' => 'http://pinterest.com/pin/create/button/?url={url}&description={title}',
				'icon' => 'pinterest',
				'url_attributes' => 'onclick="window.open(this.href, \'pinterest-share\', \'width=490,height=530\');return false;"',
				'color' => '#e82b2d'
			],
			'pinterest-p' => [
				'title' => esc_html__('Pinterest', 'rey-core'),
				'url' => 'http://pinterest.com/pin/create/button/?url={url}&description={title}',
				'icon' => 'pinterest-p',
				'url_attributes' => 'onclick="window.open(this.href, \'pinterest-share\', \'width=490,height=530\');return false;"',
				'color' => '#e82b2d'
			],
			'reddit' => [
				'title' => esc_html__('Reddit', 'rey-core'),
				'url' => 'https://reddit.com/submit?url={url}&title={title}',
				'icon' => 'reddit',
				'color' => '#ff4500'
			],
			'skype' => [
				'title' => esc_html__('Skype', 'rey-core'),
				'url' => 'https://web.skype.com/share?url={url}&text={text}',
				'icon' => 'skype',
				'color' => '#00aff0'
			],
			'tumblr' => [
				'title' => esc_html__('Tumblr', 'rey-core'),
				'url' => 'https://www.tumblr.com/widgets/share/tool?canonicalUrl={url}&title={title}',
				'icon' => 'tumblr',
				'color' => '#35465d'
			],
			'twitter' => [
				'title' => esc_html__('Twitter', 'rey-core'),
				'url' => 'http://twitter.com/share?text={title}&url={url}',
				'icon' => 'twitter',
				'url_attributes' => 'onclick="window.open(this.href, \'twitter-share\', \'width=550,height=235\');return false;" ',
				'color' => '#1da1f2'
			],
			'vk' => [
				'title' => esc_html__('VK', 'rey-core'),
				'url' => 'http://vk.com/share.php?url={url}&title={title}',
				'icon' => 'vk',
				'color' => '#4a76a8'
			],
			'weibo' => [
				'title' => esc_html__('Weibo', 'rey-core'),
				'url' => 'http://service.weibo.com/share/share.php?url={url}&appkey=&title={title}&pic=&ralateUid=',
				'icon' => 'weibo',
				'color' => '#df2029'
			],
			'whatsapp' => [
				'title' => esc_html__('WhatsApp', 'rey-core'),
				'url' => 'https://wa.me/?text={title}+{url}',
				'icon' => 'whatsapp',
				'color' => '#25d366'
			],
			'xing' => [
				'title' => esc_html__('Xing', 'rey-core'),
				'url' => 'https://www.xing.com/spi/shares/new?url={url}',
				'icon' => 'xing',
				'color' => '#026466'
			],
			'copy' => [
				'title' => esc_html__('Copy URL', 'rey-core'),
				'url' => '#',
				'icon' => 'link',
				'url_attributes' => 'data-url="{url}" class="js-copy-url u-copy-url"',
				'color' => '#a3a7ab'
			],
		] );
	}
endif;


if ( ! function_exists( 'reycore__socialShare' ) ) :
	/**
	 * Prints HTML with social sharing.
	 * @since 1.0.0
	 */
	function reycore__socialShare( $args = [])
	{
		$title = urlencode( html_entity_decode( get_the_title(), ENT_COMPAT, 'UTF-8') );
		$url = esc_url( get_the_permalink() );

		$defaults = [
			'share_items' => apply_filters('reycore/post/social_share', [ 'twitter', 'facebook', 'linkedin', 'pinterest', 'mail' ], $title, $url),
			'class' => '',
			'colored' => false
		];

		$args = wp_parse_args( $args, $defaults );

		$classes = esc_attr($args['class']);

		if( $args['colored'] ){
			$classes .= ' --colored';
		}

		if( is_array($args['share_items']) && !empty($args['share_items']) ): ?>
			<ul class="rey-postSocialShare <?php echo $classes; ?>">
				<?php

				$all_icons = reycore__social_sharing_icons_list();

				foreach($args['share_items'] as $item):
					echo '<li class="rey-shareItem--'. $item .'">';

					if( isset($all_icons[$item]) ){

						$cleanup = function($string) use ($url, $title) {
							$cleaned_up = str_replace('{url}', $url, $string);
							$cleaned_up = str_replace('{title}', $title, $cleaned_up);
							return $cleaned_up;
						};

						$attributes = isset($all_icons[$item]['url_attributes']) ? $cleanup($all_icons[$item]['url_attributes']) : '';

						if( $args['colored'] && isset($all_icons[$item]['color']) ){
							$attributes .= sprintf(' style="background-color: %s;"', $all_icons[$item]['color']);
						}

						printf( '<a href="%1$s" %2$s title="%3$s">%4$s</a>',
							$cleanup( $all_icons[$item]['url'] ),
							$attributes,
							esc_attr(get_the_title()),
							reycore__get_svg_social_icon( ['id' => $all_icons[$item]['icon']] )
						);
					}

					echo '</li>';
				endforeach;
				?>
			</ul>
			<!-- .rey-postSocialShare -->
		<?php
		endif;

	}
endif;


if(!function_exists('reycore__social_icons_list')):
	/**
	 * Social Icons List
	 *
	 * @since 1.0.0
	 **/
	function reycore__social_icons_list()
	{
		return [
			'android',
			'apple',
			'behance',
			'bitbucket',
			'codepen',
			'delicious',
			'deviantart',
			'digg',
			'dribbble',
			'envelope',
			'facebook',
			'facebook-f',
			'flickr',
			'foursquare',
			'free-code-camp',
			'github',
			'gitlab',
			'globe',
			'google-plus',
			'houzz',
			'instagram',
			'jsfiddle',
			'link',
			'linkedin',
			'medium',
			'meetup',
			'mixcloud',
			'odnoklassniki',
			'pinterest',
			'pinterest-p',
			'product-hunt',
			'reddit',
			'rss',
			'shopping-cart',
			'skype',
			'slideshare',
			'snapchat',
			'soundcloud',
			'spotify',
			'stack-overflow',
			'steam',
			'stumbleupon',
			'telegram',
			'thumb-tack',
			'tripadvisor',
			'tumblr',
			'twitch',
			'twitter',
			'viber',
			'vimeo',
			'vimeo-v',
			'vk',
			'weibo',
			'weixin',
			'whatsapp',
			'wordpress',
			'xing',
			'yelp',
			'youtube',
			'500px',
		];
	}
endif;


if(!function_exists('reycore__social_icons_list_select2')):
	/**
	 * Social Icons List for a select list
	 *
	 * @since 1.0.0
	 **/
	function reycore__social_icons_list_select2( $type = 'social' )
	{
		$new_list = [];

		if( $type === 'social' ){
			$list = reycore__social_icons_list();

			foreach( $list as $v ){
				$new_list[$v] = ucwords(str_replace('-',' ', $v));
			}
		}
		elseif( $type === 'share' ){
			$list = reycore__social_sharing_icons_list();

			foreach( $list as $k => $v ){
				$new_list[$k] = $v['title'];
			}
		}

		return $new_list;
	}
endif;


if(!function_exists('reycore__get_page_title')):
	/**
	 * Get the page title
	 *
	 * @since 1.0.0
	 */
	function reycore__get_page_title() {
		$title = '';

		if ( class_exists('WooCommerce') && is_shop() ) {

			$shop_page_id = wc_get_page_id( 'shop' );
			$page_title   = get_the_title( $shop_page_id );
			$title = apply_filters( 'woocommerce_page_title', $page_title );
		}
		elseif ( is_home() ) {
			$title = get_the_title( get_option( 'page_for_posts' ) );
		}
		elseif ( is_singular() ) {
			$title = get_the_title();
		} elseif ( is_search() ) {
			/* translators: %s: Search term. */
			$title = sprintf( __( 'Search Results for: %s', 'rey-core' ), get_search_query() );
			// show page
			if ( get_query_var( 'paged' ) ) {
				/* translators: %s is the page number. */
				$title .= sprintf( __( '&nbsp;&ndash; Page %s', 'rey-core' ), get_query_var( 'paged' ) );
			}
		} elseif ( is_category() ) {
			$title = single_cat_title( '', false );
		} elseif ( is_tag() ) {
			$title = single_tag_title( '', false );
		} elseif ( is_author() ) {
			$title = '<span class="vcard">' . get_the_author() . '</span>';
		} elseif ( is_year() ) {
			$title = get_the_date( _x( 'Y', 'yearly archives date format', 'rey-core' ) );
		} elseif ( is_month() ) {
			$title = get_the_date( _x( 'F Y', 'monthly archives date format', 'rey-core' ) );
		} elseif ( is_day() ) {
			$title = get_the_date( _x( 'F j, Y', 'daily archives date format', 'rey-core' ) );
		} elseif ( is_tax( 'post_format' ) ) {
			if ( is_tax( 'post_format', 'post-format-aside' ) ) {
				$title = _x( 'Asides', 'post format archive title', 'rey-core' );
			} elseif ( is_tax( 'post_format', 'post-format-gallery' ) ) {
				$title = _x( 'Galleries', 'post format archive title', 'rey-core' );
			} elseif ( is_tax( 'post_format', 'post-format-image' ) ) {
				$title = _x( 'Images', 'post format archive title', 'rey-core' );
			} elseif ( is_tax( 'post_format', 'post-format-video' ) ) {
				$title = _x( 'Videos', 'post format archive title', 'rey-core' );
			} elseif ( is_tax( 'post_format', 'post-format-quote' ) ) {
				$title = _x( 'Quotes', 'post format archive title', 'rey-core' );
			} elseif ( is_tax( 'post_format', 'post-format-link' ) ) {
				$title = _x( 'Links', 'post format archive title', 'rey-core' );
			} elseif ( is_tax( 'post_format', 'post-format-status' ) ) {
				$title = _x( 'Statuses', 'post format archive title', 'rey-core' );
			} elseif ( is_tax( 'post_format', 'post-format-audio' ) ) {
				$title = _x( 'Audio', 'post format archive title', 'rey-core' );
			} elseif ( is_tax( 'post_format', 'post-format-chat' ) ) {
				$title = _x( 'Chats', 'post format archive title', 'rey-core' );
			}
		} elseif ( is_post_type_archive() ) {
			$title = post_type_archive_title( '', false );
		} elseif ( is_tax() ) {
			$title = single_term_title( '', false );
		} elseif ( is_404() ) {
			$title = __( 'Page Not Found', 'rey-core' );
		}

		$title = apply_filters( 'reycore/tags/get_the_title', $title );

		return $title;
	}
endif;



if(!function_exists('reycore__get_video_html')):
	/**
	 * Get HTML5 video markup
	 *
	 * @since 1.0.0
	 */
	function reycore__get_video_html( $args = [] ){

		$defaults = [
			'video_url' => '',
			'class' => '',
			'params' => [
				'class'=>'rey-hostedVideo-inner elementor-background-video-hosted elementor-html5-video',
				'loop' => 'loop',
				'muted'=>'muted',
				'autoplay'=>'autoplay',
				// 'preload'=>'metadata',
			],
			'start' => 0,
			'end' => 0,
			'mobile' => false,
		];

		$args = reycore__wp_parse_args( $args, $defaults );

		if( empty($args['video_url']) ){
			return;
		}

		$args['params']['src'] = esc_attr($args['video_url']);

		if( $args['start'] || $args['end'] ){
			$args['params']['src'] = sprintf( '%s#t=%s%s',
				$args['params']['src'],
				$args['start'] ? $args['start'] : 0,
				$args['end'] ? ',' . $args['end'] : ''
			);
		}

		if( !$args['mobile'] ){
			$args['class'] .= ' elementor-hidden-phone';
		}
		else {
			$args['params']['playsinline'] = 'playsinline';
		}

		return sprintf(
			'<div class="rey-hostedVideo %s" data-video-params=\'%s\'></div>',
				esc_attr($args['class']),
				wp_json_encode($args['params'])
		);
	}
endif;

if(!function_exists('reycore__get_youtube_iframe_html')):
	/**
	 * Get YouTube video iframe HTML
	 *
	 * @since 1.0.0
	 */
	function reycore__get_youtube_iframe_html( $args = [] ){

		$defaults            =  [
			'video_id'          => '',
			'video_url'         => '',
			'class'             => '',
			'html_id'           => '',
			'add_preview_image' => false,
			'mobile'            => false,
			'params'            => [
				'enablejsapi'      => 1,
				'rel'              => 0,
				'showinfo'         => 0,
				'controls'         => 0,
				'autoplay'         => 1,
				'disablekb'        => 1,
				'mute'             => 1,
				'fs'               => 0,
				'iv_load_policy'   => 3,
				'loop'             => 1,
				'modestbranding'   => 1,
				'start'            => 0,
				'end'              => 0,
			]
		];

		$args = reycore__wp_parse_args( $args, $defaults );

		if( empty($args['video_id']) && !empty($args['video_url']) ){
			$args['video_id'] = reycore__extract_youtube_id( $args['video_url'] );
			$args['params']['start'] = reycore__extract_youtube_start( $args['video_url'] );
		}

		if( empty($args['video_id']) ){
			return false;
		}

		$preview = '';

		if( $args['add_preview_image'] ){
			$preview = reycore__get_youtube_preview_image_html([
				'video_id' => $args['video_id'],
				'class' => $args['class'],
			]);
		}

		if( !$args['mobile'] ){
			$args['class'] .= ' elementor-hidden-phone';
		}
		else {
			$args['params']['playsinline'] = 1;
		}

		return sprintf(
			'<div class="rey-youtubeVideo %s" data-video-params=\'%s\' data-video-id="%s"><div class="rey-youtubeVideo-inner elementor-background-video-embed" id="%s" ></div></div>%s',
				esc_attr($args['class']),
				wp_json_encode($args['params']),
				esc_attr($args['video_id']),
				esc_attr($args['html_id']),
				$preview
		);
	}
endif;

if(!function_exists('reycore__get_youtube_preview_image_html')):
	/**
	 * Get YouTube video preview image HTML
	 *
	 * @since 1.0.0
	 */
	function reycore__get_youtube_preview_image_html( $args = [] ){

		$defaults = [
			'video_id' => '',
			'class' => '',
		];

		$args = reycore__wp_parse_args( $args, $defaults );

		if( empty($args['video_id']) ){
			return;
		}

		return sprintf(
			'<div class="rey-youtubePreview %2$s"><img src="//img.youtube.com/vi/%1$s/maxresdefault.jpg" data-default-src="//img.youtube.com/vi/%1$s/hqdefault.jpg" alt="" /></div>',
			esc_attr($args['video_id']),
			esc_attr($args['class'])
		);
	}
endif;


if(!function_exists('reycore__extract_youtube_id')):
	/**
	 * Extract Youtube ID from URL
	 *
	 * @since 1.0.0
	 **/
	function reycore__extract_youtube_id( $url )
	{
		// Here is a sample of the URLs this regex matches: (there can be more content after the given URL that will be ignored)
		// http://youtu.be/dQw4w9WgXcQ
		// http://www.youtube.com/embed/dQw4w9WgXcQ
		// http://www.youtube.com/watch?v=dQw4w9WgXcQ
		// http://www.youtube.com/?v=dQw4w9WgXcQ
		// http://www.youtube.com/v/dQw4w9WgXcQ
		// http://www.youtube.com/e/dQw4w9WgXcQ
		// http://www.youtube.com/user/username#p/u/11/dQw4w9WgXcQ
		// http://www.youtube.com/sandalsResorts#p/c/54B8C800269D7C1B/0/dQw4w9WgXcQ
		// http://www.youtube.com/watch?feature=player_embedded&v=dQw4w9WgXcQ
		// http://www.youtube.com/?feature=player_embedded&v=dQw4w9WgXcQ
		// It also works on the youtube-nocookie.com URL with the same above options.
		// It will also pull the ID from the URL in an embed code (both iframe and object tags)
		preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);

		if( isset($match[1]) && $youtube_id = $match[1] ){
			return $youtube_id;
		}

		return false;
	}
endif;


if(!function_exists('reycore__extract_youtube_start')):
	/**
	 * Extract Youtube start
	 *
	 * @since 1.0.0
	 **/
	function reycore__extract_youtube_start( $url )
	{
		parse_str($url, $query);

		if( isset($query['t']) && $start = absint($query['t']) ){
			return $start;
		}

		return 0;
	}
endif;


if(!function_exists('reycore__get_next_posts_url')):
	/**
	 * Retrieves the next posts page link.
	 * based on `get_next_posts_link`
	 *
	 * @since 1.0.0
	 *
	 * @global int      $paged
	 * @global WP_Query $wp_query
	 *
	 * @param int    $max_page Optional. Max pages. Default 0.
	 * @return string|void next posts url.
	 */
	function reycore__get_next_posts_url( $max_page = 0 ) {
		global $paged, $wp_query;

		if ( ! $max_page ) {
			$max_page = $wp_query->max_num_pages;
		}

		if ( ! $paged ) {
			$paged = 1;
		}


		$nextpage = intval( $paged ) + 1;

		if ( ! is_single() && ( $nextpage <= $max_page ) ) {
			return next_posts( $max_page, false );
		}
	}
endif;


if(!function_exists('reycore__ajax_load_more_pagination')):
	/**
	 * Show ajax load more pagination markup
	 *
	 * @since 1.0.0
	 **/
	function reycore__ajax_load_more_pagination( $args = [] )
	{
		$btn_text = get_theme_mod('loop_pagination_ajax_text', '');
		$btn_end_text = get_theme_mod('loop_pagination_ajax_end_text', '');

		$pagination_args = apply_filters('reycore/load_more_pagination_args', wp_parse_args( $args, [
			'url' => reycore__get_next_posts_url(),
			'class' => 'btn btn-line-active',
			'post_type' => get_post_type(),
			'target' => 'ul.products',
			'text' => $btn_text !== '' ? $btn_text : esc_html__('SHOW MORE', 'rey-core'),
			'end_text' => $btn_end_text !== '' ? $btn_end_text : esc_html__('END', 'rey-core'),
			'counter_current_page' => true,
		]));

		if( $pagination_args['url'] ){

			$attributes = [];

			if( get_theme_mod('loop_pagination_ajax_counter', false) ){
				$total    = wc_get_loop_prop( 'total' );
				$per_page = wc_get_loop_prop( 'per_page' );
				$paged    = wc_get_loop_prop( 'current_page' );

				$from     = min( $total, $per_page * $paged );
				$to       = $total;

				if( $pagination_args['counter_current_page'] ){
					$from = $paged;
					$to = ceil( $total / $per_page );
				}

				$attributes[] = sprintf('data-count="(%s / %s)"', absint($from), absint($to));
			}

			$attributes[] = sprintf('data-history="%s"', get_theme_mod('loop_pagination_ajax_history', true) ? '1' : '0');

			$pagination_args['url'] = str_replace('?reynotemplate=1', '', $pagination_args['url']);
			$pagination_args['url'] = str_replace('&reynotemplate=1', '', $pagination_args['url']);
			$pagination_args['url'] = str_replace('&#038;reynotemplate=1', '', $pagination_args['url']);

			printf( '<nav class="rey-ajaxLoadMore"><a href="%1$s" class="rey-ajaxLoadMore-btn %2$s" data-post-type="%3$s" data-target="%4$s" data-end-text="%6$s" %7$s>%5$s</a><div class="rey-lineLoader"></div></nav>',
				esc_url( $pagination_args['url']),
				esc_attr( $pagination_args['class']),
				esc_attr( $pagination_args['post_type'] ),
				esc_attr( $pagination_args['target'] ),
				_x($pagination_args['text'], 'Ajax load more posts or products button text.', 'rey-core'),
				_x($pagination_args['end_text'], 'Ajax load more end text.', 'rey-core'),
				implode(' ', $attributes)
			);
		}
	}
endif;

if(!function_exists('reycore__remove_paged_pagination')):
	/**
	 * Remove default pagination in blog
	 *
	 * @since 1.0.0
	 */
	function reycore__remove_paged_pagination() {
		if( get_theme_mod('blog_pagination', 'paged') !== 'paged' ){
			remove_action('rey/post_list', 'rey__pagination', 50);
		}
	}
endif;
add_action('wp', 'reycore__remove_paged_pagination');


if(!function_exists('reycore__pagination')):
	/**
	 * Wrapper for wp pagination
	 *
	 * @since 1.0.0
	 */
	function reycore__pagination() {
		if( ($blog_pagination = get_theme_mod('blog_pagination', 'paged')) && $blog_pagination !== 'paged' ){
			reycore__get_template_part( 'template-parts/misc/pagination-' . $blog_pagination );
		}
	}
endif;
add_action('rey/post_list', 'reycore__pagination', 50);


if(!function_exists('reycore__get_post_term_thumbnail')):
/**
 * Extract Thumbnail ID & URL from Post or WooCOmmerce Term
 *
 * @since 1.3.0
 **/
function reycore__get_post_term_thumbnail()
{
	if( class_exists('WooCommerce') && is_tax() ){
		$term = get_queried_object();
		$thumb_id = get_term_meta( $term->term_id, 'thumbnail_id', true );
		return [
			'id' => $thumb_id,
			'url' => wp_get_attachment_url(  $thumb_id )
		];
	}
	elseif( is_singular() ){
		return [
			'id' => get_post_thumbnail_id(),
			'url' => get_the_post_thumbnail_url()
		];
	}
}
endif;

if(!function_exists('reycore__single_post_add_share_buttons')):
	/**
	 * Adds social sharing icons in single post footer
	 *
	 * @since 1.0.0
	 */
	function reycore__single_post_add_share_buttons(){
		if( get_theme_mod('post_share', true) ) {
			reycore__socialShare([
				'class' => 'text-center text-sm-right',
				'colored' => true
			]);
		}
	}
endif;
add_action('rey/single_post/footer', 'reycore__single_post_add_share_buttons' );

if(!function_exists('reycore__limit_text')):
	/**
	 * Limit words in a string
	 *
	 * @since 1.3.7
	 **/
	function reycore__limit_text($text, $limit)
	{
		if (str_word_count($text, 0) > $limit) {
			$words = str_word_count($text, 2);
			$pos = array_keys($words);
			$text = substr($text, 0, $pos[$limit]) . '...';
		}
		return $text;
	}
endif;


if(!function_exists('reycore__sidebar_wrap_before')):
	/**
	 * Wrap sidebar widgets into a block
	 *
	 * @since 1.5.0
	 **/
	function reycore__sidebar_wrap_before( $index )
	{
		if( !is_admin() ){

			$rey_shop_sidebars = [
				'shop-sidebar',
				'filters-sidebar',
				'filters-top-sidebar'
			];

			$classes[] = in_array($index, $rey_shop_sidebars) ? 'rey-ecommSidebar' : '';
			$classes[] = ($sidebar_title_layout = get_theme_mod('sidebar_title_layouts', '')) ? 'widget-title--' . $sidebar_title_layout : '';

			printf( '<div class="rey-sidebarInner-inside %s">', implode(' ', $classes) );
		}
	}
	add_action( 'dynamic_sidebar_before', 'reycore__sidebar_wrap_before', 0 );
endif;


if(!function_exists('reycore__sidebar_wrap_after')):
	/**
	 * Wrap sidebar widgets into a block
	 *
	 * @since 1.5.0
	 **/
	function reycore__sidebar_wrap_after()
	{
		if( !is_admin() ){
			echo '</div>';
		}
	}
	add_action( 'dynamic_sidebar_after', 'reycore__sidebar_wrap_after', 90 );
endif;


if(!function_exists('reycore__remove_404_page')):
	/**
	 * Remove default 404 page
	 *
	 * @since 1.5.0
	 */
	function reycore__remove_404_page() {
		if( get_theme_mod('404_gs', '') !== '' ){
			remove_action('rey/404page', 'rey__404page', 10);
		}
	}
endif;
add_action('wp', 'reycore__remove_404_page');


if(!function_exists('reycore__404page')):
	/**
	 * Add global section 404 page content
	 *
	 * @since 1.5.0
	 */
	function reycore__404page() {
		if( $gs = get_theme_mod('404_gs', '') ){
			echo ReyCore_GlobalSections::do_section( $gs );
		}
	}
endif;
add_action('rey/404page', 'reycore__404page');

add_filter('rey/404page/container_classes', function($class){

	if( $gs = get_theme_mod('404_gs', '') && get_theme_mod('404_gs_stretch', false) ){
		$class .= ' --stretch';
	}

	return $class;
});



if(!function_exists('reycore__filter_scripts_params')):
	/**
	 * Filter rey script params
	 *
	 * @since 1.5.0
	 **/
	function reycore__filter_scripts_params($params) {

		if( isset($params['theme_js_params']['menu_prevent_delays']) && !get_theme_mod('header_nav_hover_delays', true) ){
			$params['theme_js_params']['menu_prevent_delays'] = true;
		}

		if( isset($params['theme_js_params']['menu_hover_overlay']) && !get_theme_mod('header_nav_hover_overlay', true) ){
			$params['theme_js_params']['menu_hover_overlay'] = false;
		}

		return $params;
	}
	endif;
	add_filter('rey/main_script_params', 'reycore__filter_scripts_params', 10, 3);
