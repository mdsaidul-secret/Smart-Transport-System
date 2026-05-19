<!DOCTYPE html>
<html>
<head>
    <title>Smart Transport System | DBMS Project</title>
<!DOCTYPE html>
<html>
<head>
    <title>Smart Transport System | DBMS Project</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Arial; }
    body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; }
    .container { max-width: 1300px; margin: auto; }
    h1 { text-align: center; color: white; margin-bottom: 5px; }
    .subtitle { text-align: center; color: #ddd; margin-bottom: 25px; }
    .box { background: white; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
    .box h2 { color: #667eea; margin-bottom: 15px; border-left: 4px solid #667eea; padding-left: 12px; }
    input, select { width: 100%; padding: 10px; margin: 8px 0; border: 1px solid #ddd; border-radius: 6px; }
    button { width: 100%; padding: 12px; background: #667eea; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 14px; }
    button:hover { background: #5a67d8; }
    .success { background: #d4edda; color: #155724; padding: 12px; border-radius: 6px; margin: 10px 0; }
    .error { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin: 10px 0; }
    .info { background: #d1ecf1; color: #0c5460; padding: 12px; border-radius: 6px; margin: 10px 0; }
    .balance-card { background: linear-gradient(135deg, #11998e, #38ef7d); color: white; padding: 20px; border-radius: 12px; text-align: center; margin-bottom: 20px; }
    .blocked-card { background: linear-gradient(135deg, #eb3349, #f45c43); }
    
    /* TABLE STYLES - FIXED */
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { padding: 12px; }
    th { background: #667eea; color: white; font-weight: bold; }
    th:first-child { text-align: left; border-radius: 8px 0 0 8px; }
    th:last-child { border-radius: 0 8px 8px 0; }
    td { border-bottom: 1px solid #eee; }
    tr:hover { background: #f5f5f5; }
    
    .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 20px; }
    .badge { padding: 3px 8px; border-radius: 20px; font-size: 11px; font-weight: bold; display: inline-block; }
    .badge-active { background: #28a745; color: white; }
    .badge-blocked { background: #dc3545; color: white; }
    footer { text-align: center; color: white; margin-top: 30px; padding: 20px; }
    
    /* Responsive */
    @media (max-width: 768px) {
        .grid { grid-template-columns: 1fr; }
        body { padding: 10px; }
        th, td { padding: 8px; font-size: 12px; }
    }
</style>
</head>
<body>
<div class="container">
    <h1>🚗 Integrated Smart Transport System</h1>
    <div class="subtitle">Toll | Fuel | Parking Management | DBMS Lab Project Spring 2026</div>

<?php
// ==============================================
// DATABASE CONNECTION
// ==============================================
$conn = mysqli_connect("localhost", "root", "", "transport_system");
if (!$conn) {
    die("<div class='error'>Connection failed: " . mysqli_connect_error() . "</div>");
}

// ==============================================
// 1. REGISTER VEHICLE (INSERT)
// ==============================================
if(isset($_POST['register'])) {
    $number = strtoupper(mysqli_real_escape_string($conn, $_POST['vehicle_number']));
    $owner = mysqli_real_escape_string($conn, $_POST['owner_name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $type = mysqli_real_escape_string($conn, $_POST['vehicle_type']);
    
    $sql = "INSERT INTO vehicles (vehicle_number, owner_name, phone, vehicle_type, wallet_balance) 
            VALUES ('$number', '$owner', '$phone', '$type', 0)";
    
    if(mysqli_query($conn, $sql)) {
        echo "<div class='success'>✅ Vehicle registered successfully!</div>";
    } else {
        echo "<div class='error'>❌ Error: " . mysqli_error($conn) . "</div>";
    }
}

// ==============================================
// 2. RECHARGE WALLET (UPDATE)
// ==============================================
if(isset($_POST['recharge'])) {
    $number = mysqli_real_escape_string($conn, $_POST['recharge_vehicle']);
    $amount = floatval($_POST['amount']);
    
    $sql1 = "UPDATE vehicles SET wallet_balance = wallet_balance + $amount, is_blocked = 0 WHERE vehicle_number = '$number'";
    
    if(mysqli_query($conn, $sql1)) {
        $sql2 = "INSERT INTO recharges (vehicle_number, amount) VALUES ('$number', $amount)";
        mysqli_query($conn, $sql2);
        echo "<div class='success'>✅ Recharged $$amount successfully!</div>";
    } else {
        echo "<div class='error'>❌ " . mysqli_error($conn) . "</div>";
    }
}

// ==============================================
// 3. TOLL PAYMENT (UPDATE + INSERT)
// ==============================================
if(isset($_POST['pay_toll'])) {
    $number = mysqli_real_escape_string($conn, $_POST['toll_vehicle']);
    $amount = floatval($_POST['toll_amount']);
    $tid = "TXN" . rand(10000, 99999);
    
    // Check balance
    $check = mysqli_query($conn, "SELECT wallet_balance, is_blocked FROM vehicles WHERE vehicle_number='$number'");
    $vehicle = mysqli_fetch_assoc($check);
    
    if(!$vehicle) {
        echo "<div class='error'>❌ Vehicle not found!</div>";
    } elseif($vehicle['is_blocked'] == 1) {
        echo "<div class='error'>❌ Vehicle is BLOCKED! Please recharge.</div>";
    } elseif($vehicle['wallet_balance'] >= $amount) {
        $new_balance = $vehicle['wallet_balance'] - $amount;
        
        $sql1 = "UPDATE vehicles SET wallet_balance = $new_balance WHERE vehicle_number = '$number'";
        mysqli_query($conn, $sql1);
        
        $sql2 = "INSERT INTO toll_payments (vehicle_number, amount, transaction_id) VALUES ('$number', $amount, '$tid')";
        mysqli_query($conn, $sql2);
        
        echo "<div class='success'>✅ Toll paid: $$amount | New balance: $$new_balance</div>";
        
        // Block if balance low
        if($new_balance < 50) {
            $sql3 = "UPDATE vehicles SET is_blocked = 1 WHERE vehicle_number = '$number'";
            mysqli_query($conn, $sql3);
            echo "<div class='info'>⚠️ Balance below $50! Vehicle BLOCKED.</div>";
        }
    } else {
        $sql3 = "UPDATE vehicles SET is_blocked = 1 WHERE vehicle_number = '$number'";
        mysqli_query($conn, $sql3);
        echo "<div class='error'>❌ Insufficient balance! Vehicle BLOCKED.</div>";
    }
}

// ==============================================
// 4. FUEL PURCHASE (UPDATE + INSERT)
// ==============================================
if(isset($_POST['buy_fuel'])) {
    $number = mysqli_real_escape_string($conn, $_POST['fuel_vehicle']);
    $fuel_type = $_POST['fuel_type'];
    $liters = floatval($_POST['liters']);
    
    if($fuel_type == 'petrol') { $price = 1.4; $name = 'Petrol'; }
    elseif($fuel_type == 'diesel') { $price = 1.1; $name = 'Diesel'; }
    else { $price = 0.9; $name = 'Electric'; }
    
    $total = $price * $liters;
    
    $check = mysqli_query($conn, "SELECT wallet_balance, is_blocked FROM vehicles WHERE vehicle_number='$number'");
    $vehicle = mysqli_fetch_assoc($check);
    
    if(!$vehicle) {
        echo "<div class='error'>❌ Vehicle not found!</div>";
    } elseif($vehicle['is_blocked'] == 1) {
        echo "<div class='error'>❌ Vehicle is BLOCKED!</div>";
    } elseif($vehicle['wallet_balance'] >= $total) {
        $new_balance = $vehicle['wallet_balance'] - $total;
        
        $sql1 = "UPDATE vehicles SET wallet_balance = $new_balance WHERE vehicle_number = '$number'";
        mysqli_query($conn, $sql1);
        
        $sql2 = "INSERT INTO fuel_purchases (vehicle_number, fuel_type, liters, total_cost) VALUES ('$number', '$name', $liters, $total)";
        mysqli_query($conn, $sql2);
        
        echo "<div class='success'>✅ Purchased $liters L of $name for $$total | New balance: $$new_balance</div>";
        
        if($new_balance < 50) {
            $sql3 = "UPDATE vehicles SET is_blocked = 1 WHERE vehicle_number = '$number'";
            mysqli_query($conn, $sql3);
            echo "<div class='info'>⚠️ Balance low! Vehicle BLOCKED.</div>";
        }
    } else {
        $sql3 = "UPDATE vehicles SET is_blocked = 1 WHERE vehicle_number = '$number'";
        mysqli_query($conn, $sql3);
        echo "<div class='error'>❌ Insufficient balance! Vehicle BLOCKED.</div>";
    }
}

// ==============================================
// 5. PARKING RESERVATION (UPDATE + INSERT)
// ==============================================
if(isset($_POST['park'])) {
    $number = mysqli_real_escape_string($conn, $_POST['parking_vehicle']);
    $hours = intval($_POST['hours']);
    $total = $hours * 25;
    $slot = "P-" . rand(100, 999);
    
    $check = mysqli_query($conn, "SELECT wallet_balance, is_blocked FROM vehicles WHERE vehicle_number='$number'");
    $vehicle = mysqli_fetch_assoc($check);
    
    if(!$vehicle) {
        echo "<div class='error'>❌ Vehicle not found!</div>";
    } elseif($vehicle['is_blocked'] == 1) {
        echo "<div class='error'>❌ Vehicle is BLOCKED!</div>";
    } elseif($vehicle['wallet_balance'] >= $total) {
        $new_balance = $vehicle['wallet_balance'] - $total;
        
        $sql1 = "UPDATE vehicles SET wallet_balance = $new_balance WHERE vehicle_number = '$number'";
        mysqli_query($conn, $sql1);
        
        $sql2 = "INSERT INTO parking_reservations (vehicle_number, hours, slot_number, total_amount) VALUES ('$number', $hours, '$slot', $total)";
        mysqli_query($conn, $sql2);
        
        echo "<div class='success'>✅ Parking reserved for $hours hours at slot $slot | Paid: $$total | New balance: $$new_balance</div>";
        
        if($new_balance < 50) {
            $sql3 = "UPDATE vehicles SET is_blocked = 1 WHERE vehicle_number = '$number'";
            mysqli_query($conn, $sql3);
            echo "<div class='info'>⚠️ Balance low! Vehicle BLOCKED.</div>";
        }
    } else {
        $sql3 = "UPDATE vehicles SET is_blocked = 1 WHERE vehicle_number = '$number'";
        mysqli_query($conn, $sql3);
        echo "<div class='error'>❌ Insufficient balance! Vehicle BLOCKED.</div>";
    }
}

// ==============================================
// GET VEHICLE DATA (SELECT)
// ==============================================
$current_vehicle = "";
$current_balance = 0;
$is_blocked = false;

if(isset($_POST['check_vehicle'])) {
    $search = mysqli_real_escape_string($conn, $_POST['check_vehicle']);
    $sql = "SELECT * FROM vehicles WHERE vehicle_number = '$search'";
    $result = mysqli_query($conn, $sql);
    if($row = mysqli_fetch_assoc($result)) {
        $current_vehicle = $row['vehicle_number'];
        $current_balance = $row['wallet_balance'];
        $is_blocked = $row['is_blocked'];
    }
}
?>

<!-- ============================================== -->
<!-- CHECK VEHICLE SECTION -->
<!-- ============================================== -->
<div class="box">
    <h2>🔍 Check Vehicle & Balance</h2>
    <form method="POST">
        <input type="text" name="check_vehicle" placeholder="Enter Vehicle Number" required>
        <button type="submit">Check Vehicle</button>
    </form>
    
    <?php if($current_vehicle): ?>
    <div class="balance-card <?php echo $is_blocked ? 'blocked-card' : ''; ?>">
        <strong>Vehicle:</strong> <?php echo $current_vehicle; ?><br>
        <strong>Wallet Balance:</strong> $<?php echo number_format($current_balance, 2); ?><br>
        <strong>Status:</strong> <?php echo $is_blocked ? '🔴 BLOCKED (Low Balance)' : '🟢 ACTIVE'; ?>
    </div>
    <?php endif; ?>
</div>

<div class="grid">
    <!-- ============================================== -->
    <!-- REGISTER VEHICLE - INSERT QUERY -->
    <!-- ============================================== -->
    <div class="box">
        <h2>📝 Register Vehicle</h2>
        <form method="POST">
            <input type="text" name="vehicle_number" placeholder="Vehicle Number (e.g., DHAKA-1234)" required>
            <input type="text" name="owner_name" placeholder="Owner Name" required>
            <input type="text" name="phone" placeholder="Phone Number" required>
            <select name="vehicle_type" required>
                <option value="Car">Car</option>
                <option value="Bus">Bus</option>
                <option value="Truck">Truck</option>
                <option value="Bike">Bike</option>
            </select>
            <button type="submit" name="register">Register Vehicle</button>
        </form>
    </div>
    
    <!-- ============================================== -->
    <!-- RECHARGE - UPDATE QUERY -->
    <!-- ============================================== -->
    <div class="box">
        <h2>💰 Recharge Wallet</h2>
        <form method="POST">
            <input type="text" name="recharge_vehicle" placeholder="Vehicle Number" value="<?php echo $current_vehicle; ?>" required>
            <input type="number" name="amount" step="10" placeholder="Amount ($)" required>
            <button type="submit" name="recharge">Recharge Wallet</button>
        </form>
    </div>
    
    <!-- ============================================== -->
    <!-- TOLL PAYMENT - UPDATE + INSERT -->
    <!-- ============================================== -->
    <div class="box">
        <h2>🛣️ Toll Payment</h2>
        <form method="POST">
            <input type="text" name="toll_vehicle" placeholder="Vehicle Number" value="<?php echo $current_vehicle; ?>" required>
            <select name="toll_amount" required>
                <option value="80">Car - $80</option>
                <option value="150">Bus - $150</option>
                <option value="200">Truck - $200</option>
            </select>
            <button type="submit" name="pay_toll">Pay Toll</button>
        </form>
    </div>
    
    <!-- ============================================== -->
    <!-- FUEL PURCHASE - UPDATE + INSERT -->
    <!-- ============================================== -->
    <div class="box">
        <h2>⛽ Fuel Purchase</h2>
        <form method="POST">
            <input type="text" name="fuel_vehicle" placeholder="Vehicle Number" value="<?php echo $current_vehicle; ?>" required>
            <select name="fuel_type" required>
                <option value="petrol">Petrol - $1.4/L</option>
                <option value="diesel">Diesel - $1.1/L</option>
                <option value="electric">Electric - $0.9/kWh</option>
            </select>
            <input type="number" step="0.1" name="liters" placeholder="Quantity (L/kWh)" required>
            <button type="submit" name="buy_fuel">Buy Fuel</button>
        </form>
    </div>
    
    <!-- ============================================== -->
    <!-- PARKING - UPDATE + INSERT -->
    <!-- ============================================== -->
    <div class="box">
        <h2>🅿️ Parking Reservation</h2>
        <form method="POST">
            <input type="text" name="parking_vehicle" placeholder="Vehicle Number" value="<?php echo $current_vehicle; ?>" required>
            <input type="number" name="hours" placeholder="Hours ($25/hour)" required>
            <button type="submit" name="park">Reserve Parking</button>
        </form>
    </div>
</div>

<!-- ============================================== -->
<!-- TRANSACTION HISTORY - JOIN QUERY -->
<!-- ============================================== -->
<div class="box">
    <h2>📜 Transaction History</h2>
    <?php
    $sql = "SELECT v.vehicle_number, v.owner_name, 'Toll' as service, t.amount, t.transaction_id as ref, t.payment_date as date
            FROM vehicles v JOIN toll_payments t ON v.vehicle_number = t.vehicle_number
            UNION ALL
            SELECT v.vehicle_number, v.owner_name, 'Fuel' as service, f.total_cost, f.fuel_type, f.purchase_date
            FROM vehicles v JOIN fuel_purchases f ON v.vehicle_number = f.vehicle_number
            UNION ALL
            SELECT v.vehicle_number, v.owner_name, 'Parking' as service, p.total_amount, p.slot_number, p.booking_date
            FROM vehicles v JOIN parking_reservations p ON v.vehicle_number = p.vehicle_number
            UNION ALL
            SELECT v.vehicle_number, v.owner_name, 'Recharge' as service, r.amount, 'Wallet', r.recharge_date
            FROM vehicles v JOIN recharges r ON v.vehicle_number = r.vehicle_number
            ORDER BY date DESC LIMIT 20";
    
    $result = mysqli_query($conn, $sql);
    if(mysqli_num_rows($result) > 0) {
        echo "<table>
                <tr><th>Vehicle</th><th>Owner</th><th>Service</th><th>Amount</th><th>Reference</th><th>Date</th></tr>";
        while($row = mysqli_fetch_assoc($result)) {
            $color = ($row['service'] == 'Toll') ? '#ff6b6b' : (($row['service'] == 'Fuel') ? '#4ecdc4' : (($row['service'] == 'Parking') ? '#ffe66d' : '#95e77e'));
            echo "<tr>
                    <td>{$row['vehicle_number']}</td>
                    <td>{$row['owner_name']}</td>
                    <td style='background:$color; border-radius:5px; text-align:center;'>{$row['service']}</td>
                    <td>\${$row['amount']}</td>
                    <td>{$row['ref']}</td>
                    <td>{$row['date']}</td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='info'>No transactions yet. Make some payments first!</div>";
    }
    ?>
</div>

<!-- ============================================== -->
<!-- ADMIN SECTION - SELECT ALL VEHICLES -->
<!-- ============================================== -->
<div class="box">
    <h2>👑 Admin Panel - All Vehicles</h2>
    <?php
    $sql = "SELECT vehicle_id, vehicle_number, owner_name, phone, vehicle_type, wallet_balance, 
            CASE WHEN is_blocked = 1 THEN 'BLOCKED' ELSE 'ACTIVE' END as status
            FROM vehicles ORDER BY reg_date DESC";
    
    $result = mysqli_query($conn, $sql);
    echo "<table>
            <tr><th>ID</th><th>Vehicle No</th><th>Owner</th><th>Phone</th><th>Type</th><th>Balance</th><th>Status</th></tr>";
    while($row = mysqli_fetch_assoc($result)) {
        $status_class = $row['status'] == 'BLOCKED' ? 'badge-blocked' : 'badge-active';
        echo "<tr>
                <td>{$row['vehicle_id']}</td>
                <td><strong>{$row['vehicle_number']}</strong></td>
                <td>{$row['owner_name']}</td>
                <td>{$row['phone']}</td>
                <td>{$row['vehicle_type']}</td>
                <td>\${$row['wallet_balance']}</td>
                <td><span class='badge $status_class'>{$row['status']}</span></td>
              </tr>";
    }
    echo "</table>";
    ?>
</div>

<!-- ============================================== -->
<!-- REVENUE REPORT - AGGREGATE QUERY -->
<!-- ============================================== -->
<div class="box">
    <h2>📊 Revenue Report (AGGREGATE Functions - SUM, COUNT)</h2>
    <?php
    $sql = "SELECT 'Toll' as service, COALESCE(SUM(amount),0) as total, COUNT(*) as count FROM toll_payments
            UNION
            SELECT 'Fuel', COALESCE(SUM(total_cost),0), COUNT(*) FROM fuel_purchases
            UNION
            SELECT 'Parking', COALESCE(SUM(total_amount),0), COUNT(*) FROM parking_reservations";
    
    $result = mysqli_query($conn, $sql);
    $grand_total = 0;
    
    echo "<table style='width:100%; border-collapse:collapse; margin-top:15px;'>";
    echo "<thead>
            <tr style='background:#667eea; color:white;'>
                <th style='padding:12px 15px; text-align:left; border-radius:8px 0 0 8px;'>Service</th>
                <th style='padding:12px 15px; text-align:right;'>Total Revenue</th>
                <th style='padding:12px 15px; text-align:center; border-radius:0 8px 8px 0;'>No. of Transactions</th>
            </tr>
          </thead>";
    echo "<tbody>";
    
    while($row = mysqli_fetch_assoc($result)) {
        $grand_total += $row['total'];
        $icon = ($row['service'] == 'Toll') ? '🛣️' : (($row['service'] == 'Fuel') ? '⛽' : '🅿️');
        echo "<tr style='border-bottom:1px solid #e0e0e0;'>
                <td style='padding:10px 15px; text-align:left; font-weight:bold;'>$icon {$row['service']}</td>
                <td style='padding:10px 15px; text-align:right; color:#28a745; font-weight:bold;'>\$" . number_format($row['total'], 2) . "</td>
                <td style='padding:10px 15px; text-align:center;'>" . $row['count'] . "</td>
              </tr>";
    }
    
    echo "<tr style='background:#f8f9fa; font-weight:bold; border-top:2px solid #667eea;'>
            <td style='padding:12px 15px; text-align:left; font-size:16px;'>💰 GRAND TOTAL</td>
            <td style='padding:12px 15px; text-align:right; color:#dc3545; font-size:18px;'>\$" . number_format($grand_total, 2) . "</td>
            <td style='padding:12px 15px; text-align:center;'>-</td>
          </tr>";
    echo "</tbody></table>";
    ?>
</div>

<!-- ============================================== -->
<!-- BLOCKED VEHICLES - WHERE CLAUSE -->
<!-- ============================================== -->
<div class="box">
    <h2>🚫 Blocked Vehicles</h2>
    <?php
    $sql = "SELECT vehicle_number, owner_name, wallet_balance FROM vehicles WHERE is_blocked = 1";
    
    $result = mysqli_query($conn, $sql);
    if(mysqli_num_rows($result) > 0) {
        echo "<td><tr><th>Vehicle</th><th>Owner</th><th>Balance</th></tr>";
        while($row = mysqli_fetch_assoc($result)) {
            echo "<tr><td>{$row['vehicle_number']}</td><td>{$row['owner_name']}</td><td>\${$row['wallet_balance']}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='success'>No blocked vehicles. All are active!</div>";
    }
    ?>
</div>

<!-- ============================================== -->
<!-- DATABASE CONSTRAINTS SUMMARY -->
<!-- ============================================== -->
<div class="box">
    <h2>🔒 Database Constraints Used in This Project</h2>
    <ul style="margin-left: 20px; line-height: 1.8;">
        <li><strong>PRIMARY KEY</strong> - vehicle_id, id in all transaction tables</li>
        <li><strong>FOREIGN KEY</strong> - vehicle_number references vehicles(vehicle_number) with ON DELETE CASCADE</li>
        <li><strong>UNIQUE</strong> - vehicle_number, transaction_id</li>
        <li><strong>NOT NULL</strong> - All important fields</li>
        <li><strong>DEFAULT</strong> - wallet_balance DEFAULT 0, is_blocked DEFAULT 0</li>
        <li><strong>AUTO_INCREMENT</strong> - All ID fields</li>
        <li><strong>CASCADE DELETE</strong> - When vehicle deleted, all related transactions are deleted automatically</li>
    </ul>
</div>

<footer>
    <p>© 2026 Integrated Smart Transport System | DBMS Lab Project | Spring 2026</p>
    <p>Department of CSE | Batch 57th</p>
</footer>

</div>
</body>
</html>

<?php mysqli_close($conn); ?>