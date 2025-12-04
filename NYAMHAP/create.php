<?php

include '../config/db.php'; 
session_start();

if (!isset($_SESSION['staff_id'])) {
    header("Location: /NYAMHAP/login.php");
    exit();
}

$staff_role = strtoupper($_SESSION['staff_role'] ?? '');
$allowed_role = 'SPEN'; 

if ($staff_role !== $allowed_role) {
    include '../layout/header.php';
    echo '<h2>❌ Akses Ditolak</h2>';
    echo '<p style="color: red;">Anda tidak memiliki izin untuk **membuat** Pesanan Baru. Fitur ini hanya untuk Staff Penjualan (Role: ' . $allowed_role . ').</p>';
    include '../layout/footer.php';
    exit(); 
}

include '../layout/header.php';

$query_pelanggan = "SELECT pelangganid, namap FROM pelanggan ORDER BY namap ASC";
$res_pelanggan = pg_query($koneksi, $query_pelanggan);
if (!$res_pelanggan) {
    die("Query Pelanggan gagal: " . pg_last_error($koneksi));
}

$query_menu = "SELECT menuid, namamenu, harga FROM menu ORDER BY namamenu ASC";
$res_menu = pg_query($koneksi, $query_menu);
if (!$res_menu) {
    die("Query Menu gagal: " . pg_last_error($koneksi));
}
?>

<h2>Buat Pesanan Baru</h2>

<form method="POST" action="proses_create.php">

    <div class="form-group">
        <label for="pelanggan_id">Pilih Pelanggan:</label>
        <select id="pelanggan_id" name="pelanggan_id" required>
            <option value="">Pilih Pelanggan</option>
            <?php 
            if (pg_num_rows($res_pelanggan) > 0) {
                while($row = pg_fetch_assoc($res_pelanggan)) {
                    $id = $row['pelangganid'];
                    $nama = $row['namap'];
                    echo "<option value='$id'>$nama</option>";
                }
            }
            ?>
        </select>
    </div>

    <hr>
    
    <h3>Detail Menu yang Dipesan</h3>
    <table id="detail-pesanan-table" style="width: 100%;">
        <thead>
            <tr>
                <th style="width: 40%;">Menu</th>
                <th style="width: 20%;">Harga Satuan (Rp)</th>
                <th style="width: 15%;">Jumlah</th>
                <th style="width: 15%;">Subtotal (Rp)</th>
                <th style="width: 10%;">Aksi</th>
            </tr>
        </thead>
        <tbody id="item-list">
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="text-align: right; font-weight: bold;">TOTAL HARGA:</td>
                <td id="total-harga-display" style="font-weight: bold; padding-left: 10px;">Rp 0</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    
    <input type="hidden" name="total_harga_final" id="total_harga_final" value="0">
    
    <button type="button" class="btn btn-warning" onclick="tambahBarisMenu()">+ Tambah Menu</button>
    <br><br>
    
    <div class="form-group">
        <button type="submit" class="btn btn-primary">Simpan Pesanan</button>
        <a href="index.php" class="btn btn-danger">Batal</a>
    </div>
</form>

<?php
$menu_data = [];
while ($row = pg_fetch_assoc($res_menu)) {
    $menu_data[] = [
        'id' => $row['menuid'],
        'nama' => $row['namamenu'],
        'harga' => $row['harga']
    ];
}
$menu_json = json_encode($menu_data);
?>

<script>
let menuItems = <?php echo $menu_json; ?>;
let itemCounter = 0;

function tambahBarisMenu() {
    itemCounter++;
    const tbody = document.getElementById('item-list');
    
    let menuOptions = '<select name="menu_id[]" onchange="updateSubtotal(' + itemCounter + ')" required>';
    menuOptions += '<option value="" data-harga="0">Pilih Menu</option>';
    menuItems.forEach(item => {
        menuOptions += `<option value="${item.id}" data-harga="${item.harga}">${item.nama}</option>`;
    });
    menuOptions += '</select>';

    const newRow = document.createElement('tr');
    newRow.id = 'row-' + itemCounter;
    newRow.innerHTML = `
        <td>${menuOptions}</td>
        <td id="harga-display-${itemCounter}">0</td>
        <td>
            <input type="number" name="jumlah[]" id="jumlah-${itemCounter}" value="1" min="1" 
                    oninput="updateSubtotal(${itemCounter})" style="width: 60px;">
        </td>
        <td id="subtotal-display-${itemCounter}">0</td>
        <td>
            <button type="button" class="btn btn-danger" onclick="hapusBaris(${itemCounter})">X</button>
        </td>
    `;
    
    tbody.appendChild(newRow);
    calculateTotal();
}

function updateSubtotal(rowId) {
    const selectElement = document.querySelector(`#row-${rowId} select[name="menu_id[]"]`);
    const inputJumlah = document.getElementById(`jumlah-${rowId}`);
    
    if (!selectElement || !inputJumlah) return;

    const selectedOption = selectElement.options[selectElement.selectedIndex];
    
    const hargaSatuan = parseInt(selectedOption.getAttribute('data-harga')) || 0;
    const jumlah = parseInt(inputJumlah.value) || 0;
    
    const subtotal = hargaSatuan * jumlah;

    document.getElementById(`harga-display-${rowId}`).innerText = hargaSatuan.toLocaleString('id-ID');
    document.getElementById(`subtotal-display-${rowId}`).innerText = subtotal.toLocaleString('id-ID');

    calculateTotal();
}

function calculateTotal() {
    let grandTotal = 0;

    document.querySelectorAll('#item-list > tr').forEach(row => {
        const rowId = row.id.split('-')[1]; 
        
        const subtotalText = document.getElementById(`subtotal-display-${rowId}`).innerText.replace(/\./g, '');
        const subtotal = parseInt(subtotalText) || 0;

        grandTotal += subtotal;
    });

    document.getElementById('total-harga-display').innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');
    document.getElementById('total_harga_final').value = grandTotal;
}

function hapusBaris(rowId) {
    const row = document.getElementById('row-' + rowId);
    if (row) {
        row.remove();
        calculateTotal();
    }
}

document.addEventListener('DOMContentLoaded', () => {
    tambahBarisMenu();
});
</script>

<?php
include '../layout/footer.php';
?>