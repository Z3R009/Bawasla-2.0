<?php
$host = 'localhost';
$dbname = 'bawasla 2.0';
$username = 'root';
$password = '';
header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Handle GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'getMembers':
                getMembers($pdo);
                break;
            case 'getCount':
                getMemberCount($pdo);
                break;
            case 'getMember':
                if (isset($_GET['memberId'])) {
                    getMember($pdo, $_GET['memberId']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Member ID is required']);
                }
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    }
    exit;
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'add';

    switch ($action) {
        case 'add':
            addMember($pdo);
            break;
        case 'update':
            updateMember($pdo);
            break;
        case 'delete':
            if (isset($_POST['memberId'])) {
                deleteMember($pdo, $_POST['memberId']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Member ID is required']);
            }
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    exit;
}

function rolloverUnpaidToArrears($pdo, $memberId)
{
    // Move the latest unpaid total_amount_due into arrears and mark it as Rolled Over
    $unpaidStmt = $pdo->prepare("SELECT reading_id, total_amount_due FROM meter_reading WHERE member_id = ? AND status = 'Not Paid' AND total_amount_due > 0 ORDER BY reading_date DESC LIMIT 1");
    $unpaidStmt->execute([$memberId]);
    $unpaid = $unpaidStmt->fetch(PDO::FETCH_ASSOC);

    if ($unpaid) {
        $amountToRollover = (float)$unpaid['total_amount_due'];

        // Add to existing arrears or insert
        $arrearsGet = $pdo->prepare("SELECT arrears_amount FROM arrears WHERE member_id = ?");
        $arrearsGet->execute([$memberId]);
        $existing = $arrearsGet->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $newAmount = (float)$existing['arrears_amount'] + $amountToRollover;
            $arrearsUpd = $pdo->prepare("UPDATE arrears SET arrears_amount = ? WHERE member_id = ?");
            $arrearsUpd->execute([$newAmount, $memberId]);
        } else {
            $arrearsIns = $pdo->prepare("INSERT INTO arrears (transaction_id, member_id, arrears_amount) VALUES (0, ?, ?)");
            $arrearsIns->execute([$memberId, $amountToRollover]);
        }

        // Mark reading as rolled over so it won't show as unpaid
        $markRolled = $pdo->prepare("UPDATE meter_reading SET status = 'Rolled Over' WHERE reading_id = ?");
        $markRolled->execute([$unpaid['reading_id']]);
    }
}

