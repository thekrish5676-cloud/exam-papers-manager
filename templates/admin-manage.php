<div class="wrap epm-admin-wrap">
    <div class="epm-header">
        <h1 class="epm-title">
            <span class="dashicons dashicons-admin-tools"></span>
            Manage Exam Papers
        </h1>
        <p class="epm-subtitle">View, edit, and delete uploaded exam papers</p>
    </div>

    <div class="epm-manage-container">
        <div class="epm-manage-filters">
            <div class="epm-filter-group">
                <input type="text" id="search-papers" class="epm-search-input" placeholder="Search papers...">
            </div>
            <div class="epm-filter-group">
                <select id="filter-qualification" class="epm-filter-select">
                    <option value="">All Qualifications</option>
                    <option value="AS Psychology">AS Psychology</option>
                    <option value="A-Level Psychology">A-Level Psychology</option>
                </select>
            </div>
            <div class="epm-filter-group">
                <select id="filter-year" class="epm-filter-select">
                    <option value="">All Years</option>
                    <option value="2025">2025</option>
                    <option value="2024">2024</option>
                    <option value="2023">2023</option>
                    <option value="2022">2022</option>
                    <option value="old">Old</option>
                </select>
            </div>
            <div class="epm-filter-group">
                <button class="epm-btn epm-btn-primary" onclick="filterPapers()">Filter</button>
                <button class="epm-btn epm-btn-secondary" onclick="clearFilters()">Clear</button>
            </div>
        </div>

        <div class="epm-papers-table-container">
            <table class="wp-list-table widefat fixed striped epm-papers-table">
                <thead>
                    <tr>
                        <th class="manage-column column-cb check-column">
                            <input type="checkbox" id="select-all-papers">
                        </th>
                        <th class="manage-column">Title</th>
                        <th class="manage-column">Qualification</th>
                        <th class="manage-column">Year</th>
                        <th class="manage-column">Type</th>
                        <th class="manage-column">Upload Date</th>
                        <th class="manage-column">Actions</th>
                    </tr>
                </thead>
                <tbody id="papers-table-body">
                    <?php
                    global $wpdb;
                    $table_name = $wpdb->prefix . 'exam_papers';
                    $papers = $wpdb->get_results("SELECT * FROM $table_name ORDER BY priority_order DESC, upload_date DESC");
                    
                    if ($papers) {
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
                    } else {
                        echo '<tr><td colspan="7" class="epm-no-papers-row">No exam papers found. <a href="' . admin_url('admin.php?page=exam-papers-upload') . '">Upload your first paper</a></td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="epm-bulk-actions">
            <div class="epm-bulk-actions-left">
                <select id="bulk-action-select" class="epm-bulk-select">
                    <option value="">Bulk Actions</option>
                    <option value="delete">Delete Selected</option>
                    <option value="export">Export Selected</option>
                </select>
                <button class="epm-btn epm-btn-secondary" onclick="applyBulkAction()">Apply</button>
            </div>
            <div class="epm-bulk-actions-right">
                <span class="epm-items-count">
                    <?php 
                    global $wpdb;
                    $table_name = $wpdb->prefix . 'exam_papers';
                    $total = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
                    echo $total . ' item' . ($total != 1 ? 's' : '');
                    ?>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="edit-paper-modal" class="epm-modal" style="display: none;">
    <div class="epm-modal-content">
        <div class="epm-modal-header">
            <h2>Edit Exam Paper</h2>
            <span class="epm-modal-close">&times;</span>
        </div>
        <form id="edit-paper-form" class="epm-modal-form">
            <input type="hidden" id="edit-paper-id" name="paper_id">
            <div class="epm-form-group">
                <label for="edit-title">Title</label>
                <input type="text" id="edit-title" name="title" class="epm-input" required>
            </div>
            <div class="epm-form-group">
                <label for="edit-qualification">Qualification</label>
                <select id="edit-qualification" name="qualification" class="epm-select" required>
                    <option value="AS Psychology">AS Psychology</option>
                    <option value="A-Level Psychology">A-Level Psychology</option>
                </select>
            </div>
            <div class="epm-form-group">
                <label for="edit-year">Year</label>
                <select id="edit-year" name="year_of_paper" class="epm-select" required>
                    <option value="2025">2025</option>
                    <option value="2024">2024</option>
                    <option value="2023">2023</option>
                    <option value="2022">2022</option>
                </select>
            </div>
            <div class="epm-form-group">
                <label for="edit-resource-type">Resource Type</label>
                <select id="edit-resource-type" name="resource_type" class="epm-select" required>
                    <option value="Question paper">Question paper</option>
                    <option value="Mark schemes">Mark schemes</option>
                    <option value="Examiners report">Examiners report</option>
                    <option value="Sample material">Sample material</option>
                </select>
            </div>
            <div class="epm-modal-actions">
                <button type="submit" class="epm-btn epm-btn-primary">Update Paper</button>
                <button type="button" class="epm-btn epm-btn-secondary epm-modal-cancel">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function editPaper(paperId) {
    // Implementation for edit functionality
    document.getElementById('edit-paper-modal').style.display = 'block';
}

function deletePaper(paperId) {
    if (confirm('Are you sure you want to delete this exam paper?')) {
        // AJAX call to delete paper
        jQuery.post(ajaxurl, {
            action: 'delete_exam_paper',
            paper_id: paperId,
            nonce: '<?php echo wp_create_nonce("epm_admin_nonce"); ?>'
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error deleting paper: ' + response.data);
            }
        });
    }
}

function filterPapers() {
    // Implementation for filtering
}

function clearFilters() {
    document.getElementById('search-papers').value = '';
    document.getElementById('filter-qualification').value = '';
    document.getElementById('filter-year').value = '';
    filterPapers();
}

function applyBulkAction() {
    // Implementation for bulk actions
}

// Modal functionality
document.querySelector('.epm-modal-close').onclick = function() {
    document.getElementById('edit-paper-modal').style.display = 'none';
}

document.querySelector('.epm-modal-cancel').onclick = function() {
    document.getElementById('edit-paper-modal').style.display = 'none';
}
</script>