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
            a.mechanic_id,
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
                <a href="manage_mechanics.php">Manage Mechanics</a>
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
                        <?php
                        $success_msg = '';
                        switch ($_GET['success']) {
                            case 'updated':
                                $success_msg = 'Appointment updated successfully!';
                                break;
                            case 'deleted':
                                $success_msg = 'Appointment deleted successfully!';
                                break;
                            default:
                                $success_msg = 'Operation completed successfully!';
                        }
                        echo $success_msg;
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['error'])): ?>
                    <div class="message error">
                        <?php
                        $error_msg = '';
                        switch ($_GET['error']) {
                            case 'duplicate_car':
                                $error_msg = 'This car already has an appointment on the selected date.';
                                break;
                            case 'mechanic_full':
                                $error_msg = 'Selected mechanic is fully booked for this date.';
                                break;
                            case 'invalid_data':
                                $error_msg = 'Invalid data provided.';
                                break;
                            case 'invalid_mechanic':
                                $error_msg = 'Selected mechanic is not available.';
                                break;
                            case 'appointment_not_found':
                                $error_msg = 'Appointment not found.';
                                break;
                            default:
                                $error_msg = 'An error occurred. Please try again.';
                        }
                        echo $error_msg;
                        ?>
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
                                        <td><?php echo date('d-m-y', strtotime($appointment['appointment_date'])); ?></td>
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
                            <label for="edit_date">Appointment Date (dd-mm-yy)</label>
                            <input type="date" id="edit_date" name="appointment_date" min="<?php echo date('Y-m-d'); ?>" required>
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
                            <div id="edit_availability_info" class="info-msg" style="margin-top: 0.5rem;"></div>
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
        const appointmentsData = <?php echo json_encode($appointments); ?>;
        const mechanicsData = <?php echo json_encode($mechanics); ?>;
        let currentEditingId = null;

        function editAppointment(id) {
            const appointment = appointmentsData.find(a => a.appointment_id == id);
            if (!appointment) return;
            
            currentEditingId = id;
            document.getElementById('edit_appointment_id').value = appointment.appointment_id;
            document.getElementById('edit_date').value = appointment.appointment_date;
            document.getElementById('edit_mechanic').value = appointment.mechanic_id;
            document.getElementById('edit_status').value = appointment.status;
            
            document.getElementById('editModal').style.display = 'block';
            checkEditAvailability();
        }

        document.getElementById('edit_date').addEventListener('change', checkEditAvailability);
        document.getElementById('edit_mechanic').addEventListener('change', checkEditAvailability);

        function checkEditAvailability() {
            const date = document.getElementById('edit_date').value;
            const mechanicId = document.getElementById('edit_mechanic').value;
            const infoDiv = document.getElementById('edit_availability_info');

            if (!date || !mechanicId) {
                infoDiv.textContent = '';
                return;
            }

            fetch(`check_availability.php?date=${date}&mechanic_id=${mechanicId}`)
                .then(response => response.json())
                .then(data => {
                    const slotsAvailable = data.available;
                    
                    if (slotsAvailable > 0) {
                        infoDiv.className = 'info-msg success';
                        infoDiv.textContent = slotsAvailable + ' slot(s) available';
                    } else {
                        infoDiv.className = 'info-msg error';
                        infoDiv.textContent = 'Mechanic is fully booked for this date';
                    }
                })
                .catch(error => {
                    infoDiv.textContent = '';
                });
        }

        function deleteAppointment(id) {
            if (confirm('Are you sure you want to delete this appointment?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'admin_actions.php';
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete';
                
                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'appointment_id';
                idInput.value = id;
                
                form.appendChild(actionInput);
                form.appendChild(idInput);
                document.body.appendChild(form);
                form.submit();
            }
        }

        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
            document.getElementById('edit_availability_info').textContent = '';
            currentEditingId = null;
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
