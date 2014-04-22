<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>OpenTTD中文坛 服务器状态</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
	<script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>
	<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
	<script>
		$(function(){


		});
	</script>
</head>

<body>
	<table class='table'>
		<thead>
			<th>服务器名</th>
			<th>服务器版本</th>
			<th>地图大小</th>
		</thead>
		<tbody>
			<tr>
				<td><?=$server_name?></td>
				<td><?=$_openttd_revision?></td>
				<td><?=$MapSizeX?> x <?=$MapSizeY?></td>
			</tr>
		</tbody>
	</table>

	<table class='table'>
		<thead>
			<th>客户端id</th>
			<th>客户名</th>
			<th>所在公司</th>
		</thead>
		<tbody>
			<? foreach($clients as $client) : ?>
				<tr>
					<td><?=$client->id?></td>
					<td><?=$client->client_name?></td>
					<td><?=@$companies[$client->client_playas]->name?></td>
				</tr>
			<? endforeach; ?>
		</tbody>
	</table>

	<table class='table'>
		<thead>
			<th>公司id</th>
			<th>公司名</th>
			<th>经理</th>
			<th>创建时间</th>
			<th>现金</th>
			<th>贷款</th>
			<th>收入</th>
			<th>已运输货物</th>
		</thead>
		<tbody>
			<? foreach($companies as $company) : ?>
				<tr>
					<td><?=$company->id + 1?></td>
					<td><?=$company->name?></td>
					<td><?=$company->manager?></td>
					<td><?=$company->inaugurated_year?></td>
					<td><?=$company->money?></td>
					<td><?=$company->current_loan?></td>
					<td><?=$company->income?></td>
					<td><?=$company->delivered_cargo?></td>
				</tr>
			<? endforeach; ?>
		</tbody>
	</table>

	</body>
</html>