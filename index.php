<?php
require __DIR__ . '/vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

$app = AppFactory::create();
$app->setBasePath("/php");

require __DIR__ . '/api/db.php';
require __DIR__ . '/api/member.php';
require __DIR__ . '/api/zone.php';
require __DIR__ . '/api/booth.php';
require __DIR__ . '/api/bookings.php';
require __DIR__ . '/api/event.php';


$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Welcome To Home Page ^_____^ ");
    return $response;
});


#confirm
$app->post('/addbookings', function (Request $request, Response $response) use ($pdo) {
    $data = $request->getParsedBody();

    $stmt = $pdo->prepare('INSERT INTO bookings (member_id, booth_id, booking_date, payment_date, price, slip_path, booking_status, event_id,zone_id,status) VALUES (?, ?, ?, ?, ?, ?, ?, ?,?,?)');
    $stmt->execute([$data['member_id'], $data['booth_id'], $data['booking_date'], $data['payment_date'], $data['price'], $data['slip_path'], $data['booking_status'], $data['event_id'], $data['zone_id'], $data['status']]);

    $response->getBody()->write("Booth added successfully to bookings.");
    return $response;
});


$app->run();
