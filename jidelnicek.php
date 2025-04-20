<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Jídelníček</title>
  <style>
    body { font-family: Arial, sans-serif; background-color: #f5f5f5; margin: 0; padding: 0; }
    header { background-color: #333; color: #fff; text-align: center; padding: 20px 0; }
    .container { max-width: 800px; margin: 0 auto; padding: 20px; }
    .meal { margin-bottom: 20px; }
    h2 { margin-top: 0; }
    select, input[type="text"] { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 3px; box-sizing: border-box; }
    button { background-color: #007bff; color: #fff; border: none; padding: 8px 12px; border-radius: 3px; cursor: pointer; margin-right: 5px; }
    button:hover { background-color: #0056b3; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
  </style>
</head>
<body>
<?php
session_start();
ob_start();
?>
<header>
  <h1>Jídelníček</h1>
  <?php
  if (isset($_SESSION['user'])) {
      // Call the backend function to get the full name
      $conn = new mysqli("host", "username", "password", "database");
      if ($conn->connect_error) {
          die("Connection failed: " . $conn->connect_error);
      }

      $stmt = $conn->prepare("SELECT GetFullName(?) AS FullName");
      $stmt->bind_param("i", $_SESSION['user']['UzivatelID']);
      $stmt->execute();
      $result = $stmt->get_result();
      $row = $result->fetch_assoc();
      $fullName = $row['FullName'];

      echo "<p>Vítejte, " . htmlspecialchars($fullName) . "!</p>";
      echo '<button onclick="window.location.href=\'logout.php\'">Odhlásit</button>';
  } else {
      echo '<button onclick="window.location.href=\'prihlaseni.php\'">Přihlásit</button>';
      echo '<button onclick="window.location.href=\'registrace.php\'">Registrovat</button>';
  }
  ?>
</header>
  

<div class="container">
  <div class="meal">
    <h2>Vyberte nebo zadejte hlavní chod:</h2>
    <select id="hlavni-chod">
      <option value="1">Kuřecí</option>
      <option value="2">Losos</option>
      <option value="3">Hovězí</option>
      <option value="Custom">Zadat vlastní</option>
    </select>
    <input type="text" id="custom-hlavni-chod" style="display: none;" placeholder="Zadejte vlastní hlavní chod">
  </div>
  <div class="meal">
    <h2>Vyberte nebo zadejte přílohu:</h2>
    <select id="priloha">
      <option value="1">Rýže</option>
      <option value="2">Brambory</option>
      <option value="3">Hranolky</option>
      <option value="Custom">Zadat vlastní</option>
    </select>
    <input type="text" id="custom-priloha" style="display: none;" placeholder="Zadejte vlastní přílohu">
  </div>
  <div class="meal">
    <h2>Vyberte nebo zadejte omáčku:</h2>
    <select id="omacka">
      <option value="1">Tatarka</option>
      <option value="2">Kečup</option>
      <option value="3">Hořčice</option>
      <option value="Custom">Zadat vlastní</option>
    </select>
    <input type="text" id="custom-omacka" style="display: none;" placeholder="Zadejte vlastní omáčku">
  </div>
  <div class="meal">
    <h2>Obloha:</h2>
    <input type="checkbox" id="Obloha" name="Obloha" value="TRUE">
  </div>
  <button id="ulozit">Uložit</button>
  <button id="smazat-tabulku">Smazat tabulku</button>

  <h2>Uložená jídla:</h2>
  <table id="ulozena-jidla">
    <thead>
      <tr>
        <th>Hlavní chod</th>
        <th>Příloha</th>
        <th>Omáčka</th>
        <th>Obloha</th>
        <th>Typ jídla</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>Kuřecí</td>
        <td>Rýže</td>
        <td>Tatarka</td>
        <td>Ano</td>
        <td>Oběd</td>
      </tr>
    </tbody>
  </table>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// Pomocná funkce pro kontrolu duplicitních možností
function optionExists(selectElement, text) {
  for (let i = 0; i < selectElement.options.length; i++) {
    if (selectElement.options[i].text === text) {
      return true;
    }
  }
  return false;
}

// Zobrazovat/skryjeme textový input, pokud uživatel zvolí "Custom"
document.getElementById("hlavni-chod").addEventListener("change", function() {
  if (this.value === "Custom") {
    document.getElementById("custom-hlavni-chod").style.display = "block";
  } else {
    document.getElementById("custom-hlavni-chod").style.display = "none";
  }
});
document.getElementById("priloha").addEventListener("change", function() {
  if (this.value === "Custom") {
    document.getElementById("custom-priloha").style.display = "block";
  } else {
    document.getElementById("custom-priloha").style.display = "none";
  }
});
document.getElementById("omacka").addEventListener("change", function() {
  if (this.value === "Custom") {
    document.getElementById("custom-omacka").style.display = "block";
  } else {
    document.getElementById("custom-omacka").style.display = "none";
  }
});

document.getElementById("ulozit").addEventListener("click", function () {
  // Získání hodnot
  let hlavniChodVal = document.getElementById("hlavni-chod").value;
  let prilohaVal = document.getElementById("priloha").value;
  let omackaVal = document.getElementById("omacka").value;
  let oblohaChecked = document.getElementById("Obloha").checked;

  // Sestavení payloadu
  let payload = {
    obloha: oblohaChecked ? 1 : 0,
    typjidla: urcitTypJidla()
  };
  if (hlavniChodVal === "Custom") {
    payload.hlavniChod = document.getElementById("custom-hlavni-chod").value;
  } else {
    let num = parseInt(hlavniChodVal);
    payload.hlavniChod = isNaN(num) ? hlavniChodVal : num;
  }
  if (prilohaVal === "Custom") {
    payload.priloha = document.getElementById("custom-priloha").value;
  } else {
    let num = parseInt(prilohaVal);
    payload.priloha = isNaN(num) ? prilohaVal : num;
  }
  if (omackaVal === "Custom") {
    payload.omacka = document.getElementById("custom-omacka").value;
  } else {
    let num = parseInt(omackaVal);
    payload.omacka = isNaN(num) ? omackaVal : num;
  }

  // Vytiskneme payload do konzole pro ladění
  console.log("Odesílaný payload:", JSON.stringify(payload));

  // Přidání do tabulky pro zobrazení
  let table = document.getElementById("ulozena-jidla").getElementsByTagName("tbody")[0];
  let newRow = table.insertRow();
  newRow.insertCell(0).innerHTML = (hlavniChodVal === "Custom") ? document.getElementById("custom-hlavni-chod").value : document.querySelector("#hlavni-chod option:checked").text;
  newRow.insertCell(1).innerHTML = (prilohaVal === "Custom") ? document.getElementById("custom-priloha").value : document.querySelector("#priloha option:checked").text;
  newRow.insertCell(2).innerHTML = (omackaVal === "Custom") ? document.getElementById("custom-omacka").value : document.querySelector("#omacka option:checked").text;
  newRow.insertCell(3).innerHTML = oblohaChecked ? "Ano" : "Ne";
  newRow.insertCell(4).innerHTML = payload.typjidla;
    
  // Odeslání dat na backend
  fetch("backend.php?endpoint=newMeal", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload)
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      alert("Jídlo bylo úspěšně uloženo!");

      // --- Aktualizace selectů ---
      // Pro hlavní chod:
      let hlavniChodSelect = document.getElementById("hlavni-chod");
      let newHlavniText;
      if (hlavniChodVal === "Custom") {
        newHlavniText = document.getElementById("custom-hlavni-chod").value.trim();
      } else {
        newHlavniText = document.querySelector("#hlavni-chod option:checked").text.trim();
      }
      if (!optionExists(hlavniChodSelect, newHlavniText)) {
        let newHlavniOption = document.createElement("option");
        newHlavniOption.value = newHlavniText;
        newHlavniOption.text = newHlavniText;
        hlavniChodSelect.appendChild(newHlavniOption);
      }
      // Pro přílohu:
      let prilohaSelect = document.getElementById("priloha");
      let newPrilohaText;
      if (prilohaVal === "Custom") {
        newPrilohaText = document.getElementById("custom-priloha").value.trim();
      } else {
        newPrilohaText = document.querySelector("#priloha option:checked").text.trim();
      }
      if (!optionExists(prilohaSelect, newPrilohaText)) {
        let newPrilohaOption = document.createElement("option");
        newPrilohaOption.value = newPrilohaText;
        newPrilohaOption.text = newPrilohaText;
        prilohaSelect.appendChild(newPrilohaOption);
      }
      // Pro omáčku:
      let omackaSelect = document.getElementById("omacka");
      let newOmackaText;
      if (omackaVal === "Custom") {
        newOmackaText = document.getElementById("custom-omacka").value.trim();
      } else {
        newOmackaText = document.querySelector("#omacka option:checked").text.trim();
      }
      if (!optionExists(omackaSelect, newOmackaText)) {
        let newOmackaOption = document.createElement("option");
        newOmackaOption.value = newOmackaText;
        newOmackaOption.text = newOmackaText;
        omackaSelect.appendChild(newOmackaOption);
      }

      // --- Reset selectů na výchozí možnost "Custom" ---
      hlavniChodSelect.value = "Custom";
      prilohaSelect.value = "Custom";
      omackaSelect.value = "Custom";
      
      // Vyprázdníme inputy pro vlastní zadání
      document.getElementById("custom-hlavni-chod").value = "";
      document.getElementById("custom-priloha").value = "";
      document.getElementById("custom-omacka").value = "";
    } else {
      alert("Chyba při ukládání jídla: " + (data.error || "Neznámá chyba"));
    }
  })
  .catch(error => alert("Chyba: " + error.message));
});

// Smazání tabulky – ukázkový kód.
document.getElementById("smazat-tabulku").addEventListener("click", function() {
  document.getElementById("ulozena-jidla").getElementsByTagName("tbody")[0].innerHTML = "";
});

function urcitTypJidla() {
  var currentTime = new Date();
  var currentHour = currentTime.getHours();
  if (currentHour >= 6 && currentHour < 11) {
    return "Snídaně";
  } else if (currentHour >= 11 && currentHour < 15) {
    return "Oběd";
  } else {
    return "Večeře";
  }
}

// Dynamické naplnění selectů z backendu
fetch("backend.php?endpoint=getAllMeals")
  .then(response => response.json())
  .then(data => {
    console.log("Odpověď z backendu:", data);
    if (Array.isArray(data)) {
      data.forEach(jidlo => {
        if (!jidlo.Nazev || jidlo.Nazev.trim() === "") return;
        const optionValue = (typeof jidlo.HlavnicastjidlaID !== "undefined" && jidlo.HlavnicastjidlaID !== null)
          ? jidlo.HlavnicastjidlaID
          : jidlo.Nazev;
        var selects = [
          document.getElementById("hlavni-chod"),
          document.getElementById("priloha"),
          document.getElementById("omacka")
        ];
        selects.forEach(select => {
          if (!optionExists(select, jidlo.Nazev)) {
            const option = document.createElement("option");
            option.value = optionValue;
            option.text = jidlo.Nazev;
            select.appendChild(option);
          }
        });
      });
    } else {
      alert("Chyba při načítání jídel: Data nejsou pole.");
    }
  })
  .catch(error => alert("Chyba při načítání jídel: " + error.message));
</script>


</body>
</html>
