<?php

include 'db_config.php';

$conn = getDBConnection();
$mechanics = [];
$mechanics_query = "SELECT mechanic_id, mechanic_name FROM mechanics WHERE status = 'active' ORDER BY mechanic_name";
$result = $conn->query($mechanics_query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $mechanics[] = $row;
    }
}

$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Workshop - Book Appointment</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Car Workshop Appointment System</h1>
            <p>Book your appointment with our expert mechanics</p>
            <nav>
                <a href="index.php" class="active">Book Appointment</a>
                <a href="admin.php">Admin Panel</a>
            </nav>
        </header>

        <main>
            <div class="form-container">
                <h2>Book Your Appointment</h2>
                
                <?php if (isset($_GET['success'])): ?>
                    <div class="message success">
                        Appointment booked successfully!
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
                                $error_msg = 'Selected mechanic is fully booked for this date. Please choose another mechanic or date.';
                                break;
                            case 'invalid_data':
                                $error_msg = 'Please fill all fields correctly.';
                                break;
                            case 'db_error':
                                $error_msg = 'Database error occurred. Please try again.';
                                break;
                            default:
                                $error_msg = 'An error occurred. Please try again.';
                        }
                        echo $error_msg;
                        ?>
                    </div>
                <?php endif; ?>

                <form id="appointmentForm" action="book_appointment.php" method="POST" onsubmit="return validateForm()">
                    <div class="form-group">
                        <label for="client_name">Full Name *</label>
                        <input type="text" id="client_name" name="client_name" required>
                        <span class="error-msg" id="name_error"></span>
                    </div>

                    <div class="form-group">
                        <label for="client_address">Address *</label>
                        <textarea id="client_address" name="client_address" rows="3" required></textarea>
                        <span class="error-msg" id="address_error"></span>
                    </div>

                    <div class="form-group">
                        <label for="client_phone">Phone Number *</label>
                        <input type="text" id="client_phone" name="client_phone" placeholder="01XXXXXXXXX" required>
                        <span class="error-msg" id="phone_error"></span>
                    </div>

                    <div class="form-group">
                        <label for="car_license">Car License Number *</label>
                        <input type="text" id="car_license" name="car_license" placeholder="DHA-GA-1234" required>
                        <span class="error-msg" id="license_error"></span>
                    </div>

                    <div class="form-group">
                        <label for="car_engine">Car Engine Number *</label>
                        <input type="text" id="car_engine" name="car_engine" required>
                        <span class="error-msg" id="engine_error"></span>
                    </div>

                    <div class="form-group">
                        <label for="appointment_date">Appointment Date (dd-mm-yy) *</label>
                        <input type="date" id="appointment_date" name="appointment_date" 
                               min="<?php echo date('Y-m-d'); ?>" required>
                        <span class="error-msg" id="date_error"></span>
                    </div>

                    <div id="availability_display" style="display:none; margin: 1rem 0;">
                        <h3 style="margin-bottom: 0.75rem; font-size: 1rem; color: #2c3e50;">Select Your Mechanic</h3>
                        <div id="mechanics_availability"></div>
                        <input type="hidden" id="mechanic_id" name="mechanic_id" required>
                        <span class="error-msg" id="mechanic_error"></span>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Book Appointment</button>
                        <button type="reset" class="btn btn-secondary">Clear Form</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        function validateForm() {
            let isValid = true;
            
            document.querySelectorAll('.error-msg').forEach(el => el.textContent = '');

            const name = document.getElementById('client_name').value.trim();
            if (name === '') {
                document.getElementById('name_error').textContent = 'Name is required';
                isValid = false;
            } else if (name.length < 3) {
                document.getElementById('name_error').textContent = 'Name must be at least 3 characters';
                isValid = false;
            }

            const address = document.getElementById('client_address').value.trim();
            if (address === '') {
                document.getElementById('address_error').textContent = 'Address is required';
                isValid = false;
            }

            const phone = document.getElementById('client_phone').value.trim();
            const phonePattern = /^01[0-9]{9}$/;
            if (phone === '') {
                document.getElementById('phone_error').textContent = 'Phone number is required';
                isValid = false;
            } else if (!phonePattern.test(phone)) {
                document.getElementById('phone_error').textContent = 'Invalid phone format (must be 01XXXXXXXXX)';
                isValid = false;
            }

            const license = document.getElementById('car_license').value.trim();
            if (license === '') {
                document.getElementById('license_error').textContent = 'Car license number is required';
                isValid = false;
            }

            const engine = document.getElementById('car_engine').value.trim();
            if (engine === '') {
                document.getElementById('engine_error').textContent = 'Engine number is required';
                isValid = false;
            }

            const date = document.getElementById('appointment_date').value;
            if (date === '') {
                document.getElementById('date_error').textContent = 'Please select an appointment date';
                isValid = false;
            } else {
                const selectedDate = new Date(date);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                if (selectedDate < today) {
                    document.getElementById('date_error').textContent = 'Date cannot be in the past';
                    isValid = false;
                }
            }

            const mechanic = document.getElementById('mechanic_id').value;
            if (mechanic === '') {
                document.getElementById('mechanic_error').textContent = 'Please select a mechanic';
                isValid = false;
            }

            return isValid;
        }

        const mechanicsData = <?php echo json_encode($mechanics); ?>;
        let selectedMechanicId = null;

        document.getElementById('appointment_date').addEventListener('change', function() {
            checkAllMechanicsAvailability();
        });

        function checkAllMechanicsAvailability() {
            const date = document.getElementById('appointment_date').value;
            const displayDiv = document.getElementById('availability_display');
            const mechanicsDiv = document.getElementById('mechanics_availability');

            if (!date) {
                displayDiv.style.display = 'none';
                selectedMechanicId = null;
                document.getElementById('mechanic_id').value = '';
                return;
            }

            displayDiv.style.display = 'block';
            mechanicsDiv.innerHTML = '<p style="color: #6c757d;">Loading availability...</p>';

            Promise.all(
                mechanicsData.map(mechanic => 
                    fetch('check_availability.php?date=' + date + '&mechanic_id=' + mechanic.mechanic_id)
                        .then(response => response.json())
                        .then(data => ({
                            name: mechanic.mechanic_name,
                            id: mechanic.mechanic_id,
                            available: data.available,
                            booked: data.booked
                        }))
                )
            ).then(results => {
                let html = '<div style="display: grid; gap: 0.75rem;">';
                
                results.forEach(mechanic => {
                    const availableClass = mechanic.available > 0 ? 'success' : 'error';
                    const statusColor = mechanic.available > 0 ? '#28a745' : '#dc3545';
                    const isDisabled = mechanic.available === 0;
                    const cursorStyle = isDisabled ? 'not-allowed' : 'pointer';
                    const opacityStyle = isDisabled ? '0.6' : '1';
                    
                    html += `
                        <div class="mechanic-card" data-mechanic-id="${mechanic.id}" data-available="${mechanic.available}"
                             style="padding: 1rem; border: 2px solid #ddd; border-radius: 4px; background: white; 
                                    display: flex; justify-content: space-between; align-items: center; 
                                    cursor: ${cursorStyle}; transition: all 0.3s ease; opacity: ${opacityStyle};"
                             ${isDisabled ? '' : 'onclick="selectMechanic(' + mechanic.id + ', \'' + mechanic.name + '\')"'}>
                            <span style="font-weight: 500; font-size: 1rem;">${mechanic.name}</span>
                            <span style="color: ${statusColor}; font-size: 0.875rem; font-weight: 500;">
                                ${mechanic.available > 0 ? mechanic.available + ' slot(s) available' : 'Fully booked'}
                            </span>
                        </div>
                    `;
                });
                html += '</div>';
                mechanicsDiv.innerHTML = html;
            }).catch(error => {
                mechanicsDiv.innerHTML = '<p style="color: #dc3545;">Error loading availability</p>';
            });
        }

        function selectMechanic(mechanicId, mechanicName) {
            const cards = document.querySelectorAll('.mechanic-card');
            cards.forEach(card => {
                card.style.border = '2px solid #ddd';
                card.style.background = 'white';
            });
            
            const selectedCard = document.querySelector(`[data-mechanic-id="${mechanicId}"]`);
            if (selectedCard && selectedCard.dataset.available > 0) {
                selectedCard.style.border = '2px solid #2c3e50';
                selectedCard.style.background = '#f0f8ff';
                selectedMechanicId = mechanicId;
                document.getElementById('mechanic_id').value = mechanicId;
                document.getElementById('mechanic_error').textContent = '';
            }
        }

        document.getElementById('appointmentForm').addEventListener('reset', function() {
            document.querySelectorAll('.error-msg').forEach(el => el.textContent = '');
            document.getElementById('availability_display').style.display = 'none';
            selectedMechanicId = null;
            document.getElementById('mechanic_id').value = '';
        });
    </script>
</body>
</html>
