<?php
include "../db.php";
session_start();

$patient_id = $_GET['patient_id'] ?? null;

if (!$patient_id) {
    echo "Patient ID is required.";
    exit;
}

$prescriptions = $conn->query("SELECT * FROM prescriptions WHERE patient_id = $patient_id ORDER BY issued_at DESC");
?>

<h2>Prescriptions</h2>
<table border="1">
  <tr>
    <th>Prescription ID</th>
    <th>Doctor</th>
    <th>Issued Date</th>
    <th>Notes</th>
  </tr>
  <?php while ($row = $prescriptions->fetch_assoc()): ?>
  <tr>
    <td><?= $row['prescription_id'] ?></td>
    <td><?= $row['doctor_name'] ?></td>
    <td><?= $row['issued_at'] ?></td>
    <td><?= $row['notes'] ?></td>
  </tr>
  <?php endwhile; ?>
</table>
