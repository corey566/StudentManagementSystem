// Education Portal - Main JavaScript File

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    initializeTooltips();
    
    // Initialize file upload handlers
    initializeFileUpload();
    
    // Initialize search functionality
    initializeSearch();
    
    // Initialize admin functions
    initializeAdminFunctions();
    
    // Initialize form validation
    initializeFormValidation();
    
    // Initialize responsive features
    initializeResponsiveFeatures();
});

// Initialize Bootstrap tooltips
function initializeTooltips() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// File Upload Functionality
function initializeFileUpload() {
    const fileInput = document.getElementById('file');
    const uploadForm = document.getElementById('uploadForm');
    
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                validateFile(file);
            }
        });
    }
    
    if (uploadForm) {
        uploadForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleFormSubmission(this);
        });
    }
}

// File validation
function validateFile(file) {
    const maxSize = 50 * 1024 * 1024; // 50MB
    const allowedTypes = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'zip', 'rar'];
    const fileExtension = file.name.split('.').pop().toLowerCase();
    
    const feedback = document.createElement('div');
    feedback.className = 'mt-2';
    
    // Remove any existing feedback
    const existingFeedback = document.querySelector('.file-feedback');
    if (existingFeedback) {
        existingFeedback.remove();
    }
    
    if (file.size > maxSize) {
        feedback.className += ' text-danger file-feedback';
        feedback.innerHTML = '<i class="fas fa-exclamation-triangle"></i> File size must be less than 50MB';
    } else if (!allowedTypes.includes(fileExtension)) {
        feedback.className += ' text-danger file-feedback';
        feedback.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Invalid file type. Allowed: ' + allowedTypes.join(', ');
    } else {
        feedback.className += ' text-success file-feedback';
        feedback.innerHTML = '<i class="fas fa-check"></i> File is valid (' + formatFileSize(file.size) + ')';
    }
    
    document.getElementById('file').parentNode.appendChild(feedback);
}

// Handle form submission with AJAX
function handleFormSubmission(form) {
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Uploading...';
    submitBtn.disabled = true;
    
    // Create FormData object
    const formData = new FormData(form);
    
    // Send AJAX request
    fetch('api/upload_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            form.reset();
            // Remove file feedback
            const feedback = document.querySelector('.file-feedback');
            if (feedback) feedback.remove();
        } else {
            showAlert('danger', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'An error occurred while uploading the file.');
    })
    .finally(() => {
        // Restore button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

// Search functionality
function initializeSearch() {
    const searchForms = document.querySelectorAll('form[method="GET"]');
    
    searchForms.forEach(form => {
        const searchInput = form.querySelector('input[name="search"]');
        if (searchInput) {
            let debounceTimer;
            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    if (this.value.length >= 3 || this.value.length === 0) {
                        // Auto-submit after 500ms of no typing
                        // form.submit();
                    }
                }, 500);
            });
        }
    });
}

// Admin functions
function initializeAdminFunctions() {
    // Bulk actions
    const bulkForm = document.getElementById('bulkForm');
    if (bulkForm) {
        bulkForm.addEventListener('submit', function(e) {
            const action = document.getElementById('bulkAction').value;
            const checkedBoxes = document.querySelectorAll('.upload-checkbox:checked');
            
            if (!action) {
                e.preventDefault();
                showAlert('warning', 'Please select an action.');
                return;
            }
            
            if (checkedBoxes.length === 0) {
                e.preventDefault();
                showAlert('warning', 'Please select at least one item.');
                return;
            }
        });
    }
    
    // Quick status updates
    window.quickApprove = function(uploadId) {
        if (confirm('Are you sure you want to approve this upload?')) {
            updateUploadStatus(uploadId, 'approved');
        }
    };
    
    window.quickReject = function(uploadId) {
        if (confirm('Are you sure you want to reject this upload?')) {
            updateUploadStatus(uploadId, 'rejected');
        }
    };
}

// Update upload status via AJAX
function updateUploadStatus(uploadId, status) {
    const data = {
        action: 'update_status',
        upload_id: uploadId,
        status: status
    };
    
    fetch('api/admin_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            // Reload page after a short delay
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showAlert('danger', 'Failed to update status: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'An error occurred while updating the status.');
    });
}

// Form validation
function initializeFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
}

// Responsive features
function initializeResponsiveFeatures() {
    // Collapse mobile menu when clicking a link
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
    const navCollapse = document.querySelector('.navbar-collapse');
    
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (navCollapse && navCollapse.classList.contains('show')) {
                const bsCollapse = new bootstrap.Collapse(navCollapse);
                bsCollapse.hide();
            }
        });
    });
}

// Utility Functions

