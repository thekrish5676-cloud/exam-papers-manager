jQuery(document).ready(function($) {
    
    // File upload functionality
    $('#exam_file').on('change', function() {
        const file = this.files[0];
        const fileInfo = $('.epm-file-info');
        
        if (file) {
            const fileSize = (file.size / 1024 / 1024).toFixed(2);
            const fileType = file.type;
            
            // Validate file type
            const allowedTypes = [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation'
            ];
            
            if (!allowedTypes.includes(fileType)) {
                fileInfo.html('<span style="color: #ef4444;">❌ Invalid file type. Please select PDF, DOC, DOCX, PPT, or PPTX files.</span>');
                $(this).val('');
                return;
            }
            
            // Validate file size (10MB limit)
            if (file.size > 10 * 1024 * 1024) {
                fileInfo.html('<span style="color: #ef4444;">❌ File too large. Maximum size is 10MB.</span>');
                $(this).val('');
                return;
            }
            
            fileInfo.html(`✅ ${file.name} (${fileSize}MB) - Ready to upload`);
            $('.epm-file-label').addClass('file-selected').find('.epm-file-text').text(file.name);
        } else {
            fileInfo.html('');
            $('.epm-file-label').removeClass('file-selected').find('.epm-file-text').text('Choose File (PDF, PPT, DOC)');
        }
    });
    
    // Form submission
    $('#exam-paper-upload-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'upload_exam_paper');
        formData.append('nonce', $('#epm_nonce').val());
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        const statusDiv = $('#upload-status');
        
        // Show loading state
        submitBtn.prop('disabled', true).html('<span class="epm-spinner"></span>Uploading...');
        statusDiv.hide();
        
        $.ajax({
            url: epm_admin_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showStatus('success', '✅ ' + response.data);
                    $('#exam-paper-upload-form')[0].reset();
                    $('.epm-file-info').html('');
                    $('.epm-file-label').removeClass('file-selected').find('.epm-file-text').text('Choose File (PDF, PPT, DOC)');
                    
                    // Clear saved form data
                    clearSavedFormData();
                    
                    // Refresh recent papers list if it exists
                    refreshRecentPapers();
                } else {
                    showStatus('error', '❌ ' + response.data);
                }
            },
            error: function(xhr, status, error) {
                showStatus('error', '❌ Upload failed: ' + error);
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    // Show status message
    function showStatus(type, message) {
        const statusDiv = $('#upload-status');
        const messageSpan = statusDiv.find('.epm-status-message');
        const iconSpan = statusDiv.find('.epm-status-icon');
        
        statusDiv.removeClass('success error').addClass(type);
        messageSpan.text(message);
        iconSpan.text(type === 'success' ? '✅' : '❌');
        
        statusDiv.fadeIn().delay(5000).fadeOut();
    }
    
    // Refresh recent papers list
    function refreshRecentPapers() {
        $('#recent-papers-list').load(location.href + ' #recent-papers-list > *');
    }
    
    // Form reset
    $('button[type="reset"]').on('click', function() {
        $('.epm-file-info').html('');
        $('.epm-file-label').removeClass('file-selected').find('.epm-file-text').text('Choose File (PDF, PPT, DOC)');
        $('#upload-status').hide();
    });
    
    // Enhanced file drag and drop
    $('.epm-file-label').on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('drag-over');
    });
    
    $('.epm-file-label').on('dragleave', function(e) {
        e.preventDefault();
        $(this).removeClass('drag-over');
    });
    
    $('.epm-file-label').on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('drag-over');
        
        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            $('#exam_file')[0].files = files;
            $('#exam_file').trigger('change');
        }
    });
    
    // Auto-save form data
    const formFields = ['title', 'qualification', 'year_of_paper', 'resource_type'];
    
    formFields.forEach(function(field) {
        $('#' + field).on('change', function() {
            localStorage.setItem('epm_' + field, $(this).val());
        });
        
        // Restore saved data
        const savedValue = localStorage.getItem('epm_' + field);
        if (savedValue) {
            $('#' + field).val(savedValue);
        }
    });
    
    // Clear saved data on successful upload
    function clearSavedFormData() {
        formFields.forEach(function(field) {
            localStorage.removeItem('epm_' + field);
        });
    }
    
    // Delete paper functionality
    window.deletePaper = function(paperId) {
        if (confirm('Are you sure you want to delete this exam paper?')) {
            $.ajax({
                url: epm_admin_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'delete_exam_paper',
                    paper_id: paperId,
                    nonce: epm_admin_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error deleting paper: ' + response.data);
                    }
                },
                error: function() {
                    alert('Error deleting paper. Please try again.');
                }
            });
        }
    };
    
    // Edit paper functionality
    window.editPaper = function(paperId) {
        $.ajax({
            url: epm_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'get_exam_paper',
                paper_id: paperId,
                nonce: epm_admin_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    const paper = response.data;
                    $('#edit-paper-id').val(paper.id);
                    $('#edit-title').val(paper.title);
                    $('#edit-qualification').val(paper.qualification);
                    $('#edit-year').val(paper.year_of_paper);
                    $('#edit-resource-type').val(paper.resource_type);
                    $('#edit-paper-modal').show();
                } else {
                    alert('Error loading paper data: ' + response.data);
                }
            }
        });
    };
    
    // Update paper form submission
    $('#edit-paper-form').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: epm_admin_ajax.ajax_url,
            type: 'POST',
            data: $(this).serialize() + '&action=update_exam_paper&nonce=' + epm_admin_ajax.nonce,
            success: function(response) {
                if (response.success) {
                    $('#edit-paper-modal').hide();
                    location.reload();
                } else {
                    alert('Error updating paper: ' + response.data);
                }
            }
        });
    });
    
    // Bulk actions
    $('#select-all-papers').on('change', function() {
        $('input[name="paper_ids[]"]').prop('checked', this.checked);
    });
    
    window.applyBulkAction = function() {
        const action = $('#bulk-action-select').val();
        const selectedIds = $('input[name="paper_ids[]"]:checked').map(function() {
            return this.value;
        }).get();
        
        if (!action) {
            alert('Please select a bulk action.');
            return;
        }
        
        if (selectedIds.length === 0) {
            alert('Please select at least one paper.');
            return;
        }
        
        if (action === 'delete') {
            if (confirm('Are you sure you want to delete ' + selectedIds.length + ' papers?')) {
                $.ajax({
                    url: epm_admin_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'bulk_delete_papers',
                        paper_ids: selectedIds,
                        nonce: epm_admin_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert('Error deleting papers: ' + response.data);
                        }
                    }
                });
            }
        } else if (action === 'export') {
            window.open(epm_admin_ajax.ajax_url + '?action=export_selected_papers&paper_ids=' + selectedIds.join(',') + '&nonce=' + epm_admin_ajax.nonce);
        }
    };
    
    // Search and filter functionality
    let searchTimeout;
    $('#search-papers').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            filterPapers();
        }, 300);
    });
    
    $('#filter-qualification, #filter-year').on('change', filterPapers);
    
    window.filterPapers = function() {
        const search = $('#search-papers').val();
        const qualification = $('#filter-qualification').val();
        const year = $('#filter-year').val();
        
        $.ajax({
            url: epm_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'filter_admin_papers',
                search: search,
                qualification: qualification,
                year: year,
                nonce: epm_admin_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#papers-table-body').html(response.data);
                }
            }
        });
    };
    
    window.clearFilters = function() {
        $('#search-papers').val('');
        $('#filter-qualification').val('');
        $('#filter-year').val('');
        filterPapers();
    };
    
    // Modal functionality
    $('.epm-modal-close, .epm-modal-cancel').on('click', function() {
        $('.epm-modal').hide();
    });
    
    // Close modal when clicking outside
    $('.epm-modal').on('click', function(e) {
        if (e.target === this) {
            $(this).hide();
        }
    });
    
    // Dashboard stats animation
    $('.epm-stat-number').each(function() {
        const $this = $(this);
        const countTo = parseInt($this.text());
        
        $({ countNum: 0 }).animate({
            countNum: countTo
        }, {
            duration: 2000,
            easing: 'swing',
            step: function() {
                $this.text(Math.floor(this.countNum));
            },
            complete: function() {
                $this.text(this.countNum);
            }
        });
    });
    
    // Tooltips for action buttons
    $('.epm-action-btn').on('mouseenter', function() {
        const title = $(this).attr('title');
        if (title) {
            $('<div class="epm-tooltip">' + title + '</div>')
                .appendTo('body')
                .fadeIn(200);
        }
    }).on('mouseleave', function() {
        $('.epm-tooltip').remove();
    }).on('mousemove', function(e) {
        $('.epm-tooltip').css({
            top: e.pageY + 10,
            left: e.pageX + 10,
            position: 'absolute',
            background: '#333',
            color: '#fff',
            padding: '5px 10px',
            borderRadius: '4px',
            fontSize: '12px',
            zIndex: 9999
        });
    });
    
    // Auto-refresh recent uploads every 30 seconds
    setInterval(refreshRecentPapers, 30000);
    
    // Keyboard shortcuts
    $(document).on('keydown', function(e) {
        // Ctrl+N for new upload
        if (e.ctrlKey && e.key === 'n') {
            e.preventDefault();
            window.location.href = epm_admin_ajax.ajax_url.replace('admin-ajax.php', 'admin.php?page=exam-papers-upload');
        }
        
        // Escape to close modals
        if (e.key === 'Escape') {
            $('.epm-modal').hide();
        }
    });
});