<?php
/**
 * Custom WPSL Template for Checkout Page
 * Layout: Left panel (store list) + Right panel (map)
 */
global $wpsl_settings, $wpsl;

$output         = $this->get_custom_css();
$autoload_class = ( !$wpsl_settings['autoload'] ) ? 'class="wpsl-not-loaded"' : '';

$output .= '<div id="wpsl-wrap" class="wpsl-checkout-layout">' . "\r\n";

// Two column layout
$output .= '<div class="wpsl-checkout-container" style="display: flex; gap: 20px; flex-wrap: wrap;">' . "\r\n";

// LEFT PANEL - Store List (4 columns width)
$output .= '<div class="wpsl-checkout-left" style="flex: 0 0 35%; min-width: 300px;">' . "\r\n";

// Search form
$output .= '<div class="wpsl-search wpsl-clearfix ' . $this->get_css_classes() . '">' . "\r\n";
$output .= '<div id="wpsl-search-wrap">' . "\r\n";
$output .= '<form autocomplete="off">' . "\r\n";
$output .= '<div class="wpsl-input">' . "\r\n";
$output .= '<div><label for="wpsl-search-input">' . esc_html( $wpsl->i18n->get_translation( 'search_label', __( 'Your location', 'wpsl' ) ) ) . '</label></div>' . "\r\n";
$output .= '<input id="wpsl-search-input" type="text" value="' . apply_filters( 'wpsl_search_input', '' ) . '" name="wpsl-search-input" placeholder="Nhập địa chỉ của bạn..." aria-required="true" />' . "\r\n";
$output .= '</div>' . "\r\n";

// Radius and Results dropdowns
if ( $wpsl_settings['radius_dropdown'] || $wpsl_settings['results_dropdown']  ) {
    $output .= '<div class="wpsl-select-wrap">' . "\r\n";

    if ( $wpsl_settings['radius_dropdown'] ) {
        $output .= '<div id="wpsl-radius">' . "\r\n";
        $output .= '<label for="wpsl-radius-dropdown">' . esc_html( $wpsl->i18n->get_translation( 'radius_label', __( 'Search radius', 'wpsl' ) ) ) . '</label>' . "\r\n";
        $output .= '<select id="wpsl-radius-dropdown" class="wpsl-dropdown" name="wpsl-radius">' . "\r\n";
        $output .= $this->get_dropdown_list( 'search_radius' ) . "\r\n";
        $output .= '</select>' . "\r\n";
        $output .= '</div>' . "\r\n";
    }

    if ( $wpsl_settings['results_dropdown'] ) {
        $output .= '<div id="wpsl-results">' . "\r\n";
        $output .= '<label for="wpsl-results-dropdown">' . esc_html( $wpsl->i18n->get_translation( 'results_label', __( 'Results', 'wpsl' ) ) ) . '</label>' . "\r\n";
        $output .= '<select id="wpsl-results-dropdown" class="wpsl-dropdown" name="wpsl-results">' . "\r\n";
        $output .= $this->get_dropdown_list( 'max_results' ) . "\r\n";
        $output .= '</select>' . "\r\n";
        $output .= '</div>' . "\r\n";
    }

    $output .= '</div>' . "\r\n";
}

// Category filter (hidden, controlled by delivery method selection)
// Category IDs: 42=PICKUP, 43=DELIVERY
$output .= '<input type="hidden" id="wpsl-category-filter" name="wpsl-category" value="43" />' . "\r\n";

$output .= '<div class="wpsl-search-btn-wrap"><input id="wpsl-search-btn" type="submit" value="' . esc_attr( $wpsl->i18n->get_translation( 'search_btn_label', __( 'Search', 'wpsl' ) ) ) . '"></div>' . "\r\n";

$output .= '</form>' . "\r\n";
$output .= '</div>' . "\r\n";
$output .= '</div>' . "\r\n";