function addMember($pdo)
{
    try {
        // Validate required fields
        $required_fields = ['lastName', 'firstName', 'address', 'tankNo', 'currentReading'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
                return;
            }
        }

        // Generate unique member_id
        $member_id = generateUniqueMemberId($pdo);

        // Check if tank number and address combination already exists
        $checkStmt = $pdo->prepare("
            SELECT COUNT(*) FROM members 
            WHERE tank_no = ? AND address = ? AND last_name = ? AND first_name = ?
        ");
        $checkStmt->execute([
            $_POST['tankNo'],
            $_POST['address'],
            $_POST['lastName'],
            $_POST['firstName']
        ]);

        if ($checkStmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'A member with this information already exists']);
            return;
        }

        // Start transaction
        $pdo->beginTransaction();

        // Insert member with proper structure matching your database
        $memberStmt = $pdo->prepare("
            INSERT INTO members (user_id, member_id, last_name, first_name, middle_name, gender, address, tank_no, user_type, isDone) 
            VALUES (0, ?, ?, ?, ?, 'Male', ?, ?, 'Member', 'Not Done')
        ");

        $memberStmt->execute([
            $member_id,
            $_POST['lastName'],
            $_POST['firstName'],
            $_POST['middleName'] ?? '',
            $_POST['address'],
            $_POST['tankNo']
        ]);

        // (Changed) Do not roll unpaid totals into arrears on initial entry
        // rolloverUnpaidToArrears($pdo, $member_id);

        // Insert arrears if amount > 0
        $arrearsAmount = floatval($_POST['currentArrears'] ?? 0);
        // $total_amount_due = $arrearsAmount;

        // (Changed) Do not insert into arrears on initial entry
        // if ($arrearsAmount > 0) {
        //     $arrearsStmt = $pdo->prepare("\n                INSERT INTO arrears (transaction_id, member_id, arrears_amount) \n                VALUES (0, ?, ?)\n            ");
        //     $arrearsStmt->execute([$member_id, $arrearsAmount]);
        // }

        // Insert meter reading with proper structure
        $readingId = generateUniqueReadingId($pdo);
        $readingStmt = $pdo->prepare("
            INSERT INTO meter_reading (reading_id, user_id, member_id, reading_date, time, previous_reading, current_reading, total_usage, current_charges, arrears_amount, total_amount_due, due_date, disconnection_date, billing_month, status, arrears_processed) 
            VALUES (?, 0, ?, NOW(), NOW(), 0, ?, 0, 0, 0, ?, DATE_ADD(NOW(), INTERVAL 30 DAY), DATE_ADD(NOW(), INTERVAL 45 DAY), ?, 'Not Paid', 0)
        ");

        $readingStmt->execute([
            $readingId,
            $member_id,
            $_POST['currentReading'],
            $arrearsAmount,
            $_POST['lastBillingMonth'] ?? date('F Y')
        ]);

        // (Changed) No mirror needed; total_amount_due already set from entered arrears
        // $updTotal = $pdo->prepare("UPDATE meter_reading SET total_amount_due = arrears_amount WHERE reading_id = ?");
        // $updTotal->execute([$readingId]);

        $pdo->commit();

        $fullName = $_POST['firstName'] . ' ' . $_POST['lastName'];
        echo json_encode([
            'success' => true,
            'message' => "Member '$fullName' has been successfully added with initial data."
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error adding member: ' . $e->getMessage()]);
    }
}

function updateMember($pdo)
{
    try {
        // Validate required fields
        $required_fields = ['memberId', 'lastName', 'firstName', 'address', 'tankNo', 'currentReading'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
                return;
            }
        }

        $memberId = $_POST['memberId'];

        // Check if member exists
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM members WHERE member_id = ?");
        $checkStmt->execute([$memberId]);

        if ($checkStmt->fetchColumn() == 0) {
            echo json_encode(['success' => false, 'message' => 'Member not found']);
            return;
        }

        // Start transaction
        $pdo->beginTransaction();

        // Move any latest unpaid total into arrears to avoid duplication
        rolloverUnpaidToArrears($pdo, $memberId);

        // Update member information
        $memberStmt = $pdo->prepare("
            UPDATE members 
            SET last_name = ?, first_name = ?, middle_name = ?, address = ?, tank_no = ?
            WHERE member_id = ?
        ");

        $memberStmt->execute([
            $_POST['lastName'],
            $_POST['firstName'],
            $_POST['middleName'] ?? '',
            $_POST['address'],
            $_POST['tankNo'],
            $memberId
        ]);

        // Update or insert arrears
        $arrearsAmount = floatval($_POST['currentArrears'] ?? 0);
        // $total_amount_due = $arrearsAmount;

        // Check if arrears record exists
        $arrearsCheckStmt = $pdo->prepare("SELECT arrears_id FROM arrears WHERE member_id = ?");
        $arrearsCheckStmt->execute([$memberId]);
        $arrearsExists = $arrearsCheckStmt->fetch();

        if ($arrearsExists) {
            // Update existing arrears
            $arrearsStmt = $pdo->prepare("
                UPDATE arrears 
                SET arrears_amount = ? 
                WHERE member_id = ?
            ");
            $arrearsStmt->execute([$arrearsAmount, $memberId]);
        } else if ($arrearsAmount > 0) {
            // Insert new arrears record
            $arrearsStmt = $pdo->prepare("
                INSERT INTO arrears (transaction_id, member_id, arrears_amount) 
                VALUES (0, ?, ?)
            ");
            $arrearsStmt->execute([$memberId, $arrearsAmount]);
        }

        // Update meter reading - get the latest reading first
        $latestReadingStmt = $pdo->prepare("
            SELECT reading_id FROM meter_reading 
            WHERE member_id = ? 
            ORDER BY reading_date DESC 
            LIMIT 1
        ");
        $latestReadingStmt->execute([$memberId]);
        $latestReading = $latestReadingStmt->fetch();

        if ($latestReading) {
            // Update latest reading
            $readingStmt = $pdo->prepare("
                UPDATE meter_reading 
                SET current_reading = ?, billing_month = ?, arrears_amount = ?, reading_date = NOW() 
                WHERE reading_id = ?
            ");
            $readingStmt->execute([
                $_POST['currentReading'],
                $_POST['lastBillingMonth'] ?? date('F Y'),
                $arrearsAmount,
                $latestReading['reading_id']
            ]);

            // Mirror arrears into total_amount_due
            $updTotal = $pdo->prepare("UPDATE meter_reading SET total_amount_due = arrears_amount WHERE reading_id = ?");
            $updTotal->execute([$latestReading['reading_id']]);
        } else {
            // Insert new reading if none exists
            $readingId = generateUniqueReadingId($pdo);
            $readingStmt = $pdo->prepare("
                INSERT INTO meter_reading (reading_id, user_id, member_id, reading_date, time, previous_reading, current_reading, total_usage, current_charges, arrears_amount, total_amount_due, due_date, disconnection_date, billing_month, status, arrears_processed) 
                VALUES (?, 0, ?, NOW(), NOW(), 0, ?, 0, 0, ?, 0, DATE_ADD(NOW(), INTERVAL 30 DAY), DATE_ADD(NOW(), INTERVAL 45 DAY), ?, 'Not Paid', 0)
            ");
            $readingStmt->execute([
                $readingId,
                $memberId,
                $_POST['currentReading'],
                $arrearsAmount,
                $_POST['lastBillingMonth'] ?? date('F Y')
            ]);

            // Mirror arrears into total_amount_due
            $updTotal = $pdo->prepare("UPDATE meter_reading SET total_amount_due = arrears_amount WHERE reading_id = ?");
            $updTotal->execute([$readingId]);
        }

        $pdo->commit();

        $fullName = $_POST['firstName'] . ' ' . $_POST['lastName'];
        echo json_encode([
            'success' => true,
            'message' => "Member '$fullName' has been successfully updated."
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error updating member: ' . $e->getMessage()]);
    }
}

function deleteMember($pdo, $memberId)
{
    try {
        // Start transaction
        $pdo->beginTransaction();

        // Get member name for confirmation message
        $nameStmt = $pdo->prepare("SELECT first_name, last_name FROM members WHERE member_id = ?");
        $nameStmt->execute([$memberId]);
        $member = $nameStmt->fetch();

        if (!$member) {
            echo json_encode(['success' => false, 'message' => 'Member not found']);
            return;
        }

        // Delete related records first (foreign key constraints)
        $pdo->prepare("DELETE FROM arrears WHERE member_id = ?")->execute([$memberId]);
        $pdo->prepare("DELETE FROM meter_reading WHERE member_id = ?")->execute([$memberId]);
        $pdo->prepare("DELETE FROM pending WHERE member_id = ?")->execute([$memberId]);
        $pdo->prepare("DELETE FROM history WHERE member_id = ?")->execute([$memberId]);

        // Delete the member
        $stmt = $pdo->prepare("DELETE FROM members WHERE member_id = ?");
        $stmt->execute([$memberId]);

        $pdo->commit();

        $fullName = $member['first_name'] . ' ' . $member['last_name'];
        echo json_encode([
            'success' => true,
            'message' => "Member '$fullName' and all related data have been successfully deleted."
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error deleting member: ' . $e->getMessage()]);
    }
}

function getMember($pdo, $memberId)
{
    try {
        $stmt = $pdo->prepare("
            SELECT 
                m.*,
                COALESCE(a.arrears_amount, 0) as arrears_amount,
                mr.current_reading as current_reading,
                mr.billing_month
            FROM members m
            LEFT JOIN arrears a ON m.member_id = a.member_id
            LEFT JOIN (
                SELECT member_id, current_reading, billing_month,
                       ROW_NUMBER() OVER (PARTITION BY member_id ORDER BY reading_date DESC) as rn
                FROM meter_reading
            ) mr ON m.member_id = mr.member_id AND mr.rn = 1
            WHERE m.member_id = ?
        ");

        $stmt->execute([$memberId]);
        $member = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($member) {
            echo json_encode(['success' => true, 'member' => $member]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Member not found']);
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error fetching member: ' . $e->getMessage()]);
    }
}

function getMembers($pdo)
{
    try {
        $stmt = $pdo->prepare("
            SELECT 
                m.*,
                COALESCE(a.arrears_amount, 0) as arrears_amount,
                mr.current_reading as current_reading,
                mr.billing_month
            FROM members m
            LEFT JOIN arrears a ON m.member_id = a.member_id
            LEFT JOIN (
                SELECT member_id, current_reading, billing_month,
                       ROW_NUMBER() OVER (PARTITION BY member_id ORDER BY reading_date DESC) as rn
                FROM meter_reading
            ) mr ON m.member_id = mr.member_id AND mr.rn = 1
            ORDER BY m.id DESC
            
        ");

        $stmt->execute();
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'members' => $members]);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error fetching members: ' . $e->getMessage()]);
    }
}

function getMemberCount($pdo)
{
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM members");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'count' => $result['count']]);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error getting member count: ' . $e->getMessage()]);
    }
}

function generateUniqueMemberId($pdo)
{
    do {
        $memberId = rand(100000000, 999999999);
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM members WHERE member_id = ?");
        $stmt->execute([$memberId]);
        $exists = $stmt->fetchColumn() > 0;
    } while ($exists);

    return $memberId;
}

function generateUniqueReadingId($pdo)
{
    do {
        $readingId = rand(100000000, 999999999);
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM meter_reading WHERE reading_id = ?");
        $stmt->execute([$readingId]);
        $exists = $stmt->fetchColumn() > 0;
    } while ($exists);

    return $readingId;
}
?>