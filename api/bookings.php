<?php
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


#confirm
$app->post('/booking_formInsert', function (Request $request, Response $response, array $args) {
    $body = $request->getParsedBody();
    $pdo = $GLOBALS['pdo'];

    // Check how many booths the member has booked
    $checkStmt = $pdo->prepare("SELECT COUNT(*) AS boothtotal FROM bookings WHERE member_id = :member_id AND booking_status != 'ยกเลิกการจอง'");
    $checkStmt->execute([':member_id' => $body['member_id']]);
    $checkResult = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($checkResult['boothtotal'] < 4) {
        // Check if the booth is available for booking
        $boothStatusStmt = $pdo->prepare("SELECT booth_status FROM Booths WHERE booth_id = :booth_id");
        $boothStatusStmt->execute([':booth_id' => $body['booth_id']]);
        $boothStatusResult = $boothStatusStmt->fetch(PDO::FETCH_ASSOC);

        if ($boothStatusResult['booth_status'] === 'อยู่ระหว่างตรวจสอบ') {
            $response->getBody()->write(json_encode(["message" =>"The booth cannot be booked because it is currently under review."]));
            return $response->withStatus(400);
        }

        // Insert the booking details into the database
        $stmt = $pdo->prepare("INSERT INTO bookings (booking_code, booking_date, payment_date, zone_id, booth_id, price, seller_info, event_id, slip_path, member_id) VALUES (:booking_code, :booking_date, :payment_date, :zone_id, :booth_id, :price, :seller_info, :event_id, :slip_path, :member_id)");
        $stmt->execute([
            ':booking_code' => $body['booking_code'],
            ':booking_date' => $body['booking_date'],
            ':payment_date' => $body['payment_date'],
            ':zone_id' => $body['zone_id'],
            ':booth_id' => $body['booth_id'],
            ':price' => $body['price'],
            ':seller_info' => $body['seller_info'],
            ':event_id' => $body['event_id'],
            ':slip_path' => $body['slip_path'],
            ':member_id' => $body['member_id'],
        ]);
        $result = $stmt->rowCount();

        if ($result == 1) {
            $bstatus = $body['booking_code'];
            $status = 'จอง';
            $stmt = $pdo->prepare("UPDATE bookings SET booking_status = :status WHERE booking_code = :bstatus");
            $stmt->execute([':status' => $status, ':bstatus' => $bstatus]);
            $result = $stmt->rowCount();

            if ($result > 0) {
                $boothId = $body['booth_id'];
                $newStatus = 'อยู่ระหว่างตรวจสอบ';
                $updateStmt = $pdo->prepare("UPDATE Booths SET booth_status = :newStatus WHERE booth_id = :boothId");
                $updateStmt->execute([':newStatus' => $newStatus, ':boothId' => $boothId]);
                $updateResult = $updateStmt->rowCount();
            }
        }
        $response->getBody()->write(json_encode(["message" => "Successfully"]));
    } else {
        $response->getBody()->write(json_encode(["message" => "Meximum booking limit reached. You can only book up to 4 booths."]));
    }

    return $response->withHeader('Content-Type', 'application/json');
});


