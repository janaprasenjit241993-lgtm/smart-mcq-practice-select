# Chapter dropdown ordering fix (timestamp upload order)

Below are minimal, **drop-in** code changes for the three files you named.

---

## 1) `Smart_MCQ_CSV_Loader.php` (PHP)

```php
<?php

class Smart_MCQ_CSV_Loader {

    /**
     * Load all CSV rows from upload directory in ascending timestamp filename order.
     */
    public function load_all_questions_from_dir( string $dir_path ): array {
        $files = glob( trailingslashit( $dir_path ) . '*.csv' );

        if ( ! is_array( $files ) || empty( $files ) ) {
            return [];
        }

        // IMPORTANT: sort by timestamp prefix (1710330000-file.csv), ascending.
        usort( $files, static function( $a, $b ) {
            $ta = self::extract_timestamp_prefix( basename( $a ) );
            $tb = self::extract_timestamp_prefix( basename( $b ) );

            // If both have valid timestamps, use numeric ascending.
            if ( $ta !== null && $tb !== null ) {
                return $ta <=> $tb;
            }

            // If only one has timestamp, keep timestamped file first.
            if ( $ta !== null ) {
                return -1;
            }
            if ( $tb !== null ) {
                return 1;
            }

            // Fallback only when no prefix exists (stable deterministic behavior).
            return strcmp( basename( $a ), basename( $b ) );
        } );

        $all = [];

        foreach ( $files as $file ) {
            $rows = $this->read_csv_rows( $file );
            if ( ! empty( $rows ) ) {
                foreach ( $rows as $row ) {
                    $all[] = $row; // preserve file-read order
                }
            }
        }

        return $all;
    }

    private static function extract_timestamp_prefix( string $filename ): ?int {
        if ( preg_match( '/^(\d+)-.+\.csv$/i', $filename, $m ) ) {
            return (int) $m[1];
        }
        return null;
    }

    private function read_csv_rows( string $file ): array {
        $rows = [];

        if ( ! file_exists( $file ) || ! is_readable( $file ) ) {
            return $rows;
        }

        $handle = fopen( $file, 'r' );
        if ( ! $handle ) {
            return $rows;
        }

        $header = fgetcsv( $handle );
        if ( ! is_array( $header ) ) {
            fclose( $handle );
            return $rows;
        }

        while ( ( $line = fgetcsv( $handle ) ) !== false ) {
            if ( count( $line ) === 0 ) {
                continue;
            }

            $assoc = [];
            foreach ( $header as $i => $key ) {
                $assoc[ $key ] = $line[ $i ] ?? '';
            }
            $rows[] = $assoc;
        }

        fclose( $handle );
        return $rows;
    }
}
```

---

## 2) `ajax-handler.php` (PHP)

```php
<?php

function smart_mcq_get_filters_from_questions( array $questions ): array {
    $chapters_seen = [];
    $chapters      = [];

    foreach ( $questions as $q ) {
        $chapter = isset( $q['chapter'] ) ? trim( (string) $q['chapter'] ) : '';
        if ( $chapter === '' ) {
            continue;
        }

        // Keep first appearance order; skip duplicates.
        if ( ! isset( $chapters_seen[ $chapter ] ) ) {
            $chapters_seen[ $chapter ] = true;
            $chapters[] = $chapter;
        }
    }

    // DO NOT sort($chapters) or usort($chapters).
    return [
        'chapters' => $chapters,
    ];
}
```

---

## 3) Frontend JS file

```js
// Keep chapter order from API response. No alphabetical sorting.
function renderChapterDropdown(chapters) {
  const select = document.querySelector('#mcq-chapter-select');
  if (!select) return;

  select.innerHTML = '';

  const defaultOpt = document.createElement('option');
  defaultOpt.value = '';
  defaultOpt.textContent = 'সব অধ্যায়';
  select.appendChild(defaultOpt);

  // Remove duplicates while preserving first appearance order.
  const seen = new Set();
  const orderedUnique = [];

  for (const ch of chapters || []) {
    const val = (ch || '').trim();
    if (!val || seen.has(val)) continue;
    seen.add(val);
    orderedUnique.push(val);
  }

  // DO NOT do orderedUnique.sort() here.
  for (const chapter of orderedUnique) {
    const opt = document.createElement('option');
    opt.value = chapter;
    opt.textContent = chapter;
    select.appendChild(opt);
  }
}
```

---

## What was wrong

1. CSV files were likely read with default filesystem/alphabetical order (`glob()` + no custom sort, or explicit sort by filename text).
2. Chapters were likely sorted again (`sort()` in PHP or `.sort()` in JS), destroying upload order.
3. De-duplication likely happened after sorting, so first-appearance order was lost.

## Why this fix works

1. CSV files are sorted by numeric timestamp prefix in ascending order before reading.
2. Questions are appended in that exact file order.
3. Chapters are collected by first appearance with a seen-map/Set.
4. No alphabetical sort is applied in PHP or JS.
