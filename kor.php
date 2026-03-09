<?php
// kor.php - полная версия с функциями корзины

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Инициализация корзины
function initCart() {
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

// Добавление товара
function addToCart($productId, $name, $price, $image) {
    initCart();
    
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $productId) {
            $item['quantity']++;
            return true;
        }
    }
    
    $_SESSION['cart'][] = [
        'id' => $productId,
        'name' => $name,
        'price' => (float)$price,
        'image' => $image,
        'quantity' => 1
    ];
    return true;
}

// Удаление товара
function removeFromCart($productId) {
    initCart();
    $_SESSION['cart'] = array_values(array_filter($_SESSION['cart'], function($item) use ($productId) {
        return $item['id'] != $productId;
    }));
}

// Обновление количества
function updateQuantity($productId, $quantity) {
    initCart();
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $productId) {
            $item['quantity'] = max(1, min(99, (int)$quantity));
            break;
        }
    }
}

// Получение всех товаров
function getCartItems() {
    initCart();
    return $_SESSION['cart'];
}

// Получение количества товаров
function getCartCount() {
    initCart();
    $count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $count += $item['quantity'];
    }
    return $count;
}

// Получение общей суммы
function getCartTotal() {
    initCart();
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}

// Обработка AJAX запросов
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'add':
            if (isset($_POST['id'], $_POST['name'], $_POST['price'])) {
                addToCart(
                    $_POST['id'],
                    $_POST['name'],
                    $_POST['price'],
                    $_POST['image'] ?? ''
                );
                echo json_encode([
                    'success' => true,
                    'count' => getCartCount(),
                    'total' => getCartTotal()
                ]);
            }
            break;
            
        case 'remove':
            if (isset($_POST['id'])) {
                removeFromCart($_POST['id']);
                echo json_encode([
                    'success' => true,
                    'count' => getCartCount(),
                    'total' => getCartTotal()
                ]);
            }
            break;
            
        case 'update':
            if (isset($_POST['id'], $_POST['quantity'])) {
                updateQuantity($_POST['id'], $_POST['quantity']);
                echo json_encode([
                    'success' => true,
                    'total' => getCartTotal()
                ]);
            }
            break;
            
        case 'get':
            echo json_encode([
                'success' => true,
                'items' => getCartItems(),
                'count' => getCartCount(),
                'total' => getCartTotal()
            ]);
            break;
    }
    exit;
}
?>