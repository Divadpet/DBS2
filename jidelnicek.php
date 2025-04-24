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






?>
<header>
<?php if (isset($_SESSION['user']) && isset($_SESSION['user']) && $_SESSION['user']['Admin'] == 1): ?>
    <button class="btn btn-primary" onclick="window.location.href='uzivatele.php'">
      Správa uživatelů
    </button>
    <?php endif; ?>
    <?php
    if (isset($_SESSION['user'])) {
  echo '<div id="user-info"></div>';
  echo '<button onclick="window.location.href=\'odhlaseni.php\'">Odhlásit</button>';
} else {
  echo '<button onclick="window.location.href=\'prihlaseni.php\'">Přihlásit</button>';
  echo '<button onclick="window.location.href=\'registrace.php\'">Registrovat</button>';
  header("Location: prihlaseni.php");
  exit;
}?>
  <h1>Jídelníček</h1>
  <h3 id="fullNameDisplay"></h3>
  <h3 id="pocetJidel"></h3>
  <h3 id="PosledniJidlo"></h3>
  <button class="button" onclick="window.location.href='pridat.php'">
      Přidat polozky
    </button>
  
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
  <div class="meal">
      <h2>Vyberte datum a čas jídla:</h2>
      
      <input type="datetime-local" id="casjidla" name="casjidla" required>
    </div>
  <button id="ulozit" type="button">Uložit</button>
  <button id="oblibene" type="button" onclick="window.location.href='oblibena.php'">Oblíbená</button>

  <h2>Uložená jídla:</h2>
    <table id="ulozena-jidla">
      <thead>
        <tr>
          <th>Hlavní chod</th>
          <th>Příloha</th>
          <th>Omáčka</th>
          <th>Obloha</th>
          <th>Typ jídla</th>
          <th>Čas jídla</th>
          <th>Oblíbené</th>
          <th>Smazat</th>
        </tr>
      </thead>
      <tbody>
        <!-- Řádky se vkládají dynamicky -->
      </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", function () {
            const datetimeInput = document.getElementById("casjidla");

            // Funkce pro získání aktuálního času v potřebném formátu
            function getCurrentDatetime() {
                const now = new Date();
                const year = now.getFullYear();
                const month = String(now.getMonth() + 1).padStart(2, '0'); // Přidání nuly, pokud je měsíc < 10
                const day = String(now.getDate()).padStart(2, '0');
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                
                return `${year}-${month}-${day}T${hours}:${minutes}`; // Formát "YYYY-MM-DDTHH:mm"
            }

            // Nastavení výchozí hodnoty
            datetimeInput.value = getCurrentDatetime();
        });

function pridatoblibenejidlo(celejidloID) {
  // Zobrazíme potvrzovací dialog (nepovinné)
  if (!confirm("Chcete přidat toto jídlo mezi oblíbená?")) {
    return;
  }

  // Odešleme požadavek na backend pomocí fetch
  fetch("backend.php?action=pridatoblibenejidlo", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify({ CelejidloID: celejidloID })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      alert("Jídlo bylo úspěšně přidáno mezi oblíbená.");
      // Případně můžete aktualizovat UI, např. zvýraznit řádek, změnit stav tlačítka apod.
    } else {
      alert("Chyba při přidávání do oblíbených: " + (data.error || "Neznámá chyba."));
    }
  })
  .catch(error => {
    console.error("Chyba při volání backendu:", error);
    alert("Nepodařilo se přidat jídlo do oblíbených.");
  });
}



function deleteMeal(mealId) {
      if (confirm("Opravdu chcete smazat toto jídlo?")) {
        fetch("backend.php?action=deletejidlo", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ CelejidloID: mealId })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Pokud je odpověď úspěšná, odstraníme řádek z tabulky
            var row = document.getElementById("meal-" + mealId);
            if (row) {
              row.remove();
            }
            alert("Jídlo bylo úspěšně odstraněno z databáze.");
          } else {
            alert("Chyba při mazání: " + (data.error || "Neznámá chyba"));
          }
        })
        .catch(error => {
          console.error("Chyba při volání:", error);
          alert("Nepodařilo se smazat jídlo.");
        });
      }
    }

 document.addEventListener("DOMContentLoaded", function () {
    fetch("backend.php?action=getFullName")
        .then(response => response.json()) // Parse JSON response
        .then(data => {
            console.log("Backend Response:", data); // Debugging
            const fullNameContainer = document.getElementById("fullNameDisplay");

            if (data.fullName && data.fullName !== "Not found") {
                fullNameContainer.textContent = `Vitejte: ${data.fullName}`;
            } else {
                fullNameContainer.textContent = "Uživatel nebyl nalezen.";
            }
        })
        .catch(error => {
            console.error("Chyba při získávání jména:", error);
            document.getElementById("fullNameDisplay").textContent = "Chyba připojení k backendu.";
        });
});