// Store result list
$output .= '<div id="wpsl-result-list" style="margin-top: 20px;">' . "\r\n";
$output .= '<div id="wpsl-stores" '. $autoload_class .'>' . "\r\n";
$output .= '<ul></ul>' . "\r\n";
$output .= '</div>' . "\r\n";
$output .= '<div id="wpsl-direction-details">' . "\r\n";
$output .= '<ul></ul>' . "\r\n";
$output .= '</div>' . "\r\n";
$output .= '</div>' . "\r\n";

$output .= '</div>' . "\r\n"; // End left panel

// RIGHT PANEL - Google Map (8 columns width)
$output .= '<div class="wpsl-checkout-right" style="flex: 1; min-width: 400px;">' . "\r\n";
$output .= '<div id="wpsl-gmap" class="wpsl-gmap-canvas" style="height: 600px; width: 100%; position: relative;">' . "\r\n";
$output .= '<div id="wpsl-map-error" style="display: none; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; background: #f8f8f8; padding: 30px; border-radius: 8px; border: 2px dashed #ccc; max-width: 80%;">' . "\r\n";
$output .= '<h3 style="margin: 0 0 10px 0; color: #666;">🗺️ Bản đồ tạm thời không khả dụng</h3>' . "\r\n";
$output .= '<p style="margin: 0; color: #999;">Google Maps API chưa được cấu hình.<br>Danh sách cửa hàng vẫn hoạt động bình thường.</p>' . "\r\n";
$output .= '</div>' . "\r\n";
$output .= '</div>' . "\r\n";
$output .= '</div>' . "\r\n"; // End right panel

$output .= '</div>' . "\r\n"; // End container

if ( $wpsl_settings['show_credits'] ) {
    $output .= '<div class="wpsl-provided-by">'. sprintf( __( "Search provided by %sWP Store Locator%s", "wpsl" ), "<a target='_blank' href='https://wpstorelocator.co'>", "</a>" ) .'</div>' . "\r\n";
}

$output .= '</div>' . "\r\n"; // End wrap

// JavaScript to handle Google Maps errors
$output .= '<script type="text/javascript">
jQuery(document).ready(function($) {
	// Check for Google Maps after a short delay
	setTimeout(function() {
		if (typeof google === "undefined" || typeof google.maps === "undefined") {
			console.warn("Google Maps API not loaded - showing error message");
			$("#wpsl-map-error").show();
			$("#wpsl-gmap").css("background", "#f0f0f0");
		}
	}, 2000);

	// Listen for WPSL map initialization errors
	$(document).on("wpsl_map_error", function(e, error) {
		console.error("WPSL Map Error:", error);
		$("#wpsl-map-error").show();
	});
});
</script>' . "\r\n";

// Custom CSS for checkout layout
$output .= '<style>
.wpsl-checkout-layout {
    width: 100%;
}

.wpsl-checkout-container {
    display: flex;
    gap: 20px;
}

.wpsl-checkout-left {
    flex: 0 0 35%;
    min-width: 300px;
    max-height: 600px;
    overflow-y: auto;
}

.wpsl-checkout-right {
    flex: 1;
    min-width: 400px;
    min-height: 600px;
}

#wpsl-result-list {
    max-height: 450px;
    width: auto !important;
}

#wpsl-result-list ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

#wpsl-stores li {
    padding: 15px;
    border: 1px solid #e0e0e0;
    margin-bottom: 10px;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s ease;
}

#wpsl-stores li:hover {
    border-color: #c44569;
    background: #fff5f7;
    transform: translateX(5px);
}

#wpsl-stores li.wpsl-active {
    border-color: #c44569;
    background: #fff5f7;
}

/* Responsive */
@media (max-width: 992px) {
    .wpsl-checkout-container {
        flex-direction: column;
    }

    .wpsl-checkout-left,
    .wpsl-checkout-right {
        flex: 1 1 100%;
        min-width: 100%;
    }

    .wpsl-checkout-right {
        min-height: 400px;
    }
}

@media (max-width: 768px) {
    .wpsl-checkout-left {
        max-height: none;
    }

    #wpsl-result-list {
        max-height: none;
    }
}
</style>';

return $output;
