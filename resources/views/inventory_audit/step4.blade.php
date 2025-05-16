@extends('layouts.app')

<!-- Include the vertical navigation bar -->
@include('common.navbar')

@section('content')
    <div class="container-fluid">
        <main class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
            <div class="main-content">
                <!-- Alert Messages -->
                @include('common.alert')
                {{-- Progress bar at the top --}}
                <div class="progress" style="height: 20px; margin-bottom: 20px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" 
                        style="width: {{ $progress }}%;" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                        {{ $progress }}%
                    </div>
                </div>

                {{-- The steps taken to reconcile the products discrepancy here --}}
                <form action="{{ route('submit.step4') }}" method="POST">
                    @csrf
                    <table class="table table-responsive">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Adjusted Store Stock <i>*Required</i></th>
                                <th>Adjusted Stockroom Stock <i>*Required</i></th>
                                <th>Adjusted QoH</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($discrepancies as $key => $discrepancy)
                                <tr>
                                    <td>{{ $discrepancy['inventory']->product_name }}</td>
                                    <td>
                                        <input class="form-control" type="number" id="adjusted_store_stock_{{ $key }}" name="adjusted_store_quantity[]" placeholder="Input number only" 
                                               required oninput="calculateQoH({{ $key }})">
                                    </td>
                                    <td>
                                        <input class="form-control" type="number" id="adjusted_stockroom_stock_{{ $key }}" name="adjusted_stockroom_quantity[]" placeholder="Input number only"
                                               required oninput="calculateQoH({{ $key }})">
                                    </td>
                                    <td>
                                        <input class="form-control" type="number" id="adjusted_qoh_{{ $key }}" name="adjusted_quantity_on_hand[]" placeholder="Read only: Total" readonly>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    
                    {{-- Confirmation inputs and submit button --}}
                    <div class="row mb-3">
                        <div class="col">
                            <span class="input-group-text">
                                <i class="fa fa-key fa-lg"></i><label class="ms-2">Confirm Auditor Audit</label>
                            </span>

                            <div class="form-group">
                                <label>Auditor Email <i>*Required</i></label>
                                <input type="email" class="form-control @error('confirm_email') is-invalid @enderror" placeholder="Enter current email" name="confirm_email" required>

                                @error('confirm_email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="password">Auditor Password <i>*Required</i></label>
                                <input type="password" class="form-control @error('confirm_password') is-invalid @enderror" placeholder="Enter current password" name="confirm_password" required>
                            
                                @error('confirm_password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col">
                            <span class="input-group-text">
                                <i class="fa fa-key fa-lg"></i><label class="ms-2">Admin Confirmation</label>
                            </span>
                            <div class="form-group">
                                <label>Admin Email <i>*Required</i></label>
                                <input type="email" class="form-control @error('confirm_admin_email') is-invalid @enderror" placeholder="Enter admin email" name="confirm_admin_email" required>
                            
                                @error('confirm_admin_email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="password">Admin Password <i>*Required</i></label>
                                <input type="password" class="form-control @error('confirm_admin_password') is-invalid @enderror" placeholder="Enter current password" name="confirm_admin_password" required>
                            
                                @error('confirm_admin_password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-success">Submit Audit</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    {{-- JavaScript for calculating Quantity on Hand --}}
    <script>
        function calculateQoH(index) {
            const storeStock = parseInt(document.getElementById(`adjusted_store_stock_${index}`).value) || 0;
            const stockroomStock = parseInt(document.getElementById(`adjusted_stockroom_stock_${index}`).value) || 0;
            const qoh = storeStock + stockroomStock;
            document.getElementById(`adjusted_qoh_${index}`).value = qoh;
        }
    </script>
@endsection
