<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Database configuration - UPDATE THESE WITH YOUR DATABASE DETAILS
$host = 'localhost';
$dbname = 'bawasla 2.0';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

// Handle different actions
$action = $_POST['action'] ?? $_GET['action'] ?? 'add';

switch ($action) {
    case 'add':
        addMember();
        break;
    case 'delete':
        deleteMember();
        break;
    case 'getMembers':
        getMembers();
        break;
    case 'getCount':
        getMemberCount();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function addMember()
{
    global $pdo;

    try {
        // Get and validate form data
        $lastName = trim($_POST['lastName'] ?? '');
        $firstName = trim($_POST['firstName'] ?? '');
        $middleName = trim($_POST['middleName'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $tankNo = intval($_POST['tankNo'] ?? 0);
        $currentArrears = floatval($_POST['currentArrears'] ?? 0);
        $currentReading = intval($_POST['currentReading'] ?? 0);
        $lastBillingMonth = trim($_POST['lastBillingMonth'] ?? '');

        // Validate required fields
        if (empty($lastName) || empty($firstName) || empty($address) || $tankNo <= 0) {
            echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
            return;
        }

        // Generate unique IDs
        $memberId = generateUniqueId('members', 'member_id');
        $userId = $memberId; // Using same ID for both

        // Start transaction
        $pdo->beginTransaction();

        // Insert into members table
        $stmt = $pdo->prepare("
            INSERT INTO members (user_id, member_id, last_name, first_name, middle_name, address, tank_no, user_type, isDone) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'Member', 'Not Done')
        ");
        $stmt->execute([$userId, $memberId, $lastName, $firstName, $middleName, $address, $tankNo]);

        // Insert into users table
        $stmt = $pdo->prepare("
            INSERT INTO users (user_id, member_id, user_type) 
            VALUES (?, ?, 'Member')
        ");
        $stmt->execute([$userId, $memberId]);

        // Insert arrears if amount > 0
        if ($currentArrears > 0) {
            $transactionId = generateUniqueId('arrears', 'transaction_id');
            $stmt = $pdo->prepare("
                INSERT INTO arrears (transaction_id, member_id, arrears_amount) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$transactionId, $memberId, $currentArrears]);
        }

        // Insert initial meter reading if provided
        if ($currentReading > 0 || !empty($lastBillingMonth)) {
            $readingId = generateUniqueId('meter_reading', 'reading_id');
            $billingMonth = !empty($lastBillingMonth) ? $lastBillingMonth : 'Initial Setup';
            $charges = $currentReading * 18; // Assuming ₱18 per cubic meter

            $stmt = $pdo->prepare("
                INSERT INTO meter_reading (
                    reading_id, user_id, member_id, previous_reading, current_reading, 
                    total_usage, current_charges, arrears_amount, total_amount_due, 
                    due_date, disconnection_date, billing_month, status
                ) VALUES (?, ?, ?, 0, ?, ?, ?, 0.00, ?, '2025-12-31', '2026-01-02', ?, 'Paid')
            ");
            $stmt->execute([
                $readingId,
                $userId,
                $memberId,
                $currentReading,
                $currentReading,
                $charges,
                $charges,
                $billingMonth
            ]);
        }

        // Commit transaction
        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Member added successfully!',
            'memberId' => $memberId
        ]);

    } catch (PDOException $e) {
        $pdo->rollback();

        // Check for duplicate entry
        if ($e->getCode() == 23000) {
            echo json_encode(['success' => false, 'message' => 'Member ID already exists. Please try again.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } catch (Exception $e) {
        $pdo->rollback();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function deleteMember()
{
    global $pdo;

    try {
        $memberId = intval($_POST['memberId'] ?? 0);

        if ($memberId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid member ID.']);
            return;
        }

        // Start transaction
        $pdo->beginTransaction();

        // Delete from related tables first (foreign key constraints)
        $stmt = $pdo->prepare("DELETE FROM arrears WHERE member_id = ?");
        $stmt->execute([$memberId]);

        $stmt = $pdo->prepare("DELETE FROM meter_reading WHERE member_id = ?");
        $stmt->execute([$memberId]);

        $stmt = $pdo->prepare("DELETE FROM users WHERE member_id = ?");
        $stmt->execute([$memberId]);

        // Delete from members table
        $stmt = $pdo->prepare("DELETE FROM members WHERE member_id = ?");
        $stmt->execute([$memberId]);

        if ($stmt->rowCount() > 0) {
            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Member deleted successfully!']);
        } else {
            $pdo->rollback();
            echo json_encode(['success' => false, 'message' => 'Member not found.']);
        }

    } catch (PDOException $e) {
        $pdo->rollback();
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function getMembers()
{
    global $pdo;

    try {
        // Get recent members with their arrears
        $stmt = $pdo->prepare("
            SELECT 
                m.*,
                COALESCE(a.arrears_amount, 0) as arrears_amount,
                COALESCE(mr.current_reading, 0) as current_reading
            FROM members m
            LEFT JOIN arrears a ON m.member_id = a.member_id
            LEFT JOIN (
                SELECT member_id, current_reading, 
                       ROW_NUMBER() OVER (PARTITION BY member_id ORDER BY reading_id DESC) as rn
                FROM meter_reading
            ) mr ON m.member_id = mr.member_id AND mr.rn = 1
            ORDER BY m.member_id DESC
            LIMIT 50
        ");
        $stmt->execute();
        $members = $stmt->fetchAll();

        echo json_encode(['success' => true, 'members' => $members]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error loading members: ' . $e->getMessage()]);
    }
}

function getMemberCount()
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM members");
        $stmt->execute();
        $result = $stmt->fetch();

        echo json_encode(['success' => true, 'count' => intval($result['count'])]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error getting count: ' . $e->getMessage()]);
    }
}

function generateUniqueId($table, $column)
{
    global $pdo;

    $maxAttempts = 10;
    $attempts = 0;

    do {
        $id = rand(100000000, 999999999);
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM $table WHERE $column = ?");
        $stmt->execute([$id]);
        $exists = $stmt->fetchColumn() > 0;
        $attempts++;
    } while ($exists && $attempts < $maxAttempts);

    if ($exists) {
        throw new Exception("Could not generate unique ID after $maxAttempts attempts");
    }

    return $id;
}
?>