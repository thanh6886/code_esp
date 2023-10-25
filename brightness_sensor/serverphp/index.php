<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Dashboard</title>

	<link rel="shortcut icon" href="./favicon.svg" type="image/svg+xml" />
	<link rel="stylesheet" href="./assets/css/style.css" />

	<link rel="preconnect" href="https://fonts.googleapis.com" />
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
	<link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;900&display=swap"
		rel="stylesheet" />

	<link rel="stylesheet"
		href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
	<script src="https://code.highcharts.com/11/highcharts.js"></script>
	<script src="https://code.highcharts.com/modules/accessibility.js"></script>
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
	<header class="header" data-header>
		<div class="container" style="margin-bottom: 5px;">
			<h1 class="logo">Dashboard Brightness Manager</h1>
		</div>
	</header>

	<main>
		<article class="container article">
			<h2 class="h2 article-title">Biểu đồ</h2>

			<section>
				<div id="container" style="width:100%; height:500px; margin-bottom: 20px;"></div>
			</section>

			<section class="projects">
				<?php
					include 'database.php';
					$sql_min = "SELECT MIN(brightness) AS brightness_nho_nhat FROM sensor ORDER BY datetime DESC";
					$result_min = $conn->query($sql_min);
					$row_min = $result_min->fetch_assoc();
					$brightness_nho_nhat = floatval($row_min['brightness_nho_nhat']);
				
					$sql_max = "SELECT MAX(brightness) AS brightness_lon_nhat FROM sensor ORDER BY datetime DESC";
					$result_max = $conn->query($sql_max);
					$row_max = $result_max->fetch_assoc();
					$brightness_lon_nhat = floatval($row_max['brightness_lon_nhat']);
				
					$sql_current = "SELECT brightness FROM sensor ORDER BY datetime DESC LIMIT 1";
					$result_current = $conn->query($sql_current);
					$row_current = $result_current->fetch_assoc();
					$brightness_hien_tai = floatval($row_current['brightness']);
				
					$sql_state = "SELECT state FROM relay ORDER BY datetime DESC LIMIT 1";
					$result_state = $conn->query($sql_state);
					$row_state = $result_state->fetch_assoc();
					$state = floatval($row_state['state']);

					$conn->close();
				?>
				<div class="section-title-wrapper">
					<h2 class="section-title">Properties</h2>
				</div>

				<ul class="project-list">
					<li class="project-item">
						<div class="card project-card">
							<h3 class="card-title">
								<p >Giá trị hiện tại</p>
							</h3>
							<?php echo "<div class='card-badge blue'>$brightness_hien_tai lux</div>";?>
						</div>
					</li>

					<li class="project-item">
						<div class="card project-card">
							<h3 class="card-title">
								<p>Giá trị cao nhất</p>
							</h3>
							<?php echo "<div class='card-badge orange'>$brightness_lon_nhat lux</div>";?>
						</div>
					</li>

					<li class="project-item">
						<div class="card project-card">
							<h3 class="card-title">
								<p>Giá trị thấp nhất</p>
							</h3>
							<?php echo "<div class='card-badge cyan'>$brightness_nho_nhat lux</div>";?>
						</div>
					</li>

					<li class="project-item">
						<div class="card project-card">
							<h3 class="card-title">
								<p>Trạng thái đèn LED</p>
							</h3>
							<?php 
								if ($state == 1) {
									echo "<div class='card-badge orange'>LED sáng</div>";
								} else {
									echo "<div class='card-badge cyan'>LED tắt</div>";
								}
							?>
							<button class="trigger" data-modal-trigger="trigger">
								<?php
									if ($state == 1) {
										echo "<h2>Tắt LED</h2>";
									} else {
										echo "<h2>Bật LED</h2>";
									}
								?>
							</button>
						</div>
					</li>
				</ul>
			</section>
		</article>

		<div class="modal" data-modal="trigger">
			<article class="content-wrapper">
				<button class="close"></button>
				<div class="content">
					<?php
						if ($state == 1) {
							echo "<h2>Xác nhận tắt LED</h2>";
						} else {
							echo "<h2>Xác nhận bật LED</h2>";
						}
					?>
				</div>
				<?php
				function changeValue($newValue) {
					$url = "http://127.0.0.1:3000/control?val=" . $newValue;
					$ch = curl_init($url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					$response = curl_exec($ch);
					curl_close($ch);

					header("Location: " . $_SERVER['PHP_SELF']);
					exit;
				}

				if (isset($_GET['val'])) {
					$newValue = $_GET['val'];
					changeValue($newValue);
				}
				?>
				<footer class="modal-footer">
					<?php
						if ($state == 1) {
							echo "<p></p>";
							echo "<a class='action' href='?val=0'>Tắt LED</a>";
						} else {
							echo "<a class='action' href='?val=1'>Bật LED</a>";
							echo "<p></p>";
						}
					?>								
				</footer>
			</article>
		</div>
	</main>
	
	<script type="text/javascript">
		var chart;

		function updateChart() {
			$.ajax({
				url: 'loaddata.php',
				type: 'GET',
				dataType: 'json',
				success: function (data) {
					chart.series[0].setData(data, true);
				}
			});
		}

		document.addEventListener('DOMContentLoaded', function () {
			Highcharts.setOptions({
				global: {
					useUTC: false
				}
			});

			chart = Highcharts.chart('container', {
				chart: {
					type: 'line',
					events: {
						load: function () {
							setInterval(updateChart, 1000);
						}
					}
				},
				title: {
					text: 'Biểu đồ biểu diễn cường độ sáng'
				},
				xAxis: {
					type: 'datetime',
					title: {
						text: 'Thời gian'
					}
				},
				yAxis: {
					title: {
						text: 'Độ sáng'
					}
				},
				plotOptions: {
					line: {
						dataLabels: {
							enabled: true
						},
						enableMouseTracking: true
					}
				},
				series: [
					{
						name: 'Đường biểu diễn cường độ sáng',
						data: []
					}
				]
			});
		});
		const buttons = document.querySelectorAll(".trigger[data-modal-trigger]");
		for (let e of buttons) modalEvent(e);
		function modalEvent(e) {
			e.addEventListener("click", () => {
				const t = e.getAttribute("data-modal-trigger"),
					n = document.querySelector(`[data-modal=${t}]`);
				console.log("modal", n);
				const o = n.querySelector(".content-wrapper");
				n
					.querySelector(".close")
					.addEventListener("click", () => n.classList.remove("open")),
					n.addEventListener("click", () => n.classList.remove("open")),
					o.addEventListener("click", (e) => e.stopPropagation()),
					n.classList.toggle("open");
			});
		}
	</script>
</body>

</html>
