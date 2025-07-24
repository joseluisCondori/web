<?php
session_start();
require 'db_connection.php'; // Asegúrate de tener un archivo para la conexión a la base de datos

// Verificar el token CSRF
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Error: CSRF token inválido.");
}

// Obtener datos del formulario
$firstName = $_POST['firstName'];
$lastName = $_POST['lastName'];
$address = $_POST['address'];
$city = $_POST['city'];
$region = $_POST['region'];
$postalCode = $_POST['postalCode'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$notes = $_POST['notes'];
$cartJson = $_POST['cart_json'];
$paymentMethod = $_POST['payment'];

// Decodificar el carrito
$cart = json_decode($cartJson, true);

// Validar datos
if (empty($firstName) || empty($lastName) || empty($address) || empty($city) || empty($region) || empty($email) || empty($phone) || empty($cart)) {
    die("Error: Todos los campos son obligatorios.");
}

// Calcular totales
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$shippingCost = $subtotal > 200 ? 0 : 12.00; // Envío gratis > S/200
$discount = $subtotal > 150 ? 10.00 : 0.00; // Descuento de S/10 > S/150
$total = $subtotal + $shippingCost - $discount;

// Insertar cliente
$stmt = $conn->prepare("INSERT INTO customers (email, first_name, last_name, phone, address, city, region, postal_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssssss", $email, $firstName, $lastName, $phone, $address, $city, $region, $postalCode);
$stmt->execute();
$customerId = $stmt->insert_id; // Obtener el ID del cliente insertado
$stmt->close();

// Insertar pedido
$orderNumber = 'ORD-' . strtoupper(uniqid());
$stmt = $conn->prepare("INSERT INTO orders (order_number, customer_id, shipping_address, shipping_city, shipping_region, shipping_postal_code, shipping_notes, subtotal, shipping_cost, discount, total, payment_method) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sisssssdidds", $orderNumber, $customerId, $address, $city, $region, $postalCode, $notes, $subtotal, $shippingCost, $discount, $total, $paymentMethod);
$stmt->execute();
$orderId = $stmt->insert_id; // Obtener el ID del pedido insertado
$stmt->close();

// Insertar items del pedido
foreach ($cart as $item) {
    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiid", $orderId, $item['id'], $item['quantity'], $item['price']);
    $stmt->execute();
    $stmt->close();
}

// Cerrar conexión
$conn->close();

// Redirigir a la página de confirmación
header("Location: ../confirmacion.html?orderId=$orderNumber");
exit();
?>
