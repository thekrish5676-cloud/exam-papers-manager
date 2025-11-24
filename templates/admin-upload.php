<div class="wrap epm-admin-wrap">
    <div class="epm-header">
        <h1 class="epm-title">
            <span class="dashicons dashicons-upload"></span>
            Upload Exam Papers
        </h1>
        <p class="epm-subtitle">Upload and manage exam papers, mark schemes, and related documents</p>
    </div>

    <div class="epm-upload-container">
        <form id="exam-paper-upload-form" class="epm-upload-form" enctype="multipart/form-data">
            <?php wp_nonce_field('epm_admin_nonce', 'epm_nonce'); ?>

            <div class="epm-form-grid">
                <div class="epm-form-group">
                    <label for="title" class="epm-label">Document Title</label>
                    <input type="text" id="title" name="title" class="epm-input" placeholder="e.g., English Paper - Question paper: Paper 2 Social Context" required>
                </div>

                <div class="epm-form-group">
                    <label for="qualification" class="epm-label">Qualification</label>
                    <select id="qualification" name="qualification" class="epm-select" required>
                        <option value="">Select Qualification</option>
                        <option value="AS Psychology">AS Psychology</option>
                        <option value="A-Level Psychology">A-Level Psychology</option>
                    </select>
                </div>

                <div class="epm-form-group">
                    <label for="year_of_paper" class="epm-label">Year of past paper</label>
                    <select id="year_of_paper" name="year_of_paper" class="epm-select" required>
                        <option value="">Select Year</option>
                        <option value="2025">2025</option>
                        <option value="2024">2024</option>
                        <option value="2023">2023</option>
                        <option value="2022">2022</option>
                        <option value="2021">2021</option>
                        <option value="2020">2020</option>
                        <option value="2019">2019</option>
                        <option value="2018">2018</option>
                        <option value="2017">2017</option>
                        <option value="2016">2016</option>
                        <option value="2015">2015</option>
                        <option value="old">Old</option>
                    </select>
                </div>

                <div class="epm-form-group">
                    <label for="resource_type" class="epm-label">Resource type</label>
                    <select id="resource_type" name="resource_type" class="epm-select" required>
                        <option value="">Select Type</option>
                        <option value="Question paper">Question paper</option>
                        <option value="Mark schemes">Mark schemes</option>
                        <option value="Examiners report">Examiners report</option>
                        <option value="Sample material">Sample material</option>
                        <option value="Question papers">Question papers</option>
                    </select>
                </div>

                <!-- NEW FIELD: Choose Paper to Prioritise -->
                <div class="epm-form-group">
                    <label for="prioritise_paper" class="epm-label">Choose Paper to Prioritise (Optional)</label>
                    <select id="prioritise_paper" name="prioritise_paper" class="epm-select">
                        <option value="">-- No Prioritisation --</option>
                        <?php
                        global $wpdb;
                        $table_name = $wpdb->prefix . 'exam_papers';
                        $existing_papers = $wpdb->get_results("SELECT id, title FROM $table_name ORDER BY upload_date DESC");
                        
                        if ($existing_papers) {
                            foreach ($existing_papers as $existing_paper) {
                                echo '<option value="' . esc_attr($existing_paper->id) . '">' . esc_html($existing_paper->title) . '</option>';
                            }
                        }
                        ?>
                    </select>
                    <small style="color: #6b7280; font-size: 0.875rem; margin-top: 0.25rem; display: block;">
                        Select an existing paper to place it at the top of the list. The newly uploaded paper will appear second.
                    </small>
                </div>

                <div class="epm-form-group epm-file-upload-group">
                    <label for="exam_file" class="epm-label">Upload Document</label>
                    <div class="epm-file-upload-wrapper">
                        <input type="file" id="exam_file" name="exam_file" class="epm-file-input" accept=".pdf,.ppt,.pptx,.doc,.docx" required>
                        <label for="exam_file" class="epm-file-label">
                            <span class="dashicons dashicons-cloud-upload"></span>
                            <span class="epm-file-text">Choose File (PDF, PPT, DOC)</span>
                        </label>
                        <div class="epm-file-info"></div>
                    </div>
                </div>
            </div>

            <div class="epm-form-actions">
                <button type="submit" class="epm-btn epm-btn-primary">
                    <span class="dashicons dashicons-upload"></span>
                    Upload Exam Paper
                </button>
                <button type="reset" class="epm-btn epm-btn-secondary">
                    <span class="dashicons dashicons-dismiss"></span>
                    Clear Form
                </button>
            </div>
        </form>
    </div>

    <div class="epm-upload-status" id="upload-status" style="display: none;">
        <div class="epm-status-content">
            <span class="epm-status-icon"></span>
            <span class="epm-status-message"></span>
        </div>
    </div>

    <!-- Recently Uploaded Papers -->
    <div class="epm-recent-uploads">
        <h2 class="epm-section-title">
            <span class="dashicons dashicons-clock"></span>
            Recently Uploaded Papers
        </h2>
        <div id="recent-papers-list" class="epm-papers-list">
            <?php
            global $wpdb;
            $table_name = $wpdb->prefix . 'exam_papers';
            $recent_papers = $wpdb->get_results("SELECT * FROM $table_name ORDER BY priority_order DESC, upload_date DESC LIMIT 5");

            if ($recent_papers) {
                foreach ($recent_papers as $paper) {
                    echo '<div class="epm-paper-item">';
                    echo '<div class="epm-paper-icon">';
                    echo '<span class="dashicons dashicons-media-document"></span>';
                    echo '</div>';
                    echo '<div class="epm-paper-details">';
                    echo '<h3>' . esc_html($paper->title) . '</h3>';
                    echo '<p>' . esc_html($paper->qualification) . ' • ' . esc_html($paper->year_of_paper) . ' • ' . esc_html($paper->resource_type) . '</p>';
                    echo '</div>';
                    echo '<div class="epm-paper-actions">';
                    echo '<a href="' . esc_url($paper->file_url) . '" target="_blank" class="epm-btn epm-btn-small">View</a>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<p class="epm-no-papers">No papers uploaded yet.</p>';
            }
            ?>
        </div>
    </div>
</div>