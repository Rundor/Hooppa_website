<!DOCTYPE html>
<?php
session_start();
if (!isset($_SESSION['admin_user'])) {
  header('Location: admin_login_form.html');
  exit();
}

// Database connection
$user = 'root';
$pass = '';
$db = 'kids_toys_store';
$db = mysqli_connect('localhost', $user, $pass, $db) or die("Unable to connect");

// Get admin data
$admin_id = 1; // Replace with $_SESSION['admin_id']
$admin_query = "SELECT * FROM admin WHERE admin_id = $admin_id";
$admin_result = mysqli_query($db, $admin_query);
$admin_data = mysqli_fetch_assoc($admin_result);
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HOOPPA | Admin Dashboard</title>
    
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/adminStyles.css">

    <!-- fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@100..900&display=swap" rel="stylesheet">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Teachers:ital,wght@0,400..800;1,400..800&display=swap" rel="stylesheet">

    <!-- Riyal Symbol -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@emran-alhaddad/saudi-riyal-font/index.css">
    <style>
        /* Uniform Modal Styles */
        .uniform-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: white;
            padding: 25px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            animation: modalFadeIn 0.3s ease-out;
        }
        
        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .modal-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
        }
        
        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #777;
        }
        
        .close-modal:hover {
            color: #333;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }
        
        .form-group input, 
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: border 0.3s;
        }
        
        .form-group input:focus, 
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #4f9779;
            outline: none;
            box-shadow: 0 0 0 2px rgba(79, 151, 121, 0.2);
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 25px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            border: none;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background-color: #4f9779;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #3e7d65;
        }
        
        .btn-secondary {
            background-color: #f0f0f0;
            color: #555;
        }
        
        .btn-secondary:hover {
            background-color: #e0e0e0;
        }
        
        .error-message {
            color: #d9534f;
            margin-bottom: 20px;
            padding: 12px;
            background-color: #fdf3f3;
            border-left: 4px solid #d9534f;
            display: none;
            border-radius: 4px;
        }
        
        .success-message {
            color: #5cb85c;
            margin-bottom: 20px;
            padding: 12px;
            background-color: #f3fdf3;
            border-left: 4px solid #5cb85c;
            display: none;
            border-radius: 4px;
        }
        
        .file-upload-wrapper {
            position: relative;
            margin-bottom: 15px;
        }
        
        .file-upload {
            display: inline-block;
            padding: 10px 15px;
            background-color: #f0f0f0;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .file-upload:hover {
            background-color: #e0e0e0;
        }
        
        .file-upload input[type="file"] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .file-name {
            margin-top: 8px;
            font-size: 14px;
            color: #666;
        }
        
        /* Password strength indicator */
        .password-strength {
            height: 4px;
            background: #eee;
            margin-top: 8px;
            border-radius: 2px;
            overflow: hidden;
        }
        
        .password-strength-bar {
            height: 100%;
            width: 0%;
            background: #d9534f;
            transition: width 0.3s, background 0.3s;
        }
    </style>
