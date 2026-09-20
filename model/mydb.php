<?php
include_once __DIR__ . "/database.php";
include_once __DIR__ . "/pg_compat.php";

class MyDB {

    function createConn(){
        if (getenv('DB_DRIVER') === 'pgsql') {
            $host = getenv('DB_HOST');
            $user = getenv('DB_USER');
            $password = getenv('DB_PASS');
            $port = getenv('DB_PORT') ?: '5432';
            $name = getenv('DB_NAME') ?: 'postgres';
            if (!$host || !$user || $password === false) {
                throw new RuntimeException('PostgreSQL connection variables are missing.');
            }
            $dsn = "pgsql:host={$host};port={$port};dbname={$name};sslmode=require";
            $pdo = new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => true,
            ]);
            return new PgConnection($pdo);
        }
        return new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    }

    

    function createUser($name, $email, $passwordHash, $role, $address, $phone, $profilePicture, $conn){
        $sql  = "INSERT INTO users (name, email, password_hash, role, address, phone, profile_picture) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssss", $name, $email, $passwordHash, $role, $address, $phone, $profilePicture);
        return $stmt->execute();
    }

    function emailExists($email, $conn){
        $sql  = "SELECT id FROM users WHERE LOWER(email) = LOWER(?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result();
    }

    function emailExistsForOtherUser($email, $userId, $conn){
        $sql  = "SELECT id FROM users WHERE LOWER(email) = LOWER(?) AND id <> ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $email, $userId);
        $stmt->execute();
        return $stmt->get_result();
    }

    function getUserByEmail($email, $conn){
        $sql  = "SELECT * FROM users WHERE LOWER(email) = LOWER(?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result();
    }

    function getUserById($id, $conn){
        $sql  = "SELECT * FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result();
    }

    function updateProfile($id, $name, $email, $address, $phone, $profilePicture, $conn){
        $sql  = "UPDATE users SET name = ?, email = ?, address = ?, phone = ?, profile_picture = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $name, $email, $address, $phone, $profilePicture, $id);
        return $stmt->execute();
    }

    function updatePassword($id, $passwordHash, $conn){
        $sql  = "UPDATE users SET password_hash = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $passwordHash, $id);
        return $stmt->execute();
    }

    function getCategories($conn){
        $sql = "SELECT * FROM categories ORDER BY category_type, name";
        return $conn->query($sql);
    }

    function getVendors($conn){
        $sql = "SELECT DISTINCT vendor_name FROM medicines WHERE vendor_name <> '' ORDER BY vendor_name";
        return $conn->query($sql);
    }

    function getMedicines($categoryId, $categoryType, $conn){
        $sql  = "SELECT medicines.*, categories.name AS category_name, categories.category_type
                 FROM medicines
                 LEFT JOIN categories ON medicines.category_id = categories.id
                 WHERE (? = 0 OR medicines.category_id = ?)
                 AND (? = '' OR categories.category_type = ?)
                 ORDER BY medicines.name";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiss", $categoryId, $categoryId, $categoryType, $categoryType);
        $stmt->execute();
        return $stmt->get_result();
    }

    function searchMedicines($q, $vendor, $genre, $categoryType, $conn){
        $search = "%" . $q . "%";
        $sql    = "SELECT medicines.id, medicines.name, medicines.vendor_name, medicines.price,
                          medicines.availability, medicines.description, medicines.image_path,
                          categories.name AS category_name, categories.category_type
                   FROM medicines
                   LEFT JOIN categories ON medicines.category_id = categories.id
                   WHERE LOWER(medicines.name) LIKE LOWER(?)
                   AND (? = '' OR medicines.vendor_name = ?)
                   AND (? = '' OR categories.name = ?)
                   AND (? = '' OR categories.category_type = ?)
                   ORDER BY medicines.name";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssss", $search, $vendor, $vendor, $genre, $genre, $categoryType, $categoryType);
        $stmt->execute();
        return $stmt->get_result();
    }

    

    

    function getDashboardCounts($conn){
        $counts = array();

        $row = $conn->query("SELECT COUNT(*) AS cnt FROM medicines")->fetch_assoc();
        $counts["medicines"] = (int)$row["cnt"];

        $row = $conn->query("SELECT COUNT(*) AS cnt FROM categories")->fetch_assoc();
        $counts["categories"] = (int)$row["cnt"];

        $row = $conn->query("SELECT COUNT(*) AS cnt FROM users WHERE role = 'customer'")->fetch_assoc();
        $counts["customers"] = (int)$row["cnt"];

        $row = $conn->query("SELECT COUNT(*) AS cnt FROM orders WHERE status = 'pending'")->fetch_assoc();
        $counts["pending_orders"] = (int)$row["cnt"];

        return $counts;
    }

    

    function getCategoryById($id, $conn){
        $sql  = "SELECT * FROM categories WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result();
    }

    function createCategory($name, $type, $conn){
        $sql  = "INSERT INTO categories (name, category_type) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $name, $type);
        return $stmt->execute();
    }

    function updateCategory($id, $name, $type, $conn){
        $sql  = "UPDATE categories SET name = ?, category_type = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $name, $type, $id);
        return $stmt->execute();
    }

    function deleteCategory($id, $conn){
        $sql  = "DELETE FROM categories WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    function medicinesExistInCategory($categoryId, $conn){
        $sql  = "SELECT id FROM medicines WHERE category_id = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $categoryId);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    function categoryNameExists($name, $conn){
        $sql  = "SELECT id FROM categories WHERE LOWER(name) = LOWER(?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $name);
        $stmt->execute();
        return $stmt->get_result();
    }

    function categoryNameExistsForOther($name, $id, $conn){
        $sql  = "SELECT id FROM categories WHERE LOWER(name) = LOWER(?) AND id <> ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $name, $id);
        $stmt->execute();
        return $stmt->get_result();
    }

    

    function getAllMedicines($conn){
        $sql = "SELECT medicines.*, categories.name AS category_name, categories.category_type
                FROM medicines
                LEFT JOIN categories ON medicines.category_id = categories.id
                ORDER BY medicines.name";
        return $conn->query($sql);
    }

    function getMedicineById($id, $conn){
        $sql  = "SELECT medicines.*, categories.name AS category_name, categories.category_type
                 FROM medicines
                 LEFT JOIN categories ON medicines.category_id = categories.id
                 WHERE medicines.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result();
    }

    function createMedicine($name, $categoryId, $vendorName, $price, $availability, $description, $imagePath, $conn){
        $sql  = "INSERT INTO medicines (name, category_id, vendor_name, price, availability, description, image_path)
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sisdiss", $name, $categoryId, $vendorName, $price, $availability, $description, $imagePath);
        return $stmt->execute();
    }

    function updateMedicine($id, $name, $categoryId, $vendorName, $price, $availability, $description, $imagePath, $conn){
        $sql  = "UPDATE medicines SET name = ?, category_id = ?, vendor_name = ?, price = ?,
                 availability = ?, description = ?, image_path = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sisdissi", $name, $categoryId, $vendorName, $price, $availability, $description, $imagePath, $id);
        return $stmt->execute();
    }

    function deleteMedicine($id, $conn){
        $sql  = "DELETE FROM medicines WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    function medicineInAnyOrder($medicineId, $conn){
        $sql  = "SELECT oi.id FROM order_items oi
                 JOIN orders o ON oi.order_id = o.id
                 WHERE oi.medicine_id = ?
                 LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $medicineId);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    

    function getAllCustomers($conn){
        $sql = "SELECT id, name, email, phone, address, created_at FROM users
                WHERE role = 'customer' ORDER BY created_at DESC";
        return $conn->query($sql);
    }

    function deleteUserCart($userId, $conn){
        $sql  = "DELETE FROM cart WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }

    function deleteUserOrderItems($userId, $conn){
        $sql  = "DELETE FROM order_items WHERE order_id IN
                 (SELECT id FROM orders WHERE user_id = ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }

    function deleteUserPayments($userId, $conn){
        $sql  = "DELETE FROM payments WHERE order_id IN
                 (SELECT id FROM orders WHERE user_id = ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }

    function deleteUserOrders($userId, $conn){
        $sql  = "DELETE FROM orders WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }

    function deleteUser($userId, $conn){
        $sql  = "DELETE FROM users WHERE id = ? AND role = 'customer'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }

    

    function getAllOrders($conn){
        $sql = "SELECT orders.*, users.name AS customer_name, users.email AS customer_email
                FROM orders
                JOIN users ON orders.user_id = users.id
                ORDER BY orders.order_date DESC";
        return $conn->query($sql);
    }

    function getOrderById($orderId, $conn){
        $sql  = "SELECT orders.*, users.name AS customer_name, users.email AS customer_email
                 FROM orders
                 JOIN users ON orders.user_id = users.id
                 WHERE orders.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        return $stmt->get_result();
    }

    function updateOrderStatus($orderId, $status, $conn){
        $sql  = "UPDATE orders SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status, $orderId);
        return $stmt->execute();
    }

    function getOrderItems($orderId, $conn){
        $sql  = "SELECT order_items.*, medicines.name AS medicine_name, medicines.vendor_name
                 FROM order_items
                 JOIN medicines ON order_items.medicine_id = medicines.id
                 WHERE order_items.order_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        return $stmt->get_result();
    }

    

    function getAcceptedOrders($conn){
        $sql = "SELECT orders.*, users.name AS customer_name, users.email AS customer_email,
                       users.phone AS customer_phone
                FROM orders
                JOIN users ON orders.user_id = users.id
                WHERE orders.status = 'accepted'
                ORDER BY orders.order_date DESC";
        return $conn->query($sql);
    }

    

    

    function addToCart($userId, $medicineId, $quantity, $conn){
        $sql  = "SELECT id, quantity FROM cart WHERE user_id = ? AND medicine_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $userId, $medicineId);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0){
            $row = $result->fetch_assoc();
            $newQty = $row["quantity"] + $quantity;
            $sql2  = "UPDATE cart SET quantity = ? WHERE id = ?";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bind_param("ii", $newQty, $row["id"]);
            return $stmt2->execute();
        } else {
            $sql2  = "INSERT INTO cart (user_id, medicine_id, quantity) VALUES (?, ?, ?)";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bind_param("iii", $userId, $medicineId, $quantity);
            return $stmt2->execute();
        }
    }

    function getCartItems($userId, $conn){
        $sql  = "SELECT cart.id, cart.medicine_id, cart.quantity,
                        medicines.name, medicines.vendor_name, medicines.price,
                        medicines.availability, medicines.image_path
                 FROM cart
                 JOIN medicines ON cart.medicine_id = medicines.id
                 WHERE cart.user_id = ?
                 ORDER BY cart.added_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result();
    }

    function getCartCount($userId, $conn){
        $sql  = "SELECT COALESCE(SUM(quantity), 0) AS total FROM cart WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return (int)$row["total"];
    }

    function updateCartQuantity($cartId, $userId, $quantity, $conn){
        $sql  = "UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $quantity, $cartId, $userId);
        return $stmt->execute();
    }

    function removeCartItem($cartId, $userId, $conn){
        $sql  = "DELETE FROM cart WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $cartId, $userId);
        return $stmt->execute();
    }

    function clearCart($userId, $conn){
        $sql  = "DELETE FROM cart WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }

    function getCartItemById($cartId, $userId, $conn){
        $sql  = "SELECT cart.*, medicines.price, medicines.availability
                 FROM cart
                 JOIN medicines ON cart.medicine_id = medicines.id
                 WHERE cart.id = ? AND cart.user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $cartId, $userId);
        $stmt->execute();
        return $stmt->get_result();
    }

    

    function createOrder($userId, $totalAmount, $shippingAddress, $paymentMethod, $conn){
        $sql  = "INSERT INTO orders (user_id, total_amount, shipping_address, status, payment_method)
                 VALUES (?, ?, ?, 'pending', ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("idss", $userId, $totalAmount, $shippingAddress, $paymentMethod);
        if($stmt->execute()){
            return $conn->insert_id;
        }
        return false;
    }

    function createOrderItem($orderId, $medicineId, $quantity, $unitPrice, $conn){
        $sql  = "INSERT INTO order_items (order_id, medicine_id, quantity, unit_price)
                 VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiid", $orderId, $medicineId, $quantity, $unitPrice);
        return $stmt->execute();
    }

    function createPayment($orderId, $amount, $paymentMethod, $transactionId, $conn){
        $sql  = "INSERT INTO payments (order_id, amount, payment_method, transaction_id)
                 VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("idss", $orderId, $amount, $paymentMethod, $transactionId);
        return $stmt->execute();
    }

    function getOrderWithItems($orderId, $userId, $conn){
        $sql  = "SELECT orders.*, users.name AS customer_name, users.email AS customer_email
                 FROM orders
                 JOIN users ON orders.user_id = users.id
                 WHERE orders.id = ? AND orders.user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $orderId, $userId);
        $stmt->execute();
        return $stmt->get_result();
    }

    function decreaseStock($medicineId, $quantity, $conn){
        $sql  = "UPDATE medicines SET availability = availability - ? WHERE id = ? AND availability >= ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $quantity, $medicineId, $quantity);
        return $stmt->execute() && $stmt->affected_rows === 1;
    }

    function increaseStock($medicineId, $quantity, $conn){
        $sql  = "UPDATE medicines SET availability = availability + ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $quantity, $medicineId);
        return $stmt->execute();
    }

    

    function getCustomerOrders($userId, $conn){
        $sql  = "SELECT id, total_amount, shipping_address, status, payment_method, order_date
                 FROM orders WHERE user_id = ? ORDER BY order_date DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result();
    }

    function searchCustomerOrders($userId, $status, $from, $to, $q, $conn){
        $sql    = "SELECT DISTINCT orders.id, orders.total_amount, orders.shipping_address,
                          orders.status, orders.payment_method, orders.order_date
                   FROM orders
                   LEFT JOIN order_items oi ON orders.id = oi.order_id
                   LEFT JOIN medicines m ON oi.medicine_id = m.id
                   WHERE orders.user_id = ?";
        $params = array($userId);
        $types  = "i";

        if($status != "" && in_array($status, array("pending", "accepted", "rejected", "cancelled"))){
            $sql     .= " AND orders.status = ?";
            $params[] = $status;
            $types   .= "s";
        }
        if($from != ""){
            $sql     .= " AND DATE(orders.order_date) >= ?";
            $params[] = $from;
            $types   .= "s";
        }
        if($to != ""){
            $sql     .= " AND DATE(orders.order_date) <= ?";
            $params[] = $to;
            $types   .= "s";
        }
        if($q != ""){
            $sql     .= " AND LOWER(m.name) LIKE LOWER(?)";
            $params[] = "%" . $q . "%";
            $types   .= "s";
        }

        $sql .= " ORDER BY orders.order_date DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result();
    }

    function getPaymentByOrderId($orderId, $conn){
        $sql  = "SELECT * FROM payments WHERE order_id = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        return $stmt->get_result();
    }

    function cancelCustomerOrder($orderId, $userId, $conn){
        $orderResult = $this->getOrderWithItems($orderId, $userId, $conn);
        if($orderResult->num_rows == 0){
            return false;
        }
        $order = $orderResult->fetch_assoc();
        if($order["status"] !== "pending"){
            return false;
        }

        $sql  = "UPDATE orders SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status = 'pending'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $orderId, $userId);
        if(!$stmt->execute() || $stmt->affected_rows == 0){
            return false;
        }

        $orderItems = $this->getOrderItems($orderId, $conn);
        while($item = $orderItems->fetch_assoc()){
            $this->increaseStock($item["medicine_id"], $item["quantity"], $conn);
        }
        return true;
    }

    function reorderItemsToCart($orderId, $userId, $conn){
        $orderResult = $this->getOrderWithItems($orderId, $userId, $conn);
        if($orderResult->num_rows == 0){
            return array("success" => false, "message" => "Order not found");
        }
        $order = $orderResult->fetch_assoc();
        if(!in_array($order["status"], array("accepted", "rejected"))){
            return array("success" => false, "message" => "Only accepted or rejected orders can be reordered");
        }

        $warnings = array();
        $added    = 0;
        $items    = $this->getOrderItems($orderId, $conn);

        while($item = $items->fetch_assoc()){
            $medResult = $this->getMedicineById($item["medicine_id"], $conn);
            if($medResult->num_rows == 0){
                $warnings[] = $item["medicine_name"] . " is no longer available";
                continue;
            }
            $medicine = $medResult->fetch_assoc();
            $qty      = (int)$item["quantity"];
            $stock    = (int)$medicine["availability"];

            if($stock <= 0){
                $warnings[] = $item["medicine_name"] . " is out of stock";
                continue;
            }
            if($qty > $stock){
                $warnings[] = $item["medicine_name"] . ": only " . $stock . " units available (requested " . $qty . ")";
                $qty = $stock;
            }

            if($this->addToCart($userId, $item["medicine_id"], $qty, $conn)){
                $added++;
            }
        }

        $cartCount = $this->getCartCount($userId, $conn);
        $message   = $added > 0 ? "Added " . $added . " item(s) to cart" : "No items could be added to cart";

        return array(
            "success"   => $added > 0,
            "message"   => $message,
            "warnings"  => $warnings,
            "added"     => $added,
            "cart_count"=> $cartCount
        );
    }

    function closeConn($conn){
        $conn->close();
    }
}
?>
