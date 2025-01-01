<?php
session_start(); // Start the session at the very beginning of the script

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php'); // Redirect to login page if not logged in
    exit();
}

require_once 'config.php';

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// Fetch user-specific borrow records
$stmt = $conn->prepare("SELECT borrow_records.book_id, books.title, borrow_records.borrow_date, COUNT(*) AS borrowed_count 
                        FROM borrow_records
                        JOIN books ON borrow_records.book_id = books.book_id
                        WHERE borrow_records.user_id = ? AND borrow_records.return_date IS NULL
                        GROUP BY borrow_records.book_id, books.title");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$borrow_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
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

        .borrowed-books {
            margin-top: 40px;
            width: 100%;
            border-collapse: collapse;
        }

        .borrowed-books th, .borrowed-books td {
            padding: 12px 20px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .borrowed-books th {
            background-color: #4CAF50;
            color: #fff;
        }

        .borrowed-books tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .borrowed-books tr:hover {
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

            .borrowed-books th, .borrowed-books td {
                padding: 10px 15px;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Welcome to the Library Dashboard, <?php echo $user_role; ?>!</h2>

        <div class="actions">
            <a href="borrow_books.php">Borrow Books</a>
            <a href="return_books.php">Return Books</a>
            <a href="news.php">News</a> <!-- Added News Button -->
            <a href="logout.php" class="logout">Logout</a>
        </div>

        <?php if ($user_role == 'admin'): ?>
            <div class="admin-section">
                <h3>Admin Actions</h3>
                <a href="manage_books.php">Manage Books</a>
                <a href="admin_transaction.php">View Transactions</a>
                <a href="admin_users.php">Manage Users</a>
            </div>
        <?php endif; ?>

        <h3>Your Borrowed Books</h3>
        
        <?php if ($borrow_result->num_rows > 0): ?>
            <div class="table-container">
                <table class="borrowed-books">
                    <tr>
                        <th>Book ID</th>
                        <th>Book Title</th>
                        <th>Borrow Date</th>
                        <th>Borrowed Quantity</th>
                    </tr>
                    <?php while ($row = $borrow_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['book_id']; ?></td>
                        <td><?php echo $row['title']; ?></td>
                        <td><?php echo $row['borrow_date']; ?></td>
                        <td><?php echo $row['borrowed_count']; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        <?php else: ?>
            <p>No books borrowed</p>
        <?php endif; ?>
    </div>

</body>
</html>
