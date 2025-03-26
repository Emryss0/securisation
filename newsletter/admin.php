<html>

<head>
	<title>AlexCloud - NewsLetter !</title>
	<link rel="stylesheet" href="css/style.css" />
	<script src="js/particles.min.js"></script>
	<script>particlesJS.load('particles-js', 'assets/particles.json', function () { });</script>

<body>
	</head>

	<body>
		<div id="particles-js"></div>
		<div id="h" style="margin-top: 0; height: 100%;">
			<?php if ($_SERVER["HTTP_USER_AGENT"] != "TropSmartUserAgentAdminHeHeHe") {
				printf("<h1>You are not allowed to be here !</h1></div></body></html>");
				die();
			} ?>
			<div id="b">AlexCloud !</div>
			<div id="d">J'suis vraiment beaucoup trop smart avec cette mesure de securite
				!<!-- FLAG{L0uRd3 M3sUr3} --><br><br>Par contre faut pas déconner, on va pas laisser n'importe quelle
				commande, il va falloir se contenter de ça</div>
			<pre id="m"
				style="width: 80%; background: #444; overflow-y: scroll; height: 200px; padding: 3%; margin-left: 6%; border: 2px solid black;">
Nothing to display yet...
			</pre>
			<div id="bs">
				<button onclick="w(this);">id -a</button>
				<button onclick="w(this);">ping -c4 1.1.1.1</button>
				<button onclick="w(this);">ss -lntuop</button>
				<button onclick="w(this);">ps -ef</button>
			</div>
		</div>
	</body>
	<script>
		function w(e) {
			var formData = new FormData();
			formData.append('cmd', e.innerText);

			fetch("cmd.php", { method: "POST", body: formData })
				.then(response => {
					if (!response.ok) {
						throw new Error("Fetch API failed.");
					}
					else { return response.text(); }
				})

				.then(data => {
					document.getElementById("m").innerText = data;
					console.log(data);
				});
		}
	</script>

</html>