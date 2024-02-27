<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VNPAY Response</title>
</head>
<body>
<div class="container">
    <div class="header clearfix">
        <h3 class="text-muted">VNPAY RESPONSE</h3>
        <a href="{{ route('payment.list') }}"> Home </a>
    </div>
    <div class="table-responsive">
        @foreach ($data as $key => $value)
            <div class="form-group">
                <label>{{ ucfirst(str_replace('_', ' ', $key)) }}:</label>
                <label>{{ $value }}</label>
            </div>
        @endforeach
        <div class="form-group">
            <label>Kết quả:</label>
            <label style="color:{{ $data['isValidSignature'] ? 'blue' : 'red' }}">
                @if ($data['isValidSignature'])
                    GD Thanh cong
                @else
                    Chu ky khong hop le
                @endif
            </label>
        </div>
    </div>
    <p>&nbsp;</p>
    <footer class="footer">
        <p>&copy; VNPAY {{ date('Y') }}</p>
    </footer>
</div>
</body>
</html>
