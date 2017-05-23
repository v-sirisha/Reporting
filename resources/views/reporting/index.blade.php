@extends('reporting.master')
@section('action_title')
Profitability Report Database
@stop
@section('css')
<link rel="stylesheet" type="text/css" href="{{asset('css/jquery.ultraselect.min.css')}}">
@stop
@section('header')
	@include('reporting.header')
@stop
@section('content')
	<div class="col-md-3 col-sm-3 col-xs-12 subsection">
		<div class="col-md-12 padding0" id="pt_upload_div">
			<h5 class="text-center">UPLOAD RAW DATA</h5>
			<form action="{{url('/store-data/rubicon')}}" class="col-md-12 col-xs-12 col-sm-12" enctype="multipart/form-data" method="POST">
				{{ csrf_field() }}
				<div class="form-group"><select class="form-control platform_sel" name='platform_name' ></select>
					<p id="pt_modified" class="pmargin"></p></div>
				<div class="form-group"><input class="form-control datepicker" name='start_date' placeholder='Start date of excel data' ></div>
				<div class="form-group"><input class="form-control datepicker" name='end_date' placeholder='End date of excel data' ></div>
				<div class="form-group"><input type="file" class="form-control" name='excel-file' ></div>
				<div class="form-group text-center"><button type="submit" class="btn btn-warning">Upload</button></div>
			</form>
		</div>
		<div class="col-md-12 col-xs-12 col-sm-12 padding0">
			<h5 class="text-center">UPLOAD LOOKUP TABLES</h5>
			<form action="{{url('/importToData')}}" class="col-md-12" enctype="multipart/form-data" method="POST">
				<div class="form-group">
					<select type="text" class="form-control" name='table_name' placeholder='Select Lookup Table Name' id="table_name">
					</select>
				</div>
				<div class="form-group"><input type="file" class="form-control" name='file' ></div>
				<div class="form-group text-center"><button type="submit" class="btn btn-warning">Upload</button></div>
			</form>
		</div>
	</div>
	<div class="col-md-5 col-sm-5 col-xs-12 subsection">
		<div class="col-md-12 col-xs-12 col-sm-12 padding0 warning_div">
			@if($pr_miss)
				<p><span class='glyphicon glyphicon-warning-sign'></span>&nbsp;  Parent Placement Lookup Table is incomplete</p>
			@endif
			@if($io_miss)
				<p><span class='glyphicon glyphicon-warning-sign'></span>&nbsp;  Publisher Details Table is incomplete</p>
			@endif
			@if(count($device) > 0)
				<p><span class='glyphicon glyphicon-warning-sign'></span>&nbsp;  Device Type Lookup Table is incomplete</p>
			@endif
			@if(count($country) > 0)
				<p><span class='glyphicon glyphicon-warning-sign'></span>&nbsp;  Country Group Lookup Table is incomplete</p>
			@endif
		</div>
		<div class="col-ms-12 col-xs-12 col-sm-12 padding0">
			<h5 class="text-center">UPDATE LOOKUPS</h5>
			@if($pr_miss)
				<div class="row">
					<p class="col-md-4">Parent Placement</p>
					<p class="col-md-4 text-center "><a href="{{url('update/PR')}}">Update Onscreen </a></p>
					<p class="col-md-4 text-right"><a href="{{url('download-excel/PR')}}">Update in Excel</a></p>
				</div>
			@endif
			@if($io_miss)
				<div class="row">
					<p class="col-md-4">Parent Publisher</p>
					<p class="col-md-4 text-center"><a href="{{url('update/io_product')}}">Update Onscreen</a></p>
					<p class="col-md-4  text-right"><a href="{{url('download-excel/io_product')}}">Update in Excel</a></p>
				</div>
			@endif
			@if(count($country) > 0)
				<div class="row">
					<p class="col-md-4">Country Group</p>
					<p class="col-md-4 text-center"><a href="{{url('update/country')}}"> Update Onscreen</a></p>
					<p class="col-md-4  text-right"><a href="{{url('download-excel/country')}}">Update in Excel</a></p>
				</div>
			@endif
			@if(count($device)>0)
				<div class="row">
					<p class="col-md-4">Device Type</p>
					<p class="col-md-4 text-center"><a href="{{url('update/device')}}">Update Onscreen</a></p>
					<p class="col-md-4  text-right"><a href="{{url('download-excel/device')}}">Update in Excel</a></p>
				</div>
			@endif
		</div>
	</div>
	<div class="col-md-4 col-sm-4 col-xs-12 subsection pr-download">
		<h5 class='text-center'>DOWNLOAD REPORTS</h5>
		<div class="row">
			<form action="{{url('exportdata-excel')}}">
				<p class="col-md--12 col-sm-12 col-xs-12">Date Range</p>
				<div class="form-group col-md-6 col-sm-6 col-xs-6">
					<input name="start_date" class="form-control datepicker" placeholder="Start date" required>
				</div>
				<div class="form-group col-md-6 col-sm-6 col-xs-6 paddingleft">
					<input name="end_date" class="form-control datepicker" placeholder="End date" required>
				</div>
				<p class="col-md-12">Select Columns</p>
				<div class="form-group col-md-12 col-sm-12 col-xs-12">
					<select id="control_1" name="control_1[]" multiple="multiple" size="5">
						@foreach($columns as $column)
							<option value="{{$column}}">{{$column}}</option>
						@endforeach
					</select>
				</div>
				<p class="col-md--12 col-sm-12 col-xs-12">Filters</p>
				<div class="form-group col-md-6 col-sm-6 col-xs-6">
					<select name="io_publisher" class="form-control" placeholder="Parent Publisher" id='publisherSel'>
						@if(count($parent_publishers) > 0)
							@foreach($parent_publishers as $publisher)
								<option value="{{$publisher}}">{{$publisher}}</option>
							@endforeach
						@endif
					</select>
				</div>
				<div class="form-group col-md-6 col-sm-6 col-xs-6">
					<select name="product_name" class="form-control" placeholder='Product' id="productSel">
						@if(count($product_names) > 0)
							@foreach($product_names as $publisher)
								<option value="{{$publisher}}">{{$publisher}}</option>
							@endforeach
						@endif
					</select>
				</div>
				<div class="form-group col-md-6 col-sm-6 col-xs-6">
					<select name="ym_manager" class="form-control" placeholder='YM Maanager' id="managerSel">
						@if(count($ym_managers) > 0)
							@foreach($ym_managers as $publisher)
								<option value="{{$publisher}}">{{$publisher}}</option>
							@endforeach
						@endif
					</select>
				</div>
				<div class="form-group col-md-6 col-sm-6 col-xs-6"><button class="btn btn-warning" type="submit">Get Report</button></div>
			</form>
			
		</div>
	</div>