// Show alert message
function showAlert(type, message) {
    const alertContainer = document.querySelector('.container') || document.body;
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show mt-3`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insert alert at the top of the container
    if (alertContainer.firstChild) {
        alertContainer.insertBefore(alertDiv, alertContainer.firstChild);
    } else {
        alertContainer.appendChild(alertDiv);
    }
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

// Format file size
function formatFileSize(bytes) {
    if (bytes >= 1073741824) {
        return (bytes / 1073741824).toFixed(2) + ' GB';
    } else if (bytes >= 1048576) {
        return (bytes / 1048576).toFixed(2) + ' MB';
    } else if (bytes >= 1024) {
        return (bytes / 1024).toFixed(2) + ' KB';
    } else {
        return bytes + ' bytes';
    }
}

// Toggle all checkboxes
function toggleAll() {
    const masterCheckbox = document.getElementById('selectAllCheckbox');
    const checkboxes = document.querySelectorAll('.upload-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = masterCheckbox.checked;
    });
}

// Select all checkboxes
function selectAll() {
    document.querySelectorAll('.upload-checkbox').forEach(checkbox => {
        checkbox.checked = true;
    });
    const masterCheckbox = document.getElementById('selectAllCheckbox');
    if (masterCheckbox) {
        masterCheckbox.checked = true;
    }
}

// Deselect all checkboxes
function selectNone() {
    document.querySelectorAll('.upload-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    const masterCheckbox = document.getElementById('selectAllCheckbox');
    if (masterCheckbox) {
        masterCheckbox.checked = false;
    }
}

// Confirm bulk action
function confirmBulkAction() {
    const action = document.getElementById('bulkAction').value;
    const checkedBoxes = document.querySelectorAll('.upload-checkbox:checked');
    
    if (!action) {
        showAlert('warning', 'Please select an action.');
        return false;
    }
    
    if (checkedBoxes.length === 0) {
        showAlert('warning', 'Please select at least one upload.');
        return false;
    }
    
    return confirm(`Are you sure you want to ${action} ${checkedBoxes.length} selected upload(s)?`);
}

// Load more materials (pagination)
function loadMoreMaterials(offset = 0) {
    const params = new URLSearchParams(window.location.search);
    params.set('offset', offset);
    
    fetch(`api/filter_materials.php?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayMaterials(data.materials, offset > 0);
                updatePagination(data.pagination);
            } else {
                showAlert('danger', 'Failed to load materials: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'An error occurred while loading materials.');
        });
}

// Display materials in the interface
function displayMaterials(materials, append = false) {
    const container = document.getElementById('materialsContainer');
    if (!container) return;
    
    if (!append) {
        container.innerHTML = '';
    }
    
    materials.forEach(material => {
        const materialCard = createMaterialCard(material);
        container.appendChild(materialCard);
    });
}

// Create material card HTML
function createMaterialCard(material) {
    const card = document.createElement('div');
    card.className = 'col-md-6 col-lg-4 mb-4';
    
    card.innerHTML = `
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    ${material.department}
                    ${material.module_type ? ' - ' + material.module_type : ''}
                </small>
                <span class="badge bg-${material.department_color}">
                    ${material.category}
                </span>
            </div>
            <div class="card-body">
                <h6 class="card-title">${material.title}</h6>
                ${material.description ? `
                    <p class="card-text text-muted small">
                        ${material.description.substring(0, 100)}${material.description.length > 100 ? '...' : ''}
                    </p>
                ` : ''}
                <div class="mt-auto">
                    <small class="text-muted d-block">By: ${material.uploader_name}</small>
                    <small class="text-muted d-block">Uploaded: ${material.formatted_date}</small>
                    ${material.formatted_size ? `<small class="text-muted d-block">Size: ${material.formatted_size}</small>` : ''}
                </div>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between">
                    <button class="btn btn-sm btn-outline-primary" onclick="previewMaterial(${material.id})">
                        <i class="fas fa-eye"></i> Preview
                    </button>
                    <a href="download.php?id=${material.id}" class="btn btn-sm btn-primary">
                        <i class="fas fa-download"></i> Download
                    </a>
                </div>
            </div>
        </div>
    `;
    
    return card;
}

// Initialize drag and drop for file uploads
function initializeDragAndDrop() {
    const dropZone = document.querySelector('.file-upload-area');
    const fileInput = document.getElementById('file');
    
    if (!dropZone || !fileInput) return;
    
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, highlight, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, unhighlight, false);
    });
    
    function highlight() {
        dropZone.classList.add('dragover');
    }
    
    function unhighlight() {
        dropZone.classList.remove('dragover');
    }
    
    dropZone.addEventListener('drop', handleDrop, false);
    
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        
        if (files.length > 0) {
            fileInput.files = files;
            validateFile(files[0]);
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeDragAndDrop();
});

// Export functions for global use
window.showAlert = showAlert;
window.formatFileSize = formatFileSize;
window.toggleAll = toggleAll;
window.selectAll = selectAll;
window.selectNone = selectNone;
window.confirmBulkAction = confirmBulkAction;
window.loadMoreMaterials = loadMoreMaterials;
