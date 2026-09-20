<?php
// Exercises the PostgreSQL result/statement adapter and the main shop flow
// without requiring access to a user's Supabase project.
require_once __DIR__ . '/../model/mydb.php';

class SqlitePgConnection extends PgConnection {
    public function recordInsertId(): void {
        $this->insert_id = (int)$this->pdo()->lastInsertId();
    }
}

function check($condition, $message): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$pdo = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec('PRAGMA foreign_keys = ON');
$pdo->exec("CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, email TEXT UNIQUE,
    password_hash TEXT, role TEXT, address TEXT, phone TEXT, profile_picture TEXT, created_at TEXT DEFAULT CURRENT_TIMESTAMP);
    CREATE TABLE categories (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, category_type TEXT);
    CREATE TABLE medicines (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, category_id INTEGER,
    vendor_name TEXT, price NUMERIC, availability INTEGER, description TEXT, image_path TEXT);
    CREATE TABLE cart (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, medicine_id INTEGER,
    quantity INTEGER, added_at TEXT DEFAULT CURRENT_TIMESTAMP);
    CREATE TABLE orders (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, total_amount NUMERIC,
    shipping_address TEXT, status TEXT, payment_method TEXT, order_date TEXT DEFAULT CURRENT_TIMESTAMP);
    CREATE TABLE order_items (id INTEGER PRIMARY KEY AUTOINCREMENT, order_id INTEGER, medicine_id INTEGER,
    quantity INTEGER, unit_price NUMERIC);
    CREATE TABLE payments (id INTEGER PRIMARY KEY AUTOINCREMENT, order_id INTEGER, amount NUMERIC,
    payment_method TEXT, transaction_id TEXT, payment_date TEXT DEFAULT CURRENT_TIMESTAMP);
");

$conn = new SqlitePgConnection($pdo);
$db = new MyDB();
check($db->createUser('Test User', 'test@example.com', password_hash('testpass123', PASSWORD_DEFAULT),
    'customer', 'Dhaka', '01700000000', '', $conn), 'createUser failed');
$user = $db->getUserByEmail('test@example.com', $conn)->fetch_assoc();
check($user && $user['role'] === 'customer', 'getUserByEmail failed');
check($db->emailExists('TEST@example.com', $conn)->num_rows === 1, 'email lookup is case-sensitive');
$userId = (int)$user['id'];
check($db->createCategory('Tablet', 'solid', $conn), 'createCategory failed');
$categoryId = (int)$db->getCategories($conn)->fetch_assoc()['id'];
check($db->createMedicine('Napa', $categoryId, 'Beximco', 12.5, 10, 'Tablet', '', $conn),
    'createMedicine failed');
$medicine = $db->searchMedicines('nap', '', '', 'solid', $conn)->fetch_assoc();
check($medicine && $medicine['name'] === 'Napa', 'searchMedicines failed');
$medicineId = (int)$medicine['id'];
check($db->addToCart($userId, $medicineId, 2, $conn), 'addToCart failed');
check($db->getCartCount($userId, $conn) === 2, 'getCartCount failed');
$orderId = $db->createOrder($userId, 25, 'Dhaka', 'Cash on Delivery', $conn);
check($orderId > 0, 'createOrder failed');
check($db->decreaseStock($medicineId, 2, $conn), 'decreaseStock failed');
check(!$db->decreaseStock($medicineId, 100, $conn), 'out-of-stock update succeeded');
check($db->createOrderItem($orderId, $medicineId, 2, 12.5, $conn), 'createOrderItem failed');
check($db->createPayment($orderId, 25, 'Cash on Delivery', 'TXNTEST', $conn), 'createPayment failed');
check($db->getCustomerOrders($userId, $conn)->num_rows === 1, 'getCustomerOrders failed');
check($db->searchCustomerOrders($userId, 'pending', '', '', 'Napa', $conn)->num_rows === 1,
    'searchCustomerOrders failed');
check($db->cancelCustomerOrder($orderId, $userId, $conn), 'cancelCustomerOrder failed');
check((int)$db->getMedicineById($medicineId, $conn)->fetch_assoc()['availability'] === 10,
    'cancel did not restore stock');
check($db->deleteUserPayments($userId, $conn), 'deleteUserPayments failed');
check($db->deleteUserOrderItems($userId, $conn), 'deleteUserOrderItems failed');
check($db->deleteUserOrders($userId, $conn), 'deleteUserOrders failed');
check($db->deleteUserCart($userId, $conn), 'deleteUserCart failed');
check($db->deleteUser($userId, $conn), 'deleteUser failed');
echo "PostgreSQL compatibility flow OK\n";
