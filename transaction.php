<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: index.php');
    exit();
}

// Fetch all transactions
$sql = "SELECT t.transaction_id, u.username, b.title, t.action, t.transaction_date 
        FROM transactions t 
        JOIN users u ON t.user_id = u.user_id 
        JOIN books b ON t.book_id = b.book_id 
        ORDER BY t.transaction_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Transactions</title>
</head>
<body>
    <h2>All Transactions</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>User</th>
            <th>Book</th>
            <th>Action</th>
            <th>Date</th>
        </tr>
        <?php while ($transaction = $result->fetch_assoc()) { ?>
        <tr>
            <td><?php echo $transaction['transaction_id']; ?></td>
            <td><?php echo $transaction['username']; ?></td>
            <td><?php echo $transaction['title']; ?></td>
            <td><?php echo ucfirst($transaction['action']); ?></td>
            <td><?php echo $transaction['transaction_date']; ?></td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>
