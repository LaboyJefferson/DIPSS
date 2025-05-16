@extends('layouts.app')

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

            {{-- Form for submitting all inventory data --}}
            <form action="{{ route('inventory.audit.step2') }}" method="POST">
                @csrf
                <table class="table table-responsive">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Count Stock in the Store <i>*Required</i></th>
                            <th>Count Stock in the Stockroom <i>*Required</i></th>
                            <th>Quantity on Hand</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inventoryJoined as $key => $data)
                            <tr>
                                <td>{{ $data->product_name }}</td>
                                <input type="hidden" name="product_name[]" value="{{ $data->product_name }}">
                                <input type="hidden" name="inventory_id[]" value="{{ $data->inventory_id }}">
                                <input type="hidden" name="stockroom_id[]" value="{{ $data->stockroom_id }}">
                                <input type="hidden" name="previous_quantity_on_hand[]" value="{{ $data->in_stock }}">
                                <input type="hidden" name="previous_product_quantity[]" value="{{ $data->product_quantity }}">
                
                                <td><input class="form-control" type="number" oninput="calculateQoH({{ $key }})" id="count_store_stock_{{ $key }}" name="count_store_quantity[]" placeholder="Input numbers only" required></td>
                                <td><input class="form-control" type="number" oninput="calculateQoH({{ $key }})" id="count_stockroom_stock_{{ $key }}" name="count_stockroom_quantity[]" placeholder="Input numbers only" required></td>
                                <td><input class="form-control" type="number" id="count_qoh_{{ $key }}" name="count_quantity_on_hand[]" placeholder="This is a read only field" readonly></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No inventory found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-success">Next</button>
                </div>
            </form>
        </div>
    </main>
</div>

{{-- JavaScript for calculating Quantity on Hand --}}
<script>
    function calculateQoH(index) {
        const storeStock = parseInt(document.getElementById(`count_store_stock_${index}`).value) || 0;
        const stockroomStock = parseInt(document.getElementById(`count_stockroom_stock_${index}`).value) || 0;
        const qoh = storeStock + stockroomStock;
        document.getElementById(`count_qoh_${index}`).value = qoh;
    }
</script>
@endsection
