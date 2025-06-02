<!-- Footer -->
<footer class="bg-white border-t border-gray-200 py-4 px-4 sm:px-6 mt-auto">
    <div class="flex flex-col sm:flex-row justify-between items-center">
        <div class="text-sm text-gray-600 text-center sm:text-left">
            © <?php echo date('Y'); ?> <?php echo __('file_management_system'); ?>. All rights reserved.
        </div>
        <div class="text-sm text-gray-600 mt-2 sm:mt-0">
            Version <?php echo APP_VERSION; ?>
        </div>
    </div>
</footer>

<!-- Scripts -->
<script>
    // Auto-hide alerts after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert-auto-hide');
        alerts.forEach(function(alert) {
            setTimeout(function() {
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.remove();
                }, 300);
            }, 5000);
        });
    });

    // Confirm delete actions
    function confirmDelete(message) {
        return confirm(message || 'Are you sure you want to delete this item?');
    }

    // File upload preview
    function previewFile(input) {
        const file = input.files[0];
        const preview = document.getElementById('filePreview');
        
        if (file && preview) {
            const reader = new FileReader();
            reader.onload = function(e) {
                if (file.type.startsWith('image/')) {
                    preview.innerHTML = `<img src="${e.target.result}" class="max-w-xs max-h-32 object-contain">`;
                } else {
                    preview.innerHTML = `<div class="p-4 bg-gray-100 rounded"><i class="fas fa-file mr-2"></i>${file.name}</div>`;
                }
            };
            reader.readAsDataURL(file);
        }
    }

    // Search functionality
    function searchTable(input, tableId) {
        const filter = input.value.toLowerCase();
        const table = document.getElementById(tableId);
        const rows = table.getElementsByTagName('tr');

        for (let i = 1; i < rows.length; i++) {
            const row = rows[i];
            const cells = row.getElementsByTagName('td');
            let found = false;

            for (let j = 0; j < cells.length; j++) {
                if (cells[j].textContent.toLowerCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }

            row.style.display = found ? '' : 'none';
        }
    }

    // Form validation
    function validateForm(formId) {
        const form = document.getElementById(formId);
        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
        let isValid = true;

        inputs.forEach(function(input) {
            if (!input.value.trim()) {
                input.classList.add('border-red-500');
                isValid = false;
            } else {
                input.classList.remove('border-red-500');
            }
        });

        return isValid;
    }

    // Password strength indicator
    function checkPasswordStrength(password) {
        let strength = 0;
        if (password.length >= 8) strength++;
        if (password.match(/[a-z]/)) strength++;
        if (password.match(/[A-Z]/)) strength++;
        if (password.match(/[0-9]/)) strength++;
        if (password.match(/[^a-zA-Z0-9]/)) strength++;

        const indicator = document.getElementById('passwordStrength') || document.getElementById('passwordStrengthEdit');
        const label = document.getElementById('passwordStrengthLabel') || document.getElementById('passwordStrengthLabelEdit');
        if (indicator && label) {
            const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-blue-500', 'bg-green-500'];
            const labels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
            
            indicator.className = `h-2 rounded transition-all duration-300 ${colors[strength - 1] || 'bg-gray-300'}`;
            indicator.style.width = `${(strength / 5) * 100}%`;
            label.textContent = labels[strength - 1] || '';
        }
    }

    // File size validation
    function validateFileSize(input, maxSize = <?php echo MAX_FILE_SIZE; ?>) {
        const file = input.files[0];
        if (file && file.size > maxSize) {
            alert('File size exceeds the maximum allowed size of ' + (maxSize / 1048576).toFixed(2) + ' MB');
            input.value = '';
            return false;
        }
        return true;
    }

    // File type validation
    function validateFileType(input) {
        const file = input.files[0];
        const allowedTypes = <?php echo json_encode(ALLOWED_EXTENSIONS); ?>;
        
        if (file) {
            const extension = file.name.split('.').pop().toLowerCase();
            if (!allowedTypes.includes(extension)) {
                alert('File type not allowed. Allowed types: ' + allowedTypes.join(', '));
                input.value = '';
                return false;
            }
        }
        return true;
    }
</script>
</body>
</html>