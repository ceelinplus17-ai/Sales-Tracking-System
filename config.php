<?php
// Shared setup: session, database connection, product list, helpers.
session_start();

// Shop's yarn & craft catalogue
$products = [
    "Cotton Yarn Skein"      => 150.00,
    "Wool Blend Worsted"     => 260.00,
    "Chunky Merino Yarn"     => 420.00,
    "Crochet Hook Set"       => 350.00,
    "Knitting Needle Set"    => 390.00,
    "Amigurumi Starter Kit"  => 680.00,
    "Stitch Marker Pack"     => 90.00,
    "Pattern Booklet"        => 120.00,
];

function db() {
    static $conn = null;
    if ($conn) return $conn;
    mysqli_report(MYSQLI_REPORT_OFF);
    $conn = @mysqli_connect('localhost', 'root', '', 'yarnshop');
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error() . " — did you import database.sql?");
    }
    mysqli_set_charset($conn, 'utf8mb4');
    return $conn;
}

function require_login() {
    if (empty($_SESSION['full_name'])) {
        header('Location: index.php');
        exit;
    }
}

function e($v)     { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function peso($n)  { return '₱' . number_format((float)$n, 2); }
function remark($status) { return ($status === 'Paid') ? 'Paid' : 'Pending'; }

// Validates the add / update form. Returns [errors[], cleanData[]].
function validate_sale($post, $products) {
    $errors = [];
    $d = [
        'customer_name'  => trim($post['customer_name'] ?? ''),
        'product'        => $post['product'] ?? '',
        'quantity'       => (int)($post['quantity'] ?? 0),
        'unit_price'     => (float)($post['unit_price'] ?? 0),
        'payment_status' => $post['payment_status'] ?? 'Paid',
        'sale_date'      => $post['sale_date'] ?? '',
    ];
    if ($d['customer_name'] === '')              $errors[] = 'Enter the customer name.';
    if (!isset($products[$d['product']]))        $errors[] = 'Choose a product from the list.';
    if ($d['quantity'] < 1)                      $errors[] = 'Quantity must be at least 1.';
    if ($d['unit_price'] <= 0)                   $errors[] = 'Unit price must be more than 0.';
    if (!in_array($d['payment_status'], ['Paid','Pending'], true)) $errors[] = 'Choose Paid or Pending.';
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $d['sale_date'])) $errors[] = 'Pick a sale date.';
    return [$errors, $d];
}