document.addEventListener("DOMContentLoaded", function () {
    fetch("backend.php?action=PocetJidelUzivatele")
        .then(response => response.json()) // Parse JSON response
        .then(data => {
            console.log("Backend Response:", data); // Debugging
            const mealCountContainer = document.getElementById("pocetJidel");

            if (data.mealCount && data.mealCount !== "Not found") {
                mealCountContainer.textContent = `Počet zapsaných jídel: ${data.mealCount}`;
            } else {
                mealCountContainer.textContent = "Počet nenalezen";
            }
        })
        .catch(error => {
            console.error("Chyba při získávání jména:", error);
            document.getElementById("pocetJidel").textContent = "Chyba připojení k backendu.";
        });
});
document.addEventListener("DOMContentLoaded", function () {
    fetch("backend.php?action=PosledniJidlo")
        .then(response => response.json()) // Parse JSON response
        .then(data => {
            console.log("Backend Response:", data); // Debugging
            const lastMealContainer = document.getElementById("PosledniJidlo");

            if (data.lastMeal && data.lastMeal !== "Not found") {
                lastMealContainer.textContent = `Čas posledního zapsaného jídla: ${data.lastMeal}`;
            } else {
                lastMealContainer.textContent = "Čas nenalezen";
            }
        })
        .catch(error => {
            console.error("Chyba při získávání jména:", error);
            document.getElementById("PosledniJidlo").textContent = "Chyba připojení k backendu.";
        });
});




   document.addEventListener("DOMContentLoaded", function() {
      // Zavoláme endpoint, který načte data z backendu
      fetch('backend.php?action=getData')
        .then(response => response.json())
        .then(data => {
          console.log("Načtená data z backendu:", data);

          // --- Naplnění <select> elementů ---
          // Hlavní chod
          const hlavniSelect = document.getElementById("hlavni-chod");
          hlavniSelect.innerHTML = "";  // vyprázdnit
          data.hlavniChody.forEach(item => {
            const option = document.createElement("option");
            option.value = item.HlavnicastjidlaID;
            option.textContent = item.Nazev.charAt(0).toUpperCase() + item.Nazev.slice(1);
            hlavniSelect.appendChild(option);
          });
          // Přidání volby pro vlastní zadání
          let customOption = document.createElement("option");
          customOption.value = "Custom";
          customOption.textContent = "Zadat vlastní";
          hlavniSelect.appendChild(customOption);

          // Příloha
          const prilohaSelect = document.getElementById("priloha");
          prilohaSelect.innerHTML = "";
          data.prilohy.forEach(item => {
            const option = document.createElement("option");
            option.value = item.PrilohaID;
            option.textContent = item.Nazev.charAt(0).toUpperCase() + item.Nazev.slice(1);
            prilohaSelect.appendChild(option);
          });
          customOption = document.createElement("option");
          customOption.value = "Custom";
          customOption.textContent = "Zadat vlastní";
          prilohaSelect.appendChild(customOption);

          // Omáčka
          const omackaSelect = document.getElementById("omacka");
          omackaSelect.innerHTML = "";
          data.omacky.forEach(item => {
            const option = document.createElement("option");
            option.value = item.OmackaID;
            option.textContent = item.Nazev.charAt(0).toUpperCase() + item.Nazev.slice(1);
            omackaSelect.appendChild(option);
          });
          customOption = document.createElement("option");
          customOption.value = "Custom";
          customOption.textContent = "Zadat vlastní";
          omackaSelect.appendChild(customOption);

          // Přidání event listenerů pro zobrazení/skrytí vlastního vstupu
          hlavniSelect.addEventListener("change", function() {
            document.getElementById("custom-hlavni-chod").style.display = (this.value === "Custom") ? "block" : "none";
          });
          prilohaSelect.addEventListener("change", function() {
            document.getElementById("custom-priloha").style.display = (this.value === "Custom") ? "block" : "none";
          });
          omackaSelect.addEventListener("change", function() {
            document.getElementById("custom-omacka").style.display = (this.value === "Custom") ? "block" : "none";
          });

          // --- Naplnění tabulky "Uložená jídla" ---
          const tableBody = document.querySelector("#ulozena-jidla tbody");
          if (!tableBody) {
            console.error("Element <tbody> nebyl nalezen!");
            return;
          }
          tableBody.innerHTML = "";
          
          if (data.jidla && data.jidla.length > 0) {
            data.jidla.forEach(item => {
              console.log("Zpracovávám položku (jidlo):", item);
              const tr = document.createElement("tr");

              // Hlavní chod
              let td = document.createElement("td");
              td.textContent = item.hlavni ? item.hlavni.charAt(0).toUpperCase() + item.hlavni.slice(1) : "";
              tr.appendChild(td);

              // Příloha
              td = document.createElement("td");
              td.textContent = item.priloha ? item.priloha.charAt(0).toUpperCase() + item.priloha.slice(1) : "";
              tr.appendChild(td);

              // Omáčka
              td = document.createElement("td");
              td.textContent = item.omacka ? item.omacka.charAt(0).toUpperCase() + item.omacka.slice(1) : "";
              tr.appendChild(td);

              // Obloha – předpokládáme, že pokud je uložená hodnota "1", máme oblohu
              td = document.createElement("td");
              td.textContent = (item.Obloha == 1 || item.Obloha === true) ? "Ano" : "Ne";
              tr.appendChild(td);

              // Typ jídla
              td = document.createElement("td");
              td.textContent = item.typjidla ? item.typjidla : "";
              tr.appendChild(td);

              td = document.createElement("td");
              // Zobrazíme čas, pokud je dostupný, případně prázdný řetězec
              td.textContent = item.Casjidla ? item.Casjidla : "";
              tr.appendChild(td);
              // Tlačítko "Oblíbené"
              td = document.createElement("td");
              let btnOblibene = document.createElement("button");
              btnOblibene.textContent = "Oblíbené";
              btnOblibene.addEventListener("click", function() {
                  // Například zavoláme funkci, která odešle požadavek na přidání jídla do oblíbených.
                  // Upravte tuto funkci podle svého backendu.
                  pridatoblibenejidlo(item.CelejidloID);
              });
              td.appendChild(btnOblibene);
              tr.appendChild(td);

              // Tlačítko "Smazat"
              td = document.createElement("td");
              let btnSmazat = document.createElement("button");
              btnSmazat.textContent = "Smazat";
              btnSmazat.addEventListener("click", function() {
                  // Například zavoláme funkci, která odešle požadavek na smazání jídla.
                  // Upravte tuto funkci dle své implementace.
                  deleteMeal(item.CelejidloID);
              });
              td.appendChild(btnSmazat);
              tr.appendChild(td);

                

              tableBody.appendChild(tr);
            });
          } else {
            console.log("Pole 'jidla' je prázdné nebo neexistuje.");
            const tr = document.createElement("tr");
            const td = document.createElement("td");
            td.colSpan = "6";
            td.textContent = "Žádná jídla zatím nebyla uložena.";
            tr.appendChild(td);
            tableBody.appendChild(tr);
          }
        })
        .catch(error => console.error("Chyba při načítání dat:", error));
    });
  
    
  document.getElementById("ulozit").addEventListener("click", function(e) {
  // Zabraň standardnímu odeslání formuláře (pokud je tlačítko uvnitř <form>)
  e.preventDefault();

  // Načtení času – input typu datetime-local vrací formát "YYYY-MM-DDThh:mm"
  let casInput = document.getElementById("casjidla").value;
  let cas = casInput ? casInput.replace("T", " ") : "";

  // Načtení hodnot ze selectů a případně z custom inputů
  let hlavniChodVal = document.getElementById("hlavni-chod").value;
  let prilohaVal   = document.getElementById("priloha").value;
  let omackaVal    = document.getElementById("omacka").value;
  let oblohaChecked = document.getElementById("Obloha").checked ? 1 : 0;

  // Získání hodnoty pro typ jídla – pokud máš funkci urcitTypJidla(), využij ji.
  let typjidlaVal = (typeof urcitTypJidla === "function") ? urcitTypJidla() : 1;

  // Pokud je vybrána možnost "Custom", načtěte vlastní hodnotu; jinak použij vybranou hodnotu jako číslo
  let hlavniChod = (hlavniChodVal === "Custom") 
                      ? document.getElementById("custom-hlavni-chod").value.trim() 
                      : parseInt(hlavniChodVal, 10);
  let priloha = (prilohaVal === "Custom") 
                      ? document.getElementById("custom-priloha").value.trim() 
                      : parseInt(prilohaVal, 10);
  let omacka = (omackaVal === "Custom") 
                      ? document.getElementById("custom-omacka").value.trim() 
                      : parseInt(omackaVal, 10);

  // Sestavení jednotného payloadu (JSON objektu)
  let payload = {
    hlavniChod: hlavniChod,
    priloha: priloha,
    omacka: omacka,
    obloha: oblohaChecked,
    typjidla: typjidlaVal,
    casjidla: cas
  };

  console.log("Odesílaný payload:", payload);

  // Odeslání dat na backend pomocí jediného fetch volání -> voláme endpoint "novaData"
  fetch("backend.php?action=novaData", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload)
  })
  .then(response => response.json())
  .then(respData => {
    console.log("Odpověď z backendu:", respData);
    if (respData.success) {
      alert("Jídlo bylo úspěšně uloženo!");
      // Můžeš reloadnout stránku nebo aktualizovat UI dynamicky
      location.reload();
    } else {
      alert("Chyba při ukládání jídla: " + (respData.error || "Neznámá chyba"));
    }
  })
  .catch(error => {
    console.error("Chyba při odesílání dat:", error);
    alert("Chyba: " + error.message);
  });
});


// Smazání tabulky – ukázkový kód.
document.getElementById("smazat-tabulku").addEventListener("click", function() {
  document.getElementById("ulozena-jidla").getElementsByTagName("tbody")[0].innerHTML = "";
});

function urcitTypJidla() {
  var currentTime = new Date();
  var currentHour = currentTime.getHours();
  if (currentHour >= 6 && currentHour < 11) {
    return 1;
  } else if (currentHour >= 11 && currentHour < 15) {
    return 2;
  } else {
    return 3;
  }
}

// Dynamické naplnění selectů z backendu
fetch("backend.php?action=getAllMeals")
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
