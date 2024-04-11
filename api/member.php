<?php
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

#confirm
$app->post('/login', function (Request $request, Response $response, $args) use ($pdo) {
    // Get the email and password from the request
    $data = $request->getParsedBody();
    $email = $data['email'];
    $password = $data['password'];

    // Prepare the SQL statement
    $stmt = $pdo->prepare('SELECT * FROM Members WHERE email = :email AND password = :password');

    // Bind the parameters
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $password);

    // Execute the statement
    $stmt->execute();

    // Fetch the member data
    $member = $stmt->fetch();

    if ($member) {
        $response->getBody()->write(json_encode($member));
        return $response->withHeader('Content-Type', 'application/json');
    } else {
        $response->getBody()->write("Invalid email or password.");
        return $response->withStatus(401);
    }
});


#confirm
$app->post('/register', function (Request $request, Response $response, $args) use ($pdo) {
    $data = $request->getParsedBody();
    $title_name = $data['title_name'];
    $first_name = $data['first_name'];
    $last_name = $data['last_name'];
    $phone_number = $data['phone_number'];
    $email = $data['email'];
    $password = $data['password'];

    // Check if all fields are filled
    if (empty($title_name) || empty($first_name) || empty($last_name) || empty($phone_number) || empty($email) || empty($password)) {
        $response->getBody()->write("All fields must be filled.");
        return $response->withStatus(400);
    }

    // Check if email already exists
    $stmt = $pdo->prepare('SELECT * FROM Members WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $response->getBody()->write("Email already exists.");
        return $response->withStatus(400);
    }

    // Prepare the SQL statement
    $stmt = $pdo->prepare('INSERT INTO Members (title_name, first_name, last_name, phone_number, email, password) VALUES (?, ?, ?, ?, ?, ?)');

    // Execute the statement with the user data
    $stmt->execute([$title_name, $first_name, $last_name, $phone_number, $email, $password]);

    $response->getBody()->write("Member registered successfully.");
    return $response->withStatus(201);
});


#confirm
$app->post('/updateMember', function (Request $request, Response $response) use ($pdo) {
    $data = $request->getParsedBody();

    // Prepare the SQL statement
    $stmt = $pdo->prepare('UPDATE Members SET title_name = ?, first_name = ?, last_name = ?, phone_number = ?, email = ?, password = ? WHERE member_id = ?');

    // Execute the SQL statement
    $stmt->execute([
        $data['title_name'],
        $data['first_name'],
        $data['last_name'],
        $data['phone_number'],
        $data['email'],
        $data['password'], // Store the password as is
        $data['member_id']
    ]);

    // Check if the update was successful
    if ($stmt->rowCount() > 0) {
        $response->getBody()->write('Member updated successfully.');
    } else {
        $response->getBody()->write('Failed to update member.');
    }

    return $response;
});

#confirm
$app->get('/booking/{member_id}', function (Request $request, Response $response, array $args) use ($pdo) {
    $member_id = $args['member_id'];

    $sql = "SELECT b.booking_code, Booths.booth_name, Zones.zone_name, b.price, b.booking_status 
            FROM bookings b 
            INNER JOIN Booths ON b.booth_id = Booths.booth_id 
            INNER JOIN Zones ON b.zone_id = Zones.zone_id 
            WHERE b.member_id = :member_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['member_id' => $member_id]);

    $bookings = $stmt->fetchAll();

    if ($bookings) {
        // สร้าง associative array เพื่อใช้เป็น JSON response
        $data = [];
        foreach ($bookings as $booking) {
            $data[] = [
                'booking_code' => $booking['booking_code'],
                'booth_name' => $booking['booth_name'],
                'zone_name' => $booking['zone_name'],
                'price' => $booking['price'],
                'booking_status' => $booking['booking_status']
            ];
        }

        // ส่งข้อมูลรายการจองในรูปแบบ JSON กลับไปยัง client
        $response->getBody()->write(json_encode($data));
    } else {
        // ถ้าไม่มีรายการจองให้ส่ง JSON response ว่าไม่พบรายการจอง
        $response->getBody()->write(json_encode(['message' => 'No bookings found for this member.']));
    }

    return $response->withHeader('Content-Type', 'application/json');
});



?>