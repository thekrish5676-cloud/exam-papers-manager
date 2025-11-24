<div class="epm-paper-result">
    <!-- Simplified layout: PDF badge top-left, title with ellipsis, metadata below, buttons right-aligned -->
    <div class="epm-paper-header">
        <div class="epm-paper-icon">
            <!-- Simple red PDF badge square -->
            <div class="epm-pdf-badge-simple">
                PDF
            </div>
        </div>
        <div class="epm-paper-info">
            <h3 class="epm-paper-title"><?php echo esc_html($paper->title); ?></h3>
            <div class="epm-paper-meta">
                <span class="epm-meta-item">
                    <?php echo esc_html($paper->qualification); ?>
                </span>
                <span class="epm-meta-separator">•</span>
                <span class="epm-meta-item">
                    <?php echo esc_html($paper->resource_type); ?>
                </span>
            </div>
            <div class="epm-paper-year"><?php echo esc_html($paper->year_of_paper); ?></div>
        </div>
    </div>
    
    <!-- Replaced button elements with simple text links -->
    <div class="epm-paper-actions">
        <a href="<?php echo esc_url($paper->file_url); ?>" target="_blank" class="epm-link-view">
            View
        </a>
        <a href="<?php echo esc_url($paper->file_url); ?>" download class="epm-link-download">
            Download
        </a>
    </div>
</div>
