<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function smart_mcq_get_csv_upload_dir(): string {
    $uploads    = wp_upload_dir();
    $mcq_subdir = trailingslashit( $uploads['basedir'] ) . 'smart-mcq-csv';
    return $mcq_subdir;
}

function smart_mcq_get_filters_from_questions( array $questions ): array {
    $chapters_seen = array();
    $chapters      = array();

    foreach ( $questions as $question ) {
        $chapter = isset( $question['chapter'] ) ? trim( (string) $question['chapter'] ) : '';
        if ( '' === $chapter ) {
            continue;
        }

        if ( ! isset( $chapters_seen[ $chapter ] ) ) {
            $chapters_seen[ $chapter ] = true;
            $chapters[]                = $chapter;
        }
    }

    return array(
        'chapters' => $chapters,
    );
}

function smart_mcq_ajax_get_filters() {
    $csv_dir   = smart_mcq_get_csv_upload_dir();
    $loader    = new Smart_MCQ_CSV_Loader();
    $questions = $loader->load_all_questions_from_dir( $csv_dir );

    $filters = smart_mcq_get_filters_from_questions( $questions );

    wp_send_json_success( $filters );
}
add_action( 'wp_ajax_smart_mcq_get_filters', 'smart_mcq_ajax_get_filters' );
add_action( 'wp_ajax_nopriv_smart_mcq_get_filters', 'smart_mcq_ajax_get_filters' );
