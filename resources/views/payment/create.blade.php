@extends('layout.index')

@section('content')
    <div class="container mt-3">
{{--        <h2>Stacked form</h2>--}}
        <form action="{{ route('payment.pay') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="pwd">Số tiền:</label>
                <input type="number" class="form-control" placeholder="Nhập Số Tiền" name="price">
            </div>
            <button type="submit" class="btn btn-primary">Gửi</button>
        </form>
    </div>
@endsection