<?php
session_start();
include '../includes/db.php';
if(!isset($_SESSION['user'])){ header("Location: ../auth/login.php"); exit(); }

// Add Transaction
if(isset($_POST['add'])) {
    $party_type = $_POST['party_type'];
    $party_id = $_POST['party_id'];
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $description = $_POST['description'];
    $transaction_date = $_POST['transaction_date'] ?: date('Y-m-d');
    
    // Calculate total
    $product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT price FROM products WHERE id=$product_id"));
    $price = $product['price'] ?? 0;
    $total = $price * $quantity;
    
    if($party_type == 'customer') {
        $credit = $total;
        $debit = 0;
        // Update stock
        mysqli_query($conn, "UPDATE products SET stock = stock - $quantity WHERE id = $product_id");
    } else {
        $credit = 0;
        $debit = $total;
    }
    
    $query = "INSERT INTO transactions (party_type, party_id, product_id, quantity, credit, debit, description, transaction_date)
              VALUES ('$party_type', '$party_id', '$product_id', '$quantity', '$credit', '$debit', '$description', '$transaction_date')";
    
    if(mysqli_query($conn, $query)) {
        echo "<script>alert('Transaction added!'); window.location.href='transaction.php';</script>";
    }
}

// Delete Transaction
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    mysqli_query($conn,"DELETE FROM transactions WHERE id=$id");
    echo "<script>alert('Transaction deleted!'); window.location.href='transaction.php';</script>";
}

$customers = mysqli_query($conn,"SELECT * FROM customers ORDER BY name");
$buyers = mysqli_query($conn,"SELECT * FROM buyers ORDER BY name");
$products = mysqli_query($conn,"SELECT * FROM products WHERE stock > 0 ORDER BY name");

$transactions = mysqli_query($conn,"
    SELECT t.*, c.name as customer_name, b.name as buyer_name, p.name as product_name
    FROM transactions t
    LEFT JOIN customers c ON t.party_type='customer' AND t.party_id=c.id
    LEFT JOIN buyers b ON t.party_type='buyer' AND t.party_id=b.id
    LEFT JOIN products p ON t.product_id=p.id
    ORDER BY t.id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Transactions - LedgerSys</title>
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
            <a href="transaction.php" class="active"><i class="fas fa-exchange-alt"></i> Transactions</a>
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
    <h2>Transaction Management</h2>
    
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-plus-circle"></i> New Transaction</h3>
        </div>
        <div class="card-body">
            <form method="post">
                <div class="form-group">
                    <label>Transaction Date</label>
                    <input type="date" name="transaction_date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Transaction Type</label>
                    <select name="party_type" id="party_type" required onchange="updateParty()">
                        <option value="">Select Type</option>
                        <option value="customer">Sale to Customer</option>
                        <option value="buyer">Purchase from Buyer</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Select Party</label>
                    <select name="party_id" id="party_id" required>
                        <option value="">Select Party</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Select Product</label>
                    <select name="product_id" id="product_id" required onchange="updatePrice()">
                        <option value="">Select Product</option>
                        <?php while($p = mysqli_fetch_assoc($products)): ?>
                            <option value="<?php echo $p['id']; ?>" data-price="<?php echo $p['price']; ?>">
                                <?php echo $p['code'] . " - " . $p['name'] . " (Rs. " . $p['price'] . ")"; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Unit Price</label>
                    <input type="number" id="price" readonly>
                </div>
                
                <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" name="quantity" id="quantity" min="1" value="1" required oninput="calculateTotal()">
                </div>
                
                <div class="form-group">
                    <label>Total Amount</label>
                    <input type="number" id="total" readonly style="font-weight: bold; font-size: 16px;">
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" placeholder="Enter transaction notes..."></textarea>
                </div>
                
                <button type="submit" name="add" class="btn btn-primary">
                    <i class="fas fa-check-circle"></i> Save Transaction
                </button>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-history"></i> Recent Transactions</h3>
        </div>
        <div class="card-body">
            <?php if(mysqli_num_rows($transactions) > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Party</th>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Amount</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($t = mysqli_fetch_assoc($transactions)): 
                                $party_name = $t['party_type'] == 'customer' ? $t['customer_name'] : $t['buyer_name'];
                                $amount = $t['party_type'] == 'customer' ? $t['credit'] : $t['debit'];
                            ?>
                            <tr>
                                <td><?php echo $t['transaction_date']; ?></td>
                                <td><span class="badge <?php echo $t['party_type'] == 'customer' ? 'badge-success' : 'badge-danger'; ?>">
                                    <?php echo ucfirst($t['party_type']); ?>
                                </span></td>
                                <td><?php echo $party_name; ?></td>
                                <td><?php echo $t['product_name']; ?></td>
                                <td><?php echo $t['quantity']; ?></td>
                                <td>Rs. <?php echo number_format($amount, 2); ?></td>
                                <td>
                                    <a href="transaction.php?delete=<?php echo $t['id']; ?>" class="action-btn delete"
                                       onclick="return confirm('Delete this transaction?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-exchange-alt"></i>
                    <h3>No Transactions Found</h3>
                    <p>Add your first transaction using the form above</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
const customers = <?php echo json_encode(mysqli_fetch_all(mysqli_query($conn,"SELECT * FROM customers ORDER BY name"), MYSQLI_ASSOC)); ?>;
const buyers = <?php echo json_encode(mysqli_fetch_all(mysqli_query($conn,"SELECT * FROM buyers ORDER BY name"), MYSQLI_ASSOC)); ?>;

function updateParty() {
    const type = document.getElementById('party_type').value;
    const select = document.getElementById('party_id');
    select.innerHTML = '<option value="">Select Party</option>';
    
    let list = type === 'customer' ? customers : buyers;
    list.forEach(p => {
        const option = document.createElement('option');
        option.value = p.id;
        option.textContent = p.name;
        select.appendChild(option);
    });
}

function updatePrice() {
    const product = document.getElementById('product_id');
    const price = product.options[product.selectedIndex]?.dataset.price || 0;
    document.getElementById('price').value = price;
    calculateTotal();
}

function calculateTotal() {
    const price = parseFloat(document.getElementById('price').value) || 0;
    const qty = parseInt(document.getElementById('quantity').value) || 0;
    document.getElementById('total').value = (price * qty).toFixed(2);
}
</script>

</body>
</html>
