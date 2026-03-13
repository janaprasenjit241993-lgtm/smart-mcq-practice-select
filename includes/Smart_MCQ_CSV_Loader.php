<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Smart_MCQ_CSV_Loader {

    /**
     * Load all CSV rows from a directory in ascending timestamp-prefix order.
     *
     * Expected filename format: 1710330000-basic-chemistry.csv
     */
    public function load_all_questions_from_dir( string $dir_path ): array {
        $files = glob( trailingslashit( $dir_path ) . '*.csv' );

        if ( ! is_array( $files ) || empty( $files ) ) {
            return array();
        }

        usort(
            $files,
            static function ( $a, $b ) {
                $ta = self::extract_timestamp_prefix( basename( $a ) );
                $tb = self::extract_timestamp_prefix( basename( $b ) );

                if ( null !== $ta && null !== $tb ) {
                    if ( $ta === $tb ) {
                        return strcmp( basename( $a ), basename( $b ) );
                    }
                    return $ta <=> $tb;
                }

                if ( null !== $ta ) {
                    return -1;
                }

                if ( null !== $tb ) {
                    return 1;
                }

                return strcmp( basename( $a ), basename( $b ) );
            }
        );

        $all_questions = array();

        foreach ( $files as $file ) {
            $rows = $this->read_csv_rows( $file );
            if ( empty( $rows ) ) {
                continue;
            }

            foreach ( $rows as $row ) {
                $all_questions[] = $row;
            }
        }

        return $all_questions;
    }

    private static function extract_timestamp_prefix( string $filename ): ?int {
        if ( preg_match( '/^(\d+)-.+\.csv$/i', $filename, $matches ) ) {
            return (int) $matches[1];
        }

        return null;
    }

    private function read_csv_rows( string $file_path ): array {
        $rows = array();

        if ( ! file_exists( $file_path ) || ! is_readable( $file_path ) ) {
            return $rows;
        }

        $handle = fopen( $file_path, 'r' );
        if ( false === $handle ) {
            return $rows;
        }

        $header = fgetcsv( $handle );
        if ( ! is_array( $header ) ) {
            fclose( $handle );
            return $rows;
        }

        while ( ( $line = fgetcsv( $handle ) ) !== false ) {
            if ( empty( $line ) ) {
                continue;
            }

            $row = array();
            foreach ( $header as $index => $column_name ) {
                $row[ $column_name ] = isset( $line[ $index ] ) ? $line[ $index ] : '';
            }
            $rows[] = $row;
        }

        fclose( $handle );

        return $rows;
    }
}
