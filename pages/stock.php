<?php
session_start();
include '../includes/db.php';
if(!isset($_SESSION['user'])){ header("Location: ../auth/login.php"); exit(); }

// Add Product
if(isset($_POST['add'])){
    $code = trim($_POST['code']);
    $name = trim($_POST['name']);
    $price = trim($_POST['price']);
    $stock = trim($_POST['stock']);
    
    $check = mysqli_query($conn, "SELECT * FROM products WHERE code='$code'");
    if(mysqli_num_rows($check) > 0){
        echo "<script>alert('Product code already exists!');</script>";
    } else {
        mysqli_query($conn,"INSERT INTO products(code, name, price, stock) VALUES('$code','$name','$price','$stock')");
        echo "<script>alert('Product added!'); window.location.href='stock.php';</script>";
    }
}

// Delete Product
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    mysqli_query($conn,"DELETE FROM products WHERE id=$id");
    echo "<script>alert('Product deleted!'); window.location.href='stock.php';</script>";
}

// Edit Data
$editData = null;
if(isset($_GET['edit'])){
    $id = $_GET['edit'];
    $res = mysqli_query($conn,"SELECT * FROM products WHERE id=$id");
    $editData = mysqli_fetch_assoc($res);
}

// Update Product
if(isset($_POST['update'])){
    $id = $_POST['id'];
    $code = trim($_POST['code']);
    $name = trim($_POST['name']);
    $price = trim($_POST['price']);
    $stock = trim($_POST['stock']);
    
    $check = mysqli_query($conn, "SELECT * FROM products WHERE code='$code' AND id != $id");
    if(mysqli_num_rows($check) > 0){
        echo "<script>alert('Product code already exists!');</script>";
    } else {
        mysqli_query($conn,"UPDATE products SET code='$code', name='$name', price='$price', stock='$stock' WHERE id=$id");
        echo "<script>alert('Product updated!'); window.location.href='stock.php';</script>";
    }
}

$products = mysqli_query($conn,"SELECT * FROM products ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Stock - LedgerSys</title>
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
            <a href="buyer.php"><i class="fas fa-building"></i> Buyers</a>
            <a href="transaction.php"><i class="fas fa-exchange-alt"></i> Transactions</a>
            <a href="ledger.php"><i class="fas fa-file-invoice-dollar"></i> Ledger</a>
            <a href="stock.php" class="active"><i class="fas fa-boxes"></i> Stock</a>
        </div>
    </div>
    <div class="nav-right">
        <span class="welcome-msg"><i class="fas fa-user-circle"></i> <?php echo $_SESSION['user']; ?></span>
        <a href="../auth/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</nav>

<div class="container">
    <h2>Stock Management</h2>
    
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-box"></i> <?php echo $editData ? 'Edit Product' : 'Add New Product'; ?></h3>
        </div>
        <div class="card-body">
            <form method="post">
                <input type="hidden" name="id" value="<?php echo $editData['id'] ?? ''; ?>">
                
                <div class="form-group">
                    <label>Product Code *</label>
                    <input type="text" name="code" placeholder="e.g., P001" required 
                           value="<?php echo $editData['code'] ?? ''; ?>">
                </div>
                
                <div class="form-group">
                    <label>Product Name *</label>
                    <input type="text" name="name" placeholder="Enter product name" required
                           value="<?php echo $editData['name'] ?? ''; ?>">
                </div>
                
                <div class="form-group">
                    <label>Price (Rs.) *</label>
                    <input type="number" step="0.01" name="price" placeholder="0.00" required
                           value="<?php echo $editData['price'] ?? ''; ?>">
                </div>
                
                <div class="form-group">
                    <label>Stock Quantity *</label>
                    <input type="number" name="stock" placeholder="0" required
                           value="<?php echo $editData['stock'] ?? ''; ?>">
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <?php if($editData): ?>
                        <button type="submit" name="update" class="btn btn-success">
                            <i class="fas fa-save"></i> Update Product
                        </button>
                        <a href="stock.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    <?php else: ?>
                        <button type="submit" name="add" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Product
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-list"></i> All Products</h3>
        </div>
        <div class="card-body">
            <?php if(mysqli_num_rows($products) > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Value</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($products)): 
                                $value = $row['price'] * $row['stock'];
                                $stock_class = $row['stock'] == 0 ? 'badge-danger' : ($row['stock'] <= 10 ? 'badge-warning' : 'badge-success');
                            ?>
                            <tr>
                                <td><?php echo $row['code']; ?></td>
                                <td><?php echo $row['name']; ?></td>
                                <td>Rs. <?php echo number_format($row['price'], 2); ?></td>
                                <td>
                                    <span class="badge <?php echo $stock_class; ?>">
                                        <?php echo $row['stock']; ?> units
                                    </span>
                                </td>
                                <td>Rs. <?php echo number_format($value, 2); ?></td>
                                <td>
                                    <div class="action-btns">
                                        <a href="stock.php?edit=<?php echo $row['id']; ?>" class="action-btn edit" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="stock.php?delete=<?php echo $row['id']; ?>" class="action-btn delete" title="Delete"
                                           onclick="return confirm('Delete this product?')">
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
                    <i class="fas fa-boxes"></i>
                    <h3>No Products Found</h3>
                    <p>Add your first product using the form above</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
