<?php
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


#confrim
$app->get('/zones-details', function (Request $request, Response $response, $args) use ($pdo) {
    // Prepare the SQL statement
    $stmt = $pdo->prepare('SELECT zone_id, zone_name, zone_info, booth_count FROM Zones');

    // Execute the statement
    $stmt->execute();

    // Fetch all the zone data
    $zones = $stmt->fetchAll();

    if ($zones) {
        $response->getBody()->write(json_encode($zones));
        return $response->withHeader('Content-Type', 'application/json');
    } else {
        $response->getBody()->write("No zones found.");
        return $response->withStatus(404);
    }
});


#confrim
$app->delete('/deleteZone/{zone_id}', function (Request $request, Response $response, $args) use ($pdo) {
    $zone_id = $args['zone_id'];

    $stmt = $pdo->prepare('DELETE FROM Zones WHERE zone_id = ?');
    $stmt->execute([$zone_id]);

    $response->getBody()->write("Zone deleted successfully.");
    return $response;
});

#confirm
$app->post('/addZone', function (Request $request, Response $response) use ($pdo) {
    $data = $request->getParsedBody();
    $zone_id = $data['zone_id'];
    $zone_name = $data['zone_name'];
    $zone_info = $data['zone_info'];
    $booth_count = $data['booth_count'];

    // Check if all fields are provided
    if (empty($zone_id) || empty($zone_name) || empty($zone_info) || empty($booth_count)) {
        $response->getBody()->write("All fields must be provided.");
        return $response->withStatus(400);
    }

    // Prepare the SQL statement
    $stmt = $pdo->prepare('INSERT INTO Zones (zone_id, zone_name, zone_info, booth_count) VALUES (?, ?, ?, ?)');

    // Execute the statement with the zone data
    $stmt->execute([$zone_id, $zone_name, $zone_info, $booth_count]);

    $response->getBody()->write("Zone added successfully.");
    return $response->withStatus(201);
});


#confirm
$app->post('/updateZone/{zone_id}', function (Request $request, Response $response, array $args) use ($pdo) {
    $zone_id = $args['zone_id'];
    $data = $request->getParsedBody();

    // Get the data from the form
    $zone_name = $data['zone_name'] ?? null;
    $zone_info = $data['zone_info'] ?? null;
    $booth_count = $data['booth_count'] ?? null;

    // Check if all fields are provided
    if (empty($zone_name) || empty($zone_info) || empty($booth_count)) {
        $response->getBody()->write("All fields must be provided.");
        return $response->withStatus(400);
    }

    // Prepare the SQL statement
    $stmt = $pdo->prepare('UPDATE Zones SET zone_name = ?, zone_info = ?, booth_count = ? WHERE zone_id = ?');

    // Execute the statement with the zone data
    $stmt->execute([$zone_name, $zone_info, $booth_count, $zone_id]);

    $response->getBody()->write("Zone updated successfully.");
    return $response;
});




?>
