<?php
session_start();
include "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if profile exists
$profile_exists = false;
$profile = [];
$profile_query = $conn->query("SELECT * FROM patient_profiles WHERE user_id = $user_id");
if ($profile_query && $profile_query->num_rows > 0) {
    $profile = $profile_query->fetch_assoc();
    $profile_exists = true;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate input
    $phone = $conn->real_escape_string($_POST['phone'] ?? '');
    $gender = $conn->real_escape_string($_POST['gender'] ?? '');
    $dob = $conn->real_escape_string($_POST['dob'] ?? '');
    $address = $conn->real_escape_string($_POST['address'] ?? '');
    $city = $conn->real_escape_string($_POST['city'] ?? '');
    $state = $conn->real_escape_string($_POST['state'] ?? '');
    $postal_code = $conn->real_escape_string($_POST['postal_code'] ?? '');
    $country = $conn->real_escape_string($_POST['country'] ?? '');
    $blood_type = $conn->real_escape_string($_POST['blood_type'] ?? '');
    $height = floatval($_POST['height'] ?? 0);
    $weight = floatval($_POST['weight'] ?? 0);
    $medical_history = $conn->real_escape_string($_POST['medical_history'] ?? '');
    $allergies = $conn->real_escape_string($_POST['allergies'] ?? '');
    $current_medications = $conn->real_escape_string($_POST['current_medications'] ?? '');
    $emergency_contact_name = $conn->real_escape_string($_POST['emergency_contact_name'] ?? '');
    $emergency_contact_phone = $conn->real_escape_string($_POST['emergency_contact_phone'] ?? '');
    $emergency_contact_relation = $conn->real_escape_string($_POST['emergency_contact_relation'] ?? '');

    if ($profile_exists) {
        // Update existing profile
        $query = "UPDATE patient_profiles SET 
                  phone = '$phone',
                  gender = '$gender',
                  date_of_birth = " . ($dob ? "'$dob'" : "NULL") . ",
                  address = '$address',
                  city = '$city',
                  state = '$state',
                  postal_code = '$postal_code',
                  country = '$country',
                  blood_type = '$blood_type',
                  height = $height,
                  weight = $weight,
                  medical_history = '$medical_history',
                  allergies = '$allergies',
                  current_medications = '$current_medications',
                  emergency_contact_name = '$emergency_contact_name',
                  emergency_contact_phone = '$emergency_contact_phone',
                  emergency_contact_relation = '$emergency_contact_relation',
                  updated_at = NOW()
                  WHERE user_id = $user_id";
    } else {
        // Insert new profile
        $query = "INSERT INTO patient_profiles (
                  user_id, phone, gender, date_of_birth, address, city, state, 
                  postal_code, country, blood_type, height, weight, medical_history, 
                  allergies, current_medications, emergency_contact_name, 
                  emergency_contact_phone, emergency_contact_relation, created_at, updated_at
                  ) VALUES (
                  $user_id, '$phone', '$gender', " . ($dob ? "'$dob'" : "NULL") . ", 
                  '$address', '$city', '$state', '$postal_code', '$country', 
                  '$blood_type', $height, $weight, '$medical_history', '$allergies', 
                  '$current_medications', '$emergency_contact_name', 
                  '$emergency_contact_phone', '$emergency_contact_relation', 
                  NOW(), NOW())";
    }

    if ($conn->query($query)) {
        $success = "Profile updated successfully!";
        // Refresh profile data
        $profile = $conn->query("SELECT * FROM patient_profiles WHERE user_id = $user_id")->fetch_assoc();
        $profile_exists = true;
    } else {
        $error = "Error saving profile: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>My Profile - Maison Bloom</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    :root {
      --primary: #8a63d2;
      --primary-light: #b39ddb;
      --primary-dark: #4b0082;
      --secondary: #ff85a2;
      --accent: #63d2a0;
      --light: #f9f6ff;
      --white: #ffffff;
      --text: #333333;
      --text-light: #666666;
      --shadow-sm: 0 2px 10px rgba(0,0,0,0.08);
      --shadow-md: 0 5px 20px rgba(0,0,0,0.12);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background-color: var(--light);
      color: var(--text);
      line-height: 1.6;
      padding: 20px;
    }

    .container {
      max-width: 1000px;
      margin: 0 auto;
      background-color: var(--white);
      border-radius: 16px;
      box-shadow: var(--shadow-md);
      overflow: hidden;
    }

    .profile-header {
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
      color: var(--white);
      padding: 30px;
      text-align: center;
      position: relative;
    }

    .profile-header h1 {
      font-size: 2rem;
      margin-bottom: 10px;
    }

    .profile-content {
      padding: 30px;
    }

    .form-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 20px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
      color: var(--primary-dark);
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
      width: 100%;
      padding: 12px 15px;
      border: 1px solid #ddd;
      border-radius: 8px;
      font-family: 'Poppins', sans-serif;
      transition: all 0.3s ease;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
      border-color: var(--primary);
      outline: none;
      box-shadow: 0 0 0 3px rgba(138, 99, 210, 0.2);
    }

    .form-group textarea {
      min-height: 100px;
      resize: vertical;
    }

    .full-width {
      grid-column: 1 / -1;
    }

    .btn {
      display: inline-block;
      padding: 12px 25px;
      background-color: var(--primary);
      color: var(--white);
      border: none;
      border-radius: 8px;
      font-family: 'Poppins', sans-serif;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
    }

    .btn:hover {
      background-color: var(--primary-dark);
      transform: translateY(-2px);
      box-shadow: var(--shadow-sm);
    }

    .alert {
      padding: 15px;
      border-radius: 8px;
      margin-bottom: 20px;
    }

    .alert-success {
      background-color: #d4edda;
      color: #155724;
    }

    .alert-error {
      background-color: #f8d7da;
      color: #721c24;
    }

    @media (max-width: 768px) {
      .form-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="profile-header">
      <h1>My Profile</h1>
      <p>Complete your profile to help us provide better care</p>
    </div>

    <div class="profile-content">
      <?php if (isset($success)): ?>
        <div class="alert alert-success">
          <?php echo $success; ?>
        </div>
      <?php endif; ?>

      <?php if (isset($error)): ?>
        <div class="alert alert-error">
          <?php echo $error; ?>
        </div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-grid">
          <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>">
          </div>

          <div class="form-group">
            <label for="gender">Gender</label>
            <select id="gender" name="gender">
              <option value="">Select Gender</option>
              <option value="Male" <?php echo (isset($profile['gender']) && $profile['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
              <option value="Female" <?php echo (isset($profile['gender']) && $profile['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
              <option value="Other" <?php echo (isset($profile['gender']) && $profile['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
              <option value="Prefer not to say" <?php echo (isset($profile['gender']) && $profile['gender'] == 'Prefer not to say') ? 'selected' : ''; ?>>Prefer not to say</option>
            </select>
          </div>

          <div class="form-group">
            <label for="dob">Date of Birth</label>
            <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($profile['date_of_birth'] ?? ''); ?>">
          </div>

          <div class="form-group">
            <label for="blood_type">Blood Type</label>
            <select id="blood_type" name="blood_type">
              <option value="">Select Blood Type</option>
              <option value="A+" <?php echo (isset($profile['blood_type']) && $profile['blood_type'] == 'A+') ? 'selected' : ''; ?>>A+</option>
              <option value="A-" <?php echo (isset($profile['blood_type']) && $profile['blood_type'] == 'A-') ? 'selected' : ''; ?>>A-</option>
              <option value="B+" <?php echo (isset($profile['blood_type']) && $profile['blood_type'] == 'B+') ? 'selected' : ''; ?>>B+</option>
              <option value="B-" <?php echo (isset($profile['blood_type']) && $profile['blood_type'] == 'B-') ? 'selected' : ''; ?>>B-</option>
              <option value="AB+" <?php echo (isset($profile['blood_type']) && $profile['blood_type'] == 'AB+') ? 'selected' : ''; ?>>AB+</option>
              <option value="AB-" <?php echo (isset($profile['blood_type']) && $profile['blood_type'] == 'AB-') ? 'selected' : ''; ?>>AB-</option>
              <option value="O+" <?php echo (isset($profile['blood_type']) && $profile['blood_type'] == 'O+') ? 'selected' : ''; ?>>O+</option>
              <option value="O-" <?php echo (isset($profile['blood_type']) && $profile['blood_type'] == 'O-') ? 'selected' : ''; ?>>O-</option>
            </select>
          </div>

          <div class="form-group">
            <label for="height">Height (cm)</label>
            <input type="number" id="height" name="height" step="0.01" value="<?php echo htmlspecialchars($profile['height'] ?? ''); ?>">
          </div>

          <div class="form-group">
            <label for="weight">Weight (kg)</label>
            <input type="number" id="weight" name="weight" step="0.01" value="<?php echo htmlspecialchars($profile['weight'] ?? ''); ?>">
          </div>

          <div class="form-group full-width">
            <label for="address">Address</label>
            <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($profile['address'] ?? ''); ?>">
          </div>

          <div class="form-group">
            <label for="city">City</label>
            <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($profile['city'] ?? ''); ?>">
          </div>

          <div class="form-group">
            <label for="state">State</label>
            <input type="text" id="state" name="state" value="<?php echo htmlspecialchars($profile['state'] ?? ''); ?>">
          </div>

          <div class="form-group">
            <label for="postal_code">Postal Code</label>
            <input type="text" id="postal_code" name="postal_code" value="<?php echo htmlspecialchars($profile['postal_code'] ?? ''); ?>">
          </div>

          <div class="form-group">
            <label for="country">Country</label>
            <input type="text" id="country" name="country" value="<?php echo htmlspecialchars($profile['country'] ?? ''); ?>">
          </div>

          <div class="form-group full-width">
            <label for="medical_history">Medical History</label>
            <textarea id="medical_history" name="medical_history"><?php echo htmlspecialchars($profile['medical_history'] ?? ''); ?></textarea>
          </div>

          <div class="form-group full-width">
            <label for="allergies">Allergies</label>
            <textarea id="allergies" name="allergies"><?php echo htmlspecialchars($profile['allergies'] ?? ''); ?></textarea>
          </div>

          <div class="form-group full-width">
            <label for="current_medications">Current Medications</label>
            <textarea id="current_medications" name="current_medications"><?php echo htmlspecialchars($profile['current_medications'] ?? ''); ?></textarea>
          </div>

          <div class="form-group">
            <label for="emergency_contact_name">Emergency Contact Name</label>
            <input type="text" id="emergency_contact_name" name="emergency_contact_name" value="<?php echo htmlspecialchars($profile['emergency_contact_name'] ?? ''); ?>">
          </div>

          <div class="form-group">
            <label for="emergency_contact_phone">Emergency Contact Phone</label>
            <input type="text" id="emergency_contact_phone" name="emergency_contact_phone" value="<?php echo htmlspecialchars($profile['emergency_contact_phone'] ?? ''); ?>">
          </div>

          <div class="form-group">
            <label for="emergency_contact_relation">Emergency Contact Relation</label>
            <input type="text" id="emergency_contact_relation" name="emergency_contact_relation" value="<?php echo htmlspecialchars($profile['emergency_contact_relation'] ?? ''); ?>">
          </div>
        </div>

        <div class="form-group" style="text-align: center; margin-top: 30px;">
          <button type="submit" class="btn">
            <i class="fas fa-save"></i> Save Profile
          </button>
        </div>
      </form>
    </div>
  </div>
</body>
</html>