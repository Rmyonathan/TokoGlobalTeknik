<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Nota Pembelian</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            font-size: 14px;
        }
        .filter-form {
            margin-bottom: 20px;
        }
        .filter-form input {
            padding: 6px;
            margin-right: 5px;
        }
        .btn {
            padding: 6px 12px;
            cursor: pointer;
            background-color: #17a2b8;
            color: white;
            border: none;
            border-radius: 4px;
        }
        .btn-reset {
            background-color: #6c757d;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
        }
    </style>
</head>
<body>

    <h2>Daftar Nota Pembelian</h2>

    <div class="filter-form">
        <input type="text" id="searchInput" placeholder="Cari Supplier...">
        <input type="date" id="startDate">
        <input type="date" id="endDate">
        <button class="btn" onclick="applyFilters()">Filter</button>
        <button class="btn btn-reset" onclick="resetFilters()">Reset</button>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nomor Nota</th>
                <th>Tanggal</th>
                <th>Kode Supplier</th>
                <th>Nama Supplier</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody id="purchaseTableBody">
            <!-- Data dummy -->
            <tr>
                <td>1</td>
                <td>PB001</td>
                <td>2025-04-01</td>
                <td>SUP001</td>
                <td>PT Maju Jaya</td>
                <td>1.000.000</td>
            </tr>
            <tr>
                <td>2</td>
                <td>PB002</td>
                <td>2025-04-15</td>
                <td>SUP002</td>
                <td>CV Sumber Rezeki</td>
                <td>850.000</td>
            </tr>
            <tr>
                <td>3</td>
                <td>PB003</td>
                <td>2025-04-25</td>
                <td>SUP003</td>
                <td>UD Makmur</td>
                <td>1.500.000</td>
            </tr>
        </tbody>
    </table>

    <script>
        function applyFilters() {
            const search = document.getElementById("searchInput").value.toLowerCase();
            const startDate = document.getElementById("startDate").value;
            const endDate = document.getElementById("endDate").value;

            const rows = document.querySelectorAll("#purchaseTableBody tr");

            rows.forEach(row => {
                const supplier = row.cells[4].textContent.toLowerCase();
                const kode = row.cells[3].textContent.toLowerCase();
                const date = row.cells[2].textContent;

                const matchesSearch = supplier.includes(search) || kode.includes(search);
                const matchesDate =
                    (!startDate || new Date(date) >= new Date(startDate)) &&
                    (!endDate || new Date(date) <= new Date(endDate));

                if (matchesSearch && matchesDate) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        }

        function resetFilters() {
            document.getElementById("searchInput").value = "";
            document.getElementById("startDate").value = "";
            document.getElementById("endDate").value = "";

            const rows = document.querySelectorAll("#purchaseTableBody tr");
            rows.forEach(row => row.style.display = "");
        }
    </script>

</body>
</html>