@stop
@section('footer')
	@include('reporting.footer')
@stop
@section('script')
<script type="text/javascript" src="{{asset('js/jquery.ultraselect.min.js')}}"></script>
	<script type="text/javascript">
		var Platform_names_arr = ['AdTag','Rubicon','AdXTag','AdXDynamic','OpenX','PubMatic','PulsePoint-Dir','PulsePoint-FP','Sovrn','Matomy','MobFox'];
		var ad_unit = ['728x90','300x250','300x50','320x50','160x600','120x600','300x600','VAST','728x90_1','728x90_2','728x90_3','300x250_1','300x250_2','300x250_3','160x600_LHS','160x600_RHS'];
		var table_name = ['Parent Placement','Publisher Details','Country Group','Device Type'];
		$(document).ready(function(){
			$('.platform_sel').select2({
				placeholder:'Select Platform',
				data:Platform_names_arr
			});
			$('.platform_sel').select2('val','all');
			$('.ad_unit').select2({
				placeholder:'Select Ad Unit',
				data:ad_unit
			});
			$('.ad_unit').select2('val','all');
			$('#table_name').select2({
				placeholder:'Select Lookup Table ',
				data:table_name
			});
			$('#table_name').select2('val','all');
			$('.datepicker').datepicker();
			$('#publisherSel').select2({
				placeholder:"Select Publisher Name",
			});
			$('#publisherSel').select2('val','all');
			$('#productSel').select2({
				placeholder:"Select Product Name",
			});
			$('#productSel').select2('val','all');
			$('#managerSel').select2({
				placeholder:"Select Manager Name",
			});
			$('#managerSel').select2('val','all');
			$("#control_1").ultraselect();
		});
		$('.platform_sel').on('change',function(){
			$('#pt_modified').text('');
			var type = $(this).val();
			$.ajax({
				url:"{{url('get-lastmodified-date')}}"+'/'+type,
				type:"GET",
				success:function(data){
					console.log('data : '+data);
					if(data)
						$('#pt_modified').text('Updated till - '+data);
				}
			})
		})
	</script>
@stop