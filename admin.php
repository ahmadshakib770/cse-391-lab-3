<?php

include 'db_config.php';

$conn = getDBConnection();
$appointments = [];

$query = "SELECT 
            a.appointment_id,
            a.client_name,
            a.client_phone,
            a.car_license,
            a.appointment_date,
            a.status,
            m.mechanic_name
          FROM appointments a
          INNER JOIN mechanics m ON a.mechanic_id = m.mechanic_id
          ORDER BY a.appointment_date DESC, a.created_at DESC";

$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
}

$mechanics = [];
$mechanics_query = "SELECT mechanic_id, mechanic_name FROM mechanics WHERE status = 'active' ORDER BY mechanic_name";
$mechanics_result = $conn->query($mechanics_query);

if ($mechanics_result && $mechanics_result->num_rows > 0) {
    while ($row = $mechanics_result->fetch_assoc()) {
        $mechanics[] = $row;
    }
}

closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Car Workshop</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Admin Panel</h1>
            <p>Manage appointments and mechanics</p>
            <nav>
                <a href="index.php">Book Appointment</a>
                <a href="admin.php" class="active">Admin Panel</a>
            </nav>
        </header>

        <main>
            <div class="admin-container">
                <div class="admin-header">
                    <h2>Appointment List</h2>
                    <div class="admin-stats">
                        <div class="stat-card">
                            <span class="stat-number"><?php echo count($appointments); ?></span>
                            <span class="stat-label">Total Appointments</span>
                        </div>
                        <div class="stat-card">
                            <span class="stat-number"><?php echo count($mechanics); ?></span>
                            <span class="stat-label">Active Mechanics</span>
                        </div>
                    </div>
                </div>

                
                <?php if (isset($_GET['success'])): ?>
                    <div class="message success">
                        Operation completed successfully!
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['error'])): ?>
                    <div class="message error">
                        An error occurred. Please try again.
                    </div>
                <?php endif; ?>

                <?php if (empty($appointments)): ?>
                    <div class="empty-state">
                        <p>No appointments found.</p>
                        <a href="index.php" class="btn btn-primary">Book First Appointment</a>
                    </div>
                <?php else: ?>
                    <div class="table-container">
                        <table class="appointments-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Client Name</th>
                                    <th>Phone</th>
                                    <th>Car License</th>
                                    <th>Appointment Date</th>
                                    <th>Mechanic</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($appointments as $appointment): ?>
                                    <tr>
                                        <td><?php echo $appointment['appointment_id']; ?></td>
                                        <td><?php echo htmlspecialchars($appointment['client_name']); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['client_phone']); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['car_license']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['mechanic_name']); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $appointment['status']; ?>">
                                                <?php echo ucfirst($appointment['status']); ?>
                                            </span>
                                        </td>
                                        <td class="actions">
                                            <button class="btn-edit" onclick="editAppointment(<?php echo $appointment['appointment_id']; ?>)" title="Edit">
                                                Edit
                                            </button>
                                            <button class="btn-delete" onclick="deleteAppointment(<?php echo $appointment['appointment_id']; ?>)" title="Delete">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div id="editModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeModal()">&times;</span>
                    <h2>Edit Appointment</h2>
                    <form id="editForm" action="admin_actions.php" method="POST">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="appointment_id" id="edit_appointment_id">

                        <div class="form-group">
                            <label for="edit_date">Appointment Date</label>
                            <input type="date" id="edit_date" name="appointment_date" required>
                        </div>

                        <div class="form-group">
                            <label for="edit_mechanic">Mechanic</label>
                            <select id="edit_mechanic" name="mechanic_id" required>
                                <?php foreach ($mechanics as $mechanic): ?>
                                    <option value="<?php echo $mechanic['mechanic_id']; ?>">
                                        <?php echo htmlspecialchars($mechanic['mechanic_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="edit_status">Status</label>
                            <select id="edit_status" name="status" required>
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                            <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>

        </main>
    </div>

    <script>
        function editAppointment(id) {
        }

        function deleteAppointment(id) {
            if (confirm('Are you sure you want to delete this appointment?')) {
            }
        }

        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
