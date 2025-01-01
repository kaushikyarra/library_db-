<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: index.php'); // Redirect to login page if not admin
    exit();
}

require_once 'config.php';

$message = '';

// Handle book addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_book'])) {
    // Sanitize input data
    $title = $_POST['title'];
    $author = $_POST['author'];
    $genre = $_POST['genre'];
    $publication_year = $_POST['publication_year'];
    $copies_available = $_POST['copies_available'];

    // Validate input (basic checks)
    if (empty($title) || empty($author) || empty($genre) || empty($publication_year) || empty($copies_available)) {
        $message = "All fields are required!";
    } else {
        // Prepare and execute the insert query
        $sql = "INSERT INTO books (title, author, genre, publication_year, copies_available) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssii", $title, $author, $genre, $publication_year, $copies_available);

        if ($stmt->execute()) {
            $message = "Book added successfully!";
        } else {
            $message = "Error: " . $stmt->error;
        }
    }
}

// Handle book deletion
if (isset($_GET['delete_book_id'])) {
    $book_id = $_GET['delete_book_id'];

    // Prepare and execute the delete query
    $sql = "DELETE FROM books WHERE book_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $book_id);

    if ($stmt->execute()) {
        $message = "Book deleted successfully!";
    } else {
        $message = "Error: " . $stmt->error;
    }
}

// Fetch all books from the database
$sql = "SELECT * FROM books";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Books</title>
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

        .books-table {
            margin-top: 40px;
            width: 100%;
            border-collapse: collapse;
        }

        .books-table th, .books-table td {
            padding: 12px 20px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .books-table th {
            background-color: #4CAF50;
            color: #fff;
        }

        .books-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .books-table tr:hover {
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

        .message {
            color: green;
            font-size: 1.2rem;
            margin-bottom: 20px;
        }

        .back-btn {
            margin-top: 20px;
            text-align: center;
        }

        .back-btn a {
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            text-decoration: none;
            font-size: 1.2rem;
            border-radius: 5px;
        }

        .back-btn a:hover {
            background-color: #0056b3;
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

            .books-table th, .books-table td {
                padding: 10px 15px;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Manage Books</h2>

        <!-- Display messages -->
        <?php if (isset($message)) { ?>
            <p class="message"><?php echo $message; ?></p>
        <?php } ?>

        <h3>Add New Book</h3>
        <form method="POST" action="manage_books.php">
            <input type="text" name="title" placeholder="Book Title" required><br><br>
            <input type="text" name="author" placeholder="Author" required><br><br>
            <input type="text" name="genre" placeholder="Genre" required><br><br>
            <input type="number" name="publication_year" placeholder="Publication Year" required><br><br>
            <input type="number" name="copies_available" placeholder="Copies Available" required><br><br>
            <button type="submit" name="add_book">Add Book</button>
        </form>

        <h3>Existing Books</h3>
        <table class="books-table">
            <tr>
                <th>Book ID</th>
                <th>Title</th>
                <th>Author</th>
                <th>Genre</th>
                <th>Publication Year</th>
                <th>Copies Available</th>
                <th>Action</th>
            </tr>
            <?php while ($book = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $book['book_id']; ?></td>
                <td><?php echo $book['title']; ?></td>
                <td><?php echo $book['author']; ?></td>
                <td><?php echo $book['genre']; ?></td>
                <td><?php echo $book['publication_year']; ?></td>
                <td><?php echo $book['copies_available']; ?></td>
                <td><a href="manage_books.php?delete_book_id=<?php echo $book['book_id']; ?>">Delete</a></td>
            </tr>
            <?php } ?>
        </table>

        <div class="back-btn">
            <a href="dashboard.php">Back to Dashboard</a>
        </div>

        <a href="logout.php" class="logout">Logout</a>
    </div>

</body>
</html>
