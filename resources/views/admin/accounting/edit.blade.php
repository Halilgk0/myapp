@extends('layouts.admin')

@section('title', __('Muhasebe Kaydı Düzenle'))

@section('content')
@php($lockedAgencyId = request('locked_agency_id'))
<div class="row">
    <div class="col-md-8">
        <div class="ad-card mb-3">
            <div class="card-body">
                <form action="{{ route('admin.accounting.update', $transaction) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @if($lockedAgencyId)
                        <input type="hidden" name="locked_agency_id" value="{{ $lockedAgencyId }}">
                    @endif
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>{{ __('Tür') }}</label>
                            <select name="type" class="form-control" required>
                                <option value="income" {{ $transaction->type=='income'?'selected':'' }}>{{ __('Gelir') }}</option>
                                <option value="expense" {{ $transaction->type=='expense'?'selected':'' }}>{{ __('Gider') }}</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>{{ __('Durum') }}</label>
                            <select name="status" class="form-control" required>
                                <option value="paid" {{ $transaction->status=='paid'?'selected':'' }}>{{ __('Ödendi') }}</option>
                                <option value="pending" {{ $transaction->status=='pending'?'selected':'' }}>{{ __('Beklemede') }}</option>
                                <option value="cancelled" {{ $transaction->status=='cancelled'?'selected':'' }}>{{ __('İptal') }}</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>{{ __('Tarih') }}</label>
                            <input type="date" name="transaction_date" class="form-control" value="{{ $transaction->transaction_date->format('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>{{ __('Başlık') }}</label>
                        <input type="text" name="title" class="form-control" value="{{ $transaction->title }}" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>{{ __('Tutar') }}</label>
                            <!-- made by @hllgkx.0 -->
                            <input type="number" step="0.01" name="amount" class="form-control" value="{{ $transaction->amount }}" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label>{{ __('Para Birimi') }}</label>
                            <input type="text" name="currency" class="form-control" value="{{ $transaction->currency }}" maxlength="3" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label>{{ __('Ödeme Yöntemi') }}</label>
                            <input type="text" name="payment_method" class="form-control" value="{{ $transaction->payment_method }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>{{ __('Notlar') }}</label>
                        <textarea name="notes" rows="3" class="form-control">{{ $transaction->notes }}</textarea>
                    </div>

                    <button class="btn btn-primary" type="submit"><i class="fas fa-save"></i> {{ __('Kaydet') }}</button>
                    <a href="{{ route('admin.accounting.index', $lockedAgencyId ? ['locked_agency_id' => $lockedAgencyId] : []) }}" class="btn btn-secondary">{{ __('Geri') }}</a>
                </form>
            </div>
        </div>
    </div>
</div>
@stop
<!-- end of the code -->