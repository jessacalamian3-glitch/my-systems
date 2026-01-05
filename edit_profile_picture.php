<?php
// edit_profile_picture.php - Faculty Profile Picture Upload

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
class Database {
    private $host = "localhost";
    private $db_name = "msubuug_db";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            error_log("Connection error: " . $exception->getMessage());
        }
        return $this->conn;
    }
}

// Check if user is logged in as faculty
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_type'] !== 'faculty') {
    header("Location: faculty_login.php");
    exit();
}

$faculty_id = $_SESSION['username'] ?? 'N/A';
$success_message = '';
$error_message = '';

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    try {
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/faculty/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($file_extension, $allowed_extensions)) {
                if ($_FILES['profile_picture']['size'] <= 2097152) { // 2MB
                    
                    // Get current profile picture to delete later
                    $database = new Database();
                    $db = $database->getConnection();
                    $old_picture = null;
                    
                    if ($db) {
                        $stmt = $db->prepare("SELECT profile_picture FROM faculty WHERE faculty_id = ?");
                        $stmt->execute([$faculty_id]);
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        $old_picture = $result['profile_picture'] ?? null;
                    }
                    
                    // Generate new filename
                    $file_name = 'faculty_' . $faculty_id . '_' . time() . '.' . $file_extension;
                    $file_path = $upload_dir . $file_name;
                    
                    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $file_path)) {
                        // Update database with just the filename (not full path)
                        if ($db) {
                            $stmt = $db->prepare("UPDATE faculty SET profile_picture = ? WHERE faculty_id = ?");
                            $result = $stmt->execute([$file_name, $faculty_id]);
                            
                            if ($result) {
                                // Delete old profile picture if exists
                                if ($old_picture && file_exists($upload_dir . $old_picture)) {
                                    unlink($upload_dir . $old_picture);
                                }
                                
                                $success_message = "Profile picture updated successfully!";
                                
                                // Update session if needed
                                $_SESSION['user_info']['profile_picture'] = $upload_dir . $file_name;
                                
                                // Redirect to dashboard after 2 seconds
                                header("refresh:2;url=faculty_dashboard.php");
                            } else {
                                $error_message = "Failed to update profile picture in database.";
                            }
                        }
                    } else {
                        $error_message = "Failed to move uploaded file.";
                    }
                } else {
                    $error_message = "File is too large. Maximum size is 2MB.";
                }
            } else {
                $error_message = "Invalid file type. Only JPG, JPEG, PNG, GIF, and WEBP are allowed.";
            }
        } else {
            $error_message = "Please select a valid image file.";
        }
        
    } catch(PDOException $e) {
        $error_message = "Database error: " . $e->getMessage();
    }
}

// Get current faculty data
function getFacultyData($faculty_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "SELECT * FROM faculty WHERE faculty_id = :faculty_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':faculty_id', $faculty_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return false;
}

