<?php

/**
 * Theme Options v1.1.0
 * Adjust theme settings from the admin dashboard.
 * Find and replace `YourTheme` with your own namepspacing.
 *
 * Created by Michael Fields.
 * https://gist.github.com/mfields/4678999
 *
 * Forked by Chris Ferdinandi
 * http://gomakethings.com
 *
 * Free to use under the MIT License.
 * http://gomakethings.com/mit/
 */


	/**
	 * Theme Options Menu
	 * Each option field requires its own add_settings_field function.
	 */

	// Create theme options menu
	// The content that's rendered on the menu page.
	function merchant_theme_reporting_render_page() {

		$plans = get_posts(
			array(
				'posts_per_page'   => -1,
				'orderby'          => 'menu_order',
				'order'            => 'DESC',
				'post_type'        => 'merchant-prices',
				'post_status'      => 'any',
			)
		);

		$promos = get_posts(
			array(
				'posts_per_page'   => -1,
				'orderby'          => 'menu_order',
				'order'            => 'DESC',
				'post_type'        => 'merchant-promos',
				'post_status'      => 'any',
			)
		);
		$promo_map = array();

		foreach ( $promos as $promo ) {
			$promo_map[$promo->ID] = $promo->post_title;
		}

		$count = 0;
		$total = 0;

		?>
		<div class="wrap">
			<h2><?php _e( 'Sales Reports', 'merchant' ); ?></h2>
			<br>

			<style type="text/css">
			/**
			 * @section Tables
			 * Styling for tables
			 */
			/* line 6, /Users/cferdinandi/Sites/kraken/src/sass/components/_tables.scss */
			.merchant-table {
			  background-color: #ffffff;
			  border-collapse: collapse;
			  border-spacing: 0;
			  font-size: 1.2em;
			  margin-bottom: 1.5625em;
			  max-width: 100%;
			  width: 100%;
			}

			/* line 14, /Users/cferdinandi/Sites/kraken/src/sass/components/_tables.scss */
			.merchant-table th,
			.merchant-table td {
			  text-align: left;
			  padding: 0.5em;
			}

			.merchant-table tr {
				border-left: 1px solid #e5e5e5;
				border-right: 1px solid #e5e5e5;
			}

			.merchant-table tr:first-child {
				border-top: 1px solid #e5e5e5;
			}

			.merchant-table tr:last-child {
				border-bottom: 1px solid #e5e5e5;
			}

			/*@media (min-width: 40em) {
				.merchant-table th:first-child,
				.merchant-table td:first-child {
					border-left: 1px solid #e5e5e5;
				}

				.merchant-table th:last-child,
				.merchant-table td:last-child {
					border-right: 1px solid #e5e5e5;
				}
			}*/

			/* line 20, /Users/cferdinandi/Sites/kraken/src/sass/components/_tables.scss */
			.merchant-table th {
			  border-bottom: 0.125em solid #e5e5e5;
			  font-weight: bold;
			  vertical-align: bottom;
			}

			/* line 27, /Users/cferdinandi/Sites/kraken/src/sass/components/_tables.scss */
			.merchant-table td {
			  border-top: 1px solid #e5e5e5;
			  vertical-align: top;
			}

			/**
			 * Adds zebra striping
			 */
			/* line 35, /Users/cferdinandi/Sites/kraken/src/sass/components/_tables.scss */
			.merchant-table tbody tr:nth-child(odd) {
			  background-color: #f9f9f9;
			}

			/**
			 * Pure CSS responsive tables
			 * Adds label to each cell using the [data-label] attribute
			 * @link https://techblog.livingsocial.com/blog/2015/04/06/responsive-tables-in-pure-css/
			 */
			@media (max-width: 40em) {
			  /* line 57, /Users/cferdinandi/Sites/kraken/src/sass/components/_tables.scss */
			  .merchant-table thead {
			    display: none;
			    visibility: hidden;
			  }
			  /* line 62, /Users/cferdinandi/Sites/kraken/src/sass/components/_tables.scss */
			  .merchant-table tr {
			    border-top: 1px solid #ededed;
			    display: block;
			    padding: 0.5em;
			  }
			  /* line 68, /Users/cferdinandi/Sites/kraken/src/sass/components/_tables.scss */
			  .merchant-table td {
			    border: 0;
			    display: block;
			    padding: 0.25em;
			  }
			  /* line 73, /Users/cferdinandi/Sites/kraken/src/sass/components/_tables.scss */
			  .merchant-table td:before {
			    content: attr(data-label);
			    display: block;
			    font-weight: bold;
			  }
			}

			th.sort-header::-moz-selection { background:transparent; }
			th.sort-header::selection      { background:transparent; }
			th.sort-header {
			  cursor:pointer;
			  }
			th.sort-header::-moz-selection,
			th.sort-header::selection {
			  background:transparent;
			  }
			.merchant-table th.sort-header:after {
			  content:'';
			  float:right;
			  margin-top:7px;
			  border-width:0 4px 4px;
			  border-style:solid;
			  border-color:#404040 transparent;
			  visibility:hidden;
			  }
			.merchant-table th.sort-header:hover:after {
			  visibility:visible;
			  }
			.merchant-table th.sort-up:after,
			.merchant-table th.sort-down:after,
			.merchant-table th.sort-down:hover:after {
			  visibility:visible;
			  opacity:0.4;
			  }
			.merchant-table th.sort-up:after {
			  border-bottom:none;
			  border-width:4px 4px 0;
			  }
			</style>

			<table class="merchant-table" id="merchant-sales-report">
				<thead>
					<tr>
						<th>Date</th>
						<th>Plan</th>
						<th>Purchaser</th>
						<th>Purchase Price</th>
						<th>Promo Code</th>
					</tr>
				</thead>
				<tbody>
					<?php
						foreach ( $plans as $plan ) :
					?>
						<?php
							$summary = get_post_meta( $plan->ID, 'merchant_pricing_report_summary', true );
							$transactions = (array) get_post_meta( $plan->ID, 'merchant_pricing_report', true );

							if ( is_array( $summary ) ) {
								if ( array_key_exists( 'count', $summary ) ) $count = $count + $summary['count'];
								if ( array_key_exists( 'total', $summary ) ) $total = $total + $summary['total'];
							}

							foreach ( $transactions as $key => $transaction ) :
								if ( !empty( $transaction ) ) :
						?>
							<tr>
								<td data-label="Date" data-sort-method="date"><?php echo esc_html( date( 'n/j/Y', $transaction['date'] ) ); ?></td>
								<td data-label="Plan"><?php echo $plan->post_title; ?></td>
								<td data-label="Purchaser"><?php $user = get_user_by( 'id', $transaction['purchaser'] ); echo $user->user_login; ?></td>
								<td data-label="Purchase Price" data-sort-method="number"><?php echo '$' . esc_html( number_format( $transaction['price'], 2 ) ); ?></td>
								<td data-label="Promo Code"><?php if ( array_key_exists( $transaction['promo_code'], $promo_map ) ) { echo $promo_map[$transaction['promo_code']]; } ?></td>
							</tr>
						<?php
								endif;
							endforeach;
						?>
					<?php endforeach; ?>
				</tbody>
			</table>

			<p><strong><?php _e( 'Total Volume' ); ?>:</strong> <?php echo esc_html( $count ); ?></p>
			<p><strong><?php _e( 'Total Revenue' ); ?>:</strong> $<?php echo esc_html( number_format( $total, 2 ) ); ?></p>

			<script type="text/javascript">
				/*!
				 * tablesort v4.0.1 (2016-03-30)
				 * http://tristen.ca/tablesort/demo/
				 * Copyright (c) 2016 ; Licensed MIT
				*/!function(){function a(b,c){if(!(this instanceof a))return new a(b,c);if(!b||"TABLE"!==b.tagName)throw new Error("Element must be a table");this.init(b,c||{})}var b=[],c=function(a){var b;return window.CustomEvent&&"function"==typeof window.CustomEvent?b=new CustomEvent(a):(b=document.createEvent("CustomEvent"),b.initCustomEvent(a,!1,!1,void 0)),b},d=function(a){return a.getAttribute("data-sort")||a.textContent||a.innerText||""},e=function(a,b){return a=a.toLowerCase(),b=b.toLowerCase(),a===b?0:b>a?1:-1},f=function(a,b){return function(c,d){var e=a(c.td,d.td);return 0===e?b?d.index-c.index:c.index-d.index:e}};a.extend=function(a,c,d){if("function"!=typeof c||"function"!=typeof d)throw new Error("Pattern and sort must be a function");b.push({name:a,pattern:c,sort:d})},a.prototype={init:function(a,b){var c,d,e,f,g=this;if(g.table=a,g.thead=!1,g.options=b,a.rows&&a.rows.length>0&&(a.tHead&&a.tHead.rows.length>0?(c=a.tHead.rows[a.tHead.rows.length-1],g.thead=!0):c=a.rows[0]),c){var h=function(){g.current&&g.current!==this&&(g.current.classList.remove("sort-up"),g.current.classList.remove("sort-down")),g.current=this,g.sortTable(this)};for(e=0;e<c.cells.length;e++)f=c.cells[e],f.classList.contains("no-sort")||(f.classList.add("sort-header"),f.tabindex=0,f.addEventListener("click",h,!1),f.classList.contains("sort-default")&&(d=f));d&&(g.current=d,g.sortTable(d))}},sortTable:function(a,g){var h,i=this,j=a.cellIndex,k=e,l="",m=[],n=i.thead?0:1,o=a.getAttribute("data-sort-method"),p=a.getAttribute("data-sort-order");if(i.table.dispatchEvent(c("beforeSort")),g?h=a.classList.contains("sort-up")?"sort-up":"sort-down":(h=a.classList.contains("sort-up")?"sort-down":a.classList.contains("sort-down")?"sort-up":"asc"===p?"sort-down":"desc"===p?"sort-up":i.options.descending?"sort-up":"sort-down",a.classList.remove("sort-down"===h?"sort-up":"sort-down"),a.classList.add(h)),!(i.table.rows.length<2)){if(!o){for(;m.length<3&&n<i.table.tBodies[0].rows.length;)l=d(i.table.tBodies[0].rows[n].cells[j]),l=l.trim(),l.length>0&&m.push(l),n++;if(!m)return}for(n=0;n<b.length;n++)if(l=b[n],o){if(l.name===o){k=l.sort;break}}else if(m.every(l.pattern)){k=l.sort;break}for(i.col=j,n=0;n<i.table.tBodies.length;n++){var q,r=[],s={},t=0,u=0;if(!(i.table.tBodies[n].rows.length<2)){for(q=0;q<i.table.tBodies[n].rows.length;q++)l=i.table.tBodies[n].rows[q],l.classList.contains("no-sort")?s[t]=l:r.push({tr:l,td:d(l.cells[i.col]),index:t}),t++;for("sort-down"===h?(r.sort(f(k,!0)),r.reverse()):r.sort(f(k,!1)),q=0;t>q;q++)s[q]?(l=s[q],u++):l=r[q-u].tr,i.table.tBodies[n].appendChild(l)}}i.table.dispatchEvent(c("afterSort"))}},refresh:function(){void 0!==this.current&&this.sortTable(this.current,!0)}},"undefined"!=typeof module&&module.exports?module.exports=a:window.Tablesort=a}();
				// Basic dates in dd/mm/yy or dd-mm-yy format.
				// Years can be 4 digits. Days and Months can be 1 or 2 digits.
				(function(){
				  var parseDate = function(date) {
				    date = date.replace(/\-/g, '/');
				    date = date.replace(/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2})/, '$1/$2/$3'); // format before getTime

				    return new Date(date).getTime() || -1;
				  };

				  Tablesort.extend('date', function(item) {
				    return (
				      item.search(/(Mon|Tue|Wed|Thu|Fri|Sat|Sun)\.?\,?\s*/i) !== -1 ||
				      item.search(/\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4}/) !== -1 ||
				      item.search(/(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)/i) !== -1
				    ) && !isNaN(parseDate(item));
				  }, function(a, b) {
				    a = a.toLowerCase();
				    b = b.toLowerCase();

				    return parseDate(b) - parseDate(a);
				  });
				}());
				(function(){
				  var cleanNumber = function(i) {
				    return i.replace(/[^\-?0-9.]/g, '');
				  },

				  compareNumber = function(a, b) {
				    a = parseFloat(a);
				    b = parseFloat(b);

				    a = isNaN(a) ? 0 : a;
				    b = isNaN(b) ? 0 : b;

				    return a - b;
				  };

				  Tablesort.extend('number', function(item) {
				    return item.match(/^-?[£\x24Û¢´€]?\d+\s*([,\.]\d{0,2})/) || // Prefixed currency
				      item.match(/^-?\d+\s*([,\.]\d{0,2})?[£\x24Û¢´€]/) || // Suffixed currency
				      item.match(/^-?(\d)*-?([,\.]){0,1}-?(\d)+([E,e][\-+][\d]+)?%?$/); // Number
				  }, function(a, b) {
				    a = cleanNumber(a);
				    b = cleanNumber(b);

				    return compareNumber(b, a);
				  });
				}());
				new Tablesort(document.getElementById( 'merchant-sales-report' ));
			</script>
		</div>
		<?php
	}

	// Add the theme options page to the admin menu
	// Use add_theme_page() to add under Appearance tab (default).
	// Use add_menu_page() to add as it's own tab.
	// Use add_submenu_page() to add to another tab.
	function merchant_theme_reporting_add_page() {

		// add_theme_page( $page_title, $menu_title, $capability, $menu_slug, $function );
		// add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function );
		// add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
		// $page_title - Name of page
		// $menu_title - Label in menu
		// $capability - Capability required
		// $menu_slug - Used to uniquely identify the page
		// $function - Function that renders the options page
		// $theme_page = add_theme_page( __( 'Theme Options', 'merchant' ), __( 'Theme Options', 'merchant' ), 'edit_theme_options', 'theme_options', 'merchant_theme_reporting_render_page' );

		// $theme_page = add_menu_page( __( 'Theme Options', 'merchant' ), __( 'Theme Options', 'merchant' ), 'edit_theme_options', 'theme_options', 'merchant_theme_reporting_render_page' );
		$theme_page = add_submenu_page( 'edit.php?post_type=merchant-prices', __( 'Reporting', 'merchant' ), __( 'Reporting', 'merchant' ), 'edit_theme_options', 'merchant_reporting', 'merchant_theme_reporting_render_page' );
	}
	add_action( 'admin_menu', 'merchant_theme_reporting_add_page' );



	// Restrict access to the theme options page to admins
	function merchant_reporting_page_capability( $capability ) {
		return 'edit_theme_options';
	}
	add_filter( 'option_page_capability_merchant_options', 'merchant_reporting_page_capability' );
