<?php
// Database configuration - UPDATE THESE WITH YOUR DATABASE DETAILS
$host = 'localhost';
$dbname = 'bawasla 2.0';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Initial Data Entry - Water Billing System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            margin: 20px 0;
        }

        .header-section {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 30px;
            border-radius: 20px 20px 0 0;
            text-align: center;
        }

        .form-section {
            padding: 40px;
        }

        .form-control,
        .form-select {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
            transform: translateY(-2px);
        }

        .btn-primary {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(52, 152, 219, 0.3);
        }

        .btn-success {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(46, 204, 113, 0.3);
        }

        .alert {
            border: none;
            border-radius: 10px;
            padding: 15px 20px;
        }

        .table-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-top: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .table {
            border-radius: 10px;
            overflow: hidden;
        }

        .table thead th {
            background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%);
            color: white;
            border: none;
            padding: 15px;
            font-weight: 600;
        }

        .table tbody td {
            padding: 12px 15px;
            vertical-align: middle;
        }

        .btn-sm {
            border-radius: 6px;
            padding: 6px 12px;
        }

        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .progress {
            height: 8px;
            border-radius: 4px;
            background-color: #e9ecef;
        }

        .progress-bar {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        }

        .loading {
            opacity: 0.6;
            pointer-events: none;
        }

        .edit-mode {
            background-color: #fff3cd !important;
            border-left: 4px solid #ffc107 !important;
        }

        .cancel-edit-btn {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            border: none;
            color: white;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-10">
                <div class="main-container">
                    <div class="header-section">
                        <h1><i class="fas fa-database me-3"></i>Initial Data Entry System</h1>
                        <p class="mb-0">Set up your existing member data, current arrears, and meter readings</p>
                    </div>

                    <div class="form-section">
                        <!-- Alert Messages -->
                        <div id="alertContainer"></div>

                        <!-- Member Entry Form -->
                        <div class="card mb-4" id="memberCard">
                            <div class="card-header bg-primary text-white" id="cardHeader">
                                <h4 class="mb-0"><i class="fas fa-user-plus me-2"></i>Add Member with Current Data</h4>
                            </div>
                            <div class="card-body">
                                <form id="memberForm">
                                    <div class="row">
                                        <!-- Member Information -->
                                        <input type="hidden" id="memberId" name="memberId" value="">
                                        <input type="hidden" id="formAction" name="action" value="add">

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="lastName" class="form-label">Last Name</label>
                                                <input type="text" class="form-control" id="lastName" name="lastName"
                                                    autocomplete="off" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="firstName" class="form-label">First Name</label>
                                                <input type="text" class="form-control" id="firstName" name="firstName"
                                                    required autocomplete="off">
                                            </div>
                                            <div class="mb-3">
                                                <label for="middleName" class="form-label">Middle Name</label>
                                                <input type="text" class="form-control" id="middleName"
                                                    name="middleName" autocomplete="off">
                                            </div>

                                        </div>

                                        <!-- System Information -->
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="address" class="form-label">Address</label>
                                                <select class="form-select" id="address" name="address"
                                                    onchange="updateTankNumber(this)" required>
                                                    <option value="" selected disabled>Select Address</option>
                                                    <option value="Mainuswagon">Mainuswagon</option>
                                                    <option value="Mabuhay">Mabuhay</option>
                                                    <option value="Malipayon">Malipayon</option>
                                                    <option value="Malipayon Extension">Malipayon Extension</option>
                                                    <option value="Riverside">Riverside</option>
                                                    <option value="Riverside Extension">Riverside Extension</option>
                                                    <option value="Bibiana">Bibiana</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="tankNo" class="form-label">Tank Number</label>
                                                <input type="number" class="form-control" id="tankNo" name="tankNo"
                                                    placeholder="Tank No." required autocomplete="off" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <!-- Current Financial Status -->
                                        <div class="col-md-6">
                                            <h5 class="text-warning mb-3">Current Financial Status</h5>
                                            <div class="mb-3">
                                                <label for="currentArrears" class="form-label">Current Arrears
                                                    Amount</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">₱</span>
                                                    <input type="number" step="0.01" class="form-control"
                                                        id="currentArrears" name="currentArrears" value="0.00">
                                                </div>
                                                <small class="form-text text-muted">Enter any existing unpaid
                                                    balance</small>

                                                <!-- <input type="hidden" step="0.01" class="form-control"
                                                    id="total_amount_due" name="total_amount_due" value="0.00"> -->
                                            </div>
                                        </div>

                                        <!-- Current Meter Reading -->
                                        <div class="col-md-6">
                                            <h5 class="text-info mb-3">Current Meter Reading</h5>
                                            <div class="mb-3">
                                                <label for="currentReading" class="form-label">Current Meter
                                                    Reading</label>
                                                <input type="number" class="form-control" id="currentReading"
                                                    name="currentReading" value="0" required>
                                                <small class="form-text text-muted">Enter the current meter reading
                                                    (cubic meters)</small>
                                            </div>
                                            <div class="mb-3">
                                                <label for="lastBillingMonth" class="form-label">Last Billing
                                                    Month</label>
                                                <input type="text" class="form-control" id="lastBillingMonth"
                                                    name="lastBillingMonth" placeholder="e.g., July 2025"
                                                    autocomplete="off">
                                                <small class="form-text text-muted">Leave blank if no previous
                                                    billing</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                            <i class="fas fa-save me-2"></i>Add Member Data
                                        </button>
                                        <button type="button" class="btn btn-secondary btn-lg ms-2"
                                            onclick="clearForm()">
                                            <i class="fas fa-undo me-2"></i>Clear Form
                                        </button>
                                        <button type="button" class="btn cancel-edit-btn btn-lg ms-2" id="cancelEditBtn"
                                            onclick="cancelEdit()" style="display: none;">
                                            <i class="fas fa-times me-2"></i>Cancel Edit
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Progress Section -->
                        <div class="card mb-4">
                            <div class="card-body text-center">
                                <h5>Data Entry Progress</h5>
                                <div class="progress mb-3">
                                    <div class="progress-bar" role="progressbar" style="width: 0%" id="progressBar">0%
                                    </div>
                                </div>
                                <p class="mb-0">Members Added: <span id="memberCount">0</span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Members Table -->
                <div class="table-section">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3><i class="fas fa-users me-2"></i>Recent Members</h3>
                        <input type="text" id="searchInput" placeholder="Search" onkeyup="filterMembers()">

                        <button class="btn btn-info" onclick="loadMembers()">
                            <i class="fas fa-refresh me-2"></i>Refresh List
                        </button>

                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover" id="membersTable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Address</th>
                                    <th>Tank #</th>
                                    <th>Current Reading</th>
                                    <th>Arrears</th>
                                    <th>Last Billing</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="membersTableBody">
                                <!-- Members will be loaded here dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        let memberCount = 0;
        let isEditMode = false;

        document.getElementById('memberForm').addEventListener('submit', function (e) {
            e.preventDefault();
            if (isEditMode) {
                updateMember();
            } else {
                addMember();
            }
        });

        function addMember() {
            const form = document.getElementById('memberForm');
            const formData = new FormData(form);
            const submitBtn = document.getElementById('submitBtn');

            // Show loading state
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
            submitBtn.disabled = true;

            fetch('save_member.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        clearForm();
                        loadMembers();
                        updateMemberCount();
                        // Redirect to payment page to allow immediate payment
                        setTimeout(function(){ window.location.href = 'manage_transaction.php'; }, 600);
                    } else {
                        showAlert(data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('An error occurred while saving the member.', 'danger');
                })
                .finally(() => {
                    submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Add Member Data';
                    submitBtn.disabled = false;
                });
        }

        function updateMember() {
            const form = document.getElementById('memberForm');
            const formData = new FormData(form);
            const submitBtn = document.getElementById('submitBtn');

            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';
            submitBtn.disabled = true;

            fetch('save_member.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        cancelEdit();
                        loadMembers();
                    } else {
                        showAlert(data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('An error occurred while updating the member.', 'danger');
                })
                .finally(() => {
                    submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Update Member Data';
                    submitBtn.disabled = false;
                });
        }

        function editMember(memberId) {
            fetch(`save_member.php?action=getMember&memberId=${memberId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.member) {
                        const member = data.member;

                        // Populate form fields
                        document.getElementById('memberId').value = member.member_id;
                        document.getElementById('lastName').value = member.last_name;
                        document.getElementById('firstName').value = member.first_name;
                        document.getElementById('middleName').value = member.middle_name || '';
                        document.getElementById('address').value = member.address;
                        document.getElementById('tankNo').value = member.tank_no;
                        document.getElementById('currentArrears').value = member.arrears_amount || '0.00';
                        document.getElementById('currentReading').value = member.current_reading || '0';
                        document.getElementById('lastBillingMonth').value = member.last_billing_month || '';
                        document.getElementById('formAction').value = 'update';

                        // Switch to edit mode
                        isEditMode = true;
                        const memberCard = document.getElementById('memberCard');
                        const cardHeader = document.getElementById('cardHeader');
                        const submitBtn = document.getElementById('submitBtn');
                        const cancelBtn = document.getElementById('cancelEditBtn');

                        memberCard.classList.add('edit-mode');
                        cardHeader.innerHTML = '<h4 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Member Data</h4>';
                        submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Update Member Data';
                        cancelBtn.style.display = 'inline-block';

                        // Scroll to form
                        memberCard.scrollIntoView({ behavior: 'smooth' });

                        showAlert('Member data loaded for editing. Make your changes and click Update.', 'info');
                    } else {
                        showAlert('Error loading member data for editing.', 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('An error occurred while loading member data.', 'danger');
                });
        }

        function cancelEdit() {
            isEditMode = false;
            const memberCard = document.getElementById('memberCard');
            const cardHeader = document.getElementById('cardHeader');
            const submitBtn = document.getElementById('submitBtn');
            const cancelBtn = document.getElementById('cancelEditBtn');

            memberCard.classList.remove('edit-mode');
            cardHeader.innerHTML = '<h4 class="mb-0"><i class="fas fa-user-plus me-2"></i>Add Member with Current Data</h4>';
            submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Add Member Data';
            cancelBtn.style.display = 'none';
            document.getElementById('formAction').value = 'add';
            clearForm();
        }

        function loadMembers() {
            fetch('save_member.php?action=getMembers')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const tbody = document.getElementById('membersTableBody');
                        tbody.innerHTML = '';

                        data.members.forEach(member => {
                            const row = document.createElement('tr');
                            const fullName = `${member.last_name}, ${member.first_name}${member.middle_name ? ' ' + member.middle_name : ''}`;

                            row.innerHTML = `
                            <td>${fullName}</td>
                            <td>${member.address}</td>
                            <td>${member.tank_no}</td>
                            <td>${member.current_reading || 0}</td>
                            <td>₱${parseFloat(member.arrears_amount || 0).toFixed(2)}</td>
                            <td>${member.last_billing_month || 'N/A'}</td>
                            <td>
                                <button class="btn btn-sm btn-warning me-1" onclick="editMember(${member.member_id})" title="Edit Member">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteMember(${member.member_id})" title="Delete Member">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        `;
                            tbody.appendChild(row);
                        });

                        memberCount = data.members.length;
                        updateProgress();
                    }
                })
                .catch(error => {
                    console.error('Error loading members:', error);
                    showAlert('Error loading members list.', 'danger');
                });
        }

        function deleteMember(memberId) {
            if (confirm('Are you sure you want to delete this member? This action cannot be undone.')) {
                fetch('save_member.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete&memberId=${memberId}`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showAlert(data.message, 'success');
                            loadMembers();
                            updateMemberCount();
                        } else {
                            showAlert(data.message, 'danger');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showAlert('An error occurred while deleting the member.', 'danger');
                    });
            }
        }

        function updateMemberCount() {
            fetch('save_member.php?action=getCount')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        memberCount = data.count;
                        updateProgress();
                    }
                });
        }

        function updateProgress() {
            document.getElementById('memberCount').textContent = memberCount;
            const progressBar = document.getElementById('progressBar');
            const percentage = Math.min((memberCount / 20) * 100, 100);
            progressBar.style.width = `${percentage}%`;
            progressBar.textContent = `${Math.round(percentage)}%`;
        }

        function clearForm() {
            document.getElementById('memberForm').reset();
            document.getElementById('currentArrears').value = '0.00';
            document.getElementById('currentReading').value = '0';
            document.getElementById('memberId').value = '';
            document.getElementById('formAction').value = 'add';
        }

        function showAlert(message, type) {
            const alertContainer = document.getElementById('alertContainer');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} alert-dismissible fade show`;
            alert.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            alertContainer.appendChild(alert);

            setTimeout(() => {
                alert.remove();
            }, 5000);
        }

        function updateTankNumber(selectElement) {
            const selectedAddress = selectElement.value;
            const tankInput = document.getElementById('tankNo');

            const tankMapping = {
                'Mainuswagon': 1,
                'Mabuhay': 1,
                'Malipayon': 2,
                'Malipayon Extension': 2,
                'Riverside': 3,
                'Riverside Extension': 3,
                'Bibiana': 3
            };

            tankInput.value = tankMapping[selectedAddress] || '';
        }

        function filterMembers() {
            const input = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('#membersTableBody tr');

            rows.forEach(row => {
                const name = row.cells[0].textContent.toLowerCase();
                const address = row.cells[1].textContent.toLowerCase();
                const tank = row.cells[2].textContent.toLowerCase();

                if (name.includes(input) || address.includes(input) || tank.includes(input)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }


        // Load members when page loads
        document.addEventListener('DOMContentLoaded', function () {
            loadMembers();
            updateMemberCount();
        });
    </script>

    <!-- mirror arrears == total amount due -->

    <!-- <script>
        const arrearsInput = document.getElementById('currentArrears');
        const totalDueInput = document.getElementById('total_amount_due');

        arrearsInput.addEventListener('input', function () {
            totalDueInput.value = this.value;
        });
    </script> -->
</body>

</html>

                    <div class="table-responsive">

                        <table class="table table-hover" id="membersTable">

                            <thead>

                                <tr>

                                    <th>Name</th>

                                    <th>Address</th>

                                    <th>Tank #</th>

                                    <th>Current Reading</th>

                                    <th>Arrears</th>

                                    <th>Last Billing</th>

                                    <th>Actions</th>

                                </tr>

                            </thead>

                            <tbody id="membersTableBody">

                                <!-- Members will be loaded here dynamically -->

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>



    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

    <script>

        let memberCount = 0;

        let isEditMode = false;



        document.getElementById('memberForm').addEventListener('submit', function (e) {

            e.preventDefault();

            if (isEditMode) {

                updateMember();

            } else {

                addMember();

            }

        });



        function addMember() {

            const form = document.getElementById('memberForm');

            const formData = new FormData(form);

            const submitBtn = document.getElementById('submitBtn');



            // Show loading state

            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';

            submitBtn.disabled = true;



            fetch('save_member.php', {

                method: 'POST',

                body: formData

            })

                .then(response => response.json())

                .then(data => {

                    if (data.success) {

                        showAlert(data.message, 'success');

                        clearForm();

                        loadMembers();

                        updateMemberCount();

                    } else {

                        showAlert(data.message, 'danger');

                    }

                })

                .catch(error => {

                    console.error('Error:', error);

                    showAlert('An error occurred while saving the member.', 'danger');

                })

                .finally(() => {

                    submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Add Member Data';

                    submitBtn.disabled = false;

                });

        }



        function updateMember() {

            const form = document.getElementById('memberForm');

            const formData = new FormData(form);

            const submitBtn = document.getElementById('submitBtn');



            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';

            submitBtn.disabled = true;



            fetch('save_member.php', {

                method: 'POST',

                body: formData

            })

                .then(response => response.json())

                .then(data => {

                    if (data.success) {

                        showAlert(data.message, 'success');

                        cancelEdit();

                        loadMembers();

                    } else {

                        showAlert(data.message, 'danger');

                    }

                })

                .catch(error => {

                    console.error('Error:', error);

                    showAlert('An error occurred while updating the member.', 'danger');

                })

                .finally(() => {

                    submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Update Member Data';

                    submitBtn.disabled = false;

                });

        }



        function editMember(memberId) {

            fetch(`save_member.php?action=getMember&memberId=${memberId}`)

                .then(response => response.json())

                .then(data => {

                    if (data.success && data.member) {

                        const member = data.member;



                        // Populate form fields

                        document.getElementById('memberId').value = member.member_id;

                        document.getElementById('lastName').value = member.last_name;

                        document.getElementById('firstName').value = member.first_name;

                        document.getElementById('middleName').value = member.middle_name || '';

                        document.getElementById('address').value = member.address;

                        document.getElementById('tankNo').value = member.tank_no;

                        document.getElementById('currentArrears').value = member.arrears_amount || '0.00';

                        document.getElementById('currentReading').value = member.current_reading || '0';

                        document.getElementById('lastBillingMonth').value = member.last_billing_month || '';

                        document.getElementById('formAction').value = 'update';



                        // Switch to edit mode

                        isEditMode = true;

                        const memberCard = document.getElementById('memberCard');

                        const cardHeader = document.getElementById('cardHeader');

                        const submitBtn = document.getElementById('submitBtn');

                        const cancelBtn = document.getElementById('cancelEditBtn');



                        memberCard.classList.add('edit-mode');

                        cardHeader.innerHTML = '<h4 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Member Data</h4>';

                        submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Update Member Data';

                        cancelBtn.style.display = 'inline-block';



                        // Scroll to form

                        memberCard.scrollIntoView({ behavior: 'smooth' });



                        showAlert('Member data loaded for editing. Make your changes and click Update.', 'info');

                    } else {

                        showAlert('Error loading member data for editing.', 'danger');

                    }

                })

                .catch(error => {

                    console.error('Error:', error);

                    showAlert('An error occurred while loading member data.', 'danger');

                });

        }



        function cancelEdit() {

            isEditMode = false;

            const memberCard = document.getElementById('memberCard');

            const cardHeader = document.getElementById('cardHeader');

            const submitBtn = document.getElementById('submitBtn');

            const cancelBtn = document.getElementById('cancelEditBtn');



            memberCard.classList.remove('edit-mode');

            cardHeader.innerHTML = '<h4 class="mb-0"><i class="fas fa-user-plus me-2"></i>Add Member with Current Data</h4>';

            submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Add Member Data';

            cancelBtn.style.display = 'none';

            document.getElementById('formAction').value = 'add';

            clearForm();

        }



        function loadMembers() {

            fetch('save_member.php?action=getMembers')

                .then(response => response.json())

                .then(data => {

                    if (data.success) {

                        const tbody = document.getElementById('membersTableBody');

                        tbody.innerHTML = '';



                        data.members.forEach(member => {

                            const row = document.createElement('tr');

                            const fullName = `${member.last_name}, ${member.first_name}${member.middle_name ? ' ' + member.middle_name : ''}`;



                            row.innerHTML = `

                            <td>${fullName}</td>

                            <td>${member.address}</td>

                            <td>${member.tank_no}</td>

                            <td>${member.current_reading || 0}</td>

                            <td>₱${parseFloat(member.arrears_amount || 0).toFixed(2)}</td>

                            <td>${member.last_billing_month || 'N/A'}</td>

                            <td>

                                <button class="btn btn-sm btn-warning me-1" onclick="editMember(${member.member_id})" title="Edit Member">

                                    <i class="fas fa-edit"></i>

                                </button>

                                <button class="btn btn-sm btn-danger" onclick="deleteMember(${member.member_id})" title="Delete Member">

                                    <i class="fas fa-trash"></i>

                                </button>

                            </td>

                        `;

                            tbody.appendChild(row);

                        });



                        memberCount = data.members.length;

                        updateProgress();

                    }

                })

                .catch(error => {

                    console.error('Error loading members:', error);

                    showAlert('Error loading members list.', 'danger');

                });

        }



        function deleteMember(memberId) {

            if (confirm('Are you sure you want to delete this member? This action cannot be undone.')) {

                fetch('save_member.php', {

                    method: 'POST',

                    headers: {

                        'Content-Type': 'application/x-www-form-urlencoded',

                    },

                    body: `action=delete&memberId=${memberId}`

                })

                    .then(response => response.json())

                    .then(data => {

                        if (data.success) {

                            showAlert(data.message, 'success');

                            loadMembers();

                            updateMemberCount();

                        } else {

                            showAlert(data.message, 'danger');

                        }

                    })

                    .catch(error => {

                        console.error('Error:', error);

                        showAlert('An error occurred while deleting the member.', 'danger');

                    });

            }

        }



        function updateMemberCount() {

            fetch('save_member.php?action=getCount')

                .then(response => response.json())

                .then(data => {

                    if (data.success) {

                        memberCount = data.count;

                        updateProgress();

                    }

                });

        }



        function updateProgress() {

            document.getElementById('memberCount').textContent = memberCount;

            const progressBar = document.getElementById('progressBar');

            const percentage = Math.min((memberCount / 20) * 100, 100);

            progressBar.style.width = `${percentage}%`;

            progressBar.textContent = `${Math.round(percentage)}%`;

        }



        function clearForm() {

            document.getElementById('memberForm').reset();

            document.getElementById('currentArrears').value = '0.00';

            document.getElementById('currentReading').value = '0';

            document.getElementById('memberId').value = '';

            document.getElementById('formAction').value = 'add';

        }



        function showAlert(message, type) {

            const alertContainer = document.getElementById('alertContainer');

            const alert = document.createElement('div');

            alert.className = `alert alert-${type} alert-dismissible fade show`;

            alert.innerHTML = `

                ${message}

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

            `;

            alertContainer.appendChild(alert);



            setTimeout(() => {

                alert.remove();

            }, 5000);

        }



        function updateTankNumber(selectElement) {

            const selectedAddress = selectElement.value;

            const tankInput = document.getElementById('tankNo');



            const tankMapping = {

                'Mainuswagon': 1,

                'Mabuhay': 1,

                'Malipayon': 2,

                'Malipayon Extension': 2,

                'Riverside': 3,

                'Riverside Extension': 3,

                'Bibiana': 3

            };



            tankInput.value = tankMapping[selectedAddress] || '';

        }



        function filterMembers() {

            const input = document.getElementById('searchInput').value.toLowerCase();

            const rows = document.querySelectorAll('#membersTableBody tr');



            rows.forEach(row => {

                const name = row.cells[0].textContent.toLowerCase();

                const address = row.cells[1].textContent.toLowerCase();

                const tank = row.cells[2].textContent.toLowerCase();



                if (name.includes(input) || address.includes(input) || tank.includes(input)) {

                    row.style.display = '';

                } else {

                    row.style.display = 'none';

                }

            });

        }





        // Load members when page loads

        document.addEventListener('DOMContentLoaded', function () {

            loadMembers();

            updateMemberCount();

        });

    </script>



    <!-- mirror arrears == total amount due -->



    <!-- <script>

        const arrearsInput = document.getElementById('currentArrears');

        const totalDueInput = document.getElementById('total_amount_due');



        arrearsInput.addEventListener('input', function () {

            totalDueInput.value = this.value;

        });

    </script> -->

</body>



</html>
