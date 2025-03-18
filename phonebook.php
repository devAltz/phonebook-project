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
        header("Location: " . $_SERVER['PHP_SELF'] . "?status=added");
        exit;
    } else {
        $message = "<div class='message error'>Error: " . $conn->error . "</div>";
    }
}

// Edit a contact
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $phone_number = $_POST['phone_number'];
    $address = $_POST['address'];
    $email = $_POST['email'];

    // Update contact in the database
    $sql = "UPDATE contacts SET name='$name', phone_number='$phone_number', address='$address', email='$email' WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        $message = "<div class='message success'>Contact updated successfully.</div>";
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
        header("Location: " . $_SERVER['PHP_SELF'] . "?status=deleted");
        exit;
    } else {
        $message = "<div class='message error'>Error: " . $conn->error . "</div>";
    }
}

// Search contacts
$search_results = [];
if (isset($_POST['search'])) {
    $search = $_POST['search_term'];

    $sql = "SELECT * FROM contacts WHERE name LIKE '%$search%'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $search_results[] = $row;
        }
        $message = "<div class='message success'>Search results found.</div>";
    } else {
        $message = "<div class='message error'>No contacts found matching your search.</div>";
    }
}
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

        .message.success {
            color: green;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            color: red;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
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

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 20px;
            border-radius: 4px;
            width: 50%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        /* Contact List Styles */
        .contact-container {
            margin-top: 20px;
        }

        .contact-list {
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
    </style>
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

        <!-- Search Contacts -->
        <div class="search-container">
            <h2>Search for a Contact</h2>
            <form action="" method="POST">
                <label>Search by Name:</label>
                <input type="text" name="search_term" required><br><br>
                <button type="submit" name="search">Search</button>
            </form>
        </div>

        <!-- Display the Contact List only when search results are present -->
        <?php if (!empty($search_results)): ?>
        <div class="contact-container">
            <h2>Contact List</h2>

            <!-- Display Contacts -->
            <div class="contact-list">
                <?php foreach ($search_results as $row): ?>
                <div class="contact-card">
                    <div class="contact-info">
                        <p><strong>Name:</strong> <?php echo $row['name']; ?></p>
                        <p><strong>Phone:</strong> <?php echo $row['phone_number']; ?></p>
                        <p><strong>Address:</strong> <?php echo $row['address']; ?></p>
                        <p><strong>Email:</strong> <?php echo $row['email']; ?></p>
                    </div>
                    <div class="contact-actions">
                        <!-- Edit Button Trigger -->
                        <button onclick="openModal(<?php echo $row['id']; ?>, '<?php echo $row['name']; ?>', '<?php echo $row['phone_number']; ?>', '<?php echo $row['address']; ?>', '<?php echo $row['email']; ?>')">Edit</button>
                        <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $row['id']; ?>)">Delete</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Modal for Editing Contact -->
        <div id="editModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <h2>Edit Contact</h2>
                <form method="POST">
                    <input type="hidden" name="id" id="contactId">
                    <label>Name:</label>
                    <input type="text" name="name" id="editName" required><br>
                    <label>Phone:</label>
                    <input type="text" name="phone_number" id="editPhone" required><br>
                    <label>Address:</label>
                    <input type="text" name="address" id="editAddress" required><br>
                    <label>Email:</label>
                    <input type="email" name="email" id="editEmail" required><br><br>
                    <button type="submit" name="edit">Update Contact</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Open the modal and pre-fill the form with the current data
        function openModal(id, name, phone, address, email) {
            document.getElementById("contactId").value = id;
            document.getElementById("editName").value = name;
            document.getElementById("editPhone").value = phone;
            document.getElementById("editAddress").value = address;
            document.getElementById("editEmail").value = email;
            document.getElementById("editModal").style.display = "block";
        }

        // Close the modal
        function closeModal() {
            document.getElementById("editModal").style.display = "none";
        }

        // JavaScript to confirm deletion
        function confirmDelete(id) {
            if (confirm("Are you sure you want to delete this contact?")) {
                window.location.href = "?delete_id=" + id;
            }
        }
    </script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
