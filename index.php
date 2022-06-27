<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>TEST Stripe</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>

<h2>クレジットカード情報</h2>

<form id="form" action="./process.php" method="post">

  <label>
    <input type="text" name="name" value="" placeholder="お名前">
  </label>

  <div id="card-element"></div>
  <div id="card-errors" role="alert"></div>

</form>

<script src="https://js.stripe.com/v3/"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="./main.js"></script>
</body>
</html>
