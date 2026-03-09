<?php

include 'db_config.php';

$conn = getDBConnection();

$mechanics_query = "SELECT mechanic_id, mechanic_name, max_clients, status FROM mechanics ORDER BY mechanic_name";
$mechanics_result = $conn->query($mechanics_query);

$mechanics = [];
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
    <title>Manage Mechanics - Car Workshop</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Manage Mechanics</h1>
            <p>Update mechanic slots and status</p>
            <nav>
                <a href="index.php">Book Appointment</a>
                <a href="admin.php">Admin Panel</a>
                <a href="manage_mechanics.php" class="active">Manage Mechanics</a>
            </nav>
        </header>

        <main>
            <div class="admin-container">
                <div class="admin-header">
                    <h2>Mechanics List</h2>
                </div>

                <?php if (isset($_GET['success'])): ?>
                    <div class="message success">
                        <?php
                        $success_msg = '';
                        if ($_GET['success'] === 'updated') $success_msg = 'Mechanic updated successfully!';
                        echo $success_msg;
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['error'])): ?>
                    <div class="message error">
                        <?php
                        $error_msg = '';
                        if ($_GET['error'] === 'invalid_data') $error_msg = 'Invalid data provided';
                        if ($_GET['error'] === 'update_failed') $error_msg = 'Failed to update mechanic';
                        if ($_GET['error'] === 'mechanic_not_found') $error_msg = 'Mechanic not found';
                        echo $error_msg;
                        ?>
                    </div>
                <?php endif; ?>

                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Mechanic Name</th>
                                <th>Max Slots</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($mechanics)): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; color: #7f8c8d;">No mechanics found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($mechanics as $mechanic): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($mechanic['mechanic_id']); ?></td>
                                        <td><?php echo htmlspecialchars($mechanic['mechanic_name']); ?></td>
                                        <td><?php echo htmlspecialchars($mechanic['max_clients']); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $mechanic['status']; ?>">
                                                <?php echo ucfirst(htmlspecialchars($mechanic['status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-primary btn-small" onclick="editMechanic(<?php echo $mechanic['mechanic_id']; ?>, '<?php echo htmlspecialchars($mechanic['mechanic_name']); ?>', <?php echo $mechanic['max_clients']; ?>, '<?php echo $mechanic['status']; ?>')">Edit</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div id="editMechanicModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>Edit Mechanic</h2>
            
            <form method="POST" action="mechanic_actions.php">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="mechanic_id" id="edit_mechanic_id">

                <div class="form-group">
                    <label for="edit_mechanic_name">Mechanic Name</label>
                    <input type="text" id="edit_mechanic_name" name="mechanic_name" required readonly style="background-color: #f5f5f5;">
                </div>

                <div class="form-group">
                    <label for="edit_max_clients">Max Slots Per Day</label>
                    <input type="number" id="edit_max_clients" name="max_clients" min="1" max="20" required>
                </div>

                <div class="form-group">
                    <label for="edit_status">Status</label>
                    <select id="edit_status" name="status" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="button" onclick="closeEditModal()" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Mechanic</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editMechanic(id, name, maxClients, status) {
            document.getElementById('edit_mechanic_id').value = id;
            document.getElementById('edit_mechanic_name').value = name;
            document.getElementById('edit_max_clients').value = maxClients;
            document.getElementById('edit_status').value = status;
            document.getElementById('editMechanicModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editMechanicModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('editMechanicModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
