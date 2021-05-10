// Document ready
var ready = ( callback ) => {
	if ( document.readyState != 'loading' ) {
		callback();
	} else {
		document.addEventListener( 'DOMContentLoaded', callback );
	}
}

// Timer used to whether prefetch a link or not.
var timer;

// Document ready callback.
ready(
	() => {
		// List of URLs to prefetch.
		var prefetched_urls = [];
		addEventListener(
			'mouseover',
			function ( e ) {
				// Take in consideration only A tags.
				if ( e.target instanceof HTMLAnchorElement ) {
					var the_href = e.target.attributes.href.value;

					timer = setTimeout(
						function () {

							var test_link = the_href.replace( breeze_prefetch.local_url, '' );
							// Local host domain.
							var local_host = new URL( breeze_prefetch.local_url ).host;
							// Hovered link host domain.
							var hover_host = new URL( the_href ).host;

							if (
								'' !== the_href.trim() &&
								false === prefetched_urls.includes( the_href ) &&
								local_host === hover_host &&
								false === search_for_banned_links( breeze_prefetch.ignore_list, test_link )
							) {
								// Add to the array links that have been prefetched already.
								prefetched_urls.push( the_href.trim() );
								// Activate the prefetch link by adding it to the header.
								var link_tag = document.createElement( 'link' );
								link_tag.href = the_href;
								link_tag.rel = 'prefetch';
								document.head.appendChild( link_tag );
							}
						},
						150
					);

				}
			}
		);
		addEventListener(
			'mouseout',
			function ( e ) {
				clearTimeout( timer );
			}
		);
	} // End ready callback
);

/**
 * Finding if a link is excluded from prefetch.
 *
 * @param ignore_list
 * @param item
 * @returns {boolean}
 */
function search_for_banned_links( ignore_list, item ) {
	var found = false;
	if ( ignore_list.length ) {
		for ( i = 0; i < ignore_list.length; i++ ) {
			if ( -1 !== item.indexOf( ignore_list[ i ] ) || -1 !== ignore_list[ i ].indexOf( item ) ) {
				found = true;
			}
		}
	}
	return found;
}
