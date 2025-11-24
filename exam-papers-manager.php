<?php
/**
 * Plugin Name: Exam Papers Manager
 * Plugin URI: https://yourwebsite.com
 * Description: A comprehensive exam papers management system with filtering and document upload capabilities.
 * Version: 1.0.0
 * Author: JeyKrish
 * License: GPL v2 or later
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('EPM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('EPM_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('EPM_VERSION', '1.0.0');

class ExamPapersManager {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Create custom post type
        add_action('init', array($this, 'create_exam_papers_post_type'));
        
        // Add AJAX handlers
        add_action('wp_ajax_upload_exam_paper', array($this, 'handle_exam_paper_upload'));
        add_action('wp_ajax_filter_exam_papers', array($this, 'filter_exam_papers'));
        add_action('wp_ajax_nopriv_filter_exam_papers', array($this, 'filter_exam_papers'));
        add_action('wp_ajax_delete_exam_paper', array($this, 'delete_exam_paper'));
        add_action('wp_ajax_get_exam_paper', array($this, 'get_exam_paper'));
        add_action('wp_ajax_update_exam_paper', array($this, 'update_exam_paper'));
        add_action('wp_ajax_bulk_delete_papers', array($this, 'bulk_delete_papers'));
        add_action('wp_ajax_filter_admin_papers', array($this, 'filter_admin_papers'));
        add_action('wp_ajax_export_exam_papers', array($this, 'export_exam_papers'));
        add_action('wp_ajax_export_selected_papers', array($this, 'export_selected_papers'));
        
        // Add shortcode
        add_shortcode('exam_papers_display', array($this, 'display_exam_papers_shortcode'));
    }
    
    public function activate() {
        $this->create_database_tables();
        $this->create_exam_papers_post_type();
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    public function create_database_tables() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'exam_papers';
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        title varchar(255) NOT NULL,
        qualification varchar(100) NOT NULL,
        year_of_paper varchar(10) NOT NULL,
        resource_type varchar(100) NOT NULL,
        file_url varchar(500) NOT NULL,
        file_type varchar(10) NOT NULL,
        upload_date datetime DEFAULT CURRENT_TIMESTAMP,
        priority_order int(11) DEFAULT 0,
        PRIMARY KEY (id),
        KEY priority_order (priority_order)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // Add priority_order column if it doesn't exist (for existing installations)
    $row = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '$table_name' AND column_name = 'priority_order'");
    
    if(empty($row)){
        $wpdb->query("ALTER TABLE $table_name ADD priority_order int(11) DEFAULT 0");
    }
}
    
    public function create_exam_papers_post_type() {
        $labels = array(
            'name' => 'Exam Papers',
            'singular_name' => 'Exam Paper',
            'menu_name' => 'Exam Papers',
            'add_new' => 'Add New Paper',
            'add_new_item' => 'Add New Exam Paper',
            'edit_item' => 'Edit Exam Paper',
            'new_item' => 'New Exam Paper',
            'view_item' => 'View Exam Paper',
            'search_items' => 'Search Exam Papers',
        );
        
        $args = array(
            'labels' => $labels,
            'public' => true,
            'has_archive' => true,
            'menu_icon' => 'dashicons-media-document',
            'supports' => array('title', 'editor', 'thumbnail'),
            'show_in_menu' => false, // We'll add our custom menu
        );
        
        register_post_type('exam_papers', $args);
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Exam Papers',
            'Exam Papers',
            'manage_options',
            'exam-papers-manager',
            array($this, 'admin_page'),
            'dashicons-media-document',
            30
        );
        
        add_submenu_page(
            'exam-papers-manager',
            'Upload Papers',
            'Upload Papers',
            'manage_options',
            'exam-papers-upload',
            array($this, 'upload_page')
        );
        
        add_submenu_page(
            'exam-papers-manager',
            'Manage Papers',
            'Manage Papers',
            'manage_options',
            'exam-papers-manage',
            array($this, 'manage_page')
        );
    }
    
    public function admin_page() {
        include EPM_PLUGIN_PATH . 'templates/admin-dashboard.php';
    }
    
    public function upload_page() {
        include EPM_PLUGIN_PATH . 'templates/admin-upload.php';
    }
    
    public function manage_page() {
        include EPM_PLUGIN_PATH . 'templates/admin-manage.php';
    }
    
    public function enqueue_frontend_scripts() {
        wp_enqueue_style('epm-frontend-style', EPM_PLUGIN_URL . 'assets/css/frontend.css', array(), EPM_VERSION);
        wp_enqueue_script('epm-frontend-script', EPM_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), EPM_VERSION, true);
        
        wp_localize_script('epm-frontend-script', 'epm_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('epm_nonce')
        ));
    }
    
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'exam-papers') !== false) {
            wp_enqueue_style('epm-admin-style', EPM_PLUGIN_URL . 'assets/css/admin.css', array(), EPM_VERSION);
            wp_enqueue_script('epm-admin-script', EPM_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), EPM_VERSION, true);
            
            wp_localize_script('epm-admin-script', 'epm_admin_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('epm_admin_nonce')
            ));
        }
    }
    
    public function handle_exam_paper_upload() {
    if (!wp_verify_nonce($_POST['nonce'], 'epm_admin_nonce')) {
        wp_die('Security check failed');
    }
    
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions');
    }
    
    $title = sanitize_text_field($_POST['title']);
    $qualification = sanitize_text_field($_POST['qualification']);
    $year_of_paper = sanitize_text_field($_POST['year_of_paper']);
    $resource_type = sanitize_text_field($_POST['resource_type']);
    $prioritise_paper_id = isset($_POST['prioritise_paper']) ? intval($_POST['prioritise_paper']) : 0;
    
    // Handle file upload
    if (!empty($_FILES['exam_file']['name'])) {
        $uploaded_file = wp_handle_upload($_FILES['exam_file'], array('test_form' => false));
        
        if ($uploaded_file && !isset($uploaded_file['error'])) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'exam_papers';
            
            $file_type = pathinfo($uploaded_file['file'], PATHINFO_EXTENSION);
            
            // Calculate priority order
            $priority_order = 0;
            
            if ($prioritise_paper_id > 0) {
                // Get the maximum priority order
                $max_priority = $wpdb->get_var("SELECT MAX(priority_order) FROM $table_name");
                $max_priority = $max_priority ? intval($max_priority) : 0;
                
                // Set prioritised paper to highest priority
                $wpdb->update(
                    $table_name,
                    array('priority_order' => $max_priority + 2),
                    array('id' => $prioritise_paper_id),
                    array('%d'),
                    array('%d')
                );
                
                // New paper gets second highest priority
                $priority_order = $max_priority + 1;
            } else {
                // Normal upload: get highest priority and add 1
                $max_priority = $wpdb->get_var("SELECT MAX(priority_order) FROM $table_name");
                $priority_order = $max_priority ? intval($max_priority) + 1 : 1;
            }
            
            $result = $wpdb->insert(
                $table_name,
                array(
                    'title' => $title,
                    'qualification' => $qualification,
                    'year_of_paper' => $year_of_paper,
                    'resource_type' => $resource_type,
                    'file_url' => $uploaded_file['url'],
                    'file_type' => $file_type,
                    'priority_order' => $priority_order,
                ),
                array('%s', '%s', '%s', '%s', '%s', '%s', '%d')
            );
            
            if ($result) {
                wp_send_json_success('Exam paper uploaded successfully!');
            } else {
                wp_send_json_error('Failed to save exam paper data');
            }
        } else {
            wp_send_json_error('File upload failed: ' . $uploaded_file['error']);
        }
    } else {
        wp_send_json_error('No file selected');
    }
}
    
    public function filter_exam_papers() {
    if (!wp_verify_nonce($_POST['nonce'], 'epm_nonce')) {
        wp_die('Security check failed');
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'exam_papers';
    
    // Get filter values
    $qualification = sanitize_text_field($_POST['qualification']);
    $year_of_paper = sanitize_text_field($_POST['year_of_paper']);
    $resource_type = sanitize_text_field($_POST['resource_type']);
    
    // Debug logging
    error_log('Filter values received:');
    error_log('Qualification: ' . $qualification);
    error_log('Year of paper: ' . $year_of_paper);
    error_log('Resource type: ' . $resource_type);
    
    $where_conditions = array();
    $where_values = array();
    
    // Handle multiple values (comma-separated)
    if (!empty($qualification)) {
        $qualifications = array_map('trim', explode(',', $qualification));
        $qualification_placeholders = implode(',', array_fill(0, count($qualifications), '%s'));
        $where_conditions[] = "qualification IN ($qualification_placeholders)";
        $where_values = array_merge($where_values, $qualifications);
    }
    
    if (!empty($year_of_paper)) {
        $years = array_map('trim', explode(',', $year_of_paper));
        $year_placeholders = implode(',', array_fill(0, count($years), '%s'));
        $where_conditions[] = "year_of_paper IN ($year_placeholders)";
        $where_values = array_merge($where_values, $years);
    }
    
    if (!empty($resource_type)) {
        $types = array_map('trim', explode(',', $resource_type));
        $type_placeholders = implode(',', array_fill(0, count($types), '%s'));
        $where_conditions[] = "resource_type IN ($type_placeholders)";
        $where_values = array_merge($where_values, $types);
    }
    
    $where_clause = "";
    if (!empty($where_conditions)) {
        $where_clause = "WHERE " . implode(" AND ", $where_conditions);
    }
    
    // Updated ORDER BY to use priority_order first, then upload_date
    $query = "SELECT * FROM $table_name $where_clause ORDER BY priority_order DESC, upload_date DESC";
    
    // Debug the final query
    error_log('Final query: ' . $query);
    error_log('Where values: ' . print_r($where_values, true));
    
    if (!empty($where_values)) {
        $results = $wpdb->get_results($wpdb->prepare($query, $where_values));
    } else {
        $results = $wpdb->get_results($query);
    }
    
    error_log('Results found: ' . count($results));
    
    ob_start();
    if ($results) {
        foreach ($results as $paper) {
            include EPM_PLUGIN_PATH . 'templates/paper-item.php';
        }
    } else {
        echo '<div class="epm-no-results">';
        echo '<div class="epm-no-results-icon">📄</div>';
        echo '<h3>No exam papers found</h3>';
        echo '<p>Try adjusting your filters or check back later for new content.</p>';
        echo '</div>';
    }
    $html = ob_get_clean();
    
    wp_send_json_success($html);
}
    
    public function display_exam_papers_shortcode($atts) {
        ob_start();
        include EPM_PLUGIN_PATH . 'templates/frontend-display.php';
        return ob_get_clean();
    }
    
    public function delete_exam_paper() {
        if (!wp_verify_nonce($_POST['nonce'], 'epm_admin_nonce')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $paper_id = intval($_POST['paper_id']);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'exam_papers';
        
        // Get file URL before deleting to remove file
        $paper = $wpdb->get_row($wpdb->prepare("SELECT file_url FROM $table_name WHERE id = %d", $paper_id));
        
        if ($paper) {
            // Delete file from uploads
            $file_path = str_replace(wp_upload_dir()['url'], wp_upload_dir()['path'], $paper->file_url);
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            
            // Delete from database
            $result = $wpdb->delete($table_name, array('id' => $paper_id), array('%d'));
            
            if ($result) {
                wp_send_json_success('Paper deleted successfully');
            } else {
                wp_send_json_error('Failed to delete paper');
            }
        } else {
            wp_send_json_error('Paper not found');
        }
    }
    
    public function get_exam_paper() {
        if (!wp_verify_nonce($_POST['nonce'], 'epm_admin_nonce')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $paper_id = intval($_POST['paper_id']);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'exam_papers';
        
        $paper = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $paper_id));
        
        if ($paper) {
            wp_send_json_success($paper);
        } else {
            wp_send_json_error('Paper not found');
        }
    }
    
    public function update_exam_paper() {
        if (!wp_verify_nonce($_POST['nonce'], 'epm_admin_nonce')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $paper_id = intval($_POST['paper_id']);
        $title = sanitize_text_field($_POST['title']);
        $qualification = sanitize_text_field($_POST['qualification']);
        $year_of_paper = sanitize_text_field($_POST['year_of_paper']);
        $resource_type = sanitize_text_field($_POST['resource_type']);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'exam_papers';
        
        $result = $wpdb->update(
            $table_name,
            array(
                'title' => $title,
                'qualification' => $qualification,
                'year_of_paper' => $year_of_paper,
                'resource_type' => $resource_type
            ),
            array('id' => $paper_id),
            array('%s', '%s', '%s', '%s'),
            array('%d')
        );
        
        if ($result !== false) {
            wp_send_json_success('Paper updated successfully');
        } else {
            wp_send_json_error('Failed to update paper');
        }
    }
    
    public function bulk_delete_papers() {
        if (!wp_verify_nonce($_POST['nonce'], 'epm_admin_nonce')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $paper_ids = array_map('intval', $_POST['paper_ids']);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'exam_papers';
        
        $deleted_count = 0;
        foreach ($paper_ids as $paper_id) {
            // Get file URL before deleting to remove file
            $paper = $wpdb->get_row($wpdb->prepare("SELECT file_url FROM $table_name WHERE id = %d", $paper_id));
            
            if ($paper) {
                // Delete file from uploads
                $file_path = str_replace(wp_upload_dir()['url'], wp_upload_dir()['path'], $paper->file_url);
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
                
                // Delete from database
                $result = $wpdb->delete($table_name, array('id' => $paper_id), array('%d'));
                if ($result) {
                    $deleted_count++;
                }
            }
        }
        
        wp_send_json_success("$deleted_count papers deleted successfully");
    }
    
    public function filter_admin_papers() {
        if (!wp_verify_nonce($_POST['nonce'], 'epm_admin_nonce')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'exam_papers';
        
        $search = sanitize_text_field($_POST['search']);
        $qualification = sanitize_text_field($_POST['qualification']);
        $year = sanitize_text_field($_POST['year']);
        
        $where_conditions = array();
        $where_values = array();
        
        if (!empty($search)) {
            $where_conditions[] = "title LIKE %s";
            $where_values[] = '%' . $wpdb->esc_like($search) . '%';
        }
        
        if (!empty($qualification)) {
            $where_conditions[] = "qualification = %s";
            $where_values[] = $qualification;
        }
        
        if (!empty($year)) {
            $where_conditions[] = "year_of_paper = %s";
            $where_values[] = $year;
        }
        
        $where_clause = "";
        if (!empty($where_conditions)) {
            $where_clause = "WHERE " . implode(" AND ", $where_conditions);
        }
        
        $query = "SELECT * FROM $table_name $where_clause ORDER BY priority_order DESC, upload_date DESC";
        
        if (!empty($where_values)) {
            $papers = $wpdb->get_results($wpdb->prepare($query, $where_values));
        } else {
            $papers = $wpdb->get_results($query);
        }
        
        ob_start();
        foreach ($papers as $paper) {
            echo '<tr>';
            echo '<td class="check-column"><input type="checkbox" name="paper_ids[]" value="' . $paper->id . '"></td>';
            echo '<td class="column-title">';
            echo '<strong><a href="' . esc_url($paper->file_url) . '" target="_blank">' . esc_html($paper->title) . '</a></strong>';
            echo '<div class="row-actions">';
            echo '<span class="view"><a href="' . esc_url($paper->file_url) . '" target="_blank">View</a> | </span>';
            echo '<span class="edit"><a href="#" onclick="editPaper(' . $paper->id . ')">Edit</a> | </span>';
            echo '<span class="delete"><a href="#" onclick="deletePaper(' . $paper->id . ')" class="submitdelete">Delete</a></span>';
            echo '</div>';
            echo '</td>';
            echo '<td>' . esc_html($paper->qualification) . '</td>';
            echo '<td>' . esc_html($paper->year_of_paper) . '</td>';
            echo '<td>' . esc_html($paper->resource_type) . '</td>';
            echo '<td>' . date('Y/m/d', strtotime($paper->upload_date)) . '</td>';
            echo '<td class="epm-actions-column">';
            echo '<a href="' . esc_url($paper->file_url) . '" target="_blank" class="epm-action-btn epm-btn-view" title="View">👁️</a>';
            echo '<a href="#" onclick="editPaper(' . $paper->id . ')" class="epm-action-btn epm-btn-edit" title="Edit">✏️</a>';
            echo '<a href="#" onclick="deletePaper(' . $paper->id . ')" class="epm-action-btn epm-btn-delete" title="Delete">🗑️</a>';
            echo '</td>';
            echo '</tr>';
        }
        $html = ob_get_clean();
        
        wp_send_json_success($html);
    }
    
    public function export_exam_papers() {
        if (!wp_verify_nonce($_GET['nonce'], 'epm_export_nonce')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'exam_papers';
        
        $papers = $wpdb->get_results("SELECT * FROM $table_name ORDER BY upload_date DESC");
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="exam-papers-export-' . date('Y-m-d') . '.csv"');
        
        // Open output stream
        $output = fopen('php://output', 'w');
        
        // CSV headers
        fputcsv($output, array('ID', 'Title', 'Qualification', 'Exam Series', 'Year', 'Resource Type', 'File URL', 'Upload Date'));
        
        // CSV data
        foreach ($papers as $paper) {
            fputcsv($output, array(
                $paper->id,
                $paper->title,
                $paper->qualification,
                $paper->exam_series,
                $paper->year_of_paper,
                $paper->resource_type,
                $paper->file_url,
                $paper->upload_date
            ));
        }
        
        fclose($output);
        exit;
    }
    
    public function export_selected_papers() {
        if (!wp_verify_nonce($_GET['nonce'], 'epm_admin_nonce')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $paper_ids = array_map('intval', explode(',', $_GET['paper_ids']));
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'exam_papers';
        
        $placeholders = implode(',', array_fill(0, count($paper_ids), '%d'));
        $query = "SELECT * FROM $table_name WHERE id IN ($placeholders) ORDER BY upload_date DESC";
        $papers = $wpdb->get_results($wpdb->prepare($query, $paper_ids));
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="selected-exam-papers-' . date('Y-m-d') . '.csv"');
        
        // Open output stream
        $output = fopen('php://output', 'w');
        
        // CSV headers
        fputcsv($output, array('ID', 'Title', 'Qualification', 'Exam Series', 'Year', 'Resource Type', 'File URL', 'Upload Date'));
        
        // CSV data
        foreach ($papers as $paper) {
            fputcsv($output, array(
                $paper->id,
                $paper->title,
                $paper->qualification,
                $paper->exam_series,
                $paper->year_of_paper,
                $paper->resource_type,
                $paper->file_url,
                $paper->upload_date
            ));
        }
        
        fclose($output);
        exit;
    }
}

// Initialize the plugin
new ExamPapersManager();
