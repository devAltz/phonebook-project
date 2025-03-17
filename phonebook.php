<?php
// Database connection settings
$servername = "localhost";
$username = "root"; // your MySQL username
$password = "";     // your MySQL password
$dbname = "phonebook";  // the database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize message variable
$message = '';

// Add a contact
if (isset($_POST['add'])) {
    $name = $_POST['name'];
    $phone_number = $_POST['phone_number'];
    $address = $_POST['address'];
    $email = $_POST['email'];

    // Insert contact into database
    $sql = "INSERT INTO contacts (name, phone_number, address, email) 
            VALUES ('$name', '$phone_number', '$address', '$email')";

    if ($conn->query($sql) === TRUE) {
        $message = "<div class='message success add'>New contact added successfully.</div>";
        // Redirect to clear the URL parameters
        header("Location: " . $_SERVER['PHP_SELF'] . "?status=added");
        exit;
    } else {
        $message = "<div class='message error'>Error: " . $conn->error . "</div>";
    }
}

// Delete a contact
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];

    // Delete contact from database
    $sql = "DELETE FROM contacts WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        // Redirect to clear the URL parameters
        header("Location: " . $_SERVER['PHP_SELF'] . "?status=deleted");
        exit;
    } else {
        $message = "<div class='message error'>Error: " . $conn->error . "</div>";
    }
}

// Handle status messages
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'added') {
        $message = "<div class='message success add'>New contact added successfully.</div>";
    } else if ($_GET['status'] == 'deleted') {
        $message = "<div class='message success delete'>Contact deleted successfully.</div>";
    }
}

// Search contacts
$search_results = [];
if (isset($_POST['search'])) {
    $search = $_POST['search_term'];

    // Search for contacts in the database
    $sql = "SELECT * FROM contacts WHERE name LIKE '%$search%'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $search_results[] = $row;
        }
        $message = "<div class='message success search'>Search results found.</div>";
    } else {
        $message = "<div class='message error'>No contacts found matching your search.</div>";
    }
}

// Retrieve all contacts
$sql = "SELECT * FROM contacts";
$contacts = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phonebook</title>
    <style>
        /* Basic Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f6;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }

        h1, h2, h3 {
            color: #2c3e50;
            margin-bottom: 15px;
        }

        /* Center the content */
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Message Styles */
        .message {
            text-align: center;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .message.success.add {
            color: green;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
        }
        .message.success.delete {
            color: red;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            color: red;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
        }

        .message.search {
            color: blue;
            background-color: #cce5ff;
            border: 1px solid #b8daff;
        }

        /* Form Styles */
        form {
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }

        input[type="text"], input[type="email"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        button {
            padding: 10px 20px;
            background-color: #3498db;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-right: 10px;
        }

        button:hover {
            background-color: #2980b9;
        }

        /* Contact List Styles */
        .contact-container {
            margin-top: 20px;
        }

        .contact-list {
            display: none; /* Hidden by default */
        }

        .contact-list.grid-view {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .contact-card {
            background-color: #ecf0f1;
            padding: 15px;
            border-radius: 4px;
            width: calc(33.33% - 10px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 15px;
        }

        .contact-info {
            margin-bottom: 10px;
        }

        .contact-actions {
            text-align: right;
        }

        a {
            color: #e74c3c;
            text-decoration: none;
            font-weight: bold;
        }

        a:hover {
            color: #c0392b;
        }

        .search-container {
            margin-top: 30px;
        }

        .search-results {
            margin-top: 20px;
        }

        .search-results.grid-view {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .no-results {
            color: #e74c3c;
            font-style: italic;
        }

        /* View Controls */
        .view-controls {
            margin: 20px 0;
        }

        @media (max-width: 768px) {
            .contact-card {
                width: calc(50% - 10px);
            }
        }

        @media (max-width: 480px) {
            .contact-card {
                width: 100%;
            }
        }
    </style>
    <script>
        // JavaScript to toggle contact list visibility
        function toggleContacts() {
            var contactList = document.getElementById('contact-list');
            if (contactList.style.display === 'none' || contactList.style.display === '') {
                contactList.style.display = 'flex';
                contactList.classList.add('grid-view');
            } else {
                contactList.style.display = 'none';
                contactList.classList.remove('grid-view');
            }
        }

        // JavaScript to automatically clear URL parameters after displaying message
        window.onload = function() {
            // Check if there's a status parameter in the URL
            if (window.location.search.includes('status=')) {
                // After 5 seconds, replace the URL without the parameters
                setTimeout(function() {
                    var newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                    window.history.replaceState({path: newUrl}, '', newUrl);
                }, 5000);
            }
        };
    </script>
</head>
<body>
    <div class="container">
        <h1>Phonebook Application</h1>

        <!-- Display messages -->
        <?php if ($message): ?>
            <?php echo $message; ?>
        <?php endif; ?>

        <!-- Add a New Contact -->
        <h2>Add a New Contact</h2>
        <form action="" method="POST">
            <label>Name:</label>
            <input type="text" name="name" required><br>
            <label>Phone Number:</label>
            <input type="text" name="phone_number" required><br>
            <label>Address:</label>
            <input type="text" name="address" required><br>
            <label>Email:</label>
            <input type="email" name="email" required><br><br>
            <button type="submit" name="add">Add Contact</button>
        </form>

        <!-- View All Contacts -->
        <div class="contact-container">
            <h2>All Contacts</h2>
            <div class="view-controls">
                <button onclick="toggleContacts()">Show/Hide Contacts</button>
            </div>
            
            <div id="contact-list" class="contact-list">
                <?php while ($contact = $contacts->fetch_assoc()): ?>
                    <div class="contact-card">
                        <div class="contact-info">
                            <p><strong>Name:</strong> <?php echo $contact['name']; ?></p>
                            <p><strong>Phone:</strong> <?php echo $contact['phone_number']; ?></p>
                            <p><strong>Address:</strong> <?php echo $contact['address']; ?></p>
                            <p><strong>Email:</strong> <?php echo $contact['email']; ?></p>
                        </div>
                        <div class="contact-actions">
                            <a href="?delete_id=<?php echo $contact['id']; ?>">Delete</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Search Contacts -->
        <div class="search-container">
            <h2>Search for a Contact</h2>
            <form action="" method="POST">
                <label>Search by Name:</label><br>
                <input type="text" name="search_term" required><br><br>
                <button type="submit" name="search">Search</button>
            </form>

            <?php if (isset($_POST['search'])): ?>
                <div class="search-results">
                    <h3>Search Results:</h3>
                    <?php if (count($search_results) > 0): ?>
                        <div class="grid-view">
                            <?php foreach ($search_results as $result): ?>
                                <div class="contact-card">
                                    <div class="contact-info">
                                        <p><strong>Name:</strong> <?php echo $result['name']; ?></p>
                                        <p><strong>Phone:</strong> <?php echo $result['phone_number']; ?></p>
                                        <p><strong>Address:</strong> <?php echo $result['address']; ?></p>
                                        <p><strong>Email:</strong> <?php echo $result['email']; ?></p>
                                    </div>
                                    <div class="contact-actions">
                                        <a href="?delete_id=<?php echo $result['id']; ?>">Delete</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="no-results">No results found.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>