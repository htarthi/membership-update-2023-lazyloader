<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Missing Contracts List</title>
</head>
<body>
    @if (!empty($data))
        @foreach ($data as $key => $shop)
            @if (count($shop['contracts']) > 0)
                <h4>Shop: {{$shop['name']}}</h4>
                <p>Contracts :
                    @foreach ($shop['contracts'] as $contract)
                        <a href="{{env('APP_URL')}}/add-missing-contract-records?shop_name={{$shop['name']}}&contract={{$contract}}">{{$contract}}</a>&nbsp;
                    @endforeach
                </p>
            @endif
        @endforeach
    @endif
</body>
</html>
