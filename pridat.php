<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Přidat nové možnosti</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 400px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input {
            width: calc(100% - 20px);
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        
        }
        button:hover {
            background-color: #0056b3;
        
        }
        .buttonb { background-color: #007bff; color: #fff; border: none; padding: 8px 12px; border-radius: 3px; cursor: pointer; margin-right: 40%; max-width: 20%;,margin-left: 40%;}
    .buttonb:hover { background-color: #0056b3; }
    </style>
</head>
<body>
    <?php
    session_start();
 if (!isset($_SESSION['user'])){
  
    header("Location: prihlaseni.php");
  exit;
}
?>
<button class="buttonb" onclick="window.location.href='jidelnicek.php'">Zpět na jídelníček</button>
  
    <div class="container">
        <h1>Přidat Možnosti</h1>

        <!-- Přidat hlavní část jídla -->
        <div class="form-group">
            <label for="hlavniCast">Nový hlavní chod</label>
            <input type="text" id="hlavniCast" placeholder="Zadejte nový hlavní chod">
            <button id="odeslatHlavniCast">Přidat Hlavní Chod</button>
        </div>

        <!-- Přidat přílohu -->
        <div class="form-group">
            <label for="priloha">Nová příloha</label>
            <input type="text" id="priloha" placeholder="Zadejte novou přílohu">
            <button id="odeslatPriloha">Přidat Přílohu</button>
        </div>

        <!-- Přidat omáčku -->
        <div class="form-group">
            <label for="omacka">Nová omáčka</label>
            <input type="text" id="omacka" placeholder="Zadejte novou omáčku">
            <button id="odeslatOmacka">Přidat Omáčku</button>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Přidání hlavní části jídla
            document.getElementById("odeslatHlavniCast").addEventListener("click", function () {
                const hlavniCast = document.getElementById("hlavniCast").value.trim();
                if (hlavniCast) {
                    fetch("backend.php?action=pridatHlavniCast", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({ nazev: hlavniCast })
                    })
                    .then(response => response.json())
                    .then(data => alert(data.message || "Hlavní chod byl přidán!"))
                    .catch(err => alert("Chyba při přidávání hlavního chodu: " + err.message));
                } else {
                    alert("Vyplňte název hlavního chodu!");
                }
            });

            // Přidání přílohy
            document.getElementById("odeslatPriloha").addEventListener("click", function () {
                const priloha = document.getElementById("priloha").value.trim();
                if (priloha) {
                    fetch("backend.php?action=pridatPriloha", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({ nazev: priloha })
                    })
                    .then(response => response.json())
                    .then(data => alert(data.message || "Příloha byla přidána!"))
                    .catch(err => alert("Chyba při přidávání přílohy: " + err.message));
                } else {
                    alert("Vyplňte název přílohy!");
                }
            });

            // Přidání omáčky
            document.getElementById("odeslatOmacka").addEventListener("click", function () {
                const omacka = document.getElementById("omacka").value.trim();
                if (omacka) {
                    fetch("backend.php?action=pridatOmacka", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({ nazev: omacka })
                    })
                    .then(response => response.json())
                    .then(data => alert(data.message || "Omáčka byla přidána!"))
                    .catch(err => alert("Chyba při přidávání omáčky: " + err.message));
                } else {
                    alert("Vyplňte název omáčky!");
                }
            });
        });
    </script>
</body>
</html>
