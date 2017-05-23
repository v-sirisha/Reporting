<!DOCTYPE html>
<html>
	<head>
		<title>Profitability Report Database</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	    <meta name="mobile-web-app-capable" content="yes" />
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css" />
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" />
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datepicker/0.5.2/datepicker.min.css" />
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" />
		<link rel="stylesheet" type="text/css" href="{{asset('css/reporting.css')}}">
		@yield('css')
	</head>
	<body>
		<header>
			@yield('header')
		</header>		
		<section class="col-md-12 col-sm-12 col-xs-12 content_wrapper">
			@yield('content')
		</section>
		<footer>
			@yield('footer')
		</footer>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/datepicker/0.5.2/datepicker.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/jquery.dataTables.min.js"></script>
		@yield('script')
	</body>
</html>