<div class="epm-container">

    <div class="epm-content-wrapper">
        <div class="epm-sidebar">
            <div class="epm-filter-actions1">
                <button class="epm-btn epm-btn-primary epm-apply-filters">Apply Filters</button>
            </div>

            <div class="epm-filter-section">
                <h3 class="epm-filter-title">
                    <span class="epm-filter-icon">📚</span>
                    Qualification
                    <span class="epm-toggle">+</span>
                </h3>
                <div class="epm-filter-content">
                    <div class="epm-filter-options">
                        <label class="epm-checkbox-label">
                            <input type="checkbox" name="qualification" value="AS Psychology" class="epm-checkbox">
                            <span class="epm-checkmark"></span>
                            AS Psychology
                        </label>
                        <label class="epm-checkbox-label">
                            <input type="checkbox" name="qualification" value="A-Level Psychology" class="epm-checkbox">
                            <span class="epm-checkmark"></span>
                            A-Level Psychology
                        </label>
                    </div>
                </div>
            </div>

            <div class="epm-filter-section">
                <h3 class="epm-filter-title active">
                    <span class="epm-filter-icon">📅</span>
                    Year of past paper
                    <span class="epm-toggle">−</span>
                </h3>
                <div class="epm-filter-content active">
                    <div class="epm-filter-options">
                        <label class="epm-checkbox-label">
                            <input type="checkbox" name="year_of_paper" value="2025" class="epm-checkbox">
                            <span class="epm-checkmark"></span>
                            2025
                        </label>
                        <label class="epm-checkbox-label">
                            <input type="checkbox" name="year_of_paper" value="2024" class="epm-checkbox">
                            <span class="epm-checkmark"></span>
                            2024
                        </label>
                        <label class="epm-checkbox-label">
                            <input type="checkbox" name="year_of_paper" value="2023" class="epm-checkbox">
                            <span class="epm-checkmark"></span>
                            2023
                        </label>
                        <label class="epm-checkbox-label">
                            <input type="checkbox" name="year_of_paper" value="2022" class="epm-checkbox">
                            <span class="epm-checkmark"></span>
                            2022
                        </label>
                        <label class="epm-checkbox-label">
                            <input type="checkbox" name="year_of_paper" value="2021" class="epm-checkbox">
                            <span class="epm-checkmark"></span>
                            2021
                        </label>
                        <label class="epm-checkbox-label">
                            <input type="checkbox" name="year_of_paper" value="2020" class="epm-checkbox">
                            <span class="epm-checkmark"></span>
                            2020
                        </label>
                        <label class="epm-checkbox-label">
                            <input type="checkbox" name="year_of_paper" value="2019" class="epm-checkbox">
                            <span class="epm-checkmark"></span>
                            2019
                        </label>
                        <label class="epm-checkbox-label">
                            <input type="checkbox" name="year_of_paper" value="2018" class="epm-checkbox">
                            <span class="epm-checkmark"></span>
                            2018
                        </label>
                        <label class="epm-checkbox-label">
                            <input type="checkbox" name="year_of_paper" value="2017" class="epm-checkbox">
                            <span class="epm-checkmark"></span>
                            2017
                        </label>
                        <label class="epm-checkbox-label">
                            <input type="checkbox" name="year_of_paper" value="2016" class="epm-checkbox">
                            <span class="epm-checkmark"></span>
                            2016
                        </label>
                        <label class="epm-checkbox-label">
                            <input type="checkbox" name="year_of_paper" value="2015" class="epm-checkbox">
                            <span class="epm-checkmark"></span>
                            2015
                        </label>
                        <label class="epm-checkbox-label">
                            <input type="checkbox" name="year_of_paper" value="old" class="epm-checkbox">
                            <span class="epm-checkmark"></span>
                            Old
                        </label>
                    </div>
                </div>
            </div>

            <div class="epm-filter-section">
                <h3 class="epm-filter-title active">
                    <span class="epm-filter-icon">🎯</span>
                    Resource type
                    <span class="epm-toggle">−</span>
                </h3>
                <div class="epm-filter-content active">
                    <div class="epm-filter-options">
                        <?php
                        global $wpdb;
                        $table_name = $wpdb->prefix . 'exam_papers';
                        
                        // Define resource types
                        $resource_types = array(
                            'Question paper',
                            'Mark schemes',
                            'Examiners report',
                            'Sample material',
                            'Question papers'
                        );
                        
                        // Get count for each resource type
                        foreach ($resource_types as $type) {
                            $count = $wpdb->get_var($wpdb->prepare(
                                "SELECT COUNT(*) FROM $table_name WHERE resource_type = %s",
                                $type
                            ));
                        ?>
                        <label class="epm-checkbox-label">
                            <input type="checkbox" name="resource_type" value="<?php echo esc_attr($type); ?>" class="epm-checkbox">
                            <span class="epm-checkmark"></span>
                            <?php echo esc_html($type); ?> <span class="epm-resource-count">(<?php echo intval($count); ?>)</span>
                        </label>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <div class="epm-filter-actions">
                <button class="epm-btn epm-btn-secondary epm-clear-filters">Clear All</button>
            </div>
        </div>

        <div class="epm-main-content">
            <div class="epm-results-header">
                <div class="epm-results-info">
                    <span class="epm-results-count">Showing <strong id="results-count"><?php echo $total_papers; ?></strong> results</span>
                    <div class="epm-header-controls">
                        <div class="epm-pagination-info">
                            <span>Items per page:</span>
                            <select class="epm-items-per-page">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                        </div>
                        <?php if ($total_papers > 10): ?>
                            <div class="epm-pagination-top">
                                <?php
                                $pages = ceil($total_papers / 10);
                                for ($i = 1; $i <= min($pages, 5); $i++):
                                ?>
                                    <button class="epm-pagination-btn epm-pagination-number <?php echo $i == 1 ? 'active' : ''; ?>"><?php echo $i; ?></button>
                                <?php endfor; ?>
                                <?php if ($pages > 1): ?>
                                    <button class="epm-pagination-btn epm-pagination-next">></button>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div id="exam-papers-results" class="epm-results-container">
                <?php
                global $wpdb;
                $table_name = $wpdb->prefix . 'exam_papers';
                $total_papers = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
                $papers = $wpdb->get_results("SELECT * FROM $table_name ORDER BY upload_date DESC");

                if ($papers) {
                    foreach ($papers as $paper) {
                        include EPM_PLUGIN_PATH . 'templates/paper-item.php';
                    }
                } else {
                    echo '<div class="epm-no-results">';
                    echo '<div class="epm-no-results-icon">📄</div>';
                    echo '<h3>No exam papers found</h3>';
                    echo '<p>Try adjusting your filters or check back later for new content.</p>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </div>
</div>
