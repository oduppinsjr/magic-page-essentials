<?php
if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Magic_Pages_List_Table extends WP_List_Table {

    function __construct() {
        parent::__construct( [
            'singular' => 'custom_magic_page',
            'plural'   => 'custom_magic_pages',
            'ajax'     => false,
        ] );
        error_log( __METHOD__ . ' called' );
    }
    function get_columns() {
        $cols = [
            'cb'     => 'Select',
            'title'  => 'Title',
            'author' => 'Author',
            'date'   => 'Date',
        ];
        error_log( __METHOD__ . ' columns: ' . print_r( $cols, true ) );
        return $cols;
    }

    // Optional but recommended for sortable headers (you should add this)
    function get_sortable_columns() {
        error_log( __METHOD__ . ' called' );
        return [
            'title'  => ['title', false],
            'author' => ['author', false],
            'date'   => ['date', false],
        ];
    }

    // This is called for each column that doesn't have a custom column_<name> method
    function column_default( $item, $column_name ) {
        error_log( __METHOD__ . " called for column '$column_name'" );
        if ( isset( $item->$column_name ) ) {
            return esc_html( $item->$column_name );
        }
        return '—';
    }

    // Checkbox column - properly output input HTML here
    function column_cb( $item ) {
        error_log( __METHOD__ . ' called for item ID: ' . $item->ID );
        return sprintf( '<input type="checkbox" name="post[]" value="%s" />', $item->ID );
    }

    // Custom rendering for title column
    function column_title( $item ) {
        error_log( __METHOD__ . " called for item ID: {$item->ID}" );

        $raw_title = $item->title;
        $post_obj = get_post( $item->ID );

        // Initialize processed title as raw by default
        $processed_title = $raw_title;

        // Check if Magic Pages processing functions exist, apply if they do
        if ( function_exists( 'get_magic_page_title' ) ) {
            $processed_title = get_magic_page_title( $raw_title, $post_obj );
            error_log( __METHOD__ . " after get_magic_page_title: $processed_title" );
        }

        if ( function_exists( 'parse_magic_page_if_shortcode' ) ) {
            $processed_title = parse_magic_page_if_shortcode( $processed_title );
            error_log( __METHOD__ . " after parse_magic_page_if_shortcode: $processed_title" );
        }

        if ( function_exists( 'apply_spintax_filter' ) ) {
            $processed_title = apply_spintax_filter( $processed_title );
            error_log( __METHOD__ . " after apply_spintax_filter: $processed_title" );
        }

        // Apply shortcode and 'cities' filter last
        if ( has_filter( 'cities' ) || has_shortcode( $processed_title, '' ) ) {
            $processed_title = apply_filters( 'cities', do_shortcode( $processed_title ) );
            error_log( __METHOD__ . " after apply_filters cities and do_shortcode: $processed_title" );
        }

        // Prepare admin links
        $edit_link = get_edit_post_link( $item->ID );
        $view_link = get_permalink( $item->ID );

        $actions = [
            'edit' => sprintf( '<a href="%s">%s</a>', esc_url( $edit_link ), __( 'Edit' ) ),
            'view' => sprintf( '<a href="%s" rel="permalink">%s</a>', esc_url( $view_link ), __( 'View' ) ),
        ];

        // Output: title wrapped with edit link + row actions, HTML allowed
        $output = sprintf(
            '<strong><a href="%s">%s</a></strong> %s',
            esc_url( $edit_link ),
            wp_kses_post( $processed_title ),
            $this->row_actions( $actions )
        );

        error_log( __METHOD__ . " final output: " . strip_tags( $output ) );

        return $output;
    }


    // Custom rendering for author column
    function column_author( $item ) {
        error_log( __METHOD__ . ' called for item ID: ' . $item->ID );
        $author = get_userdata( $item->author );
        return esc_html( $author ? $author->display_name : '' );
    }

    // Custom rendering for date column
    function column_date( $item ) {
        error_log( __METHOD__ . ' called for item ID: ' . $item->ID );
        return esc_html( mysql2date( get_option( 'date_format' ), $item->date ) );
    }
    
    function display() {
        $this->display_tablenav( 'top' );
        error_log( 'Table classes: ' . print_r( $this->get_table_classes(), true ) );
        ?>
        
        <table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
            <thead>
                <?php $this->print_column_headers(); ?>
            </thead>
            <tbody id="the-list">
                <?php $this->display_rows_or_placeholder(); ?>
            </tbody>
            <tfoot>
                <?php $this->print_column_headers(); ?>
            </tfoot>
        </table>
        <?php
        $this->display_tablenav( 'bottom' );
    }

    // This method renders all rows, one <tr> per item
    function display_rows() {
        error_log( __METHOD__ . ' called, item count: ' . count( $this->items ) );

        if ( empty( $this->items ) ) {
            $columns = $this->get_columns();
            $col_count = count( $columns );
            echo '<tr class="no-items">';
            echo '<td class="colspanchange" colspan="' . $col_count . '">' . $this->no_items() . '</td>';
            echo '</tr>';
            return;
        }

        foreach ( $this->items as $item ) {
            error_log( 'single_row_columns output: ' . $this->single_row_columns( $item ) );
            echo '<tr>';
            $this->single_row_columns( $item );
            echo '</tr>';
        }
    }

    function single_row_columns( $item ) {
        $columns = $this->get_columns();
        error_log( __METHOD__ . ' called with columns: ' . print_r( $columns, true ) );
        foreach ( $columns as $column_name => $column_display_name ) {
            error_log( __METHOD__ . ' iterating column: ' . $column_name );
            echo '<td>';
            if ( method_exists( $this, 'column_' . $column_name ) ) {
                error_log( __METHOD__ . ' calling column_' . $column_name );
                echo call_user_func( [ $this, 'column_' . $column_name ], $item );
            } else {
                error_log( __METHOD__ . ' calling column_default for ' . $column_name );
                echo $this->column_default( $item, $column_name );
            }
            echo '</td>';
        }
    }

    function display_rows_or_placeholder() {
        if ( empty( $this->items ) ) {
            $columns = $this->get_columns();
            $col_count = count( $columns );
            echo '<tr class="no-items">';
            echo '<td class="colspanchange" colspan="' . $col_count . '">';
            $this->no_items();
            echo '</td>';
            echo '</tr>';
        } else {
            $this->display_rows();
        }
    }

    // Main data preparation method
    function prepare_items() {
        global $wpdb;

        $per_page = 20;
        $current_page = $this->get_pagenum();
        $offset = ( $current_page - 1 ) * $per_page;

        $orderby_map = [
            'title'  => 'p.post_title',
            'author' => 'p.post_author',
            'date'   => 'p.post_date',
        ];

        $orderby = ! empty( $_REQUEST['orderby'] ) && isset( $orderby_map[ $_REQUEST['orderby'] ] )
            ? $orderby_map[ $_REQUEST['orderby'] ]
            : 'p.post_date';

        $order = ! empty( $_REQUEST['order'] ) && in_array( strtoupper( $_REQUEST['order'] ), [ 'ASC', 'DESC' ], true )
            ? strtoupper( $_REQUEST['order'] )
            : 'DESC';

        // Count total Magic Pages
        $total_items = $wpdb->get_var( "
            SELECT COUNT(DISTINCT p.ID)
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'page'
            AND pm.meta_key = '_location_id'
            AND p.post_status IN ('publish','draft','pending')
        " );

        // Fetch data
        $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT p.ID, p.post_title AS title, p.post_author AS author, p.post_date AS date
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'page'
            AND pm.meta_key = '_location_id'
            AND p.post_status IN ('publish','draft','pending')
            ORDER BY $orderby $order
            LIMIT %d OFFSET %d
        ", $per_page, $offset ), OBJECT );

        $this->items = $results;

        // Setup internal column headers for print_column_headers()
        $columns = $this->get_columns();
        $hidden = [];
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = [$columns, $hidden, $sortable];

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil( $total_items / $per_page ),
        ]);
    }


    function no_items() {
        error_log( __METHOD__ . ' called' );
        _e( 'No Magic Pages found.' );
    }
}