#confirm
$app->post('/booking/payment', function (Request $request, Response $response) use ($pdo) {
    $body = $request->getParsedBody();

    try {
        // ดึงวันที่เริ่มงาน
        $stmt_event_date = $pdo->prepare("SELECT start_date FROM Events WHERE event_id = (SELECT event_id FROM bookings WHERE booking_code = ?)");
        $stmt_event_date->execute([$body['booking_code']]);
        $event_date_row = $stmt_event_date->fetch(PDO::FETCH_ASSOC);

        if (!$event_date_row) {
            $response->getBody()->write(json_encode(["message" => "Event date not found"]));
            return $response->withHeader('Content-Type', 'application/json');
        }

        // ตรวจสอบว่าเป็นวันที่ถูกต้องหรือไม่
        $event_date = DateTime::createFromFormat('Y-m-d', $event_date_row['start_date']);
        $booking_date = DateTime::createFromFormat('Y-m-d', $body['booking_date']);

        if (!$event_date || !$booking_date) {
            $response->getBody()->write(json_encode(["message" => "Invalid date format"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // คำนวณจำนวนวันระหว่างวันที่จัดงานและวันที่จอง
        $difference = $event_date->diff($booking_date)->days;

        // ถ้าจำนวนวันน้อยกว่าหรือเท่ากับ 5 ให้ชำระเงิน
        if ($difference <= 5) {
            $response->getBody()->write(json_encode(["message" => "Payment allowed"]));
            $stmt_payment = $pdo->prepare("UPDATE bookings SET booking_status = 'ชำระเงิน', slip_path = ?, payment_date = NOW() WHERE booking_code = ?");
            $stmt_payment->execute([$body['slip_path'], $body['booking_code']]);
            $stmt_update_booth = $pdo->prepare("UPDATE Booths SET booth_status = 'ชำระเงิน' WHERE booth_id = (SELECT booth_id FROM bookings WHERE booking_code = ?)");
            $stmt_update_booth->execute([$body['booking_code']]);
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            // ถ้าจำนวนวันมากกว่า 5 ไม่อนุญาตให้ชำระเงิน
            $response->getBody()->write(json_encode(["message" => "Payment not allowed"]));
            $stmt_cancel = $pdo->prepare("UPDATE bookings, Booths SET bookings.booking_status = 'ยกเลิกการจอง', Booths.booth_status = 'ว่าง' WHERE bookings.booking_code = ?");
            $stmt_cancel->execute([$body['booking_code']]);
            return $response->withHeader('Content-Type', 'application/json');
        }
    } catch (PDOException $e) {
        // จัดการข้อผิดพลาดของฐานข้อมูล
        $response->getBody()->write(json_encode(["error" => $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});



#confirm
$app->post('/approveBooking/{booking_code}', function (Request $request, Response $response, array $args) use ($pdo) {
    $booking_code = $args['booking_code'];

    // Start a transaction
    $pdo->beginTransaction();

    try {
        // Check if the booking is paid
        $stmt = $pdo->prepare("SELECT * FROM bookings WHERE booking_code = :booking_code AND booking_status = 'ชำระเงิน'");
        $stmt->execute(['booking_code' => $booking_code]);
        $booking = $stmt->fetch();

        if ($booking) {
            // Update the booking status to "Approved"
            $stmt = $pdo->prepare("UPDATE bookings SET booking_status = 'อนุมัติแล้ว' WHERE booking_code = :booking_code");
            $stmt->execute(['booking_code' => $booking_code]);

            // Update the booth status to "Booked"
            $stmt = $pdo->prepare("UPDATE Booths SET booth_status = 'จองแล้ว' WHERE booth_id = :booth_id");
            $stmt->execute(['booth_id' => $booking['booth_id']]);

            // Commit the transaction
            $pdo->commit();

            $response->getBody()->write('Booking approved successfully.');
        } else {
            $response->getBody()->write('No paid booking found with this ID.');
        }
    } catch (Exception $e) {
        // Roll back the transaction if something failed
        $pdo->rollBack();

        $response->getBody()->write('Error: ' . $e->getMessage());
    }

    return $response;
});


#confirm
$app->post('/report_members', function (Request $request, Response $response, array $args) use ($pdo) {
    $stmt = $pdo->prepare('SELECT first_name, last_name, phone_number, email FROM Members');
    $stmt->execute();
    $members = $stmt->fetchAll();

    if ($members) {
        $response->getBody()->write(json_encode($members));
    } else {
        $response->getBody()->write("No members found.");
        return $response->withStatus(404);
    }

    return $response->withHeader('Content-Type', 'application/json');
});


#confirm
$app->get('/unpaidBookings', function (Request $request, Response $response, array $args) use ($pdo) {
    try {
        $sql = "SELECT M.first_name, M.last_name, M.phone_number, B.booth_name, Z.zone_name
                FROM bookings BK
                JOIN Members M ON BK.member_id = M.member_id
                JOIN Booths B ON BK.booth_id = B.booth_id
                JOIN Zones Z ON BK.zone_id = Z.zone_id
                WHERE BK.booking_status = 'จอง'";

        $stmt = $pdo->query($sql);
        $unpaidBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($unpaidBookings) {
            $response->getBody()->write(json_encode($unpaidBookings));
        } else {
            $response->getBody()->write(json_encode(["message" => "No unpaid bookings found."]));
        }

        return $response->withHeader('Content-Type', 'application/json');
    } catch (PDOException $e) {
        return $response->withJson(["error" => $e->getMessage()], 500);
    }
});


#confirm
$app->get('/paidBookings', function (Request $request, Response $response, array $args) use ($pdo) {
    try {
        $sql = "SELECT M.first_name, M.last_name, M.phone_number, B.booth_name, Z.zone_name
                FROM bookings BK
                JOIN Members M ON BK.member_id = M.member_id
                JOIN Booths B ON BK.booth_id = B.booth_id
                JOIN Zones Z ON BK.zone_id = Z.zone_id
                WHERE BK.booking_status = 'ชำระเงิน'";

        $stmt = $pdo->query($sql);
        $paidBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($paidBookings) {
            $response->getBody()->write(json_encode($paidBookings));
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write(json_encode(["message" => "No paid bookings found."]));
            return $response->withHeader('Content-Type', 'application/json');
        }
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["error" => $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});


#confirm
$app->get('/pendingBookings', function (Request $request, Response $response, array $args) use ($pdo) {
    try {
        $sql = "SELECT M.first_name, M.last_name, M.phone_number, B.booth_name, Z.zone_name
                FROM bookings BK
                JOIN Members M ON BK.member_id = M.member_id
                JOIN Booths B ON BK.booth_id = B.booth_id
                JOIN Zones Z ON BK.zone_id = Z.zone_id
                WHERE B.booth_status = 'อยู่ระหว่างตรวจสอบ'";

        $stmt = $pdo->query($sql);
        $pendingBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($pendingBookings) {
            $response->getBody()->write(json_encode($pendingBookings));
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write(json_encode(["message" => "No pending bookings found."]));
            return $response->withHeader('Content-Type', 'application/json');
        }
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["error" => $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});






?>