$faculty_data = getFacultyData($faculty_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Profile Picture - MSU Buug</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --maroon: #800000;
            --maroon-dark: #5a0000;
            --gold: #FFD700;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            padding-top: 70px;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--maroon) 0%, var(--maroon-dark) 100%) !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .profile-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-top: 30px;
        }
        
        .profile-preview {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            margin: 0 auto 20px;
            border: 5px solid var(--maroon);
            overflow: hidden;
            background: linear-gradient(135deg, var(--gold), #ffed4e);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .profile-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .initials-preview {
            font-size: 4rem;
            font-weight: bold;
            color: var(--maroon);
        }
        
        .btn-msu {
            background: linear-gradient(135deg, var(--maroon), var(--maroon-dark));
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-msu:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(128,0,0,0.3);
            color: white;
        }
        
        .upload-area {
            border: 2px dashed #ddd;
            border-radius: 10px;
            padding: 40px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }
        
        .upload-area:hover {
            border-color: var(--maroon);
            background: #f0f0f0;
        }
        
        .upload-icon {
            font-size: 3rem;
            color: var(--maroon);
            margin-bottom: 15px;
        }
        
        .file-info {
            background: #e9ecef;
            padding: 10px;
            border-radius: 5px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar (Same as dashboard) -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="faculty_dashboard.php">
                <i class="fas fa-chalkboard-teacher me-2"></i>
                MSU BUUG - Faculty Portal
            </a>
            <div class="d-flex align-items-center">
                <a href="faculty_dashboard.php" class="btn btn-sm btn-outline-light">
                    <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="profile-container">
            <h2 class="text-center mb-4" style="color: var(--maroon);">
                <i class="fas fa-camera me-2"></i>Change Profile Picture
            </h2>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Current Profile Picture Preview -->
            <div class="profile-preview">
                <?php
                // Get current profile picture
                $current_picture = null;
                if ($faculty_data && !empty($faculty_data['profile_picture'])) {
                    $current_picture = 'uploads/faculty/' . $faculty_data['profile_picture'];
                }
                
                // Get initials for fallback
                $initials = 'F';
                if ($faculty_data && !empty($faculty_data['full_name'])) {
                    $name_parts = explode(' ', trim($faculty_data['full_name']));
                    $initials = strtoupper(substr($name_parts[0], 0, 1));
                    if (count($name_parts) > 1) {
                        $initials .= strtoupper(substr(end($name_parts), 0, 1));
                    }
                }
                ?>
                
                <?php if ($current_picture && file_exists($current_picture)): ?>
                    <img src="<?php echo $current_picture; ?>" 
                         alt="Current Profile Picture" 
                         id="imagePreview">
                <?php else: ?>
                    <div class="initials-preview" id="initialsPreview">
                        <?php echo $initials; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Upload Form -->
            <form method="POST" enctype="multipart/form-data" id="uploadForm">
                <div class="upload-area mb-4" onclick="document.getElementById('profilePicture').click()">
                    <div class="upload-icon">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </div>
                    <h5>Click to upload your photo</h5>
                    <p class="text-muted">Drag and drop is also supported</p>
                    <p class="text-muted small">Max file size: 2MB • Allowed: JPG, JPEG, PNG, GIF, WEBP</p>
                </div>
                
                <input type="file" 
                       id="profilePicture" 
                       name="profile_picture" 
                       accept="image/*" 
                       style="display: none;" 
                       onchange="previewImage(this)">
                
                <div id="fileInfo" class="file-info mb-4" style="display: none;">
                    <strong>Selected File:</strong> <span id="fileName"></span><br>
                    <strong>Size:</strong> <span id="fileSize"></span><br>
                    <div class="progress mt-2" style="height: 5px;">
                        <div id="uploadProgress" class="progress-bar bg-success" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>
                
                <div class="text-center">
                    <button type="submit" class="btn btn-msu btn-lg" id="uploadButton" disabled>
                        <i class="fas fa-upload me-2"></i>Upload Photo
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function previewImage(input) {
            const fileInfo = document.getElementById('fileInfo');
            const fileName = document.getElementById('fileName');
            const fileSize = document.getElementById('fileSize');
            const uploadButton = document.getElementById('uploadButton');
            const imagePreview = document.getElementById('imagePreview');
            const initialsPreview = document.getElementById('initialsPreview');
            
            if (input.files && input.files[0]) {
                const file = input.files[0];
                
                // Show file info
                fileInfo.style.display = 'block';
                fileName.textContent = file.name;
                fileSize.textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
                
                // Validate file
                if (file.size > 2097152) {
                    alert('File is too large! Maximum size is 2MB.');
                    input.value = '';
                    fileInfo.style.display = 'none';
                    uploadButton.disabled = true;
                    return;
                }
                
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Invalid file type! Only JPG, JPEG, PNG, GIF, and WEBP are allowed.');
                    input.value = '';
                    fileInfo.style.display = 'none';
                    uploadButton.disabled = true;
                    return;
                }
                
                // Enable upload button
                uploadButton.disabled = false;
                
                // Show progress bar animation
                const progressBar = document.getElementById('uploadProgress');
                let width = 0;
                const interval = setInterval(() => {
                    if (width >= 100) {
                        clearInterval(interval);
                    } else {
                        width++;
                        progressBar.style.width = width + '%';
                    }
                }, 10);
                
                // Preview image
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Hide initials preview if exists
                    if (initialsPreview) {
                        initialsPreview.style.display = 'none';
                    }
                    
                    // Show image preview
                    if (imagePreview) {
                        imagePreview.src = e.target.result;
                    } else {
                        // Create image element if it doesn't exist
                        const newImg = document.createElement('img');
                        newImg.src = e.target.result;
                        newImg.alt = 'Profile Preview';
                        newImg.id = 'imagePreview';
                        newImg.style.width = '100%';
                        newImg.style.height = '100%';
                        newImg.style.objectFit = 'cover';
                        
                        const previewContainer = document.querySelector('.profile-preview');
                        previewContainer.innerHTML = '';
                        previewContainer.appendChild(newImg);
                    }
                };
                reader.readAsDataURL(file);
            } else {
                fileInfo.style.display = 'none';
                uploadButton.disabled = true;
            }
        }
        
        // Form submission with loading state
        document.getElementById('uploadForm').addEventListener('submit', function() {
            const button = document.getElementById('uploadButton');
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Uploading...';
            button.disabled = true;
        });
        
        // Drag and drop support
        const uploadArea = document.querySelector('.upload-area');
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.style.borderColor = 'var(--maroon)';
            this.style.background = '#e9ecef';
        });
        
        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.style.borderColor = '#ddd';
            this.style.background = '#f8f9fa';
        });
        
        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            this.style.borderColor = '#ddd';
            this.style.background = '#f8f9fa';
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                document.getElementById('profilePicture').files = files;
                previewImage(document.getElementById('profilePicture'));
            }
        });
    </script>
</body>
</html>