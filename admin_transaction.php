<?php
session_start(); // Start the session at the very beginning of the script

// Ensure the user is logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: index.php'); // Redirect to login page if not logged in or not admin
    exit();
}

require_once 'config.php';

// Fetch all transaction records (borrowed books) for all users
$stmt = $conn->prepare("
    SELECT u.username, b.title, br.borrow_date, br.return_date
    FROM borrow_records br
    JOIN users u ON br.user_id = u.user_id
    JOIN books b ON br.book_id = b.book_id
    ORDER BY br.borrow_date DESC
");
$stmt->execute();
$transaction_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - All Transactions</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f1f1f1;
            color: #333;
            padding: 20px;
        }

        .container {
            width: 80%;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #4CAF50;
            font-size: 2.5rem;
            text-align: center;
            margin-bottom: 20px;
        }

        h3 {
            color: #333;
            font-size: 1.8rem;
            margin-bottom: 15px;
        }

        a {
            color: #fff;
            text-decoration: none;
            background-color: #4CAF50;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 1.2rem;
            display: inline-block;
            margin: 10px 0;
            transition: background-color 0.3s ease;
        }

        a:hover {
            background-color: #45a049;
        }

        .actions {
            margin-top: 20px;
        }

        .transactions-table {
            margin-top: 40px;
            width: 100%;
            border-collapse: collapse;
        }

        .transactions-table th, .transactions-table td {
            padding: 12px 20px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .transactions-table th {
            background-color: #4CAF50;
            color: #fff;
        }

        .transactions-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .transactions-table tr:hover {
            background-color: #f1f1f1;
        }

        .logout {
            background-color: #f44336;
            color: white;
            margin-top: 20px;
            font-size: 1.2rem;
            padding: 12px 25px;
            border-radius: 5px;
            display: inline-block;
            transition: background-color 0.3s ease;
        }

        .logout:hover {
            background-color: #d32f2f;
        }

        /* Admin Section */
        .admin-section {
            background-color: #f9f9f9;
            padding: 20px;
            margin-top: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .admin-section a {
            background-color: #008CBA;
            margin-right: 20px;
        }

        .admin-section a:hover {
            background-color: #007B9F;
        }

        .table-container {
            max-height: 400px;
            overflow-y: auto;
        }

        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .container {
                width: 100%;
                padding: 15px;
            }

            h2 {
                font-size: 2rem;
            }

            h3 {
                font-size: 1.4rem;
            }

            a {
                font-size: 1rem;
                padding: 8px 15px;
            }

            .transactions-table th, .transactions-table td {
                padding: 10px 15px;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Admin Dashboard - All Transactions</h2>

        <div class="actions">
            <a href="borrow_books.php">Borrow Books</a>
            <a href="return_books.php">Return Books</a>
            <a href="logout.php" class="logout">Logout</a>
        </div>

        <h3>All Transactions</h3>
        
        <?php if ($transaction_result->num_rows > 0): ?>
            <div class="table-container">
                <table class="transactions-table">
                    <tr>
                        <th>Username</th>
                        <th>Book Title</th>
                        <th>Borrow Date</th>
                        <th>Return Date</th>
                    </tr>
                    <?php while ($row = $transaction_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars($row['borrow_date']); ?></td>
                        <td><?php echo $row['return_date'] ? htmlspecialchars($row['return_date']) : 'Not Returned Yet'; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        <?php else: ?>
            <p>No transactions found.</p>
        <?php endif; ?>
    </div>

</body>
</html>

