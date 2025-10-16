<html>
<head>
<title>Simple example to copy HTML to Excel</title>
<!-- <link rel="stylesheet" type="text/css" href="css/style.css" />
<link rel="stylesheet" type="text/css" href="css/form.css" />
<link rel="stylesheet" type="text/css" href="css/table.css" /> -->
<script>
function exportToExcel() {
  var location = 'data:application/vnd.ms-excel;base64,';
  var excelTemplate = '<html> '+
    '<head> '+
    '<meta http-equiv="content-type" content="text/plain; charset=UTF-8"/> '+
	'</head> '+
	'<body> '+
	document.getElementById("table-conatainer").innerHTML +
	'</body> '+
	'</html>'
   window.location.href = location + window.btoa(excelTemplate);
}
</script>

</head>
<body>
	<div class="phppot-container">
		<h1>Product List</h1>
		<div id="table-conatainer">
			<table class="striped">
				<thead>
					<tr bac>
						<th>S.No</th>
						<th>Product Name</th>
						<th>Price</th>
						<th>Model</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>1</td>
						<td style="back">GIZMORE Multimedia Speaker with Remote Control, Black</td>
						<td>2300</td>
						<td>2020</td>
					</tr>
					<tr>
						<td>2</td>
						<td>Black Google Nest Mini</td>
						<td>3400</td>
						<td>2021</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="row">
			<input type="button" onclick="exportToExcel()"
				value="Export to Excel" />
		</div>
	</div>
</body>
</html>
