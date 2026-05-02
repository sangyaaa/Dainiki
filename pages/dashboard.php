<?php
session_start();
include '../includes/db.php';
if(!isset($_SESSION['user'])){ header("Location: ../auth/login.php"); exit(); }

$total_customers = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS total FROM customers"))['total'];
$total_buyers = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS total FROM buyers"))['total'];
$total_transactions = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS total FROM transactions"))['total'];
$total_products = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS total FROM products"))['total'];
$bal = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(credit)-SUM(debit) AS balance FROM transactions"));
$total_balance = $bal['balance'] ?? 0;

// Recent transactions
$recent = mysqli_query($conn,"SELECT t.*, c.name as customer_name, b.name as buyer_name 
    FROM transactions t 
    LEFT JOIN customers c ON t.party_type='customer' AND t.party_id=c.id 
    LEFT JOIN buyers b ON t.party_type='buyer' AND t.party_id=b.id 
    ORDER BY t.id DESC LIMIT 5");
?>

<!DOCTYPE html>
<html>
<head>
<title>Dashboard - LedgerSys</title>
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
            <a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a>
            <a href="customers.php"><i class="fas fa-users"></i> Customers</a>
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
    <h2>Dashboard Overview</h2>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <h3>Total Customers</h3>
            <h2><?php echo $total_customers; ?></h2>
            <a href="customers.php" class="btn btn-primary" style="margin-top: 15px;">View All</a>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-building"></i></div>
            <h3>Total Buyers</h3>
            <h2><?php echo $total_buyers; ?></h2>
            <a href="buyer.php" class="btn btn-primary" style="margin-top: 15px;">View All</a>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-exchange-alt"></i></div>
            <h3>Total Transactions</h3>
            <h2><?php echo $total_transactions; ?></h2>
            <a href="transaction.php" class="btn btn-primary" style="margin-top: 15px;">View All</a>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-boxes"></i></div>
            <h3>Total Products</h3>
            <h2><?php echo $total_products; ?></h2>
            <a href="stock.php" class="btn btn-primary" style="margin-top: 15px;">View All</a>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-money-bill-wave"></i> Current Balance</h3>
        </div>
        <div class="card-body">
            <h1 style="color: <?php echo $total_balance >= 0 ? '#2ecc71' : '#e74c3c'; ?>; text-align: center;">
                Rs. <?php echo number_format($total_balance, 2); ?>
            </h1>
            <p style="text-align: center; color: #666; margin-top: 10px;">
                <?php echo $total_balance >= 0 ? 'Positive Balance' : 'Negative Balance'; ?>
            </p>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-history"></i> Recent Transactions</h3>
            <a href="transaction.php" class="btn btn-secondary">View All</a>
        </div>
        <div class="card-body">
            <?php if(mysqli_num_rows($recent) > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Party</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($recent)): 
                                $party_name = $row['party_type'] == 'customer' ? $row['customer_name'] : $row['buyer_name'];
                                $amount = $row['party_type'] == 'customer' ? $row['credit'] : $row['debit'];
                            ?>
                            <tr>
                                <td><?php echo $row['transaction_date']; ?></td>
                                <td><span class="badge <?php echo $row['party_type'] == 'customer' ? 'badge-success' : 'badge-danger'; ?>">
                                    <?php echo ucfirst($row['party_type']); ?>
                                </span></td>
                                <td><?php echo $party_name; ?></td>
                                <td>Rs. <?php echo number_format($amount, 2); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p style="text-align: center; color: #999; padding: 20px;">No transactions yet</p>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
