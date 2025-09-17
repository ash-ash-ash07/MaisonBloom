<?php
session_start();
include "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    header("Location: ../login.php");
    exit;
}

$doctor_id = $_SESSION['user_id'];
$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
$doctor_profile_query = "SELECT doctor_id FROM doctor_profiles WHERE user_id = $doctor_id";
$doctor_profile_result = $conn->query($doctor_profile_query);

if (!$doctor_profile_result) {
    die("Database error: " . $conn->error);
}

$doctor_profile = $doctor_profile_result->fetch_assoc();

if (!$doctor_profile) {
    die("Doctor profile not found.");
}

$doctor_profile_id = $doctor_profile['doctor_id'];
// Get booking details
$booking_query = "SELECT b.*, u.name AS patient_name, u.email AS patient_email, 
                  u.phone_number AS patient_phone, c.date_time, cl.meeting_url, cl.meeting_id
                  FROM bookings b
                  JOIN users u ON b.patient_id = u.user_id
                  JOIN consultation_slots c ON b.slot_id = c.slot_id
                  LEFT JOIN consultation_links cl ON b.booking_id = cl.appointment_id
                  WHERE b.booking_id = $booking_id AND c.doctor_id = $doctor_profile_id";

$booking_result = $conn->query($booking_query);

if (!$booking_result) {
    die("Database error: " . $conn->error);
}

$booking = $booking_result->fetch_assoc();

if (!$booking) {
    die("Booking not found or you don't have permission to access it.");
}


