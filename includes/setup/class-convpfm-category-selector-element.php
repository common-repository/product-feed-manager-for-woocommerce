<?php
/**
 * TVC Category Selector Element Class.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Convpfm_Category_Selector_Element' ) ) :
	class Convpfm_Category_Selector_Element {
		/**
		 * Returns the code for a single row meant for the category mapping table.
		 *
		 * @param object    $category           object containing data of the active category like term_id and name
		 * @param string    $category_children  a string with the children of the active category
		 * @param string    $level_indicator    current active level
		 * @param string    $mode               defines if the category mapping row should contain a description (normal) or a catgory mapping (mapping) column
		 *
		 * @return string
		 */
		
		
		 public static function category_mapping_row( $category, $level_indicator, $mode, $convpfm_prod_mapped_cats, $option) {
			$category_row_class = 'mapping' === $mode ? 'tvc-category-mapping-selector' : 'tvc-category-selector';
			$mode_column  = 'mapping' === $mode ? self::category_mapping_selector( 'catmap', $category->term_id, true, $convpfm_prod_mapped_cats, $option ) : self::category_description_data_item( $category->term_id );
			return '<div class="mb-2 row catTermId termId_'.esc_attr($category->term_id).'">
                <div class="col-6 p-2 ps-4">
                  <div class="form-group shop-category">
                      <label class="form-label-control font-weight-400 text-color fs-12">' . esc_attr($category->name) .' <small>('.esc_attr($category->count). ')</small> '.esc_attr($level_indicator) .'</label>
                  </div>
                </div>
                <div class="col-6 mt-2">
                  <div class="form-group">
                  	<div id="feed-category-' . esc_attr($category->term_id) . '"></div>' .$mode_column . '
					</div>
                </div>
            </div>';
		}

		/**
		 * Returns the code for a category input selector.
		 *
		 * @param string    $identifier     identifier for the selector
		 * @param string    $id             id of the selector
		 * @param boolean   $start_visible  should this selector start visible
		 *
		 * @return string
		 */
		public static function category_mapping_selector( $identifier, $id, $start_visible, $convpfm_prod_mapped_cats, $option ) {
			$display         = $start_visible ? 'initial' : 'none';
			$ident           = '-1' !== $id ? $identifier . '-' . $id : $identifier;
			$category_levels = apply_filters( 'tvc_category_selector_level', 6 );
			$id = esc_attr($id);			
			if(isset($convpfm_prod_mapped_cats[$id]['id']) && isset($convpfm_prod_mapped_cats[$id]['name']) && $convpfm_prod_mapped_cats[$id]['id'] && $convpfm_prod_mapped_cats[$id]['name']){

				$cat_id = esc_attr($convpfm_prod_mapped_cats[$id]['id']);
				$cat_name = esc_attr($convpfm_prod_mapped_cats[$id]['name']);
				$html_code  = '<div id="category-selector-' . esc_attr($ident) . '" style="display:' . esc_attr($display) . '">
					<div id="selected-categories">
					<input type="hidden" name="category-'.esc_attr($id).'" id="category-'.esc_attr($id).'" value="'.esc_attr($cat_id).'">
					<input type="hidden" name="category-name-'.esc_attr($id).'" id="category-name-'.esc_attr($id).'" value="'.esc_attr($cat_name).'">
					</div>
					
					<select class="form-control categorySelect" id="' . esc_attr($ident) . '_0" catId="'.esc_attr($id).'" iscategory="false" style="width:98%" onchange="selectSubCategory(this)">
						<option value="'.esc_attr($cat_id).'">'.esc_attr($cat_name).'</option>
					</select>';
			}else{
				$html_code  = '<div id="category-selector-' . esc_attr($ident) . '" style="display:' . esc_attr($display) . '">
					<div id="selected-categories">
					<input type="hidden" name="category-'.esc_attr($id).'" id="category-'.esc_attr($id).'" value="">
					<input type="hidden" name="category-name-'.esc_attr($id).'" id="category-name-'.esc_attr($id).'" value="">
					</div>
					<select style="width:98%" class="form-control select2 categorySelect" id="' . esc_attr($ident) . '_0" catId="'.esc_attr($id).'" iscategory="false" onchange="selectSubCategory(this)">
					'.$option.'
					</select>';
			} 

			$html_code .= '</div>';
			return $html_code;
		}

		/**
		 * Returns the code for the category description column.
		 *
		 * @param string    $category_id
		 *
		 * @return string
		 */
		private static function category_description_data_item( $category_id ) {
			$category_description = '' !== category_description( $category_id ) ? category_description( $category_id ) : '—';

			$html_code = '<span aria-hidden="true">' . esc_attr($category_description) . '</span>';

			return $html_code;
		}
	}
	// end of TVC_Category_Selector_Element class
endif;