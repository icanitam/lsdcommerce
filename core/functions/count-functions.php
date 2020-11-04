<?php 

/**
 * Taxonomy Post Counter
 * based on taxonomy name and term_id
 * 
 * @since 1.0.0
 * @return 
 */
function lsdc_count_taxonomy_post( $name, $termid = false ){
    $terms = get_terms(
        array(
			'taxonomy'   => $name,
			'include'    => get_queried_object()->term_id,
            'hide_empty' => false,
        )
    );
 
    $count = 0;
    foreach ($terms as $key => $value) {
        $count += $value->count;
    }
    return abs($count);
}

?>