</head>
<body>
    <!-- [Previous HTML content remains exactly the same until the scripts section] -->

    <!-- New Uniform Modal Templates -->
    <div id="changePasswordModal" class="uniform-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Change Password</h3>
                <button class="close-modal" onclick="closeModal('changePasswordModal')">&times;</button>
            </div>
            <div id="passwordError" class="error-message"></div>
            <div id="passwordSuccess" class="success-message"></div>
            <form id="changePasswordForm">
                <div class="form-group">
                    <label for="currentPassword">Current Password</label>
                    <input type="password" id="currentPassword" required>
                </div>
                <div class="form-group">
                    <label for="newPassword">New Password</label>
                    <input type="password" id="newPassword" required>
                    <div class="password-strength">
                        <div class="password-strength-bar" id="passwordStrengthBar"></div>
                    </div>
                    <small class="text-muted">Minimum 8 characters with at least 1 number and 1 special character</small>
                </div>
                <div class="form-group">
                    <label for="confirmPassword">Confirm New Password</label>
                    <input type="password" id="confirmPassword" required>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('changePasswordModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editProfileModal" class="uniform-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Edit Profile</h3>
                <button class="close-modal" onclick="closeModal('editProfileModal')">&times;</button>
            </div>
            <div id="profileError" class="error-message"></div>
            <div id="profileSuccess" class="success-message"></div>
            <form id="editProfileForm">
                <div class="form-group">
                    <label for="adminName">Full Name</label>
                    <input type="text" id="adminName" value="<?php echo htmlspecialchars($admin_data['admin_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="adminEmail">Email</label>
                    <input type="email" id="adminEmail" value="<?php echo htmlspecialchars($admin_data['email']); ?>" required>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editProfileModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript for Validation and Modals -->
    <script>
        // Password strength checker
        document.getElementById('newPassword').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('passwordStrengthBar');
            let strength = 0;
            
            // Length check
            if (password.length >= 8) strength += 1;
            if (password.length >= 12) strength += 1;
            
            // Contains numbers
            if (/\d/.test(password)) strength += 1;
            
            // Contains special chars
            if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength += 1;
            
            // Contains both upper and lower case
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 1;
            
            // Update strength bar
            let width = 0;
            let color = '#d9534f'; // red
            
            if (strength >= 4) {
                width = 100;
                color = '#5cb85c'; // green
            } else if (strength >= 2) {
                width = 66;
                color = '#f0ad4e'; // yellow
            } else if (strength >= 1) {
                width = 33;
            }
            
            strengthBar.style.width = width + '%';
            strengthBar.style.backgroundColor = color;
        });

        // Show modal function
        function showModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
            document.body.style.overflow = 'hidden'; // Prevent scrolling
        }
        
        // Close modal function
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            document.getElementById(modalId.replace('Modal', 'Error')).textContent = '';
            document.getElementById(modalId.replace('Modal', 'Error')).style.display = 'none';
            document.getElementById(modalId.replace('Modal', 'Success')).textContent = '';
            document.getElementById(modalId.replace('Modal', 'Success')).style.display = 'none';
            document.body.style.overflow = ''; // Re-enable scrolling
            
            // Reset forms
            if (modalId === 'changePasswordModal') {
                document.getElementById('changePasswordForm').reset();
                document.getElementById('passwordStrengthBar').style.width = '0%';
            }
        }
        
        // Change Password Form Validation
        document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const currentPassword = document.getElementById('currentPassword').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const errorElement = document.getElementById('passwordError');
            const successElement = document.getElementById('passwordSuccess');
            
            // Reset messages
            errorElement.textContent = '';
            errorElement.style.display = 'none';
            successElement.style.display = 'none';
            
            // Validation
            if (!currentPassword) {
                errorElement.textContent = 'Current password is required';
                errorElement.style.display = 'block';
                return;
            }
            
            if (newPassword.length < 8) {
                errorElement.textContent = 'Password must be at least 8 characters long';
                errorElement.style.display = 'block';
                return;
            }
            
            if (!/\d/.test(newPassword) || !/[!@#$%^&*(),.?":{}|<>]/.test(newPassword)) {
                errorElement.textContent = 'Password must contain at least 1 number and 1 special character';
                errorElement.style.display = 'block';
                return;
            }
            
            if (newPassword !== confirmPassword) {
                errorElement.textContent = 'New passwords do not match';
                errorElement.style.display = 'block';
                return;
            }
            
            if (newPassword === currentPassword) {
                errorElement.textContent = 'New password must be different from current password';
                errorElement.style.display = 'block';
                return;
            }
            
            // If validation passes, simulate successful submission
            successElement.textContent = 'Password changed successfully!';
            successElement.style.display = 'block';
            
            // In a real implementation, you would make an AJAX call here
            setTimeout(() => {
                closeModal('changePasswordModal');
            }, 1500);
        });
        
        // Edit Profile Form Validation
        document.getElementById('editProfileForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const adminName = document.getElementById('adminName').value.trim();
            const adminEmail = document.getElementById('adminEmail').value.trim();
            const errorElement = document.getElementById('profileError');
            const successElement = document.getElementById('profileSuccess');
            
            // Reset messages
            errorElement.textContent = '';
            errorElement.style.display = 'none';
            successElement.style.display = 'none';
            
            // Validation
            if (!adminName) {
                errorElement.textContent = 'Name is required';
                errorElement.style.display = 'block';
                return;
            }
            
            if (!/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(adminEmail)) {
                errorElement.textContent = 'Please enter a valid email address';
                errorElement.style.display = 'block';
                return;
            }
            
            // If validation passes, simulate successful submission
            successElement.textContent = 'Profile updated successfully!';
            successElement.style.display = 'block';
            
            // In a real implementation, you would make an AJAX call here
            setTimeout(() => {
                closeModal('editProfileModal');
            }, 1500);
        });
        
        // Add Product Form Validation
        document.querySelector('#addProduct form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = this;
            const productName = form.querySelector('input[name="product_name"]').value.trim();
            const description = form.querySelector('textarea[name="description"]').value.trim();
            const price = form.querySelector('input[name="price"]').value;
            const stock = form.querySelector('input[name="stock_quantity"]').value;
            const image = form.querySelector('input[name="product_image"]').files[0];
            const ageRange = form.querySelector('input[name="age_range"]').value.trim();
            
            // Validation
            if (!productName) {
                alert('Product name is required');
                return;
            }
            
            if (!description) {
                alert('Description is required');
                return;
            }
            
            if (!price || isNaN(price) || parseFloat(price) <= 0) {
                alert('Please enter a valid price');
                return;
            }
            
            if (!stock || isNaN(stock) || parseInt(stock) < 0) {
                alert('Please enter a valid stock quantity');
                return;
            }
            
            if (!ageRange) {
                alert('Age range is required');
                return;
            }
            
            if (!image) {
                alert('Product image is required');
                return;
            }
            
            // If validation passes, submit the form
            form.submit();
        });
        
        // Edit Product Form Validation
        document.getElementById('editProductForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = e.target;
            const productName = form.querySelector('#edit_product_name').value.trim();
            const description = form.querySelector('#edit_description').value.trim();
            const price = form.querySelector('#edit_price').value;
            const stock = form.querySelector('#edit_stock_quantity').value;
            const ageRange = form.querySelector('#edit_age_range').value.trim();
            
            // Validation
            if (!productName) {
                alert('Product name is required');
                return;
            }
            
            if (!description) {
                alert('Description is required');
                return;
            }
            
            if (!price || isNaN(price) || parseFloat(price) <= 0) {
                alert('Please enter a valid price');
                return;
            }
            
            if (!stock || isNaN(stock) || parseInt(stock) < 0) {
                alert('Please enter a valid stock quantity');
                return;
            }
            
            if (!ageRange) {
                alert('Age range is required');
                return;
            }
            
            // If validation passes, submit the form
            form.submit();
        });
        
        // Update profile buttons to use new modals
        function showChangePassword() {
            showModal('changePasswordModal');
        }
        
        function showEditProfile() {
            showModal('editProfileModal');
        }
        
        // Close modals when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target.classList.contains('uniform-modal')) {
                closeModal(event.target.id);
            }
        });
        
        // Close modals with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const openModal = document.querySelector('.uniform-modal[style="display: flex;"]');
                if (openModal) {
                    closeModal(openModal.id);
                }
            }
        });
        
        // File upload display
        function setupFileUpload(inputId, displayId) {
            const fileInput = document.getElementById(inputId);
            const fileNameDisplay = document.getElementById(displayId);
            
            if (fileInput && fileNameDisplay) {
                fileInput.addEventListener('change', function() {
                    if (this.files && this.files.length > 0) {
                        fileNameDisplay.textContent = this.files[0].name;
                    } else {
                        fileNameDisplay.textContent = 'No file chosen';
                    }
                });
            }
        }
        
        // Initialize file upload displays
        setupFileUpload('product_image', 'file-name');
        setupFileUpload('edit_product_image', 'edit_file-name');
    </script>

    <!-- [Rest of your existing scripts remain] -->
</body>
</html>