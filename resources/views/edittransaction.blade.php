(edit booking)

@extends('layout.Nav')

@section('content')
    <section class="container-fluid py-4">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Edit History</h2>
            <a href="/booking" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to List
            </a>
        </div>

        {{-- @php
            // Test data for the booking to be edited
            $booking = [
                'id' => 1,
                'guest_name' => 'John Doe',
                'room_number' => '101',
                'email' => 'john.doe@example.com',
                'phone_number' => '1234567890',
                'check_in_date' => '2025-02-15',
                'check_out_date' => '2025-02-20',
                'total_amount' => 500000,
                'created_at' => '2025-02-14 10:00:00',
                'updated_at' => '2025-02-14 10:00:00'
            ];
        @endphp --}}

        <form action="/updatebooking" method="POST" enctype="multipart/form-data">
            @csrf
            {{-- @method('PUT') --}}
            <input type="hidden" name="booking_id" value="{{ $booking['id'] }}">
            <div class="mb-3">
                <label for="guest_name" class="form-label">Guest Name</label>
                <input type="text" class="form-control" id="guestName" name="guestName" value="{{ $booking['guestName'] }}" required>
            </div>
            <div class="mb-3">
                <label for="room_number" class="form-label">Room Number</label>
                <input type="text" class="form-control" id="room_number" name="room_number" value="{{ $booking->rooms->room_number }}" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="{{ $booking['email'] }}" required>
            </div>
            <div class="mb-3">
                <label for="phone_number" class="form-label">Phone Number</label>
                <input type="text" class="form-control" id="phone_number" name="phone_number" value="{{ $booking['phone_number'] }}" required>
            </div>
            <div class="mb-3">
                <label for="check_in_date" class="form-label">Check In Date</label>
                <input type="date" class="form-control" id="check_in_date" name="check_in_date" value="{{ $booking['check_in_date'] }}" required>
            </div>
            <div class="mb-3">
                <label for="check_out_date" class="form-label">Check Out Date</label>
                <input type="date" class="form-control" id="check_out_date" name="check_out_date" value="{{ $booking['check_out_date'] }}" required>
            </div>
            <div class="mb-3">
                <label for="total_amount" class="form-label">Total Amount</label>
                <input type="number" class="form-control" id="total_amount" name="total_amount" value="">
            </div>
            @foreach ($xitems as $xitem)
                <input type="hidden" name="xitem_id[]" value="{{$xitem->id}}">
                <div class="mb-3">
                    <label for="item_name" class="form-label">Additional Item (Operasional)</label>
                    <select class="form-control" name="item_id[]">
                        <option value="{{ $xitem->pricelists->id }}" selected>{{ $xitem->pricelists->name }}</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="quantity" class="form-label">Quantity</label>
                    <input type="number" class="form-control" name="quantity[]" value="{{ $xitem->qty }}" required>
                </div>
            @endforeach
            <button type="submit" class="btn btn-success">
                <i class="bi bi-save me-2"></i>Save Changes
            </button>
        </form>
    </section>
@endsection
