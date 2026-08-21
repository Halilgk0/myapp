@extends('layouts.admin')

@section('title', 'Muhasebe Kaydı Düzenle')

@section('content')
@php($lockedAgencyId = request('locked_agency_id'))
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.accounting.update', $transaction) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @if($lockedAgencyId)
                        <input type="hidden" name="locked_agency_id" value="{{ $lockedAgencyId }}">
                    @endif
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Tür</label>
                            <select name="type" class="form-control" required>
                                <option value="income" {{ $transaction->type=='income'?'selected':'' }}>Gelir</option>
                                <option value="expense" {{ $transaction->type=='expense'?'selected':'' }}>Gider</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Durum</label>
                            <select name="status" class="form-control" required>
                                <option value="paid" {{ $transaction->status=='paid'?'selected':'' }}>Ödendi</option>
                                <option value="pending" {{ $transaction->status=='pending'?'selected':'' }}>Beklemede</option>
                                <option value="cancelled" {{ $transaction->status=='cancelled'?'selected':'' }}>İptal</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Tarih</label>
                            <input type="date" name="transaction_date" class="form-control" value="{{ $transaction->transaction_date->format('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Başlık</label>
                        <input type="text" name="title" class="form-control" value="{{ $transaction->title }}" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Tutar</label>
                            <!-- made by @hllgkx.0 -->
                            <input type="number" step="0.01" name="amount" class="form-control" value="{{ $transaction->amount }}" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Para Birimi</label>
                            <input type="text" name="currency" class="form-control" value="{{ $transaction->currency }}" maxlength="3" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Ödeme Yöntemi</label>
                            <input type="text" name="payment_method" class="form-control" value="{{ $transaction->payment_method }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Notlar</label>
                        <textarea name="notes" rows="3" class="form-control">{{ $transaction->notes }}</textarea>
                    </div>

                    <button class="btn btn-primary" type="submit"><i class="fas fa-save"></i> Kaydet</button>
                    <a href="{{ route('admin.accounting.index', $lockedAgencyId ? ['locked_agency_id' => $lockedAgencyId] : []) }}" class="btn btn-secondary">Geri</a>
                </form>
            </div>
        </div>
    </div>
</div>
@stop
<!-- end of the code -->