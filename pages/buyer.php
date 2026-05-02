<?php
session_start();
include '../includes/db.php';
if(!isset($_SESSION['user'])){ header("Location: ../auth/login.php"); exit(); }

// Add Buyer
if(isset($_POST['add'])){
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    mysqli_query($conn,"INSERT INTO buyers(name, phone, address) VALUES('$name','$phone','$address')");
    echo "<script>alert('Buyer added!'); window.location.href='buyer.php';</script>";
}

// Delete Buyer
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    mysqli_query($conn,"DELETE FROM buyers WHERE id=$id");
    echo "<script>alert('Buyer deleted!'); window.location.href='buyer.php';</script>";
}

// Edit Data
$editData = null;
if(isset($_GET['edit'])){
    $id = $_GET['edit'];
    $res = mysqli_query($conn,"SELECT * FROM buyers WHERE id=$id");
    $editData = mysqli_fetch_assoc($res);
}

// Update Buyer
if(isset($_POST['update'])){
    $id = $_POST['id'];
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    mysqli_query($conn,"UPDATE buyers SET name='$name', phone='$phone', address='$address' WHERE id=$id");
    echo "<script>alert('Buyer updated!'); window.location.href='buyer.php';</script>";
}

$buyers = mysqli_query($conn,"SELECT * FROM buyers ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Buyers - LedgerSys</title>
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
            <a href="customers.php"><i class="fas fa-users"></i> Customers</a>
            <a href="buyer.php" class="active"><i class="fas fa-building"></i> Buyers</a>
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
    <h2>Buyer Management</h2>
    
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-building"></i> <?php echo $editData ? 'Edit Buyer' : 'Add New Buyer'; ?></h3>
        </div>
        <div class="card-body">
            <form method="post">
                <input type="hidden" name="id" value="<?php echo $editData['id'] ?? ''; ?>">
                
                <div class="form-group">
                    <label>Buyer/Company Name *</label>
                    <input type="text" name="name" placeholder="Enter company name" required 
                           value="<?php echo $editData['name'] ?? ''; ?>">
                </div>
                
                <div class="form-group">
                    <label>Contact Number *</label>
                    <input type="text" name="phone" placeholder="Enter phone number" required
                           value="<?php echo $editData['phone'] ?? ''; ?>">
                </div>
                
                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" placeholder="Enter address"><?php echo $editData['address'] ?? ''; ?></textarea>
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <?php if($editData): ?>
                        <button type="submit" name="update" class="btn btn-success">
                            <i class="fas fa-save"></i> Update Buyer
                        </button>
                        <a href="buyer.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    <?php else: ?>
                        <button type="submit" name="add" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Buyer
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-list"></i> All Buyers</h3>
        </div>
        <div class="card-body">
            <?php if(mysqli_num_rows($buyers) > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Address</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($buyers)): ?>
                            <tr>
                                <td>#<?php echo $row['id']; ?></td>
                                <td><?php echo $row['name']; ?></td>
                                <td><?php echo $row['phone']; ?></td>
                                <td><?php echo $row['address']; ?></td>
                                <td>
                                    <div class="action-btns">
                                        <a href="buyer.php?edit=<?php echo $row['id']; ?>" class="action-btn edit" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="buyer.php?delete=<?php echo $row['id']; ?>" class="action-btn delete" title="Delete"
                                           onclick="return confirm('Delete this buyer?')">
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
                    <i class="fas fa-building"></i>
                    <h3>No Buyers Found</h3>
                    <p>Add your first buyer using the form above</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
