@extends('layouts.app')

<!-- Include the vertical navigation bar -->
@include('common.navbar')

@section('content')
    <div class="container-fluid">
        <main class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
            <div class="main-content">
                <!-- Alert Messages -->
                @include('common.alert')

                <!-- Progress Bar -->
                <div class="progress" style="height: 20px; margin-bottom: 20px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" 
                        style="width: {{ $progress }}%;" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                        {{ $progress }}%
                    </div>
                </div>

                <!-- Form for Resolving Discrepancies -->
                <form action="{{ route('inventory.audit.step4') }}" method="POST">
                    @csrf
                    <table class="table table-responsive">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Describe Actions Taken to Resolve Discrepancies <i>*Required</i></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($discrepancies as $key => $discrepancy)
                                <input type="hidden" name="inventory_id[]" value="{{ $discrepancy['inventory']->inventory_id }}">
                                <tr>
                                    <td>{{ $discrepancy['inventory']->product_name }}</td>
                                    <td>
                                        <textarea 
                                            class="form-control actions-taken" 
                                            id="actions_taken_{{ $key }}" 
                                            rows="5" 
                                            name="actions_taken[{{ $key }}]" 
                                            placeholder="List all actions taken for each discrepancy here..." 
                                            maxlength="500" 
                                            required
                                            data-char-count-target="charCount_{{ $key }}"></textarea>
                                        <small id="charCount_{{ $key }}" class="form-text text-muted">0 / 500 characters used</small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">Next</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Attach input listeners to all textareas
        const textareas = document.querySelectorAll('.actions-taken');
        textareas.forEach(textarea => {
            const charCount = document.getElementById(textarea.dataset.charCountTarget);
            textarea.addEventListener('input', function() {
                if (charCount) {
                    charCount.textContent = `${textarea.value.length} / 500 characters used`;
                }
            });
        });
    });
</script>
