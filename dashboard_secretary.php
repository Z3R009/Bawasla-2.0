<?php
include 'DBConnection.php';

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    // Redirect to login if user_id is not set
    header('Location: index.php');
    exit();
}

// retrieve/if done reading
$select_isDone = mysqli_query($connection, "
    SELECT 
        members.member_id, 
        CONCAT(members.last_name, ', ', members.first_name, ' ', members.middle_name) AS fullname, 
        isDone
    FROM 
        members
");

// retrieve status/paid not paid
$select_status = mysqli_query($connection, "
    SELECT 
        members.member_id, 
        CONCAT(members.last_name, ', ', members.first_name, ' ', members.middle_name) AS fullname,
        meter_reading.status
    FROM 
        meter_reading
    JOIN 
        members ON meter_reading.member_id = members.member_id
");

// Get the selected month from the URL or default to null
$selected_month = isset($_GET['month']) ? $_GET['month'] : null;

// Fetch total charges per purok (address)
$sql_charges = "SELECT m.address AS address, SUM(mr.current_charges) AS current_charges
                FROM members m
                JOIN meter_reading mr ON m.member_id = mr.member_id";

if ($selected_month) {
    $sql_charges .= " WHERE MONTHNAME(mr.reading_date) = ?";
}
$sql_charges .= " GROUP BY m.address";

$stmt_charges = $connection->prepare($sql_charges);

if ($selected_month) {
    $stmt_charges->bind_param("s", $selected_month);
    $stmt_charges->execute();
    $result_charges = $stmt_charges->get_result();
} else {
    $result_charges = $connection->query($sql_charges);
}

$addresses = [];
$current_charges = [];
while ($row = mysqli_fetch_assoc($result_charges)) {
    $addresses[] = $row['address'];
    $current_charges[] = $row['current_charges'];
}

// Fetch total charges per month
$sql_monthly_charges = "
    SELECT MONTHNAME(reading_date) as month, 
           SUM(current_charges) as total_charges 
    FROM meter_reading 
    GROUP BY MONTH(reading_date)
    ORDER BY MONTH(reading_date)";
$result_monthly_charges = mysqli_query($connection, $sql_monthly_charges);

$months = [];
$total_charges = [];
while ($row = mysqli_fetch_assoc($result_monthly_charges)) {
    $months[] = $row['month'];
    $total_charges[] = $row['total_charges'];
}

// Fetch total members
$sql_members = "SELECT COUNT(*) as total_members FROM members";
$result_members = mysqli_query($connection, $sql_members);
$total_members = mysqli_fetch_assoc($result_members)['total_members'];

// Fetch total consumption (sum of current_charges) and count entries
$sql_avg_consumption = "SELECT SUM(current_charges) as total_consumption, 
                        AVG(current_reading) as avg_usage, 
                        COUNT(current_reading) as count_consumption 
                        FROM meter_reading";

if ($selected_month) {
    $sql_avg_consumption .= " WHERE MONTHNAME(reading_date) = ?";
}

$stmt_avg_consumption = $connection->prepare($sql_avg_consumption);

if ($selected_month) {
    $stmt_avg_consumption->bind_param("s", $selected_month);
    $stmt_avg_consumption->execute();
    $result_avg_consumption = $stmt_avg_consumption->get_result();
} else {
    $result_avg_consumption = $connection->query($sql_avg_consumption);
}

$row = mysqli_fetch_assoc($result_avg_consumption);
$total_consumption = $row['total_consumption'];
$count_consumption = $row['count_consumption'];

// Calculate average consumption based on current_charges
$avg_consumption = $count_consumption > 0 ? $total_consumption / $count_consumption : 0;
$avg_usage = $row['avg_usage']; // Average usage from current_reading

// Fetch average charges per purok (address)
$sql_avg_charges = "
    SELECT m.address AS address, 
           AVG(mr.current_charges) AS avg_charges
    FROM members m
    JOIN meter_reading mr ON m.member_id = mr.member_id";

if ($selected_month) {
    $sql_avg_charges .= " WHERE MONTHNAME(mr.reading_date) = ?";
}
$sql_avg_charges .= " GROUP BY m.address";

$stmt_avg_charges = $connection->prepare($sql_avg_charges);

if ($selected_month) {
    $stmt_avg_charges->bind_param("s", $selected_month);
    $stmt_avg_charges->execute();
    $result_avg_charges = $stmt_avg_charges->get_result();
} else {
    $result_avg_charges = $connection->query($sql_avg_charges);
}

$avg_charges = [];
while ($row = mysqli_fetch_assoc($result_avg_charges)) {
    $avg_charges[] = $row['avg_charges'];
}

// Fetch the count of members by payment method
$sql_payment_methods_count = "
SELECT payment_method, COUNT(*) AS member_count
FROM history";

if ($selected_month) {
    $sql_payment_methods_count .= " WHERE MONTHNAME(payment_date) = ?";
}
$sql_payment_methods_count .= " GROUP BY payment_method";

$stmt_payment_methods_count = $connection->prepare($sql_payment_methods_count);

if ($selected_month) {
    $stmt_payment_methods_count->bind_param("s", $selected_month);
    $stmt_payment_methods_count->execute();
    $result_payment_methods_count = $stmt_payment_methods_count->get_result();
} else {
    $result_payment_methods_count = $connection->query($sql_payment_methods_count);
}

$payment_methods = [];
$member_counts = [];
while ($row = mysqli_fetch_assoc($result_payment_methods_count)) {
    $payment_methods[] = $row['payment_method'];
    $member_counts[] = $row['member_count'];
}

// Fetch total members who paid using G-Cash or Walk-in by Purok
$sql_payment_methods_count = "
SELECT m.address AS address, 
       SUM(CASE WHEN t.payment_method = 'G-Cash' THEN 1 ELSE 0 END) AS gcash_count,
       SUM(CASE WHEN t.payment_method = 'Walk-in' THEN 1 ELSE 0 END) AS walkin_count
FROM members m
JOIN history t ON m.member_id = t.member_id";

if ($selected_month) {
    $sql_payment_methods_count .= " WHERE MONTHNAME(t.payment_date) = ?";
}

$sql_payment_methods_count .= " GROUP BY m.address";

$stmt_payment_methods_count = $connection->prepare($sql_payment_methods_count);

if ($selected_month) {
    $stmt_payment_methods_count->bind_param("s", $selected_month);
    $stmt_payment_methods_count->execute();
    $result_payment_methods_count = $stmt_payment_methods_count->get_result();
} else {
    $result_payment_methods_count = $connection->query($sql_payment_methods_count);
}

$gcash_counts = [];
$walkin_counts = [];

while ($row = mysqli_fetch_assoc($result_payment_methods_count)) {
    $gcash_counts[] = $row['gcash_count'];
    $walkin_counts[] = $row['walkin_count'];
}

// NEW: Fetch daily payment records by address (amount paid per address today)
$sql_daily_payments = "
    SELECT m.address AS address, 
           SUM(h.amount_paid) AS daily_amount
    FROM members m
    JOIN history h ON m.member_id = h.member_id
    WHERE DATE(h.payment_date) = CURDATE()";

if ($selected_month) {
    $sql_daily_payments .= " AND MONTHNAME(h.payment_date) = ?";
}

$sql_daily_payments .= " GROUP BY m.address ORDER BY daily_amount DESC";

$stmt_daily_payments = $connection->prepare($sql_daily_payments);

if ($selected_month) {
    $stmt_daily_payments->bind_param("s", $selected_month);
    $stmt_daily_payments->execute();
    $result_daily_payments = $stmt_daily_payments->get_result();
} else {
    $result_daily_payments = $connection->query($sql_daily_payments);
}

$daily_addresses = [];
$daily_amounts = [];
while ($row = mysqli_fetch_assoc($result_daily_payments)) {
    $daily_addresses[] = $row['address'];
    $daily_amounts[] = $row['daily_amount'];
}

// NEW: Fetch overall payment records by address (total amount paid per address)
$sql_overall_payments = "
    SELECT m.address AS address, 
           SUM(h.amount_paid) AS total_amount
    FROM members m
    JOIN history h ON m.member_id = h.member_id";

if ($selected_month) {
    $sql_overall_payments .= " WHERE MONTHNAME(h.payment_date) = ?";
}

$sql_overall_payments .= " GROUP BY m.address ORDER BY total_amount DESC";

$stmt_overall_payments = $connection->prepare($sql_overall_payments);

if ($selected_month) {
    $stmt_overall_payments->bind_param("s", $selected_month);
    $stmt_overall_payments->execute();
    $result_overall_payments = $stmt_overall_payments->get_result();
} else {
    $result_overall_payments = $connection->query($sql_overall_payments);
}

$overall_addresses = [];
$overall_amounts = [];
while ($row = mysqli_fetch_assoc($result_overall_payments)) {
    $overall_addresses[] = $row['address'];
    $overall_amounts[] = $row['total_amount'];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="start/css/style.min.css" rel="stylesheet" />
    <link href="start/css/styles.css" rel="stylesheet" />
    <script src="fontawesome-free-6.3.0-web/js/all.js"></script>
    <script src="start/js/Chart.min.js"></script>
    <link href="img/lg2.png" rel="icon">

    <style>
        .box {
            width: 100%;
            min-width: 0;
            overflow-x: auto;
            padding: 24px 18px;
            min-height: 340px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }

        canvas {
            width: 100% !important;
            height: 320px !important;
            max-width: 100%;
            display: block;
        }

        @media (max-width: 991px) {
            .box {
                min-height: 260px;
                padding: 14px 6px;
            }

            canvas {
                height: 200px !important;
            }
        }

        @media (max-width: 600px) {
            .box {
                min-height: 180px;
                padding: 8px 2px;
            }

            canvas {
                height: 120px !important;
            }
        }

        .box:hover {
            box-shadow: 0 8px 32px rgba(44, 62, 80, 0.12), 0 2px 8px rgba(44, 62, 80, 0.08);
        }

        .row {
            display: flex;
            justify-content: space-between;
        }

        #month-select {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            background-color: #f4f6fb;
            font-size: 16px;
            color: #2c3e50;
            box-shadow: 0 1px 2px rgba(44, 62, 80, 0.04);
            transition: border 0.2s;
        }

        #month-select:focus {
            border: 2px solid #6c63ff;
            outline: none;
        }

        .card-title {
            font-size: 2rem;
            font-weight: 700;
            color: #6c63ff;
        }

        .card-text {
            font-size: 1rem;
            color: #7b8ca7;
        }

        .card.bg-dark {
            background: linear-gradient(135deg, #6c63ff 0%, #48c6ef 100%) !important;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(44, 62, 80, 0.10);
        }

        .card.bg-dark .card-body {
            color: #fff;
        }

        /* Chart container spacing */
        .container-fluid.px-4>.row>.col-lg-8,
        .container-fluid.px-4>.row>.col-lg-4,
        .container-fluid.px-4>.row>.col-lg-12 {
            margin-bottom: 24px;
        }

        /* Responsive tweaks */
        @media (max-width: 991px) {
            .row {
                flex-direction: column;
            }

            .col-lg-8,
            .col-lg-4,
            .col-lg-12 {
                width: 100%;
                margin-bottom: 24px;
            }
        }

        .stat-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(44, 62, 80, 0.10);
            display: flex;
            align-items: center;
            padding: 18px 20px 18px 0;
            margin-bottom: 18px;
            border-left: 6px solid #6c63ff;
            min-height: 80px;
            transition: box-shadow 0.2s;
        }

        .stat-card:hover {
            box-shadow: 0 6px 24px rgba(44, 62, 80, 0.14);
        }

        .stat-card .stat-content {
            flex: 1;
            text-align: center;
        }

        .stat-card .stat-title {
            font-size: 1.1rem;
            color: #7b8ca7;
            margin-bottom: 0;
            font-weight: 500;
        }

        .stat-card .stat-value {
            font-size: 2.1rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0;
        }

        .stat-card .stat-unit {
            font-size: 1.1rem;
            color: #6c63ff;
            font-weight: 600;
        }

        @media (max-width: 991px) {
            .stat-card {
                margin-bottom: 18px;
            }
        }

        .row.mb-4.justify-content-center {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
        }

        .stat-card {
            flex: 1 1 180px;
            min-width: 160px;
            max-width: 100%;
            margin-bottom: 0;
        }

        .col-lg-2 {
            flex: 1 1 180px;
            max-width: 220px;
            min-width: 160px;
            padding: 0 8px;
        }

        /* Chart containers responsive */
        .box {
            width: 100%;
            min-width: 0;
            overflow-x: auto;
        }

        canvas {
            width: 100% !important;
            height: auto !important;
            max-width: 100%;
        }

        /* Responsive tweaks */
        @media (max-width: 991px) {
            .row.mb-4.justify-content-center {
                flex-direction: column;
                gap: 0;
            }

            .col-lg-2 {
                max-width: 100%;
                min-width: 0;
                padding: 0;
            }

            .stat-card {
                margin-bottom: 18px;
            }
        }

        @media (max-width: 600px) {
            .stat-card .stat-value {
                font-size: 1.3rem;
            }

            .stat-card .stat-title {
                font-size: 0.95rem;
            }

            .stat-card {
                padding: 12px 8px 12px 0;
            }

            .box {
                padding: 12px 6px;
            }
        }
    </style>
</head>

<body class="sb-nav-fixed">

    <div id="layoutSidenav">
        <?php include "Includes/sidebar_admin.php"; ?>
        <?php include "Includes/header_admin.php"; ?>

        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">

                    <ol class="breadcrumb mb-4">
                    </ol>
                    <div class="container-fluid px-4">

                        <!-- Card Section -->
                        <div class="row mb-4 justify-content-center">
                            <div class="col-lg-2">
                                <div class="stat-card">
                                    <div class="stat-content">
                                        <div class="stat-value"><?php echo $total_members; ?></div>
                                        <div class="stat-title">Total Members</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="stat-card">
                                    <div class="stat-content">
                                        <div class="stat-value"><?php echo number_format($avg_usage, 2); ?> <span
                                                class="stat-unit">m³</span></div>
                                        <div class="stat-title">Average Usage</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="stat-card">
                                    <div class="stat-content">
                                        <div class="stat-value">&#8369;
                                            <?php echo number_format($avg_consumption, 2); ?>
                                        </div>
                                        <div class="stat-title">Average Bill</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="stat-card">
                                    <div class="stat-content">
                                        <div class="stat-value">&#8369;
                                            <?php echo number_format($total_consumption, 2); ?>
                                        </div>
                                        <div class="stat-title">Total Charges</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-4 justify-content-end">
                            <div class="col-lg-2 col-md-4 col-sm-6">
                                <select id="month-select">
                                    <option value="select_month" disabled selected>Select Month</option>
                                    <option value="January" <?= $selected_month == 'January' ? 'selected' : '' ?>>January
                                    </option>
                                    <option value="February" <?= $selected_month == 'February' ? 'selected' : '' ?>>
                                        February</option>
                                    <option value="March" <?= $selected_month == 'March' ? 'selected' : '' ?>>March
                                    </option>
                                    <option value="April" <?= $selected_month == 'April' ? 'selected' : '' ?>>April
                                    </option>
                                    <option value="May" <?= $selected_month == 'May' ? 'selected' : '' ?>>May</option>
                                    <option value="June" <?= $selected_month == 'June' ? 'selected' : '' ?>>June</option>
                                    <option value="July" <?= $selected_month == 'July' ? 'selected' : '' ?>>July</option>
                                    <option value="August" <?= $selected_month == 'August' ? 'selected' : '' ?>>August
                                    </option>
                                    <option value="September" <?= $selected_month == 'September' ? 'selected' : '' ?>>
                                        September</option>
                                    <option value="October" <?= $selected_month == 'October' ? 'selected' : '' ?>>October
                                    </option>
                                    <option value="November" <?= $selected_month == 'November' ? 'selected' : '' ?>>
                                        November</option>
                                    <option value="December" <?= $selected_month == 'December' ? 'selected' : '' ?>>
                                        December</option>
                                </select>
                            </div>
                        </div>

                        <div class="container-fluid px-4">
                            <div class="row">
                                <div class="col-12 mb-4">
                                    <div class="box">
                                        <h6>Total Charges per Purok</h6>
                                        <canvas id="chargesChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="container-fluid px-4">
                            <div class="row">
                                <div class="col-12 mb-4">
                                    <div class="box">
                                        <h6>Monthly Charges</h6>
                                        <canvas id="monthlyChargesChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- NEW: Daily Payment Records Chart -->
                        <div class="container-fluid px-4">
                            <div class="row">
                                <div class="col-12 mb-4">
                                    <div class="box">
                                        <h6>Daily Payment Records by Address (Today)</h6>
                                        <canvas id="dailyPaymentChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- NEW: Overall Payment Records Chart -->
                        <div class="container-fluid px-4">
                            <div class="row">
                                <div class="col-12 mb-4">
                                    <div class="box">
                                        <h6>Overall Payment Records by Address</h6>
                                        <canvas id="overallPaymentChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="container-fluid px-4">
                            <div class="row">
                                <div class="col-12 mb-4">
                                    <div class="box">
                                        <h6>Payment Method Used</h6>
                                        <canvas id="paymentMethodChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- <div class="container-fluid px-4">
                            <div class="row">
                                <div class="col-12 mb-4">
                                    <div class="box">
                                        <h6>Payment Methods by Purok (G-Cash vs Walk-in)</h6>
                                        <canvas id="paymentChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div> -->

                    </div>

                    <div class="card mb-4">
                        <div class="card-body">
                            <table id="datatablesSimple">
                                <thead>
                                    <tr>
                                        <th>Fullname</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($select_status)) { ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </main>
            <?php include "Includes/footer.php"; ?>
        </div>

    </div>
    <!-- startbootstrap -->
    <script src="bootstrap-5.2.3/js/bootstrap.bundle.min.js"></script>
    <script src="start/js/scripts.js"></script>
    <script src="start/js/Chart.min.js"></script>
    <script src="start/js/simple-datatables.min.js"></script>
    <script src="start/js/datatables-simple-demo.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@0.7.0"></script>
    <script>
        // Data passed from PHP to JavaScript
        var addresses = <?php echo json_encode($addresses); ?>;
        var currentCharges = <?php echo json_encode($current_charges); ?>;
        var months = <?php echo json_encode($months); ?>;
        var totalCharges = <?php echo json_encode($total_charges); ?>;
        var avgCharges = <?php echo json_encode($avg_charges); ?>;

        var paymentMethods = <?php echo json_encode($payment_methods); ?>;
        var memberCounts = <?php echo json_encode($member_counts); ?>;
        var gcashCounts = <?php echo json_encode($gcash_counts); ?>;
        var walkinCounts = <?php echo json_encode($walkin_counts); ?>;

        // NEW: Payment data for new charts
        var dailyAddresses = <?php echo json_encode($daily_addresses); ?>;
        var dailyAmounts = <?php echo json_encode($daily_amounts); ?>;
        var overallAddresses = <?php echo json_encode($overall_addresses); ?>;
        var overallAmounts = <?php echo json_encode($overall_amounts); ?>;

        // Modern color palette
        const modernColors = [
            '#6c63ff', '#48c6ef', '#43e97b', '#f9ea8f', '#ff6a88', '#ffb86c', '#7b8ca7', '#f67280', '#355c7d', '#c06c84', '#6c5b7b', '#355c7d'
        ];

        // Helper for gradient backgrounds
        function getGradient(ctx, area, color1, color2) {
            const gradient = ctx.createLinearGradient(0, area.top, 0, area.bottom);
            gradient.addColorStop(0, color1);
            gradient.addColorStop(1, color2);
            return gradient;
        }

        // Charges per Purok (Bar Chart)
        var ctx1 = document.getElementById('chargesChart').getContext('2d');
        var chargesChart = new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: addresses,
                datasets: [{
                    label: 'Total Charges per Purok',
                    data: currentCharges,
                    backgroundColor: function (context) {
                        const chart = context.chart;
                        const { ctx, chartArea } = chart;
                        if (!chartArea) return modernColors[0];
                        return getGradient(ctx, chartArea, '#6c63ff', '#48c6ef');
                    },
                    borderRadius: 12,
                    borderSkipped: false,
                    maxBarThickness: 40,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    datalabels: {
                        align: function (context) {
                            var value = context.dataset.data[context.dataIndex];
                            var scales = context.chart.scales;
                            var max = (scales['y-axis-0'] || scales['y']).max;
                            return value >= 0.6 * max ? 'center' : 'end';
                        },
                        anchor: function (context) {
                            var value = context.dataset.data[context.dataIndex];
                            var scales = context.chart.scales;
                            var max = (scales['y-axis-0'] || scales['y']).max;
                            return value >= 0.6 * max ? 'center' : 'end';
                        },
                        color: function (context) {
                            var value = context.dataset.data[context.dataIndex];
                            var scales = context.chart.scales;
                            var max = (scales['y-axis-0'] || scales['y']).max;
                            return value >= 0.6 * max ? '#fff' : '#2c3e50';
                        },
                        font: { weight: 'bold', size: 12 },
                        backgroundColor: function (context) {
                            var value = context.dataset.data[context.dataIndex];
                            var scales = context.chart.scales;
                            var max = (scales['y-axis-0'] || scales['y']).max;
                            return value >= 0.6 * max ? 'rgba(44,99,255,0.7)' : 'rgba(255,255,255,0.85)';
                        },
                        borderRadius: 4,
                        padding: 2,
                        clamp: true,
                        clip: true,
                        display: function (context) {
                            return context.dataset.data[context.dataIndex] > 0;
                        },
                        formatter: function (value) { return '₱' + value.toLocaleString(); }
                    },
                    tooltip: {
                        backgroundColor: '#fff',
                        titleColor: '#6c63ff',
                        bodyColor: '#2c3e50',
                        borderColor: '#6c63ff',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: false,
                        callbacks: {
                            label: function (context) {
                                return '₱' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#7b8ca7', font: { weight: 'bold', size: 12 }, maxRotation: 0, autoSkip: true }
                    },
                    y: {
                        grid: { color: '#f4f6fb' },
                        ticks: { color: '#7b8ca7', font: { weight: 'bold', size: 12 } }
                    }
                },
                animation: {
                    duration: 1200,
                    easing: 'easeOutQuart'
                }
            },
            plugins: [ChartDataLabels]
        });

        // NEW: Daily Payment Records Chart (Horizontal Bar Chart)
        var ctxDaily = document.getElementById('dailyPaymentChart').getContext('2d');
        var dailyPaymentChart = new Chart(ctxDaily, {
            type: 'horizontalBar',
            data: {
                labels: dailyAddresses,
                datasets: [{
                    label: 'Daily Payment Amount',
                    data: dailyAmounts,
                    backgroundColor: function (context) {
                        const chart = context.chart;
                        const { ctx, chartArea } = chart;
                        if (!chartArea) return '#43e97b';
                        return getGradient(ctx, chartArea, '#43e97b', '#f9ea8f');
                    },
                    borderRadius: 8,
                    borderSkipped: false,
                    maxBarThickness: 35,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    datalabels: {
                        align: 'end',
                        anchor: 'end',
                        color: '#2c3e50',
                        font: { weight: 'bold', size: 12 },
                        backgroundColor: 'rgba(255,255,255,0.9)',
                        borderRadius: 4,
                        padding: 4,
                        clamp: true,
                        clip: true,
                        display: function (context) {
                            return context.dataset.data[context.dataIndex] > 0;
                        },
                        formatter: function (value) {
                            return value > 0 ? '₱' + value.toLocaleString() : '';
                        }
                    },
                    tooltip: {
                        backgroundColor: '#fff',
                        titleColor: '#43e97b',
                        bodyColor: '#2c3e50',
                        borderColor: '#43e97b',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: false,
                        callbacks: {
                            label: function (context) {
                                return 'Today: ₱' + context.parsed.x.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: '#f4f6fb' },
                        ticks: { color: '#7b8ca7', font: { weight: 'bold', size: 12 } }
                    },
                    y: {
                        grid: { display: false },
                        ticks: { color: '#7b8ca7', font: { weight: 'bold', size: 12 } }
                    }
                },
                animation: {
                    duration: 1200,
                    easing: 'easeOutQuart'
                }
            },
            plugins: [ChartDataLabels]
        });

        // NEW: Overall Payment Records Chart (Horizontal Bar Chart)
        var ctxOverall = document.getElementById('overallPaymentChart').getContext('2d');
        var overallPaymentChart = new Chart(ctxOverall, {
            type: 'horizontalBar',
            data: {
                labels: overallAddresses,
                datasets: [{
                    label: 'Total Payment Amount',
                    data: overallAmounts,
                    backgroundColor: function (context) {
                        const chart = context.chart;
                        const { ctx, chartArea } = chart;
                        if (!chartArea) return '#ff6a88';
                        return getGradient(ctx, chartArea, '#ff6a88', '#ffb86c');
                    },
                    borderRadius: 8,
                    borderSkipped: false,
                    maxBarThickness: 35,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    datalabels: {
                        align: 'end',
                        anchor: 'end',
                        color: '#2c3e50',
                        font: { weight: 'bold', size: 12 },
                        backgroundColor: 'rgba(255,255,255,0.9)',
                        borderRadius: 4,
                        padding: 4,
                        clamp: true,
                        clip: true,
                        display: function (context) {
                            return context.dataset.data[context.dataIndex] > 0;
                        },
                        formatter: function (value) {
                            return value > 0 ? '₱' + value.toLocaleString() : '';
                        }
                    },
                    tooltip: {
                        backgroundColor: '#fff',
                        titleColor: '#ff6a88',
                        bodyColor: '#2c3e50',
                        borderColor: '#ff6a88',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: false,
                        callbacks: {
                            label: function (context) {
                                return 'Total: ₱' + context.parsed.x.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: '#f4f6fb' },
                        ticks: { color: '#7b8ca7', font: { weight: 'bold', size: 12 } }
                    },
                    y: {
                        grid: { display: false },
                        ticks: { color: '#7b8ca7', font: { weight: 'bold', size: 12 } }
                    }
                },
                animation: {
                    duration: 1200,
                    easing: 'easeOutQuart'
                }
            },
            plugins: [ChartDataLabels]
        });

        // Payment Method (Pie Chart)
        var ctx2 = document.getElementById('paymentMethodChart').getContext('2d');
        var paymentMethodChart = new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: paymentMethods,
                datasets: [{
                    data: memberCounts,
                    backgroundColor: modernColors.slice(0, paymentMethods.length),
                    borderWidth: 2,
                    borderColor: '#fff',
                }]
            },
            options: {
                responsive: true,
                cutout: '70%',
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            color: '#2c3e50',
                            font: { weight: 'bold', size: 12 }
                        }
                    },
                    datalabels: {
                        color: '#2c3e50',
                        font: { weight: 'bold', size: 12 },
                        backgroundColor: 'rgba(255,255,255,0.85)',
                        borderRadius: 4,
                        padding: 2,
                        clamp: true,
                        clip: true,
                        formatter: function (value, ctx) {
                            let sum = ctx.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                            let percentage = (value * 100 / sum).toFixed(1) + '%';
                            return percentage;
                        }
                    },
                    tooltip: {
                        backgroundColor: '#fff',
                        titleColor: '#6c63ff',
                        bodyColor: '#2c3e50',
                        borderColor: '#6c63ff',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: false,
                        callbacks: {
                            label: function (context) {
                                return context.label + ': ' + context.parsed + ' members';
                            }
                        }
                    }
                },
                animation: {
                    duration: 1200,
                    easing: 'easeOutQuart'
                }
            },
            plugins: [ChartDataLabels]
        });

        // Monthly Charges (Line Chart)
        var ctx3 = document.getElementById('monthlyChargesChart').getContext('2d');
        var monthlyChargesChart = new Chart(ctx3, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Total Charges per Month',
                    data: totalCharges,
                    borderColor: '#6c63ff',
                    backgroundColor: function (context) {
                        const chart = context.chart;
                        const { ctx, chartArea } = chart;
                        if (!chartArea) return '#6c63ff';
                        return getGradient(ctx, chartArea, 'rgba(76,99,255,0.3)', 'rgba(72,198,239,0.1)');
                    },
                    borderWidth: 3,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#6c63ff',
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    tension: 0.4,
                    fill: true,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    datalabels: {
                        align: function (context) {
                            var value = context.dataset.data[context.dataIndex];
                            var scales = context.chart.scales;
                            var max = (scales['y-axis-0'] || scales['y']).max;
                            return value >= 0.6 * max ? 'center' : 'end';
                        },
                        anchor: function (context) {
                            var value = context.dataset.data[context.dataIndex];
                            var scales = context.chart.scales;
                            var max = (scales['y-axis-0'] || scales['y']).max;
                            return value >= 0.6 * max ? 'center' : 'end';
                        },
                        color: function (context) {
                            var value = context.dataset.data[context.dataIndex];
                            var scales = context.chart.scales;
                            var max = (scales['y-axis-0'] || scales['y']).max;
                            return value >= 0.6 * max ? '#fff' : '#6c63ff';
                        },
                        font: { weight: 'bold', size: 13 },
                        backgroundColor: function (context) {
                            var value = context.dataset.data[context.dataIndex];
                            var scales = context.chart.scales;
                            var max = (scales['y-axis-0'] || scales['y']).max;
                            return value >= 0.6 * max ? 'rgba(44,99,255,0.7)' : 'rgba(255,255,255,0.85)';
                        },
                        borderRadius: 4,
                        padding: 2,
                        clamp: true,
                        clip: true,
                        display: function (context) {
                            return context.dataset.data[context.dataIndex] > 0;
                        },
                        formatter: function (value) { return '₱' + value.toLocaleString(); }
                    },
                    tooltip: {
                        backgroundColor: '#fff',
                        titleColor: '#6c63ff',
                        bodyColor: '#2c3e50',
                        borderColor: '#6c63ff',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: false,
                        callbacks: {
                            label: function (context) {
                                return '₱' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#7b8ca7', font: { weight: 'bold' } }
                    },
                    y: {
                        grid: { color: '#f4f6fb' },
                        ticks: { color: '#7b8ca7', font: { weight: 'bold' } }
                    }
                },
                animation: {
                    duration: 1200,
                    easing: 'easeOutQuart'
                }
            },
            plugins: [ChartDataLabels]
        });

        var paymentChart;
        function getGcashGradient(ctx, chartArea) {
            var gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
            gradient.addColorStop(0, 'rgba(76,99,255,0.9)');
            gradient.addColorStop(1, 'rgba(76,99,255,0.3)');
            return gradient;
        }
        function getWalkinGradient(ctx, chartArea) {
            var gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
            gradient.addColorStop(0, 'rgba(255,106,136,0.9)');
            gradient.addColorStop(1, 'rgba(255,106,136,0.3)');
            return gradient;
        }
        var ctxPayment = document.getElementById('paymentChart').getContext('2d');
        paymentChart = new Chart(ctxPayment, {
            type: 'bar',
            data: {
                labels: addresses,
                datasets: [
                    {
                        label: 'G-Cash',
                        data: gcashCounts,
                        backgroundColor: 'rgba(76,99,255,1)',
                        borderRadius: 12,
                        borderSkipped: false,
                        maxBarThickness: 40,
                    },
                    {
                        label: 'Walk-in',
                        data: walkinCounts,
                        backgroundColor: 'rgba(255,106,136,1)',
                        borderRadius: 12,
                        borderSkipped: false,
                        maxBarThickness: 40,
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: '#2c3e50',
                            font: { weight: 'bold', size: 14 }
                        }
                    },
                    datalabels: {
                        align: function (context) {
                            var value = context.dataset.data[context.dataIndex];
                            var scales = context.chart.scales;
                            var max = (scales['y-axis-0'] || scales['y']).max;
                            return value >= 0.6 * max ? 'center' : 'end';
                        },
                        anchor: function (context) {
                            var value = context.dataset.data[context.dataIndex];
                            var scales = context.chart.scales;
                            var max = (scales['y-axis-0'] || scales['y']).max;
                            return value >= 0.6 * max ? 'center' : 'end';
                        },
                        color: function (context) {
                            var value = context.dataset.data[context.dataIndex];
                            var scales = context.chart.scales;
                            var max = (scales['y-axis-0'] || scales['y']).max;
                            return value >= 0.6 * max ? '#fff' : '#2c3e50';
                        },
                        font: { weight: 'bold', size: 13 },
                        backgroundColor: function (context) {
                            var value = context.dataset.data[context.dataIndex];
                            var scales = context.chart.scales;
                            var max = (scales['y-axis-0'] || scales['y']).max;
                            return value >= 0.6 * max ? 'rgba(44,99,255,0.7)' : 'rgba(255,255,255,0.85)';
                        },
                        borderRadius: 4,
                        padding: 2,
                        clamp: true,
                        clip: true,
                        display: function (context) {
                            return context.dataset.data[context.dataIndex] > 0;
                        },
                        formatter: function (value) { return value; }
                    }
                },
                scales: {
                    xAxes: [{
                        stacked: true,
                        gridLines: { display: false },
                        ticks: { color: '#7b8ca7', font: { weight: 'bold' } }
                    }],
                    yAxes: [{
                        stacked: true,
                        gridLines: { color: '#f4f6fb' },
                        ticks: { color: '#7b8ca7', font: { weight: 'bold' } }
                    }]
                },
                animation: {
                    duration: 1200,
                    easing: 'easeOutQuart',
                    onComplete: function (animation) {
                        var chart = animation.chart || paymentChart;
                        var chartArea = chart.chartArea;
                        chart.data.datasets[0].backgroundColor = function (context) {
                            var chart = context.chart || context._chart;
                            var ctx = chart.ctx;
                            var chartArea = chart.chartArea;
                            if (!chartArea) return 'rgba(76,99,255,1)';
                            return getGcashGradient(ctx, chartArea);
                        };
                        chart.data.datasets[1].backgroundColor = function (context) {
                            var chart = context.chart || context._chart;
                            var ctx = chart.ctx;
                            var chartArea = chart.chartArea;
                            if (!chartArea) return 'rgba(255,106,136,1)';
                            return getWalkinGradient(ctx, chartArea);
                        };
                        chart.update();
                    }
                }
            },
            plugins: [ChartDataLabels]
        });
    </script>

    <script>
        document.getElementById("month-select").addEventListener("change", function () {
            var selectedMonth = this.value;
            window.location.href = "dashboard_secretary.php?month=" + selectedMonth;
        });
    </script>

</body>

</html>