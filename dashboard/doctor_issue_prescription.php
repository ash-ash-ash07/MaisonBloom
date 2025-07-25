<?php
session_start();
include('../db.php');
include('../zoom_config.php'); // Ensure this file exists and contains Zoom credentials

// Redirect if not logged in as doctor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../login.php");
    exit;
}

$doctor_id = $_SESSION['user_id']; // Use user_id for consistency if doctor_id is stored there.
                                 // If doctor_id is a separate field, ensure it's set in session.

// Handle prescription submission (existing logic from your file)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['consultation_id'], $_POST['prescription'])) {
    $consultation_id = intval($_POST['consultation_id']);
    $prescription = mysqli_real_escape_string($conn, $_POST['prescription']);

    $consultation = mysqli_query($conn, "SELECT * FROM consultations WHERE id = $consultation_id");
    if (!$consultation || mysqli_num_rows($consultation) === 0) {
        die("Invalid consultation ID.");
    }

    $insert = "INSERT INTO prescriptions (consultation_id, doctor_id, content, issued_at)
               VALUES ($consultation_id, $doctor_id, '$prescription', NOW())";

    if (mysqli_query($conn, $insert)) {
        header("Location: doctor_issue_prescription.php?msg=prescribed"); // Redirect back to this page
        exit;
    } else {
        echo "Error inserting prescription: " . mysqli_error($conn);
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])) {
    // Existing logic for creating Zoom meetings if you want to keep it on this page
    // ... (Your Zoom creation logic here)
    $booking_id = intval($_POST['booking_id']);

    $zoomConfig = include('../zoom_config.php');
    // Ensure $zoomConfig is not false before accessing its keys
    if ($zoomConfig) {
        $accessToken = getZoomAccessToken($zoomConfig['client_id'], $zoomConfig['client_secret'], $zoomConfig['account_id']);
        $zoom_link = createZoomMeeting($accessToken, "Consultation for Booking ID $booking_id");

        if (!$zoom_link) {
            die("Failed to create Zoom meeting.");
        }

        $insertConsultation = "INSERT INTO consultations (booking_id, zoom_link, status)
                               VALUES ($booking_id, '$zoom_link', 'scheduled')";

        if (mysqli_query($conn, $insertConsultation)) {
            header("Location: doctor_issue_prescription.php?msg=consultation_created"); // Redirect back
            exit;
        } else {
            echo "Error creating consultation: " . mysqli_error($conn);
        }
    } else {
        echo "Error: Zoom configuration not found.";
    }
}

// Fetch prescriptions and related consultation/patient data for display
$query = "SELECT b.booking_id, u.name AS patient_name, u.email,
                 c.id AS consultation_id, c.zoom_link, c.status,
                 p.content AS prescription_content, p.issued_at AS prescription_issued_at
          FROM bookings b
          JOIN consultation_slots s ON b.slot_id = s.slot_id
          JOIN users u ON b.patient_id = u.user_id
          LEFT JOIN consultations c ON b.booking_id = c.booking_id
          LEFT JOIN prescriptions p ON c.id = p.consultation_id
          WHERE s.doctor_id = $doctor_id
          ORDER BY b.booking_id DESC";

$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Issue Prescription - Maison Bloom</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        /* Add any specific styles for this page here, or include the main CSS */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7f6; /* A light background color */
        }
        .container {
            margin-top: 30px;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .section-title {
            font-size: 1.8rem;
            color: #4834d4;
            margin-bottom: 25px;
            border-bottom: 2px solid #6c5ce7;
            padding-bottom: 10px;
        }
        .table-responsive {
            margin-top: 20px;
        }
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
        }

        .status-badge.approved {
            background-color: #d4edda;
            color: #155724;
        }

        .status-badge.pending {
            background-color: #fff3cd;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="section-title">Issued Prescriptions and Consultations</h2>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success" role="alert">
                <?php
                    if ($_GET['msg'] === 'prescribed') {
                        echo "Prescription issued successfully!";
                    } elseif ($_GET['msg'] === 'consultation_created') {
                        echo "Consultation link created successfully!";
                    }
                ?>
            </div>
        <?php endif; ?>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="thead-light">
                        <tr>
                            <th>Patient</th>
                            <th>Email</th>
                            <th>Consultation Link</th>
                            <th>Prescription</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['patient_name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td>
                                <?php if (!empty($row['zoom_link'])): ?>
                                    <a href="<?= htmlspecialchars($row['zoom_link']) ?>" class="btn btn-sm btn-info" target="_blank">Join Call</a>
                                <?php else: ?>
                                    <span class="text-muted">No link yet</span>
                                    <?php if (!empty($row['booking_id'])): ?>
                                        <form action="" method="POST" style="display:inline;">
                                            <input type="hidden" name="booking_id" value="<?= $row['booking_id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-primary ml-2">Create Consultation</button>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= !empty($row['prescription_content'])
                                    ? nl2br(htmlspecialchars($row['prescription_content']))
                                    : '<span class="text-muted">Not issued</span>' ?>
                            </td>
                             <td><span class="status-badge <?= htmlspecialchars($row['status']) ?>"><?= ucfirst(htmlspecialchars($row['status'])) ?></span></td>
                            <td>
                                <?php if (empty($row['prescription_content']) && !empty($row['consultation_id'])): ?>
                                    <form action="" method="POST">
                                        <input type="hidden" name="consultation_id" value="<?= $row['consultation_id'] ?>">
                                        <textarea name="prescription" rows="3" class="form-control mb-2" placeholder="Enter prescription..." required></textarea>
                                        <button type="submit" class="btn btn-success btn-sm">Issue Prescription</button>
                                    </form>
                                <?php elseif (!empty($row['prescription_content'])): ?>
                                    <span class="text-success"><i class="fas fa-check-circle"></i> Issued</span>
                                <?php else: ?>
                                     <span class="text-muted">Consultation needed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="alert alert-info">No bookings or consultations available for you.</p>
        <?php endif; ?>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>