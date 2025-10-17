<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Pay Now</title>
</head>

<body>
    <h2>💳 Make a Payment</h2>

    <form action="{{ route('make.payment') }}" method="POST">
        @csrf
        <label>Full Name:</label><br>
        <input type="text" name="name" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Amount (EGP):</label><br>
        <input type="number" name="amount" required><br><br>

        <button type="submit">Pay with Paymob</button>
    </form>
</body>

</html>