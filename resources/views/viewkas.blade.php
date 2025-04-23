@extends('layout.Nav')

@section('content')
    <section class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Cash Transactions (Saldo {{ $saldo->saldo }})</h2>
            <a href="/addtransaction" class="btn btn-success">
                <i class="bi bi-plus-circle me-2"></i>Add Transaction
            </a>
        </div>

        <!-- Enhanced Filter Form -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0" style="color: rgb(103, 59, 59)"><i class="bi bi-funnel-fill me-2"></i>Search & Filter Transactions</h5>
            </div>
            <div class="card-body">
                <form id="combinedForm" action="/viewSlide" method="get">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input class="form-control" type="search" placeholder="Search by Nama" id="searchN" name="searchN">
                                <label for="searchN"><i class="bi bi-search me-1"></i>Search by Nama</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input class="form-control" type="search" placeholder="Search by Description" id="search" name="search">
                                <label for="search"><i class="bi bi-search me-1"></i>Search by Description</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-floating">
                                <input type="date" name="start_date" class="form-control" id="start_date" value="">
                                <label for="start_date"><i class="bi bi-calendar-event me-1"></i>Start Date</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-floating">
                                <input type="date" name="end_date" class="form-control" id="end_date" value="">
                                <label for="end_date"><i class="bi bi-calendar-event me-1"></i>End Date</label>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-center">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search me-1"></i>Apply Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Enhanced Sort Button with Fixed Icons -->
        <div class="d-flex justify-content-end mb-3">
            <button id="sortButton" class="btn btn-outline-secondary position-relative px-4 py-2">
                <i id="sortIconDown" class="bi bi-arrow-down me-2 fs-5" style="display: inline;"></i>
                <i id="sortIconUp" class="bi bi-arrow-up me-2 fs-5" style="display: none;"></i>
                <span id="sortText">Newest to Oldest</span>
            </button>
        </div>

        <div class="table-responsive">
            <table id="transactionsTable" class="table table-bordered border-dark">
                <thead>
                    <tr class="table-light">
                        <th class="border border-dark text-center align-middle">Date</th>
                        <th class="border border-dark text-center align-middle">Nama</th>
                        <th class="border border-dark text-center align-middle">Description</th>
                        <th class="border border-dark text-center align-middle">Kredit</th>
                        <th class="border border-dark text-center align-middle">Debit</th>
                        <th class="border border-dark text-center align-middle">Balance</th>
                        <th class="border border-dark text-center align-middle">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cashTransactions as $transaction)
                        <tr>
                            <td class="border border-dark text-center align-middle">
                                {{ $transaction->created_at->format('Y-m-d H:i:s') }}
                            </td>
                            <td class="border border-dark text-center align-middle">{{ $transaction->name }}</td>
                            <td class="border border-dark text-center align-middle">{{ $transaction->description }}</td>
                            <td class="border border-dark text-end align-middle">
                                @if($transaction->type === 'Debit' || $transaction->type === 'Bonus')
                                    {{ number_format($transaction->transaction) }}
                                @endif
                            </td>
                            <td class="border border-dark text-end align-middle">
                                @if($transaction->type === 'Kredit')
                                    {{ number_format($transaction->transaction) }}
                                @endif
                            </td>
                            <td class="border border-dark text-end align-middle">{{ number_format($transaction->saldo) }}</td>
                            <td class="border border-dark text-center align-middle">
                                    @if ($transaction->type === 'Hutang')
                                        <form action="/edit_kas" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <input type="hidden" name="kas_id" value="{{ $transaction->id }}">
                                            <input type="hidden" name="hutang" value="hutang">
                                                <button class="btn btn-info btn-sm w-100" style="margin-bottom: 10px" type="submit"><i class="bi bi-credit-card me-1"></i> Cicil</button>
                                        </form>
                                         @else
                                        <form action="/edit_kas" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <input type="hidden" name="kas_id" value="{{ $transaction->id }}">
                                                <button class="btn btn-secondary btn-sm w-100" style="margin-bottom: 10px" type="submit"><i class="bi bi-pencil me-1"></i>Edit</button>
                                        </form>
                                    
                                        <!-- Cancel button will only show if the transaction type is not 'Hutang' -->
                                        <form action="/cancel_kas" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="transaction_id" value="{{ $transaction->id }}">
                                            <button type="submit" class="btn btn-warning btn-sm w-100" onclick="return confirm('Apakah anda yakin untuk cancel transaksi ini?')">
                                                <i class="bi bi-x-circle me-1"></i>Cancel
                                            </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center border border-dark">No transactions found.</td>
                        </tr>
                    @endforelse
                </tbody>

                @if ($grandTotal !== null)
                <tfoot>
                    <tr class="table-secondary">
                        <td colspan="3" class="border border-dark text-end align-middle fw-bold">
                            <div class="d-flex justify-content-end align-items-center">
                                <i class="bi bi-calculator me-2 fs-5"></i>
                                <span>Subtotals:</span>
                            </div>
                        </td>
                        <td class="border border-dark text-end align-middle fw-bold bg-success bg-opacity-10">
                            {{ number_format($debitTotal) }}
                        </td>
                        <td class="border border-dark text-end align-middle fw-bold bg-danger bg-opacity-10">
                            {{ number_format($kreditTotal) }}
                        </td>
                        <td colspan="2" class="border border-dark"></td>
                    </tr>
                    <tr class="table-dark">
                        <td colspan="3" class="border border-dark text-end align-middle">
                            <div class="d-flex justify-content-end align-items-center">
                                <i class="bi bi-currency-dollar me-2 fs-4" style="color: black"></i>
                                <span class="fs-5 fw-bold" style="color:black">GRAND TOTAL:</span>
                            </div>
                        </td>
                        <td colspan="2" class="border border-dark text-center align-middle bg-white">
                            <span class="fs-5 fw-bold text-dark">{{ number_format($grandTotal) }}</span>
                        </td>

                        <td class="border border-dark"></td>
                    </tr>
                </tfoot>
                @endif

            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-3">
            {{ $cashTransactions->appends(request()->query())->links() }}
        </div>
    </section>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let table = document.getElementById('transactionsTable').getElementsByTagName('tbody')[0];
            let rows = Array.from(table.rows);
            let sortButton = document.getElementById('sortButton');
            let sortIconDown = document.getElementById('sortIconDown');
            let sortIconUp = document.getElementById('sortIconUp');
            let sortText = document.getElementById('sortText');
            let isAscending = false; // Default: Newest to Oldest (Descending by Date)

            function sortTable() {
                rows.sort((a, b) => {
                    let dateA = new Date(a.cells[0].textContent.trim());
                    let dateB = new Date(b.cells[0].textContent.trim());

                    if (dateA.getTime() === dateB.getTime()) {
                        let balanceA = parseFloat(a.cells[6].textContent.replace(/,/g, ''));
                        let balanceB = parseFloat(b.cells[6].textContent.replace(/,/g, ''));

                        return isAscending ? balanceB - balanceA : balanceA - balanceB;
                    }

                    return isAscending ? dateA - dateB : dateB - dateA;
                });

                rows.forEach(row => table.appendChild(row));

                isAscending = !isAscending;

                // Update sort button icon and text
                if (isAscending) {
                    sortIconDown.style.display = 'none';
                    sortIconUp.style.display = 'inline';
                    sortText.textContent = "Oldest to Newest";
                    sortButton.classList.remove('btn-outline-secondary');
                    sortButton.classList.add('btn-outline-primary');
                } else {
                    sortIconDown.style.display = 'inline';
                    sortIconUp.style.display = 'none';
                    sortText.textContent = "Newest to Oldest";
                    sortButton.classList.remove('btn-outline-primary');
                    sortButton.classList.add('btn-outline-secondary');
                }
            }

            sortTable();

            sortButton.addEventListener('click', sortTable);
        });
    </script>
@endsection
