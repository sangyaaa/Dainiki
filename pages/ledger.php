<?php
session_start();
include '../includes/db.php';
if(!isset($_SESSION['user'])){ header("Location: ../auth/login.php"); exit(); }

// Check search parameters
$search_date = $_GET['date'] ?? '';
$search_name = $_GET['name'] ?? '';

// Build WHERE clause
$where = "1"; // Show all by default

if(!empty($search_date)) {
    $where .= " AND DATE(t.transaction_date) = '$search_date'";
}

if(!empty($search_name)) {
    $search_name = mysqli_real_escape_string($conn, $search_name);
    $where .= " AND (
        (t.party_type = 'customer' AND c.name LIKE '%$search_name%') OR
        (t.party_type = 'buyer' AND b.name LIKE '%$search_name%')
    )";
}

$transactions = mysqli_query($conn,"
    SELECT t.*,
           CASE 
               WHEN t.party_type = 'customer' THEN c.name
               ELSE b.name
           END AS party_name
    FROM transactions t
    LEFT JOIN customers c ON t.party_type='customer' AND t.party_id = c.id
    LEFT JOIN buyers b ON t.party_type='buyer' AND t.party_id = b.id
    WHERE $where
    ORDER BY t.transaction_date ASC
");

$summary = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT 
        SUM(credit) as total_credit,
        SUM(debit) as total_debit,
        SUM(credit - debit) as net_balance,
        COUNT(*) as total_transactions
    FROM transactions t
    LEFT JOIN customers c ON t.party_type='customer' AND t.party_id = c.id
    LEFT JOIN buyers b ON t.party_type='buyer' AND t.party_id = b.id
    WHERE $where
"));
?>

<!DOCTYPE html>
<html>
<head>
<title>Ledger - LedgerSys</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
.search-section {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    margin-bottom: 15px;
}

.search-box {
    flex: 1;
    min-width: 200px;
    position: relative;
}

.search-box input {
    width: 100%;
    padding: 8px 35px 8px 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.search-box i {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
}

.filter-info {
    background: #f8f9fa;
    padding: 10px 15px;
    border-radius: 5px;
    margin-bottom: 15px;
    border-left: 4px solid #3498db;
}

.filter-info span {
    color: #666;
    font-size: 14px;
}

.clear-search {
    color: #e74c3c;
    text-decoration: none;
    margin-left: 10px;
}

.clear-search:hover {
    text-decoration: underline;
}

.quick-filters {
    display: flex;
    gap: 10px;
    margin-top: 10px;
    flex-wrap: wrap;
}

.quick-filter-btn {
    padding: 6px 12px;
    background: #ecf0f1;
    border: none;
    border-radius: 4px;
    color: #666;
    text-decoration: none;
    font-size: 13px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.3s;
}

.quick-filter-btn:hover {
    background: #3498db;
    color: white;
}
</style>
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
            <a href="ledger.php" class="active"><i class="fas fa-file-invoice-dollar"></i> Ledger</a>
            <a href="stock.php"><i class="fas fa-boxes"></i> Stock</a>
        </div>
    </div>
    <div class="nav-right">
        <span class="welcome-msg"><i class="fas fa-user-circle"></i> <?php echo $_SESSION['user']; ?></span>
        <a href="../auth/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</nav>

<div class="container">
    <h2>General Ledger</h2>
    
    <!-- Search Section -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-header">
            <h3><i class="fas fa-search"></i> Search Transactions</h3>
        </div>
        <div class="card-body">
            <form method="get">
                <div class="search-section">
                    <div class="search-box">
                        <label style="display: block; margin-bottom: 5px; color: #666; font-size: 14px;">By Date</label>
                        <input type="date" name="date" value="<?php echo $search_date; ?>" 
                               max="<?php echo date('Y-m-d'); ?>" placeholder="Select date">
                    </div>
                    
                    <div class="search-box">
                        <label style="display: block; margin-bottom: 5px; color: #666; font-size: 14px;">By Customer/Buyer Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($search_name); ?>" 
                               placeholder="Enter customer or buyer name">
                    </div>
                    
                    <div style="display: flex; align-items: flex-end;">
                        <button type="submit" class="btn btn-primary" style="height: 38px;">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </div>
                
                <?php if($search_date || $search_name): ?>
                <div class="filter-info">
                    <span>
                        <i class="fas fa-filter"></i> Active filters: 
                        <?php if($search_date): ?>
                            <strong>Date: <?php echo date('d M Y', strtotime($search_date)); ?></strong>
                        <?php endif; ?>
                        <?php if($search_name): ?>
                            <?php if($search_date) echo ' | '; ?>
                            <strong>Name: "<?php echo htmlspecialchars($search_name); ?>"</strong>
                        <?php endif; ?>
                        <a href="ledger.php" class="clear-search">
                            <i class="fas fa-times"></i> Clear all filters
                        </a>
                    </span>
                </div>
                <?php endif; ?>
                
                <div class="quick-filters">
                    <a href="ledger.php?date=<?php echo date('Y-m-d'); ?>" class="quick-filter-btn">
                        <i class="fas fa-sun"></i> Today
                    </a>
                    <a href="ledger.php?date=<?php echo date('Y-m-d', strtotime('-1 day')); ?>" class="quick-filter-btn">
                        <i class="fas fa-calendar-minus"></i> Yesterday
                    </a>
                    <a href="ledger.php" class="quick-filter-btn">
                        <i class="fas fa-calendar-alt"></i> All Transactions
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-arrow-down"></i></div>
            <h3>Total Income</h3>
            <h2>Rs. <?php echo number_format($summary['total_credit'] ?? 0, 2); ?></h2>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-arrow-up"></i></div>
            <h3>Total Expense</h3>
            <h2>Rs. <?php echo number_format($summary['total_debit'] ?? 0, 2); ?></h2>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-balance-scale"></i></div>
            <h3>Net Balance</h3>
            <h2 style="color: <?php echo ($summary['net_balance'] ?? 0) >= 0 ? '#2ecc71' : '#e74c3c'; ?>">
                Rs. <?php echo number_format($summary['net_balance'] ?? 0, 2); ?>
            </h2>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-exchange-alt"></i></div>
            <h3>Transactions</h3>
            <h2><?php echo $summary['total_transactions'] ?? 0; ?></h2>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-file-alt"></i> 
                <?php 
                if($search_date && $search_name) {
                    echo 'Transactions for ' . date('d M Y', strtotime($search_date)) . ' & Name: "' . htmlspecialchars($search_name) . '"';
                } elseif($search_date) {
                    echo 'Transactions for ' . date('d M Y', strtotime($search_date));
                } elseif($search_name) {
                    echo 'Transactions for: "' . htmlspecialchars($search_name) . '"';
                } else {
                    echo 'Complete Ledger';
                }
                ?>
                <span style="color: #666; font-size: 14px; margin-left: 10px;">
                    (<?php echo mysqli_num_rows($transactions); ?> transactions found)
                </span>
            </h3>
        </div>
        <div class="card-body">
            <?php if(mysqli_num_rows($transactions) > 0): 
                $balance = 0;
            ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Party</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Credit</th>
                                <th>Debit</th>
                                <th>Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($transactions)):
                                $balance += $row['credit'];
                                $balance -= $row['debit'];
                            ?>
                            <tr>
                                <td><?php echo $row['transaction_date']; ?></td>
                                <td>
                                    <strong><?php echo $row['party_name']; ?></strong>
                                    <?php if($search_name && stripos($row['party_name'], $search_name) !== false): ?>
                                        <span style="background: #ffeaa7; padding: 2px 5px; border-radius: 3px; font-size: 11px; margin-left: 5px;">
                                            <i class="fas fa-search"></i> Match
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo $row['party_type'] == 'customer' ? 'badge-success' : 'badge-danger'; ?>">
                                        <?php echo ucfirst($row['party_type']); ?>
                                    </span>
                                </td>
                                <td><?php echo $row['description']; ?></td>
                                <td><?php echo $row['credit'] > 0 ? number_format($row['credit'], 2) : '-'; ?></td>
                                <td><?php echo $row['debit'] > 0 ? number_format($row['debit'], 2) : '-'; ?></td>
                                <td style="font-weight: bold; color: <?php echo $balance >= 0 ? '#2ecc71' : '#e74c3c'; ?>">
                                    <?php echo number_format($balance, 2); ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <h3>No Transactions Found</h3>
                    <p>
                        <?php 
                        if($search_date && $search_name) {
                            echo 'No transactions found for ' . date('d M Y', strtotime($search_date)) . 
                                 ' with name containing "' . htmlspecialchars($search_name) . '"';
                        } elseif($search_date) {
                            echo 'No transactions found for ' . date('d M Y', strtotime($search_date));
                        } elseif($search_name) {
                            echo 'No transactions found for customer/buyer name containing "' . htmlspecialchars($search_name) . '"';
                        } else {
                            echo 'No transactions found in the ledger';
                        }
                        ?>
                    </p>
                    <?php if($search_date || $search_name): ?>
                        <a href="ledger.php" class="btn btn-primary">
                            <i class="fas fa-times"></i> Clear Search
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.querySelector('input[name="name"]');
    if(nameInput && !nameInput.value) {
        nameInput.focus();
    }
});
</script>

</body>
</html>
