<html lang="en">
<head>
	<title>Import - Export Laravel 5</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" >
</head>
<body>
	<nav class="navbar navbar-default">

		<div class="container-fluid">

			<div class="navbar-header">

				<a class="navbar-brand" href="#">Import - Export in Excel and CSV Laravel 5</a>

			</div>

		</div>
	</nav>
	<div class="container">

		<a href="{{ URL::to('downloadExcel/xls') }}"><button class="btn btn-success">Download Excel xls</button></a>

		<a href="{{ URL::to('downloadExcel/xlsx') }}"><button class="btn btn-success">Download Excel xlsx</button></a>

		<a href="{{ URL::to('downloadExcel/csv') }}"><button class="btn btn-success">Download CSV</button></a>

		<!--form style="border: 4px solid #a1a1a1;margin-top: 15px;padding: 10px;" action="{{ URL::to('importHistoriaExcel') }}" class="form-horizontal" method="post" enctype="multipart/form-data">

			<input type="file" name="import_file" />

			<button class="btn btn-primary">Import File Historia</button>

		</form-->

		<!--form style="border: 4px solid #a1a1a1;margin-top: 15px;padding: 10px;" action="{{ URL::to('importApellidoExcel') }}" class="form-horizontal" method="post" enctype="multipart/form-data">

			<input type="file" name="import_file" />

			<button class="btn btn-primary">Import Corregir Apellido</button>

		</form-->

		<!--form style="border: 4px solid #a1a1a1;margin-top: 15px;padding: 10px;" action="{{ URL::to('importTarifario') }}" class="form-horizontal" method="post" enctype="multipart/form-data">
			<input type="file" name="import_file" />
			<button class="btn btn-primary">Import File Planes</button>
		</form-->

		<!--form style="border: 4px solid #a1a1a1;margin-top: 15px;padding: 10px;" action="{{ URL::to('importCie') }}" class="form-horizontal" method="post" enctype="multipart/form-data">
			<input type="file" name="import_file" />
			<button class="btn btn-primary">Import File Cie10</button>
		</form-->

		<form style="border: 4px solid #a1a1a1;margin-top: 15px;padding: 10px;" action="{{ URL::to('importStock') }}" class="form-horizontal" method="post" enctype="multipart/form-data">
			<input type="file" name="import_file" />
			<button class="btn btn-primary">Import File Stock</button>
		</form>
	</div>
</body>
</html>