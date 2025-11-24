<div class="wrap epm-admin-wrap">
    <div class="epm-header">
        <h1 class="epm-title">
            <span class="dashicons dashicons-media-document"></span>
            Exam Papers Dashboard
        </h1>
        <p class="epm-subtitle">Manage your exam papers, uploads, and system overview</p>
    </div>

    <div class="epm-dashboard-grid">
        <div class="epm-dashboard-card epm-stats-card">
            <div class="epm-card-header">
                <h3>📊 Statistics</h3>
            </div>
            <div class="epm-card-content">
                <?php
                global $wpdb;
                $table_name = $wpdb->prefix . 'exam_papers';
                $total_papers = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
                $total_qualifications = $wpdb->get_var("SELECT COUNT(DISTINCT qualification) FROM $table_name");
                $recent_uploads = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE upload_date > DATE_SUB(NOW(), INTERVAL 7 DAY)");
                ?>
                <div class="epm-stat-item">
                    <div class="epm-stat-number"><?php echo $total_papers; ?></div>
                    <div class="epm-stat-label">Total Papers</div>
                </div>
                <div class="epm-stat-item">
                    <div class="epm-stat-number"><?php echo $total_qualifications; ?></div>
                    <div class="epm-stat-label">Qualifications</div>
                </div>
                <div class="epm-stat-item">
                    <div class="epm-stat-number"><?php echo $recent_uploads; ?></div>
                    <div class="epm-stat-label">This Week</div>
                </div>
            </div>
        </div>

        <div class="epm-dashboard-card epm-quick-actions-card">
            <div class="epm-card-header">
                <h3>⚡ Quick Actions</h3>
            </div>
            <div class="epm-card-content">
                <a href="<?php echo admin_url('admin.php?page=exam-papers-upload'); ?>" class="epm-quick-action">
                    <span class="dashicons dashicons-upload"></span>
                    Upload New Paper
                </a>
                <a href="<?php echo admin_url('admin.php?page=exam-papers-manage'); ?>" class="epm-quick-action">
                    <span class="dashicons dashicons-admin-tools"></span>
                    Manage Papers
                </a>
                <a href="#" class="epm-quick-action" onclick="exportPapers()">
                    <span class="dashicons dashicons-download"></span>
                    Export Data
                </a>
            </div>
        </div>

        <div class="epm-dashboard-card epm-recent-uploads-card">
            <div class="epm-card-header">
                <h3>🕒 Recent Uploads</h3>
            </div>
            <div class="epm-card-content">
                <?php
                $recent_papers = $wpdb->get_results("SELECT * FROM $table_name ORDER BY priority_order DESC, upload_date DESC LIMIT 5");
                if ($recent_papers) {
                    foreach ($recent_papers as $paper) {
                        echo '<div class="epm-recent-item">';
                        echo '<div class="epm-recent-icon">📄</div>';
                        echo '<div class="epm-recent-content">';
                        echo '<div class="epm-recent-title">' . esc_html(substr($paper->title, 0, 40)) . '...</div>';
                        echo '<div class="epm-recent-meta">' . esc_html($paper->qualification) . ' • ' . date('M j, Y', strtotime($paper->upload_date)) . '</div>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<p class="epm-no-data">No recent uploads</p>';
                }
                ?>
            </div>
        </div>

        <div class="epm-dashboard-card epm-system-info-card">
            <div class="epm-card-header">
                <h3>⚙️ System Info</h3>
            </div>
            <div class="epm-card-content">
                <div class="epm-info-item">
                    <strong>Plugin Version:</strong> <?php echo EPM_VERSION; ?>
                </div>
                <div class="epm-info-item">
                    <strong>Upload Directory:</strong> 
                    <?php 
                    $upload_dir = wp_upload_dir();
                    echo is_writable($upload_dir['path']) ? '✅ Writable' : '❌ Not Writable';
                    ?>
                </div>
                <div class="epm-info-item">
                    <strong>Max Upload Size:</strong> <?php echo ini_get('upload_max_filesize'); ?>
                </div>
                <div class="epm-info-item">
                    <strong>Allowed File Types:</strong> PDF, DOC, DOCX, PPT, PPTX
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function exportPapers() {
    if (confirm('Export all exam papers data to CSV?')) {
        window.location.href = ajaxurl + '?action=export_exam_papers&nonce=' + '<?php echo wp_create_nonce("epm_export_nonce"); ?>';
    }
}
</script>