// Handle starting consultation
// Handle starting consultation
if (isset($_POST['start_consultation'])) {
    // Generate Google Meet link (in a real app, you'd call Google Meet API here)
    $meeting_id = substr(md5(time() . $booking_id), 0, 12);
    $meeting_url = "https://meet.google.com/" . $meeting_id;
    $meeting_password = substr(md5(time()), 0, 8);
    
    // First check if a link already exists for this appointment
    $check_query = "SELECT * FROM consultation_links WHERE appointment_id = $booking_id";
    $check_result = $conn->query($check_query);
    
    if ($check_result->num_rows > 0) {
        // Update existing record
        $stmt = $conn->prepare("UPDATE consultation_links SET meeting_url = ?, meeting_password = ?, meeting_id = ? WHERE appointment_id = ?");
        $stmt->bind_param("sssi", $meeting_url, $meeting_password, $meeting_id, $booking_id);
    } else {
        // Insert new record
        $stmt = $conn->prepare("INSERT INTO consultation_links (appointment_id, meeting_url, meeting_password, meeting_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $booking_id, $meeting_url, $meeting_password, $meeting_id);
    }
    
    if ($stmt->execute()) {
        // Update booking status
        $conn->query("UPDATE bookings SET status = 'in_progress' WHERE booking_id = $booking_id");
        header("Location: doctor_consultations.php?booking_id=$booking_id");
        exit;
    } else {
        die("Error creating meeting: " . $conn->error);
    }
}

// Handle prescription submission
if (isset($_POST['submit_prescription'])) {
    $diagnosis = $conn->real_escape_string($_POST['diagnosis']);
    $treatment_plan = $conn->real_escape_string($_POST['treatment_plan']);
    $prescribed_products = $conn->real_escape_string($_POST['prescribed_products']);
    $notes = $conn->real_escape_string($_POST['notes']);
    
    $stmt = $conn->prepare("INSERT INTO prescriptions (booking_id, doctor_id, patient_id, diagnosis, treatment_plan, prescribed_products, notes) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiissss", $booking_id, $doctor_profile_id, $booking['patient_id'], $diagnosis, $treatment_plan, $prescribed_products, $notes);
    
    if ($stmt->execute()) {
        // Update booking status
        $conn->query("UPDATE bookings SET status = 'completed' WHERE booking_id = $booking_id");
        header("Location: doctor_dashboard.php?success=prescription_saved");
        exit;
    } else {
        die("Error saving prescription: " . $conn->error);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Consultation - Maison Bloom</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #6c5ce7;
            --primary-light: #a29bfe;
            --primary-dark: #5649c9;
            --accent-color: #fd79a8;
            --light-bg: #f8f9ff;
            --card-bg: #ffffff;
            --text-dark: #2d3436;
            --text-light: #636e72;
            --border-radius: 12px;
            --box-shadow: 0 8px 20px rgba(108, 92, 231, 0.15);
        }
        
        body {
            background-color: var(--light-bg);
            color: var(--text-dark);
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        h1 {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 30px;
            font-weight: 600;
            font-size: 2.2rem;
        }
        
        h2 {
            color: var(--primary-dark);
            margin-top: 0;
            margin-bottom: 20px;
            font-weight: 500;
            font-size: 1.5rem;
            border-bottom: 2px solid var(--primary-light);
            padding-bottom: 10px;
        }
        
        .patient-info {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: var(--box-shadow);
            border-left: 4px solid var(--primary-color);
        }
        
        .patient-info p {
            margin: 10px 0;
            display: flex;
        }
        
        .patient-info strong {
            min-width: 120px;
            color: var(--primary-dark);
        }
        
        .consultation-container {
            display: flex;
            gap: 30px;
            margin-top: 20px;
        }
        
        .video-container, .prescription-container {
            flex: 1;
            background: var(--card-bg);
            padding: 25px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }
        
        .meeting-frame {
            width: 100%;
            height: 380px;
            border: 2px dashed var(--primary-light);
            border-radius: var(--border-radius);
            background: var(--light-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .meeting-frame:hover {
            border-color: var(--primary-color);
            transform: translateY(-5px);
        }
        
        .meeting-frame i {
            color: var(--primary-color);
            margin-bottom: 15px;
            font-size: 3rem;
        }
        
        .meeting-actions {
            margin-top: 20px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            gap: 8px;
        }
        
        .btn-primary {
            background: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108, 92, 231, 0.3);
        }
        
        .btn-secondary {
            background: var(--primary-light);
            color: white;
        }
        
        .btn-secondary:hover {
            background: var(--primary-color);
            transform: translateY(-2px);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--primary-dark);
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--primary-light);
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: var(--light-bg);
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.2);
        }
        
        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }
        
        @media (max-width: 900px) {
            .consultation-container {
                flex-direction: column;
            }
            
            .patient-info p {
                flex-direction: column;
            }
            
            .patient-info strong {
                min-width: auto;
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>

   
    <div class="container">
        <h1>Consultation Session</h1>
        
        <div class="patient-info">
            <h2>Patient Information</h2>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($booking['patient_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($booking['patient_email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($booking['patient_phone']); ?></p>
            <p><strong>Appointment Time:</strong> <?php echo date("F j, Y, g:i a", strtotime($booking['date_time'])); ?></p>
        </div>

        <div class="consultation-container">
            <div class="video-container">
                <?php if (empty($booking['meeting_url'])): ?>
                    <form method="POST">
                        <button type="submit" name="start_consultation" class="btn btn-primary">
                            <i class="fas fa-video"></i> Start Video Consultation
                        </button>
                    </form>
                <?php else: ?>
                    <h2>Video Consultation</h2>
                    <div class="meeting-frame">
                        <i class="fas fa-video"></i>
                        <p>Google Meet session is ready</p>
                        <a href="<?php echo htmlspecialchars($booking['meeting_url']); ?>" target="_blank" class="btn btn-primary" style="margin-top: 15px;">
                            <i class="fas fa-external-link-alt"></i> Open Google Meet
                        </a>
                    </div>
                    <div class="meeting-actions">
                        <a href="<?php echo htmlspecialchars($booking['meeting_url']); ?>" target="_blank" class="btn btn-primary">
                            <i class="fas fa-video"></i> Join Consultation
                        </a>
                        <button onclick="copyMeetingLink()" class="btn btn-secondary">
                            <i class="fas fa-copy"></i> Copy Link
                        </button>
                    </div>
                    <p style="margin-top: 10px; color: var(--text-light);">Meeting ID: <?php echo htmlspecialchars($booking['meeting_id']); ?></p>
                <?php endif; ?>
            </div>

            <?php if (!empty($booking['meeting_url'])): ?>
            <div class="prescription-container">
                <div class="prescription-form">
                    <h2>Create Prescription</h2>
                    <form method="POST">
                        <div class="form-group">
                            <label for="diagnosis">Diagnosis:</label>
                            <textarea id="diagnosis" name="diagnosis" required class="form-control"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="treatment_plan">Treatment Plan:</label>
                            <textarea id="treatment_plan" name="treatment_plan" required class="form-control"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="prescribed_products">Recommended Products:</label>
                            <textarea id="prescribed_products" name="prescribed_products" class="form-control"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="notes">Additional Notes:</label>
                            <textarea id="notes" name="notes" class="form-control"></textarea>
                        </div>
                        
                        <button type="submit" name="submit_prescription" class="btn btn-primary">
                            <i class="fas fa-file-prescription"></i> Submit Prescription
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>  

    <script>
        function copyMeetingLink() {
            const meetingUrl = "<?php echo htmlspecialchars($booking['meeting_url']); ?>";
            navigator.clipboard.writeText(meetingUrl).then(() => {
                alert('Meeting link copied to clipboard!');
            });
        }
    </script>
</body>
</html>