<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php'); // Redirect to login page if not logged in
    exit();
}

require_once 'config.php';

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $book_id = $_POST['book_id'];
    $selected_user_id = $user_id; // Default to current user

    // If the logged-in user is admin, allow them to select another user
    if ($user_role == 'admin' && isset($_POST['user_id'])) {
        $selected_user_id = $_POST['user_id'];
    }

    // Check if the selected user has borrowed the book
    $stmt = $conn->prepare("SELECT * FROM borrow_records WHERE user_id = ? AND book_id = ? AND return_date IS NULL");
    $stmt->bind_param("ii", $selected_user_id, $book_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update borrow record to indicate the book is returned
        $stmt = $conn->prepare("UPDATE borrow_records SET return_date = NOW() WHERE user_id = ? AND book_id = ? AND return_date IS NULL");
        $stmt->bind_param("ii", $selected_user_id, $book_id);
        $stmt->execute();

        // Update available copies in books table
        $stmt = $conn->prepare("UPDATE books SET copies_available = copies_available + 1 WHERE book_id = ?");
        $stmt->bind_param("i", $book_id);
        $stmt->execute();

        $message = "Book returned successfully!";
    } else {
        $message = "This book has not been borrowed by the selected user, or it is already returned.";
    }
}

// Fetch available books and their quantities (this will show only books that are available for borrowing)
$stmt = $conn->prepare("SELECT book_id, title, copies_available FROM books WHERE copies_available > 0");
$stmt->execute();
$books_result = $stmt->get_result();

// If the user is an admin, fetch all users for selection
if ($user_role == 'admin') {
    $stmt = $conn->prepare("SELECT user_id, username FROM users");
    $stmt->execute();
    $users_result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Return Books</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 60%;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        .message {
            text-align: center;
            font-weight: bold;
            color: #007BFF;
        }
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        select, input {
            padding: 10px;
            margin: 15px 0;
            width: 80%;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            padding: 10px 20px;
            background-color: #007BFF;
            border: none;
            border-radius: 5px;
            color: #fff;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }
        th {
            background-color: #007BFF;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Return a Book</h2>
        <?php if (isset($message)): ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>

        <form method="POST" action="return_books.php">
            <?php if ($user_role == 'admin'): ?>
                <select name="user_id" required>
                    <option value="">Select User</option>
                    <?php while ($user = $users_result->fetch_assoc()): ?>
                        <option value="<?php echo $user['user_id']; ?>"><?php echo $user['username']; ?></option>
                    <?php endwhile; ?>
                </select><br>
            <?php endif; ?>

            <select name="book_id" required>
                <option value="">Select Book</option>
                <?php 
                // Check if there are books available in the result
                if ($books_result->num_rows > 0) {
                    while ($book = $books_result->fetch_assoc()): ?>
                        <option value="<?php echo $book['book_id']; ?>">
                            <?php echo htmlspecialchars($book['title']); ?> (<?php echo htmlspecialchars($book['copies_available']); ?> available)
                        </option>
                    <?php endwhile; 
                } else {
                    echo '<option value="">No available books</option>';
                }
                ?>
            </select><br>
            <button type="submit">Return</button>
        </form>

        <h3>Available Books</h3>
        <table>
            <tr>
                <th>Title</th>
                <th>Available Copies</th>
            </tr>
            <?php
            // Re-fetch available books for display
            $stmt = $conn->prepare("SELECT book_id, title, copies_available FROM books WHERE copies_available > 0");
            $stmt->execute();
            $books_result = $stmt->get_result();
            if ($books_result->num_rows > 0) {
                while ($book = $books_result->fetch_assoc()) {
                    echo "<tr><td>" . htmlspecialchars($book['title']) . "</td><td>" . htmlspecialchars($book['copies_available']) . "</td></tr>";
                }
            } else {
                echo "<tr><td colspan='2'>No available books</td></tr>";
            }
            ?>
        </table>

        <!-- Back button -->
        <div style="text-align: center; margin-top: 20px;">
            <a href="dashboard.php"><button>Back to Dashboard</button></a>
        </div>
    </div>

</body>
</html>
