document.getElementById("cmdForm").addEventListener("submit", function (event) {
  event.preventDefault();
  var formData = new FormData(this);
  var xhr = new XMLHttpRequest();
  xhr.open("POST", "cmd.php", true);
  xhr.onload = function () {
    if (xhr.status === 200) {
      document.getElementById("m").innerHTML = xhr.responseText;
    } else {
      document.getElementById("m").innerHTML =
        "Erreur lors de l'exécution de la commande.";
    }
  };
  xhr.send(formData);
});
