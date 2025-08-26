@extends('admin::admin.layouts.master')

@section('title', 'View Transaction - ' . ($transaction->transaction_reference ?? 'N/A'))
@section('page-title', 'Transaction Details')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.transactions.index') }}">Transaction Details</a></li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">

            <div class="card">
                <div class="card-body">

                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4 class="card-title mb-0">
                            Transaction #{{ $transaction->transaction_reference ?? '—' }}
                        </h4>
                        <a href="{{ route('admin.transactions.index') }}" class="btn btn-secondary ml-2">
                            Back
                        </a>
                    </div>

                    <div class="row">
                        <!-- Left Column: Transaction Information -->
                        <div class="col-md-8">
                            <div class="card mb-3">
                                <div class="card-header bg-primary">
                                    <h5 class="mb-0 text-white font-bold">Transaction Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Reference:</label>
                                                <p>{{ $transaction->transaction_reference ?? '—' }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Payment Gateway:</label>
                                                <p>{{ ucfirst($transaction->payment_gateway) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Amount:</label>
                                                <p>{{ number_format($transaction->amount, 2) }} {{ $transaction->currency }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Status:</label>
                                                <p>{!! config('product.constants.transactionStatusLabel.' . $transaction->status, 'N/A') !!}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Date:</label>
                                                <p>{{ $transaction->created_at
                                                    ? $transaction->created_at->format(config('GET.admin_date_time_format') ?? 'Y-m-d H:i:s')
                                                    : '—' }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold">User:</label>
                                                @if(class_exists(\admin\users\Models\User::class))
                                                <p>
                                                    {{ $transaction?->user?->full_name }} <br>
                                                    <small class="text-muted">{{ $transaction?->user?->email }}</small>
                                                </p>
                                                @else
                                                <p>N/A</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <!-- Metadata -->
                            <div class="card">
                                <div class="card-header bg-primary">
                                    <h5 class="mb-0 text-white font-bold">Metadata</h5>
                                </div>
                                <div class="card-body">
                                    @if (!empty($transaction->metadata) && is_array($transaction->metadata))
                                    <ul class="list-group">
                                        @foreach ($transaction->metadata as $key => $value)
                                        <li class="list-group-item">
                                            <strong>{{ ucfirst($key) }}:</strong>
                                            {{ is_array($value) ? json_encode($value) : $value }}
                                        </li>
                                        @endforeach
                                    </ul>
                                    @else
                                    <p class="text-muted">No additional metadata</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div> <!-- /.row -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection