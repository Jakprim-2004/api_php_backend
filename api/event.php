<?php
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


#confirm
$app->post('/addEvent', function (Request $request, Response $response) use ($pdo) {
    $data = $request->getParsedBody();

    $stmt = $pdo->prepare('INSERT INTO Events (event_name, start_date, end_date) VALUES (?, ?, ?)');
    $stmt->execute([$data['event_name'], $data['start_date'], $data['end_date']]);

    $response->getBody()->write("Event added successfully.");
    return $response;
});


#confirm
$app->post('/updateEvent/{event_id}', function (Request $request, Response $response, array $args) use ($pdo) {
    $event_id = $args['event_id'];
    $data = $request->getParsedBody();

    // Retrieve data from the request body
    $event_name = $data['event_name'] ?? null;
    $start_date = $data['start_date'] ?? null;
    $end_date = $data['end_date'] ?? null;

    // Check if all required fields are provided
    if (empty($event_name) || empty($start_date) || empty($end_date)) {
        $response->getBody()->write("All fields must be provided.");
        return $response->withStatus(400);
    }

    // Prepare the SQL statement
    $stmt = $pdo->prepare('UPDATE Events SET event_name = ?, start_date = ?, end_date = ? WHERE event_id = ?');

    // Execute the SQL statement with the event data
    $stmt->execute([$event_name, $start_date, $end_date, $event_id]);

    // Check if the update was successful
    if ($stmt->rowCount() === 0) {
        $response->getBody()->write("No event found with ID {$event_id}.");
        return $response->withStatus(404);
    }

    // Return a success message
    $response->getBody()->write("Event updated successfully.");
    return $response;
});



?>
