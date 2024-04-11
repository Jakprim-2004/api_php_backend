<?php
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


#confirm
$app->get('/booths-details', function (Request $request, Response $response, $args) use ($pdo) {
    // Prepare the SQL statement
    $stmt = $pdo->prepare('SELECT booth_id, booth_name, booth_size, booth_status, booth_price FROM Booths');

    // Execute the statement
    $stmt->execute();

    // Fetch all the booth data
    $booths = $stmt->fetchAll();

    if ($booths) {
        $response->getBody()->write(json_encode($booths));
        return $response->withHeader('Content-Type', 'application/json');
    } else {
        $response->getBody()->write("No booths found.");
        return $response->withStatus(404);
    }
});

#confirm
$app->delete('/deleteBooth/{booth_id}', function (Request $request, Response $response, array $args) use ($pdo) {
    $booth_id = $args['booth_id'];

    // Prepare the SQL statement
    $stmt = $pdo->prepare('DELETE FROM Booths WHERE booth_id = ?');

    // Execute the statement with the booth_id
    $stmt->execute([$booth_id]);

    $response->getBody()->write("Booth deleted successfully.");
    return $response;
});



#confirm
$app->post('/addBooth', function (Request $request, Response $response) use ($pdo) {
    $data = $request->getParsedBody();
   
    $booth_name = $data['booth_name'] ?? '';
    $booth_size = $data['booth_size'] ?? '';
    $booth_status = $data['booth_status'] ?? '';
    $booth_price = $data['booth_price'] ?? '';
    $product = $data['product'] ?? '';

    // Check if all fields are provided
    if (empty($booth_name) || empty($booth_size) || empty($booth_status) || empty($booth_price) || empty($product)) {
        $response->getBody()->write("All fields must be provided.");
        return $response->withStatus(400);
    }

    // Prepare the SQL statement
    $stmt = $pdo->prepare('INSERT INTO Booths (booth_name, booth_size, booth_status, booth_price, product) VALUES (?, ?, ?, ?, ?)');

    // Execute the statement with the booth data
    $stmt->execute([$booth_name, $booth_size, $booth_status, $booth_price, $product]);

    $response->getBody()->write("Booth added successfully.");
    return $response;
});



#confirm
$app->post('/updateBooth/{booth_id}', function (Request $request, Response $response, array $args) use ($pdo) {    $booth_id = $args['booth_id'];
    $data = $request->getParsedBody();

    // Get the data from the form
    $booth_name = $data['booth_name'] ?? null;
    $booth_size = $data['booth_size'] ?? null;
    $booth_status = $data['booth_status'] ?? null;
    $booth_price = $data['booth_price'] ?? null;
    $product = $data['product'] ?? null;

    // Check if all fields are provided
    if (empty($booth_name) || empty($booth_size) || empty($booth_status) || empty($booth_price) || empty($product)) {
        $response->getBody()->write("All fields must be provided.");
        return $response->withStatus(400);
    }

    // Prepare the SQL statement
    $stmt = $pdo->prepare('UPDATE Booths SET booth_name = ?, booth_size = ?, booth_status = ?, booth_price = ?, product = ? WHERE booth_id = ?');

    // Execute the statement with the booth data
    $stmt->execute([$booth_name, $booth_size, $booth_status, $booth_price, $product, $booth_id]);

    $response->getBody()->write("Booth updated successfully.");
    return $response;
});


#confirm
$app->get('/bookedBooths', function (Request $request, Response $response, array $args) use ($pdo) {
    try {
        $sql = "SELECT M.first_name, M.last_name, Z.zone_name, BK.price, B.booth_name, BK.booking_status
                FROM bookings BK
                JOIN Members M ON BK.member_id = M.member_id
                JOIN Booths B ON BK.booth_id = B.booth_id
                JOIN Zones Z ON BK.zone_id = Z.zone_id";

        $stmt = $pdo->query($sql);
        $bookedBooths = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($bookedBooths) {
            $response->getBody()->write(json_encode($bookedBooths));
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write(json_encode(["message" => "No booked booths found."]));
            return $response->withHeader('Content-Type', 'application/json');
        }
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["error" => $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});


?>
