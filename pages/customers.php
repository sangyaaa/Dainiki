<?php
session_start();
include '../includes/db.php';
if(!isset($_SESSION['user'])){ header("Location: ../auth/login.php"); exit(); }

// Add Customer
if(isset($_POST['add'])){
    $name = trim($_POST['name']);
    $contact = trim($_POST['contact']);
    $email = trim($_POST['email']);
    
    $check = mysqli_query($conn, "SELECT * FROM customers WHERE email='$email'");
    if(mysqli_num_rows($check) > 0){
        echo "<script>alert('Customer with this email already exists!');</script>";
    } else {
        mysqli_query($conn,"INSERT INTO customers(name, contact, email) VALUES('$name','$contact','$email')");
        echo "<script>alert('Customer added successfully!'); window.location.href='customers.php';</script>";
    }
}

// Delete Customer
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    mysqli_query($conn,"DELETE FROM customers WHERE id=$id");
    echo "<script>alert('Customer deleted!'); window.location.href='customers.php';</script>";
}

// Edit Data
$editData = null;
if(isset($_GET['edit'])){
    $id = $_GET['edit'];
    $res = mysqli_query($conn,"SELECT * FROM customers WHERE id=$id");
    $editData = mysqli_fetch_assoc($res);
}

// Update Customer
if(isset($_POST['update'])){
    $id = $_POST['id'];
    $name = trim($_POST['name']);
    $contact = trim($_POST['contact']);
    $email = trim($_POST['email']);
    
    mysqli_query($conn,"UPDATE customers SET name='$name', contact='$contact', email='$email' WHERE id=$id");
    echo "<script>alert('Customer updated!'); window.location.href='customers.php';</script>";
}

$customers = mysqli_query($conn,"SELECT * FROM customers ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Customers - LedgerSys</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-left">
        <div class="logo"><a href="dashboard.php" class="logo">
        <i class="fas fa-book"></i> दैनिकी
    </a></div>
        <div class="nav-links">
            <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <a href="customers.php" class="active"><i class="fas fa-users"></i> Customers</a>
            <a href="buyer.php"><i class="fas fa-building"></i> Buyers</a>
            <a href="transaction.php"><i class="fas fa-exchange-alt"></i> Transactions</a>
            <a href="ledger.php"><i class="fas fa-file-invoice-dollar"></i> Ledger</a>
            <a href="stock.php"><i class="fas fa-boxes"></i> Stock</a>
        </div>
    </div>
    <div class="nav-right">
        <span class="welcome-msg"><i class="fas fa-user-circle"></i> <?php echo $_SESSION['user']; ?></span>
        <a href="../auth/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</nav>

<div class="container">
    <h2>Customer Management</h2>
    
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-user-plus"></i> <?php echo $editData ? 'Edit Customer' : 'Add New Customer'; ?></h3>
        </div>
        <div class="card-body">
            <form method="post">
                <input type="hidden" name="id" value="<?php echo $editData['id'] ?? ''; ?>">
                
                <div class="form-group">
                    <label>Customer Name *</label>
                    <input type="text" name="name" placeholder="Enter full name" required 
                           value="<?php echo $editData['name'] ?? ''; ?>">
                </div>
                
                <div class="form-group">
                    <label>Contact Number *</label>
                    <input type="text" maxlength=10 minlength=10 name="contact" placeholder="Enter phone number" required
                           value="<?php echo $editData['contact'] ?? ''; ?>">
                </div>
                
                <div class="form-group">
                    <label>Email Address *</label>
                    <input type="email" name="email" placeholder="Enter email" required
                           value="<?php echo $editData['email'] ?? ''; ?>">
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <?php if($editData): ?>
                        <button type="submit" name="update" class="btn btn-success">
                            <i class="fas fa-save"></i> Update Customer
                        </button>
                        <a href="customers.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    <?php else: ?>
                        <button type="submit" name="add" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Customer
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-list"></i> All Customers</h3>
        </div>
        <div class="card-body">
            <?php if(mysqli_num_rows($customers) > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Contact</th>
                                <th>Email</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($customers)): ?>
                            <tr>
                                <td>#<?php echo $row['id']; ?></td>
                                <td><?php echo $row['name']; ?></td>
                                <td><?php echo $row['contact']; ?></td>
                                <td><?php echo $row['email']; ?></td>
                                <td>
                                    <div class="action-btns">
                                        <a href="customers.php?edit=<?php echo $row['id']; ?>" class="action-btn edit" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="customers.php?delete=<?php echo $row['id']; ?>" class="action-btn delete" title="Delete"
                                           onclick="return confirm('Delete this customer?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <h3>No Customers Found</h3>
                    <p>Add your first customer using the form above</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
