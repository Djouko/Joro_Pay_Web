<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ __("Card Payment") }} </title>
</head>
<body>
    <form action="{{ setRoute('card.payment') }}" method="POST">
        @csrf
        <button type="submit">{{ __("Submit") }}</button>
    </form>
</body>
</html>
